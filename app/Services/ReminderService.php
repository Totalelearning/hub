<?php

namespace App\Services;

use App\Models\AssignmentAuditEvent;
use App\Models\AssignmentReminder;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Notifications\AssignmentReminderNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReminderService
{
    public function __construct(
        private readonly AssignmentService $assignments,
        private readonly AssignmentSettingsService $settings,
    ) {
    }

    public function syncPending(): Collection
    {
        if (! Schema::hasTable('assignment_reminders')) {
            return collect();
        }

        $candidates = $this->pendingCandidates();
        return $candidates->map(function (array $candidate) {
            $cooldownDays = $this->reminderCooldownDays((string) $candidate['reminder_type']);

            if ($cooldownDays > 0 && AssignmentReminder::query()
                ->where('user_id', $candidate['user_id'])
                ->where('learning_module_id', $candidate['learning_module_id'])
                ->where('reminder_type', $candidate['reminder_type'])
                ->where('created_at', '>=', now()->subDays($cooldownDays))
                ->exists()
            ) {
                return null;
            }

            return AssignmentReminder::query()->updateOrCreate(
                [
                    'user_id' => $candidate['user_id'],
                    'learning_module_id' => $candidate['learning_module_id'],
                    'reminder_type' => $candidate['reminder_type'],
                    'due_on' => $candidate['due_on'],
                ],
                [
                    'status' => 'pending',
                    'sent_at' => null,
                ],
            );
        })->filter()->values();
    }

    public function pendingQueue(): Collection
    {
        if (! Schema::hasTable('assignment_reminders')) {
            return collect();
        }

        return AssignmentReminder::query()
            ->with(['user.preference', 'module'])
            ->where('status', 'pending')
            ->orderBy('due_on')
            ->orderBy('reminder_type')
            ->get();
    }

    public function markSent(AssignmentReminder $reminder): AssignmentReminder
    {
        if (Schema::hasTable('notifications') && $reminder->user) {
            $reminder->user->notify(new AssignmentReminderNotification($reminder->loadMissing('module')));
        }

        $reminder->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        if (Schema::hasTable('assignment_audit_events')) {
            AssignmentAuditEvent::query()->create([
                'actor_user_id' => auth()->id(),
                'target_user_id' => $reminder->user_id,
                'learning_module_id' => $reminder->learning_module_id,
                'entity_type' => 'assignment_reminder',
                'entity_id' => $reminder->id,
                'action' => 'reminder_marked_sent',
                'meta' => [
                    'reminder_type' => $reminder->reminder_type,
                    'due_on' => optional($reminder->due_on)?->toDateString(),
                    'learner_email' => $reminder->user?->email,
                    'module_title' => $reminder->module?->title,
                ],
            ]);
        }

        return $reminder->fresh(['user.preference', 'module']);
    }

    public function pendingCandidates(): Collection
    {
        $users = User::query()
            ->with('preference')
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => filled($user->preference?->role))
            ->values();

        $requiredModules = LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true)
            ->orderBy('title')
            ->get();

        $progressByUserAndModule = ModuleProgress::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('learning_module_id', $requiredModules->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $rows) => $rows->keyBy('learning_module_id'));

        return $users
            ->flatMap(function (User $user) use ($requiredModules, $progressByUserAndModule) {
                $userProgress = $progressByUserAndModule->get($user->id, collect());

                return $requiredModules->map(function (LearningModule $module) use ($user, $userProgress) {
                    $progress = $userProgress->get($module->id);
                    $assignment = $this->assignments->forUser($user, $module, $progress);

                    if (! $assignment['is_required']) {
                        return null;
                    }

                    $reminderType = match (true) {
                        $assignment['is_overdue'] => 'overdue',
                        $assignment['is_due_soon'] => 'due_soon',
                        default => null,
                    };

                    if ($reminderType === null) {
                        $reminderType = $this->classifyNudgeType($module, $progress, $assignment);
                    }

                    if ($reminderType === null) {
                        return null;
                    }

                    return [
                        'user_id' => $user->id,
                        'learning_module_id' => $module->id,
                        'reminder_type' => $reminderType,
                        'due_on' => ($assignment['renewal']['due_at'] ?? now())->toDateString(),
                    ];
                })->filter()->values();
            })
            ->values();
    }

    public function classifyNudgeType(LearningModule $module, ?ModuleProgress $progress, array $assignment): ?string
    {
        if (
            ! ($assignment['is_incomplete_required'] ?? false)
            || (($assignment['urgency'] ?? null) !== 'required')
        ) {
            return null;
        }

        if ($this->isInactiveNudgeCandidate($progress)) {
            return 'inactive_nudge';
        }

        if ($this->isNotStartedNudgeCandidate($module, $progress)) {
            return 'not_started_nudge';
        }

        return null;
    }

    public function isInactiveNudgeCandidate(?ModuleProgress $progress): bool
    {
        $inactiveAfterDays = max(0, (int) config('learning_assignments.inactive_nudge_after_days', 7));
        $inactiveAfterDays = max(0, $this->settings->value('inactive_nudge_after_days', $inactiveAfterDays));
        if ($inactiveAfterDays <= 0) {
            return false;
        }

        return $progress !== null
            && $progress->status !== 'completed'
            && $progress->status !== 'not_started'
            && $progress->last_activity_at !== null
            && $progress->last_activity_at->lte(now()->subDays($inactiveAfterDays));
    }

    public function isNotStartedNudgeCandidate(LearningModule $module, ?ModuleProgress $progress): bool
    {
        $notStartedAfterDays = max(0, (int) config('learning_assignments.not_started_nudge_after_days', 10));
        $notStartedAfterDays = max(0, $this->settings->value('not_started_nudge_after_days', $notStartedAfterDays));
        if ($notStartedAfterDays <= 0) {
            return false;
        }

        if ($progress !== null && ! in_array($progress->status, ['not_started'], true)) {
            return false;
        }

        $openSince = $module->available_from ?? $module->created_at ?? now();
        if ($module->available_from !== null && $module->available_from->isFuture()) {
            return false;
        }

        return $openSince->lte(now()->subDays($notStartedAfterDays));
    }

    private function reminderCooldownDays(string $reminderType): int
    {
        return match ($reminderType) {
            'inactive_nudge' => max(0, $this->settings->value(
                'inactive_nudge_cooldown_days',
                (int) config('learning_assignments.inactive_nudge_cooldown_days', 3),
            )),
            'not_started_nudge' => max(0, $this->settings->value(
                'not_started_nudge_cooldown_days',
                (int) config('learning_assignments.not_started_nudge_cooldown_days', 5),
            )),
            default => 0,
        };
    }
}
