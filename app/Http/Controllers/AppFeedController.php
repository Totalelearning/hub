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
use App\Services\GamificationService;
use App\Services\ReinforcementService;
use Illuminate\View\View;

class AppFeedController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $modules = LearningModule::query()
            ->where('status', 'published')
            ->with('prerequisites:id,title')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $savedModuleIds = [];
        $userId = $user->id;
        $isLocal = app()->environment('local');

        $savedModuleIds = SavedLearningModule::query()
            ->where('user_id', $userId)
            ->pluck('learning_module_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $savedAtByModuleId = SavedLearningModule::query()
            ->where('user_id', $userId)
            ->get(['learning_module_id', 'created_at'])
            ->keyBy(fn ($row) => (int) $row->learning_module_id);

        $progressByModuleId = ModuleProgress::query()
            ->where('user_id', $userId)
            ->whereIn('learning_module_id', $modules->pluck('id'))
            ->get()
            ->keyBy('learning_module_id');

        $ranker = app(FeedRankingService::class);
        $assignments = app(AssignmentService::class);
        $paths = app(LearningPathService::class);
        $reinforcement = app(ReinforcementService::class);

        $modules = $modules
            ->filter(fn (LearningModule $module) => $ranker->isVisibleToUser($user, $module))
            ->map(function (LearningModule $module) use ($progressByModuleId, $ranker, $assignments, $user, $isLocal, $savedModuleIds, $savedAtByModuleId) {
                $progress = $progressByModuleId->get($module->id);
                $assignment = $assignments->forUser($user, $module, $progress);
                $ranking = $ranker->rank($user, $module, $progress);
                $result = $ranking['result'];

                $module->setAttribute('feed_score', (int) $result['score']);
                $module->setAttribute('user_progress_status', $progress?->status ?? 'not_started');
                $module->setAttribute('user_progress_percent', (int) ($progress?->percent_complete ?? 0));
                $module->setAttribute('renewal', $result['renewal']);
                $module->setAttribute('role_targeting', $result['role_targeting']);
                $module->setAttribute('compliance_targeting', $result['compliance_targeting']);
                $module->setAttribute('prerequisites_state', $result['prerequisites']);
                $module->setAttribute('assignment', $assignment);
                $module->setAttribute('feed_highlights', $result['highlights'] ?? []);
                $module->setAttribute('is_saved', in_array((int) $module->id, $savedModuleIds, true));
                $module->setAttribute('saved_at', $savedAtByModuleId->get((int) $module->id)?->created_at);

                if ($isLocal) {
                    $module->setAttribute('feed_score_breakdown', $result['breakdown']);
                    $module->setAttribute('feed_ranking_meta', $ranking['meta'] ?? []);
                }

                return $module;
            })
            ->sortByDesc(fn (LearningModule $module) => sprintf(
                '%010d-%010d',
                (int) $module->feed_score,
                optional($module->created_at)?->getTimestamp() ?? 0,
            ))
            ->values();

        $requiredModules = $modules
            ->filter(fn (LearningModule $module) => (bool) ($module->assignment['is_required'] ?? false))
            ->values();

        $recommendedModules = $modules
            ->reject(fn (LearningModule $module) => (bool) ($module->assignment['is_required'] ?? false))
            ->values();

        $inProgressModules = $modules
            ->filter(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') === 'in_progress')
            ->values();

        $completedModules = $modules
            ->filter(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') === 'completed')
            ->values();

        $savedModules = $modules
            ->filter(fn (LearningModule $module) => (bool) ($module->is_saved ?? false))
            ->sortByDesc(fn (LearningModule $module) => optional($module->saved_at)?->getTimestamp() ?? 0)
            ->values();

        $courseCategories = $modules
            ->groupBy(fn (LearningModule $module) => trim((string) ($module->topic ?: 'General')))
            ->map(function ($topicModules, string $topic) {
                return [
                    'topic' => $topic,
                    'count' => $topicModules->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(8)
            ->values();

        $savedFocusModule = $savedModules->first(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') === 'in_progress')
            ?? $savedModules->first(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') !== 'completed')
            ?? $savedModules->first();

        $savedFocusAction = $savedFocusModule
            ? [
                'module' => $savedFocusModule,
                'label' => ($savedFocusModule->user_progress_status ?? 'not_started') === 'in_progress' ? 'Resume saved module' : 'Open saved module',
                'summary' => ($savedFocusModule->user_progress_status ?? 'not_started') === 'in_progress'
                    ? 'Your saved shortlist includes a module already in progress.'
                    : 'Jump back into the module at the top of your saved shortlist.',
            ]
            : null;

        $activeModule = $inProgressModules->first()
            ?? $requiredModules->first(fn (LearningModule $module) => (bool) ($module->assignment['is_incomplete_required'] ?? false))
            ?? $modules->first();

        $secondarySpotlightModule = $recommendedModules->first()
            ?? $requiredModules->skip(1)->first()
            ?? $modules->skip(1)->first();

        $assignmentSummary = [
            'required_total' => $requiredModules->count(),
            'required_overdue' => $requiredModules->filter(fn (LearningModule $module) => (bool) ($module->assignment['is_overdue'] ?? false))->count(),
            'required_due_soon' => $requiredModules->filter(fn (LearningModule $module) => (bool) ($module->assignment['is_due_soon'] ?? false))->count(),
            'required_incomplete' => $requiredModules->filter(fn (LearningModule $module) => (bool) ($module->assignment['is_incomplete_required'] ?? false))->count(),
            'required_compliance_scoped' => $requiredModules->filter(fn (LearningModule $module) => (bool) ($module->compliance_targeting['is_targeted'] ?? false))->count(),
        ];

        $visibleProgressAverage = $modules->isNotEmpty()
            ? (int) round((float) ($modules->avg(fn (LearningModule $module) => (int) ($module->user_progress_percent ?? 0)) ?? 0))
            : 0;

        $completionRate = $modules->isNotEmpty()
            ? (int) round(($completedModules->count() / $modules->count()) * 100)
            : 0;

        $nextDueAt = $requiredModules
            ->pluck('renewal.due_at')
            ->filter()
            ->sort()
            ->first();

        $pendingReminders = AssignmentReminder::query()
            ->with('module:id,title,source_type')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('due_on')
            ->limit(5)
            ->get();

        $reinforcementTouchpoints = $reinforcement->syncForUser($user);
        $dueReinforcementTouchpoints = $reinforcementTouchpoints
            ->whereIn('computed_status', ['due', 'pending'])
            ->take(3)
            ->values();
        $reinforcementSummary = [
            'total' => $reinforcementTouchpoints->count(),
            'due' => $reinforcementTouchpoints->where('computed_status', 'due')->count(),
            'pending' => $reinforcementTouchpoints->where('computed_status', 'pending')->count(),
            'completed' => $reinforcementTouchpoints->where('computed_status', 'completed')->count(),
        ];

        $pathCollection = $paths->visiblePathsForUser($user)
            ->map(function ($path) use ($paths, $user) {
                $summary = $paths->progressSummary($user, $path);
                $stepStates = $paths->stepStates($user, $path);
                $nextStep = $stepStates->first(fn (array $state) => ! $state['is_completed'] && $state['is_unlocked']);

                $path->setAttribute('summary', $summary);
                $path->setAttribute('step_states', $stepStates);
                $path->setAttribute('next_step', $nextStep);

                return $path;
            })
            ->values();

        $activePath = $pathCollection
            ->first(fn ($path) => ($path->summary['completed_steps'] ?? 0) < ($path->summary['total_steps'] ?? 0))
            ?? $pathCollection->first();

        $priorityActions = collect();

        $overdueModule = $requiredModules->first(fn (LearningModule $module) => (bool) ($module->assignment['is_overdue'] ?? false));
        if ($overdueModule) {
            $priorityActions->push([
                'label' => 'Overdue required learning',
                'title' => $overdueModule->title,
                'summary' => 'This required module is overdue and should be reviewed first.',
                'href' => route('app.modules.show', ['module' => $overdueModule->id]),
                'cta' => 'Open overdue module',
                'tone' => 'danger',
            ]);
        }

        $dueSoonModule = $requiredModules->first(fn (LearningModule $module) => ! ($module->assignment['is_overdue'] ?? false) && (bool) ($module->assignment['is_due_soon'] ?? false));
        if ($dueSoonModule) {
            $priorityActions->push([
                'label' => 'Due soon',
                'title' => $dueSoonModule->title,
                'summary' => 'This required module is approaching its due date.',
                'href' => route('app.modules.show', ['module' => $dueSoonModule->id]),
                'cta' => 'Review due soon module',
                'tone' => 'warning',
            ]);
        }

        $nextPathModule = $activePath->next_step['module'] ?? null;
        if ($nextPathModule) {
            $priorityActions->push([
                'label' => 'Path next step',
                'title' => $nextPathModule->title,
                'summary' => 'This is the next unlocked step in your current learning path.',
                'href' => route('app.modules.show', ['module' => $nextPathModule->id]),
                'cta' => 'Continue path',
                'tone' => 'primary',
            ]);
        }

        if ($pendingReminders->isNotEmpty()) {
            $firstReminder = $pendingReminders->first();
            $priorityActions->push([
                'label' => 'Reminder queue',
                'title' => $firstReminder->module?->title ?? 'Reminder centre',
                'summary' => 'You have '.$pendingReminders->count().' pending reminder record'.($pendingReminders->count() === 1 ? '' : 's').'.',
                'href' => route('app.reminders'),
                'cta' => 'Open reminders',
                'tone' => 'info',
            ]);
        }

        if ($dueReinforcementTouchpoints->isNotEmpty()) {
            $firstTouchpoint = $dueReinforcementTouchpoints->first();
            $priorityActions->push([
                'label' => 'Reinforcement due',
                'title' => $firstTouchpoint->module?->title ?? $firstTouchpoint->title,
                'summary' => 'Keep the knowledge fresh with a short follow-up and record proof of retention.',
                'href' => route('app.reminders'),
                'cta' => 'Open reinforcement queue',
                'tone' => $firstTouchpoint->computed_status === 'due' ? 'warning' : 'info',
            ]);
        }

        $priorityActions = $priorityActions
            ->unique(fn (array $action) => $action['href'])
            ->take(3)
            ->values();

        $recentLearningActivity = LearningEvent::query()
            ->where('user_id', $userId)
            ->where('entity_type', 'learning_module')
            ->whereIn('event_type', ['module_viewed', 'module_saved', 'scorm_launched', 'scorm_runtime_committed'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (LearningEvent $event) use ($modules) {
                $module = $modules->firstWhere('id', (int) $event->entity_id);

                return [
                    'event_type' => $event->event_type,
                    'module_id' => (int) $event->entity_id,
                    'module_title' => $module?->title ?? ('Module #'.$event->entity_id),
                    'occurred_at' => $event->created_at,
                    'summary' => match ($event->event_type) {
                        'scorm_runtime_committed' => sprintf(
                            'Runtime saved with status %s%s.',
                            (string) ($event->metadata['status'] ?? $event->metadata['lesson_status'] ?? $event->metadata['completion_status'] ?? 'updated'),
                            isset($event->metadata['score_raw']) ? ' Score '.(string) $event->metadata['score_raw'].'.' : ''
                        ),
                        'scorm_launched' => 'SCORM player opened.',
                        'module_saved' => 'Saved to your shortlist.',
                        default => 'Viewed in the learner workspace.',
                    },
                ];
            })
            ->values();

        $latestScormRuntimeByModuleId = LearningEvent::query()
            ->where('user_id', $userId)
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->latest('id')
            ->get()
            ->unique('entity_id')
            ->keyBy(fn (LearningEvent $event) => (int) $event->entity_id);

        $latestCompletedProgress = ModuleProgress::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        $latestCompletionSummary = null;
        if ($latestCompletedProgress) {
            $completedModule = $modules->firstWhere('id', (int) $latestCompletedProgress->learning_module_id);
            $runtimeEvent = $latestScormRuntimeByModuleId->get((int) $latestCompletedProgress->learning_module_id);
            $sessionSeconds = isset($runtimeEvent?->metadata['session_seconds'])
                ? (int) $runtimeEvent->metadata['session_seconds']
                : \App\Support\ScormRuntimeMetrics::parseSessionSeconds($runtimeEvent?->metadata['session_time'] ?? null);

            $latestCompletionSummary = [
                'module' => $completedModule,
                'module_title' => $completedModule?->title ?? ('Module #'.$latestCompletedProgress->learning_module_id),
                'completed_at' => $latestCompletedProgress->completed_at,
                'percent_complete' => (int) $latestCompletedProgress->percent_complete,
                'status' => $runtimeEvent?->metadata['status'] ?? ($runtimeEvent?->metadata['lesson_status'] ?? $latestCompletedProgress->status),
                'score_raw' => $runtimeEvent?->metadata['score_raw'] ?? null,
                'session_label' => \App\Support\ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                'lesson_location' => $runtimeEvent?->metadata['lesson_location'] ?? null,
                'source_type' => $completedModule?->source_type ?? null,
            ];
        }

        $completionNextActions = collect();

        if ($latestCompletionSummary) {
            if ($nextPathModule && (int) $nextPathModule->id !== (int) ($latestCompletionSummary['module']?->id ?? 0)) {
                $completionNextActions->push([
                    'label' => 'Next path step',
                    'title' => $nextPathModule->title,
                    'summary' => 'Keep your learning path moving with the next unlocked module.',
                    'href' => route('app.modules.show', ['module' => $nextPathModule->id]),
                    'cta' => 'Open next step',
                    'tone' => 'primary',
                ]);
            }

            $nextRequiredModule = $requiredModules->first(function (LearningModule $module) use ($latestCompletionSummary) {
                return (bool) ($module->assignment['is_incomplete_required'] ?? false)
                    && (int) $module->id !== (int) ($latestCompletionSummary['module']?->id ?? 0);
            });

            if ($nextRequiredModule) {
                $completionNextActions->push([
                    'label' => 'Required next',
                    'title' => $nextRequiredModule->title,
                    'summary' => 'This required module is the next incomplete item in your learner record.',
                    'href' => route('app.modules.show', ['module' => $nextRequiredModule->id]),
                    'cta' => 'Start required module',
                    'tone' => ($nextRequiredModule->assignment['is_overdue'] ?? false) ? 'danger' : 'warning',
                ]);
            }

            $nextRecommendedModule = $recommendedModules->first(function (LearningModule $module) use ($latestCompletionSummary) {
                return (int) $module->id !== (int) ($latestCompletionSummary['module']?->id ?? 0)
                    && ($module->user_progress_status ?? 'not_started') !== 'completed';
            });

            if ($nextRecommendedModule) {
                $completionNextActions->push([
                    'label' => 'Recommended next',
                    'title' => $nextRecommendedModule->title,
                    'summary' => 'Keep momentum going with another visible module matched to your profile.',
                    'href' => route('app.modules.show', ['module' => $nextRecommendedModule->id]),
                    'cta' => 'Open recommendation',
                    'tone' => 'info',
                ]);
            }

            $completionNextActions->push([
                'label' => 'Dashboard',
                'title' => 'Return to your learner dashboard',
                'summary' => 'Review reminders, saved modules, and your latest priority actions.',
                'href' => route('app.feed'),
                'cta' => 'Back to dashboard',
                'tone' => 'neutral',
            ]);
        }

        $completionNextActions = $completionNextActions
            ->unique(fn (array $action) => $action['href'])
            ->take(3)
            ->values();

        $gamification = app(GamificationService::class)->userSummary($user);

        $latestActivity = $recentLearningActivity->first();

        $momentumSummary = [
            'last_activity_at' => $latestActivity['occurred_at'] ?? null,
            'last_activity_summary' => $latestActivity['summary'] ?? 'No recent learner activity recorded yet.',
            'last_activity_module_title' => $latestActivity['module_title'] ?? null,
            'next_action_label' => $priorityActions->first()['cta'] ?? (($activeModule?->id ?? null) ? 'Continue learning' : 'Browse dashboard'),
            'next_action_href' => $priorityActions->first()['href'] ?? (($activeModule?->id ?? null) ? route('app.modules.show', ['module' => $activeModule->id]) : route('app.feed')),
            'risk_label' => $assignmentSummary['required_overdue'] > 0
                ? 'High risk'
                : ($assignmentSummary['required_due_soon'] > 0 ? 'Watchlist' : 'On track'),
        ];

        // Course-level enrolment stats from course_user pivot
        $courseEnrolments = \Illuminate\Support\Facades\DB::table('course_user')
            ->where('user_id', $userId)
            ->select('status')
            ->get();
        $courseCompletedCount = $courseEnrolments->where('status', 'completed')->count();
        $courseInProgressCount = $courseEnrolments->where('status', 'in_progress')->count();
        $courseTotalCount = $courseEnrolments->count();
        $courseCompletionRate = $courseTotalCount > 0
            ? (int) round(($courseCompletedCount / $courseTotalCount) * 100)
            : 0;

        $dashboardSummary = [
            'visible_total' => $modules->count(),
            'required_total' => $assignmentSummary['required_total'],
            'completed_total' => $courseCompletedCount,
            'in_progress_total' => $courseInProgressCount,
            'courses_total' => $courseTotalCount,
            'saved_total' => count($savedModuleIds),
            'pending_reminders_total' => $pendingReminders->count(),
            'reinforcement_total' => $reinforcementSummary['total'],
            'average_progress_percent' => $courseTotalCount > 0
                ? (int) round(($courseCompletedCount * 100 + $courseInProgressCount * 50) / $courseTotalCount)
                : 0,
            'completion_rate_percent' => $courseCompletionRate,
            'next_due_at' => $nextDueAt,
        ];

        $assignedCourses = Course::query()
            ->where('status', 'published')
            ->whereHas('assignedUsers', fn ($q) => $q->where('user_id', $userId))
            ->withCount('modules')
            ->with('modules:id,title')
            ->orderByDesc('updated_at')
            ->get();

        // Attach per-course progress for the current user
        if ($assignedCourses->isNotEmpty()) {
            $allCourseModuleIds = $assignedCourses->flatMap(fn ($c) => $c->modules->pluck('id'))->unique();
            $progressMap = \App\Models\ModuleProgress::where('user_id', $userId)
                ->whereIn('learning_module_id', $allCourseModuleIds)
                ->get()
                ->keyBy('learning_module_id');

            $enrolmentStatusMap = \Illuminate\Support\Facades\DB::table('course_user')
                ->where('user_id', $userId)
                ->whereIn('course_id', $assignedCourses->pluck('id'))
                ->pluck('status', 'course_id');

            $assignedCourses->each(function ($course) use ($progressMap, $enrolmentStatusMap) {
                $moduleIds = $course->modules->pluck('id');
                $total = $moduleIds->count();
                $completed = $moduleIds->filter(fn ($id) => ($progressMap->get($id)?->status ?? '') === 'completed')->count();
                $avgPercent = $total > 0
                    ? (int) round($moduleIds->map(fn ($id) => (int) ($progressMap->get($id)?->percent_complete ?? 0))->avg())
                    : 0;
                $course->setAttribute('course_progress_percent', $avgPercent);
                $course->setAttribute('course_completed_modules', $completed);
                $course->setAttribute('enrolment_status', $enrolmentStatusMap->get($course->id));
            });
        }

        // Override module-level completion rate with course-level
        $completionRate = $courseCompletionRate;
        $averageProgress = $courseTotalCount > 0
            ? (int) round(($courseCompletedCount * 100 + $courseInProgressCount * 50) / $courseTotalCount)
            : 0;

        return view('app.feed', [
            'modules' => $modules,
            'assignedCourses' => $assignedCourses,
            'requiredModules' => $requiredModules,
            'recommendedModules' => $recommendedModules,
            'inProgressModules' => $inProgressModules,
            'completedModules' => $completedModules,
            'savedModules' => $savedModules,
            'courseCategories' => $courseCategories,
            'savedFocusAction' => $savedFocusAction,
            'activeModule' => $activeModule,
            'secondarySpotlightModule' => $secondarySpotlightModule,
            'assignmentSummary' => $assignmentSummary,
            'dashboardSummary' => $dashboardSummary,
            'pendingReminders' => $pendingReminders,
            'reinforcementTouchpoints' => $dueReinforcementTouchpoints,
            'reinforcementSummary' => $reinforcementSummary,
            'paths' => $pathCollection,
            'activePath' => $activePath,
            'priorityActions' => $priorityActions,
            'momentumSummary' => $momentumSummary,
            'recentLearningActivity' => $recentLearningActivity,
            'latestCompletionSummary' => $latestCompletionSummary,
            'completionNextActions' => $completionNextActions,
            'savedModuleIds' => $savedModuleIds,
            'preference' => $user->preference,
            'gamification' => $gamification,
        ]);
    }

    public function required(): View
    {
        $data = $this->index()->getData();
        $userId = auth()->id();

        // All courses (including completed), sorted: outstanding first, completed last
        $allCourses = Course::query()
            ->where('status', 'published')
            ->whereHas('assignedUsers', fn ($q) => $q->where('user_id', $userId))
            ->withCount('modules')
            ->with('modules:id,title')
            ->get();

        if ($allCourses->isNotEmpty()) {
            $allModuleIds = $allCourses->flatMap(fn ($c) => $c->modules->pluck('id'))->unique();
            $progressMap = ModuleProgress::where('user_id', $userId)
                ->whereIn('learning_module_id', $allModuleIds)
                ->get()
                ->keyBy('learning_module_id');

            $allCourses->each(function ($course) use ($progressMap) {
                $moduleIds = $course->modules->pluck('id');
                $total = $moduleIds->count();
                $completed = $moduleIds->filter(fn ($id) => ($progressMap->get($id)?->status ?? '') === 'completed')->count();
                $avgPercent = $total > 0
                    ? (int) round($moduleIds->map(fn ($id) => (int) ($progressMap->get($id)?->percent_complete ?? 0))->avg())
                    : 0;
                $course->setAttribute('course_progress_percent', $avgPercent);
                $course->setAttribute('course_completed_modules', $completed);
            });
        }

        $pivotStatuses = \Illuminate\Support\Facades\DB::table('course_user')
            ->where('user_id', $userId)
            ->pluck('status', 'course_id');

        $allCourses->each(fn ($c) => $c->setAttribute('enrolment_status', $pivotStatuses[$c->id] ?? 'assigned'));

        $allCourses = $allCourses->sortBy(fn ($c) => match ($c->enrolment_status) {
            'assigned' => 0,
            'in_progress' => 1,
            'completed' => 2,
            default => 1,
        })->values();

        $completedCount = $allCourses->where('enrolment_status', 'completed')->count();
        $inProgressCount = $allCourses->where('enrolment_status', 'in_progress')->count();
        $outstandingCount = $allCourses->count() - $completedCount;

        $data['assignedCourses'] = $allCourses;
        $data['catalogueTitle'] = 'Courses';
        $data['catalogueSubtitle'] = 'All your assigned courses. Outstanding and required training is shown first.';
        $data['catalogueModules'] = collect();
        $data['catalogueCountLabel'] = $allCourses->count() . ' course' . ($allCourses->count() !== 1 ? 's' : '');
        $data['catalogueEmptyMessage'] = null;
        $data['activeLearnerPage'] = 'courses';
        $data['catalogueSectionLabel'] = 'Courses';
        $data['cataloguePrimaryCtaLabel'] = 'Back to dashboard';
        $data['cataloguePrimaryCtaHref'] = route('app.feed');
        $data['catalogueSummaryCards'] = collect([
            [
                'label' => 'Outstanding',
                'value' => $outstandingCount,
                'summary' => 'Courses still to complete.',
            ],
            [
                'label' => 'In progress',
                'value' => $inProgressCount,
                'summary' => 'Courses currently being worked on.',
            ],
            [
                'label' => 'Completed',
                'value' => $completedCount,
                'summary' => 'Courses finished.',
            ],
        ]);

        return view('app.feed-catalogue', $data);
    }

    public function recommended(): View
    {
        $data = $this->index()->getData();
        $data['catalogueTitle'] = 'Recommended';
        $data['catalogueSubtitle'] = 'Personalized courses ranked by topic fit, learner activity, and progress.';
        $data['catalogueModules'] = $data['recommendedModules'];
        $data['catalogueCountLabel'] = $data['recommendedModules']->count().' recommended';
        $data['catalogueEmptyMessage'] = 'No recommended modules are visible right now.';
        $data['activeLearnerPage'] = 'recommended';
        $data['catalogueSectionLabel'] = 'Recommended';
        $data['cataloguePrimaryCtaLabel'] = 'Back to dashboard';
        $data['cataloguePrimaryCtaHref'] = route('app.feed');
        $data['catalogueSummaryCards'] = collect([
            [
                'label' => 'Visible modules',
                'value' => $data['recommendedModules']->count(),
                'summary' => 'Courses matched to your current profile.',
            ],
            [
                'label' => 'Topic matches',
                'value' => $data['recommendedModules']->filter(fn (LearningModule $module) => ! empty($module->topic))->pluck('topic')->unique()->count(),
                'summary' => 'Different topic areas currently in view.',
            ],
            [
                'label' => 'Ready now',
                'value' => $data['recommendedModules']->filter(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') !== 'completed')->count(),
                'summary' => 'Recommendations you can open right away.',
            ],
        ]);

        return view('app.feed-catalogue', $data);
    }

    public function saved(): View
    {
        $data = $this->index()->getData();
        $data['catalogueTitle'] = 'Saved';
        $data['catalogueSubtitle'] = 'Modules you bookmarked so you can come back to them quickly.';
        $data['catalogueModules'] = $data['savedModules'];
        $data['catalogueCountLabel'] = $data['savedModules']->count().' saved';
        $data['catalogueEmptyMessage'] = 'You have not saved any modules yet.';
        $data['activeLearnerPage'] = 'saved';
        $data['catalogueSectionLabel'] = 'Saved';
        $data['cataloguePrimaryCtaLabel'] = ($data['savedFocusAction']['label'] ?? null) ?: 'Back to dashboard';
        $data['cataloguePrimaryCtaHref'] = isset($data['savedFocusAction']['module'])
            ? route('app.modules.show', ['module' => $data['savedFocusAction']['module']->id])
            : route('app.feed');
        $data['catalogueSummaryCards'] = collect([
            [
                'label' => 'In progress',
                'value' => $data['savedModules']->filter(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') === 'in_progress')->count(),
                'summary' => 'Saved modules you have already started.',
            ],
            [
                'label' => 'Not started',
                'value' => $data['savedModules']->filter(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') === 'not_started')->count(),
                'summary' => 'Bookmarks waiting for a first visit.',
            ],
            [
                'label' => 'Completed',
                'value' => $data['savedModules']->filter(fn (LearningModule $module) => ($module->user_progress_status ?? 'not_started') === 'completed')->count(),
                'summary' => 'Saved courses already finished.',
            ],
        ]);

        return view('app.feed-catalogue', $data);
    }
}
