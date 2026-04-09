<?php

namespace App\Http\Controllers;

use App\Models\AssignmentReminder;
use App\Models\Course;
use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\SavedLearningModule;
use App\Services\AssignmentService;
use App\Services\FeedRankingService;
use App\Services\LearningPathService;
use App\Support\ScormRuntimeMetrics;
use Illuminate\View\View;

class AppModuleController extends Controller
{
    public function show(LearningModule $module): View
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $module->loadMissing('prerequisites:id,title');

        $progress = $user->moduleProgress()
            ->where('learning_module_id', $module->id)
            ->first();

        $progressByModuleId = $user->moduleProgress()
            ->where('learning_module_id', '!=', $module->id)
            ->get()
            ->keyBy('learning_module_id');

        $ranker = app(FeedRankingService::class);
        $assignments = app(AssignmentService::class);
        abort_unless($ranker->isVisibleToUser($user, $module), 404);

        $ranking = $ranker->rank($user, $module, $progress);
        $score = $ranking['result'];
        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'topic' => $module->topic,
            ],
        ]);

        $latestScormRuntime = null;
        $scormActivitySummary = null;
        $moduleReminderSummary = null;
        $recentModuleEvents = collect();
        $completionNextActions = collect();

        if ($module->source_type === 'scorm') {
            $scormLaunchEvents = LearningEvent::query()
                ->where('user_id', $user->id)
                ->where('event_type', 'scorm_launched')
                ->where('entity_type', 'learning_module')
                ->where('entity_id', $module->id);

            $scormRuntimeEvents = LearningEvent::query()
                ->where('user_id', $user->id)
                ->where('event_type', 'scorm_runtime_committed')
                ->where('entity_type', 'learning_module')
                ->where('entity_id', $module->id);

            $latestScormLaunch = (clone $scormLaunchEvents)->latest('id')->first();
            $latestScormEvent = LearningEvent::query()
                ->where('user_id', $user->id)
                ->where('event_type', 'scorm_runtime_committed')
                ->where('entity_type', 'learning_module')
                ->where('entity_id', $module->id)
                ->latest('id')
                ->first();

            if ($latestScormEvent) {
                $sessionSeconds = isset($latestScormEvent->metadata['session_seconds'])
                    ? (int) $latestScormEvent->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($latestScormEvent->metadata['session_time'] ?? null);
                $runtimeStatus = $latestScormEvent->metadata['status']
                    ?? $latestScormEvent->metadata['completion_status']
                    ?? $latestScormEvent->metadata['lesson_status']
                    ?? ($progress?->status ?? 'not_started');

                $latestScormRuntime = [
                    'status' => $runtimeStatus,
                    'status_label' => ucfirst(str_replace('_', ' ', (string) $runtimeStatus)),
                    'is_completed' => in_array(strtolower((string) $runtimeStatus), ['completed', 'passed'], true)
                        || (int) ($latestScormEvent->metadata['percent_complete'] ?? ($progress?->percent_complete ?? 0)) >= 100,
                    'score_raw' => $latestScormEvent->metadata['score_raw'] ?? null,
                    'session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                    'percent_complete' => $latestScormEvent->metadata['percent_complete'] ?? ($progress?->percent_complete ?? 0),
                    'lesson_location' => $latestScormEvent->metadata['lesson_location'] ?? null,
                    'recorded_at' => $latestScormEvent->created_at,
                    'completed_at' => $progress?->completed_at,
                ];
            }

            $scormActivitySummary = [
                'launch_count' => (clone $scormLaunchEvents)->count(),
                'attempt_count' => (clone $scormRuntimeEvents)->count(),
                'latest_launch_at' => $latestScormLaunch?->created_at,
            ];
        }

        $moduleReminders = AssignmentReminder::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->id)
            ->latest('id')
            ->limit(5)
            ->get();

        $isSaved = SavedLearningModule::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->id)
            ->exists();

        $moduleReminderSummary = [
            'pending_count' => $moduleReminders->where('status', 'pending')->count(),
            'sent_count' => $moduleReminders->where('status', 'sent')->count(),
            'latest_due_on' => $moduleReminders->pluck('due_on')->filter()->sort()->first(),
            'latest_sent_at' => $moduleReminders->pluck('sent_at')->filter()->sortDesc()->first(),
        ];

        $recentModuleEvents = LearningEvent::query()
            ->where('user_id', $user->id)
            ->where('entity_type', 'learning_module')
            ->where('entity_id', $module->id)
            ->whereIn('event_type', ['module_viewed', 'module_saved', 'scorm_launched', 'scorm_runtime_committed'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (LearningEvent $event) {
                return [
                    'event_type' => $event->event_type,
                    'occurred_at' => $event->created_at,
                    'summary' => match ($event->event_type) {
                        'scorm_runtime_committed' => sprintf(
                            'Runtime saved with status %s%s.',
                            (string) ($event->metadata['status'] ?? $event->metadata['lesson_status'] ?? $event->metadata['completion_status'] ?? 'updated'),
                            isset($event->metadata['score_raw']) ? ' and score '.(string) $event->metadata['score_raw'] : ''
                        ),
                        'scorm_launched' => 'SCORM player opened for this module.',
                        'module_saved' => 'Saved to your shortlist.',
                        default => 'Viewed in the learner workspace.',
                    },
                ];
            })
            ->values();

        $latestModuleEvent = $recentModuleEvents->first();
        $moduleActionSummary = [
            'risk_label' => ($score['renewal']['is_due'] ?? false)
                ? 'Needs attention now'
                : (($score['renewal']['is_due_soon'] ?? false) ? 'Due soon' : 'On track'),
            'last_activity_at' => $latestModuleEvent['occurred_at'] ?? null,
            'last_activity_summary' => $latestModuleEvent['summary'] ?? 'No learner activity has been recorded for this module yet.',
            'next_action_label' => $module->source_type === 'scorm' && $module->latestScormAsset()
                ? 'Launch SCORM prototype'
                : (($progress?->status ?? 'not_started') === 'in_progress' ? 'Continue module' : 'Start module'),
            'next_action_href' => $module->source_type === 'scorm' && $module->latestScormAsset()
                ? route('app.modules.scorm.launch', ['module' => $module->id])
                : '#module-summary',
            'reminder_href' => route('app.reminders', ['module_id' => $module->id]),
            'reminder_summary' => ($moduleReminderSummary['pending_count'] ?? 0) > 0
                ? 'You have pending reminder activity for this module.'
                : (! empty($moduleReminderSummary['latest_due_on'])
                    ? 'A reminder due date is already scheduled for this module.'
                    : 'No pending reminder activity is recorded yet.'),
        ];

        $relatedModules = LearningModule::query()
            ->where('status', 'published')
            ->whereKeyNot($module->id)
            ->latest()
            ->limit(12)
            ->get()
            ->filter(fn (LearningModule $candidate) => $ranker->isVisibleToUser($user, $candidate))
            ->map(function (LearningModule $candidate) use ($progressByModuleId, $ranker, $assignments, $user) {
                $candidateProgress = $progressByModuleId->get($candidate->id);
                $candidateRanking = $ranker->rank($user, $candidate, $candidateProgress);
                $candidateAssignment = $assignments->forUser($user, $candidate, $candidateProgress);
                $candidate->setAttribute('feed_score', (int) ($candidateRanking['result']['score'] ?? 0));
                $candidate->setAttribute('user_progress_status', $candidateProgress?->status ?? 'not_started');
                $candidate->setAttribute('user_progress_percent', (int) ($candidateProgress?->percent_complete ?? 0));
                $candidate->setAttribute('assignment', $candidateAssignment);

                return $candidate;
            })
            ->sortByDesc(fn (LearningModule $candidate) => (int) ($candidate->feed_score ?? 0))
            ->take(2)
            ->values();

        if (($progress?->status ?? 'not_started') === 'completed') {
            $pathService = app(LearningPathService::class);
            $pathNextStep = $pathService->visiblePathsForUser($user)
                ->map(function ($path) use ($pathService, $user) {
                    $stepStates = $pathService->stepStates($user, $path);
                    $nextStep = $stepStates->first(fn (array $state) => ! $state['is_completed'] && $state['is_unlocked']);

                    return [
                        'path' => $path,
                        'next_step' => $nextStep,
                    ];
                })
                ->first(function (array $row) use ($module) {
                    return ($row['next_step']['module']->id ?? null) !== null
                        && (int) ($row['next_step']['module']->id ?? 0) !== (int) $module->id;
                });

            if (($pathNextStep['next_step']['module'] ?? null) instanceof LearningModule) {
                $completionNextActions->push([
                    'label' => 'Continue path',
                    'title' => $pathNextStep['next_step']['module']->title,
                    'summary' => 'This is the next unlocked step in your learning path after completing this module.',
                    'href' => route('app.modules.show', ['module' => $pathNextStep['next_step']['module']->id]),
                    'cta' => 'Open next path step',
                    'tone' => 'primary',
                ]);
            }

            $nextRequiredModule = $relatedModules
                ->first(fn (LearningModule $candidate) => ($candidate->assignment['is_required'] ?? false) && ($candidate->user_progress_status ?? 'not_started') !== 'completed');

            if ($nextRequiredModule) {
                $completionNextActions->push([
                    'label' => 'Required next',
                    'title' => $nextRequiredModule->title,
                    'summary' => 'This required module is still incomplete and is the strongest next compliance action.',
                    'href' => route('app.modules.show', ['module' => $nextRequiredModule->id]),
                    'cta' => 'Open required module',
                    'tone' => 'danger',
                ]);
            }

            $nextRecommendedModule = $relatedModules
                ->first(fn (LearningModule $candidate) => ($candidate->user_progress_status ?? 'not_started') !== 'completed');

            if ($nextRecommendedModule) {
                $completionNextActions->push([
                    'label' => 'Recommended next',
                    'title' => $nextRecommendedModule->title,
                    'summary' => 'Keep momentum going with the next visible module ranked for you.',
                    'href' => route('app.modules.show', ['module' => $nextRecommendedModule->id]),
                    'cta' => 'Open recommended module',
                    'tone' => 'info',
                ]);
            }

            $completionNextActions->push([
                'label' => 'Return to dashboard',
                'title' => 'Learner dashboard',
                'summary' => 'Go back to your live dashboard to see updated reminders, priorities, and saved learning.',
                'href' => route('app.feed'),
                'cta' => 'Back to dashboard',
                'tone' => 'neutral',
            ]);

            $completionNextActions = $completionNextActions
                ->unique(fn (array $action) => $action['href'])
                ->take(3)
                ->values();
        }

        // Course context — if the user arrived from a course page
        $courseContext = null;
        $courseId = (int) request()->query('course', 0);
        if ($courseId > 0) {
            $courseCandidate = Course::with(['modules' => fn ($q) => $q->orderBy('course_module.sort_order')])
                ->where('status', 'published')
                ->find($courseId);

            if ($courseCandidate && $courseCandidate->modules->contains('id', $module->id)) {
                $courseModules = $courseCandidate->modules;
                $currentIndex = $courseModules->search(fn ($m) => $m->id === $module->id);
                $nextInCourse = ($currentIndex !== false && $currentIndex < $courseModules->count() - 1)
                    ? $courseModules[$currentIndex + 1]
                    : null;
                $prevInCourse = ($currentIndex !== false && $currentIndex > 0)
                    ? $courseModules[$currentIndex - 1]
                    : null;

                // Check if this is the last module and all others are completed
                $isLastModule = $nextInCourse === null;
                $allOthersComplete = false;
                if ($isLastModule) {
                    $otherModuleIds = $courseModules->where('id', '!=', $module->id)->pluck('id');
                    $completedOthers = ModuleProgress::where('user_id', $user->id)
                        ->whereIn('learning_module_id', $otherModuleIds)
                        ->where('status', 'completed')
                        ->count();
                    $allOthersComplete = $completedOthers >= $otherModuleIds->count();
                }

                $courseContext = [
                    'course' => $courseCandidate,
                    'current_index' => $currentIndex,
                    'total_modules' => $courseModules->count(),
                    'next_module' => $nextInCourse,
                    'prev_module' => $prevInCourse,
                    'is_last_module' => $isLastModule,
                    'all_others_complete' => $allOthersComplete,
                ];

                // Inject "Next in course" as the top completion action
                if ($nextInCourse && ($progress?->status ?? 'not_started') === 'completed') {
                    $completionNextActions->prepend([
                        'label' => 'Next in course',
                        'title' => $nextInCourse->title,
                        'summary' => 'Continue with the next module in ' . $courseCandidate->title . '.',
                        'href' => route('app.modules.show', ['module' => $nextInCourse->id, 'course' => $courseCandidate->id]),
                        'cta' => 'Continue course',
                        'tone' => 'primary',
                    ]);
                    $completionNextActions = $completionNextActions
                        ->unique(fn (array $action) => $action['href'])
                        ->take(3)
                        ->values();
                }

                // If last module completed, inject "Back to course" action
                if ($isLastModule && ($progress?->status ?? 'not_started') === 'completed') {
                    $completionNextActions->prepend([
                        'label' => $allOthersComplete ? 'Course complete!' : 'Back to course',
                        'title' => $courseCandidate->title,
                        'summary' => $allOthersComplete
                            ? 'You\'ve finished every module in this course. View your completion summary.'
                            : 'Return to the course page to see your progress.',
                        'href' => route('app.courses.show', $courseCandidate),
                        'cta' => $allOthersComplete ? 'View course result' : 'Back to course',
                        'tone' => $allOthersComplete ? 'primary' : 'neutral',
                    ]);
                    $completionNextActions = $completionNextActions
                        ->unique(fn (array $action) => $action['href'])
                        ->take(3)
                        ->values();
                }
            }
        }

        return view('app.module-show', [
            'module' => $module,
            'progress' => $progress,
            'latestScormAsset' => $module->latestScormAsset(),
            'latestScormRuntime' => $latestScormRuntime,
            'scormActivitySummary' => $scormActivitySummary,
            'preference' => $user->preference,
            'score' => $score,
            'renewal' => $score['renewal'] ?? [],
            'roleTargeting' => $score['role_targeting'] ?? [],
            'complianceTargeting' => $score['compliance_targeting'] ?? [],
            'prerequisites' => $score['prerequisites'] ?? [],
            'acknowledgement' => $score['acknowledgement'] ?? [],
            'assignment' => $score['assignment'] ?? [],
            'rankingMeta' => $ranking['meta'] ?? [],
            'isSaved' => $isSaved,
            'moduleReminderSummary' => $moduleReminderSummary,
            'moduleActionSummary' => $moduleActionSummary,
            'recentModuleEvents' => $recentModuleEvents,
            'relatedModules' => $relatedModules,
            'completionNextActions' => $completionNextActions,
            'courseContext' => $courseContext,
        ]);
    }
}
