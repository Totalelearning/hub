<?php

namespace App\Services;

use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\AssignmentReminder;
use App\Models\ReinforcementQuestionSet;
use App\Models\ReinforcementResponse;
use App\Models\ReinforcementTouchpoint;
use App\Models\User;
use App\Notifications\AssignmentReminderNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReinforcementService
{
    /**
     * @return list<int>
     */
    private function intervals(LearningModule $module): array
    {
        $intervals = collect($module->reinforcement_intervals_days ?? [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $days) => $days > 0 && $days <= 3650)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $intervals !== [] ? $intervals : [7, 30];
    }

    public function syncForUser(User $user): Collection
    {
        if (! Schema::hasTable('reinforcement_touchpoints')) {
            return collect();
        }

        $completedProgress = ModuleProgress::query()
            ->with('module:id,title,source_type,reinforcement_intervals_days')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get();

        foreach ($completedProgress as $progress) {
            /** @var ModuleProgress $progress */
            $module = $progress->module;
            if (! $module) {
                continue;
            }

            $approvedQuestionSet = $this->approvedQuestionSet($module);

            foreach ($this->intervals($module) as $intervalDays) {
                $touchpointKey = sprintf('u%s-m%s-d%s', $user->id, $progress->learning_module_id, $intervalDays);
                $dueOn = $progress->completed_at->copy()->addDays($intervalDays)->startOfDay();

                ReinforcementTouchpoint::query()->firstOrCreate(
                    ['touchpoint_key' => $touchpointKey],
                    [
                        'user_id' => $user->id,
                        'learning_module_id' => $progress->learning_module_id,
                        'module_progress_id' => $progress->id,
                        'reinforcement_question_set_id' => $approvedQuestionSet?->id,
                        'interval_days' => $intervalDays,
                        'title' => $module->title.' reinforcement',
                        'prompt' => $this->promptForInterval($module, $intervalDays, $approvedQuestionSet),
                        'proof_type' => 'knowledge_check',
                        'due_on' => $dueOn,
                        'status' => $dueOn->isPast() ? 'due' : 'pending',
                        'metadata' => [
                            'module_title' => $module->title,
                            'source_type' => $module->source_type,
                            'interval_label' => $intervalDays.'-day reinforcement',
                            'question_set_id' => $approvedQuestionSet?->id,
                            'question_set_status' => $approvedQuestionSet?->status,
                            'question_count' => $approvedQuestionSet?->questions()->count() ?? 0,
                        ],
                    ]
                );
            }
        }

        return $this->touchpointsForUser($user);
    }

    public function touchpointsForUser(User $user): Collection
    {
        if (! Schema::hasTable('reinforcement_touchpoints')) {
            return collect();
        }

        return ReinforcementTouchpoint::query()
            ->with('module:id,title,source_type')
            ->where('user_id', $user->id)
            ->orderByRaw("CASE WHEN status = 'due' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('due_on')
            ->get()
            ->map(function (ReinforcementTouchpoint $touchpoint) {
                $status = $touchpoint->status;
                if ($status !== 'completed' && $touchpoint->due_on && $touchpoint->due_on->isPast()) {
                    $status = 'due';
                }

                $touchpoint->setAttribute('computed_status', $status);

                return $touchpoint;
            })
            ->values();
    }

    public function summaryForUser(User $user): array
    {
        $touchpoints = $this->syncForUser($user);

        return [
            'total' => $touchpoints->count(),
            'due' => $touchpoints->where('computed_status', 'due')->count(),
            'pending' => $touchpoints->where('computed_status', 'pending')->count(),
            'completed' => $touchpoints->where('computed_status', 'completed')->count(),
            'latest_completed' => $touchpoints
                ->where('computed_status', 'completed')
                ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => optional($touchpoint->completed_at)->getTimestamp() ?? 0)
                ->first(),
        ];
    }

    public function completeForUser(ReinforcementTouchpoint $touchpoint, User $user): ReinforcementTouchpoint
    {
        abort_unless(Schema::hasTable('reinforcement_touchpoints'), 404);
        abort_unless($touchpoint->user_id === $user->id, 403);

        $module = $touchpoint->module ?? LearningModule::query()->find($touchpoint->learning_module_id);
        $proofSummary = sprintf(
            'Completed %s follow-up for %s.',
            $touchpoint->interval_days.'-day',
            $module?->title ?? ('Module #'.$touchpoint->learning_module_id)
        );

        $touchpoint->update([
            'status' => 'completed',
            'completed_at' => now(),
            'proof_summary' => $proofSummary,
        ]);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'reinforcement_completed',
            'entity_type' => 'reinforcement_touchpoint',
            'entity_id' => $touchpoint->id,
            'metadata' => [
                'module_id' => $touchpoint->learning_module_id,
                'module_title' => $module?->title,
                'interval_days' => $touchpoint->interval_days,
                'due_on' => optional($touchpoint->due_on)->toDateString(),
                'proof_type' => $touchpoint->proof_type,
                'proof_summary' => $proofSummary,
                'source_type' => $module?->source_type,
            ],
        ]);

        return $touchpoint->fresh(['module']);
    }

    public function submitAnswers(ReinforcementTouchpoint $touchpoint, User $user, array $answers): array
    {
        abort_unless(Schema::hasTable('reinforcement_touchpoints'), 404);
        abort_unless($touchpoint->user_id === $user->id, 403);

        $touchpoint->loadMissing(['module', 'reinforcementQuestionSet.questions', 'responses']);

        $questionSet = $touchpoint->reinforcementQuestionSet;
        abort_unless($questionSet !== null, 404);

        $questions = $questionSet->questions;
        abort_unless($questions->isNotEmpty(), 422);

        $results = $questions->map(function ($question) use ($touchpoint, $user, $answers) {
            $selected = strtoupper((string) ($answers[$question->id] ?? ''));
            $isCorrect = $selected !== '' && $selected === strtoupper((string) $question->correct_answer);

            ReinforcementResponse::query()->updateOrCreate(
                [
                    'reinforcement_touchpoint_id' => $touchpoint->id,
                    'reinforcement_question_id' => $question->id,
                    'user_id' => $user->id,
                ],
                [
                    'selected_answer' => $selected !== '' ? $selected : null,
                    'is_correct' => $isCorrect,
                    'answered_at' => now(),
                    'metadata' => [
                        'correct_answer' => $question->correct_answer,
                        'question_text' => $question->question_text,
                    ],
                ]
            );

            return [
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
                'selected_answer' => $selected,
                'remediation_learning_module_id' => $question->remediation_learning_module_id,
            ];
        })->values();

        $incorrect = $results->where('is_correct', false)->values();
        $module = $touchpoint->module ?? LearningModule::query()->find($touchpoint->learning_module_id);

        if ($incorrect->isEmpty()) {
            $completed = $this->completeForUser($touchpoint, $user);

            return [
                'status' => 'completed',
                'touchpoint' => $completed,
                'incorrect_count' => 0,
                'remediation_module_ids' => [],
            ];
        }

        $remediationModuleIds = $incorrect
            ->pluck('remediation_learning_module_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        foreach ($remediationModuleIds as $moduleId) {
            $reminder = AssignmentReminder::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'learning_module_id' => $moduleId,
                    'reminder_type' => 'reinforcement_follow_up',
                    'due_on' => now()->toDateString(),
                ],
                [
                    'status' => 'pending',
                ]
            );

            if ($reminder->wasRecentlyCreated) {
                $user->notify(new AssignmentReminderNotification($reminder->fresh('module')));
            }
        }

        $remediationTitles = LearningModule::query()
            ->whereIn('id', $remediationModuleIds->all())
            ->pluck('title')
            ->values();

        $proofSummary = sprintf(
            'Incorrect reinforcement answer%s recorded for %s. %d remediation follow-up module%s assigned%s.',
            $incorrect->count() === 1 ? '' : 's',
            $module?->title ?? ('Module #'.$touchpoint->learning_module_id),
            $remediationModuleIds->count(),
            $remediationModuleIds->count() === 1 ? '' : 's',
            $remediationTitles->isNotEmpty() ? ': '.$remediationTitles->join(', ') : ''
        );

        $metadata = $touchpoint->metadata ?? [];
        $metadata['last_incorrect_count'] = $incorrect->count();
        $metadata['last_remediation_module_ids'] = $remediationModuleIds->all();

        $touchpoint->update([
            'status' => 'needs_retry',
            'proof_summary' => $proofSummary,
            'metadata' => $metadata,
        ]);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'reinforcement_failed',
            'entity_type' => 'reinforcement_touchpoint',
            'entity_id' => $touchpoint->id,
            'metadata' => [
                'module_id' => $touchpoint->learning_module_id,
                'module_title' => $module?->title,
                'interval_days' => $touchpoint->interval_days,
                'incorrect_count' => $incorrect->count(),
                'remediation_module_ids' => $remediationModuleIds->all(),
                'proof_type' => $touchpoint->proof_type,
                'source_type' => $module?->source_type,
            ],
        ]);

        return [
            'status' => 'needs_retry',
            'touchpoint' => $touchpoint->fresh(['module', 'reinforcementQuestionSet.questions', 'responses']),
            'incorrect_count' => $incorrect->count(),
            'remediation_module_ids' => $remediationModuleIds->all(),
        ];
    }

    private function promptForInterval(LearningModule $module, int $intervalDays, ?ReinforcementQuestionSet $approvedQuestionSet = null): string
    {
        if ($approvedQuestionSet) {
            $questionCount = $approvedQuestionSet->questions()->count();

            return match ($intervalDays) {
                7 => sprintf('Seven-day check-in: answer %d reviewed reinforcement question%s for %s.', $questionCount, $questionCount === 1 ? '' : 's', $module->title),
                30 => sprintf('Thirty-day reinforcement: complete %d reviewed reinforcement question%s for %s.', $questionCount, $questionCount === 1 ? '' : 's', $module->title),
                default => sprintf('Complete the approved reinforcement question set for %s.', $module->title),
            };
        }

        return match ($intervalDays) {
            7 => 'Seven-day check-in: confirm the key points from '.$module->title.' still feel clear in practice.',
            30 => 'Thirty-day reinforcement: review the main risk signals and show you still remember the right response from '.$module->title.'.',
            default => 'Reinforcement follow-up for '.$module->title.'.',
        };
    }

    private function approvedQuestionSet(LearningModule $module): ?ReinforcementQuestionSet
    {
        if (! Schema::hasTable('reinforcement_question_sets')) {
            return null;
        }

        return ReinforcementQuestionSet::query()
            ->with('questions')
            ->where('learning_module_id', $module->id)
            ->where('status', 'approved')
            ->latest('reviewed_at')
            ->latest('id')
            ->first();
    }
}
