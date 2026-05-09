<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAuditEvent;
use App\Models\LearningEvent;
use App\Models\LearningAsset;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\AssignmentReminder;
use App\Models\ReinforcementQuestionSet;
use App\Models\ReinforcementTouchpoint;
use App\Models\User;
use App\Services\LearningModuleRevisionService;
use App\Services\ReinforcementQuestionDraftService;
use App\Services\ScormPackageService;
use App\Services\AssignmentService;
use App\Support\ScormDemoScenario;
use App\Support\ScormRuntimeMetrics;
use Database\Seeders\PrototypeDemoSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLearningModuleController extends Controller
{
    public function index(): View
    {
        Gate::authorize('admin-access');

        $modules = LearningModule::query()
            ->with(['owner', 'assets'])
            ->withCount('prerequisites')
            ->orderByDesc('updated_at')
            ->orderBy('title')
            ->get();
        $scormSummaries = $modules
            ->mapWithKeys(fn (LearningModule $module) => [$module->id => $this->scormSummary($module)])
            ->all();
        $moduleOperationalStates = $modules
            ->mapWithKeys(function (LearningModule $module) use ($scormSummaries) {
                return [$module->id => [
                    'readiness' => $this->moduleReadinessState($module, $scormSummaries[$module->id] ?? null),
                    'visibility' => $this->moduleVisibilityState($module),
                    'impact' => $this->moduleVisibilityImpact($module),
                ]];
            })
            ->all();

        return view('app.admin-learning-modules-index', [
            'modules' => $modules,
            'scormSummaries' => $scormSummaries,
            'moduleOperationalStates' => $moduleOperationalStates,
            'moduleOverviewSummary' => [
                'total' => $modules->count(),
                'publish_ready' => collect($moduleOperationalStates)->filter(fn (array $state) => (bool) ($state['readiness']['can_publish'] ?? false))->count(),
                'publish_blocked' => collect($moduleOperationalStates)->filter(fn (array $state) => ! (bool) ($state['readiness']['can_publish'] ?? false))->count(),
                'live_now' => collect($moduleOperationalStates)->filter(fn (array $state) => ($state['visibility']['timing_state'] ?? null) === 'live now')->count(),
                'scheduled' => collect($moduleOperationalStates)->filter(fn (array $state) => ($state['visibility']['timing_state'] ?? null) === 'scheduled')->count(),
                'expired' => collect($moduleOperationalStates)->filter(fn (array $state) => ($state['visibility']['timing_state'] ?? null) === 'expired')->count(),
                'live_without_audience' => collect($moduleOperationalStates)->filter(fn (array $state) => ($state['visibility']['timing_state'] ?? null) === 'live now' && (int) (($state['impact']['counts']['visible_now'] ?? 0)) === 0)->count(),
                'scorm_ready' => $modules->filter(function (LearningModule $module) use ($moduleOperationalStates) {
                    $state = $moduleOperationalStates[$module->id]['readiness'] ?? null;

                    return ($module->source_type ?? 'manual') === 'scorm'
                        && (bool) ($state['can_publish'] ?? false);
                })->count(),
            ],
            'topics' => \App\Models\Topic::ordered()->get(),
            'courses' => \App\Models\Course::withCount(['modules', 'assignedUsers'])
                ->with(['owner', 'modules:id'])
                ->orderByDesc('updated_at')
                ->get()
                ->each(function ($course) {
                    $moduleIds = $course->modules->pluck('id');
                    $approvedCount = $moduleIds->isEmpty() ? 0 : ReinforcementQuestionSet::whereIn('learning_module_id', $moduleIds)
                        ->where('status', 'approved')
                        ->distinct('learning_module_id')
                        ->count('learning_module_id');
                    $course->setAttribute('question_readiness_approved', $approvedCount);
                    $course->setAttribute('question_readiness_total', $moduleIds->count());
                }),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('admin-write');

        return view('app.admin-learning-modules-form', $this->createFormData(
            new LearningModule([
                'status' => 'draft',
                'difficulty' => 'beginner',
                'source_type' => 'manual',
                'target_roles' => [],
                'review_status' => 'draft',
                'is_required' => false,
                'requires_acknowledgement' => false,
                'reinforcement_intervals_days' => [7, 30],
            ]),
            'Create Module',
            'Create Module',
            null
        ));
    }

    public function createScorm(): View
    {
        Gate::authorize('admin-write');

        return view('app.admin-learning-modules-form', $this->createFormData(
            new LearningModule([
                'status' => 'draft',
                'difficulty' => 'beginner',
                'source_type' => 'scorm',
                'target_roles' => [],
                'review_status' => 'draft',
                'is_required' => false,
                'requires_acknowledgement' => false,
                'reinforcement_intervals_days' => [7, 30],
            ]),
            'Create SCORM Module',
            'Create SCORM Module',
            'Create the module metadata first, then upload the SCORM package from the SCORM Package panel.'
        ));
    }

    public function scormOverview(): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-scorm-overview', $this->scormOverviewData());
    }

    public function scormOverviewExport(): StreamedResponse
    {
        Gate::authorize('admin-access');

        $data = $this->scormOverviewData();
        $filename = 'scorm-overview-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['section', 'key', 'value_1', 'value_2', 'value_3', 'value_4', 'value_5', 'value_6']);

            foreach ($data['summary'] as $key => $value) {
                fputcsv($handle, ['summary', $key, $value instanceof Carbon ? $value->format('Y-m-d H:i:s') : $value]);
            }

            $latestProof = $data['recentCompletions']->first();
            if ($latestProof !== null) {
                fputcsv($handle, [
                    'latest_completion_proof',
                    $latestProof['completed_at']?->format('Y-m-d H:i:s'),
                    $latestProof['module_title'] ?? '',
                    $latestProof['learner_name'] ?? '',
                    $latestProof['percent_complete'] ?? '',
                    $latestProof['score_raw'] ?? '',
                    $latestProof['session_label'] ?? '',
                    $latestProof['lesson_location'] ?? '',
                ]);
            }

            $latestReinforcementProof = $data['recentReinforcementProof']->first();
            if ($latestReinforcementProof !== null) {
                fputcsv($handle, [
                    'latest_reinforcement_proof',
                    $latestReinforcementProof['completed_at']?->format('Y-m-d H:i:s'),
                    $latestReinforcementProof['module_title'] ?? '',
                    $latestReinforcementProof['learner_name'] ?? '',
                    $latestReinforcementProof['interval_days'] ?? '',
                    $latestReinforcementProof['proof_type'] ?? '',
                    $latestReinforcementProof['proof_summary'] ?? '',
                    $latestReinforcementProof['due_on']?->format('Y-m-d') ?? '',
                ]);
            }

            $latestReinforcementFailure = $data['recentReinforcementFailures']->first();
            if ($latestReinforcementFailure !== null) {
                fputcsv($handle, [
                    'latest_reinforcement_failure',
                    $latestReinforcementFailure['updated_at']?->format('Y-m-d H:i:s'),
                    $latestReinforcementFailure['module_title'] ?? '',
                    $latestReinforcementFailure['learner_name'] ?? '',
                    $latestReinforcementFailure['interval_days'] ?? '',
                    $latestReinforcementFailure['proof_type'] ?? '',
                    $latestReinforcementFailure['proof_summary'] ?? '',
                    $latestReinforcementFailure['remediation_count'] ?? '',
                ]);
            }

            foreach ($data['moduleRows'] as $row) {
                fputcsv($handle, [
                    'module_rows',
                    $row['title'],
                    $row['compliance_area'],
                    $row['learner_count'],
                    $row['completed_count'],
                    $row['in_progress_count'],
                    $row['completion_rate'].'%',
                    $row['launch_count'],
                ]);
            }

            foreach ($data['recentLaunches'] as $row) {
                fputcsv($handle, [
                    'recent_launches',
                    $row['when']?->format('Y-m-d H:i:s'),
                    $row['module_title'],
                    $row['learner_name'],
                    $row['launch_path'],
                ]);
            }

            foreach ($data['recentAttempts'] as $row) {
                fputcsv($handle, [
                    'recent_attempts',
                    $row['when']?->format('Y-m-d H:i:s'),
                    $row['module_title'],
                    $row['learner_name'],
                    $row['status'],
                    $row['score_raw'],
                    $row['session_label'],
                    $row['percent_complete'],
                ]);
            }

            foreach ($data['recentCompletions'] as $row) {
                fputcsv($handle, [
                    'recent_completions',
                    $row['completed_at']?->format('Y-m-d H:i:s'),
                    $row['module_title'],
                    $row['learner_name'],
                    $row['percent_complete'],
                    $row['score_raw'] ?? '',
                    $row['session_label'],
                    $row['lesson_location'] ?? '',
                ]);
            }

            foreach ($data['recentReinforcementProof'] as $row) {
                fputcsv($handle, [
                    'recent_reinforcement_proof',
                    $row['completed_at']?->format('Y-m-d H:i:s'),
                    $row['module_title'],
                    $row['learner_name'],
                    $row['interval_days'],
                    $row['proof_type'],
                    $row['proof_summary'],
                    $row['due_on']?->format('Y-m-d') ?? '',
                ]);
            }

            foreach ($data['recentReinforcementFailures'] as $row) {
                fputcsv($handle, [
                    'recent_reinforcement_failures',
                    $row['updated_at']?->format('Y-m-d H:i:s'),
                    $row['module_title'],
                    $row['learner_name'],
                    $row['interval_days'],
                    $row['proof_type'],
                    $row['proof_summary'],
                    $row['remediation_count'],
                ]);
            }

            foreach ($data['topScores'] as $row) {
                fputcsv($handle, [
                    'top_scores',
                    $row['when']?->format('Y-m-d H:i:s'),
                    $row['module_title'],
                    $row['learner_name'],
                    $row['score_raw'],
                ]);
            }

            foreach ($data['learnerLeaderboard'] as $row) {
                fputcsv($handle, [
                    'learner_leaderboard',
                    $row['learner_name'],
                    $row['attempt_count'],
                    $row['average_score'],
                    $row['best_score'],
                    $row['average_session_label'],
                ]);
            }

            foreach ($data['mostActiveLearners'] as $row) {
                fputcsv($handle, [
                    'most_active_learners',
                    $row['learner_name'],
                    $row['launch_count'],
                    $row['attempt_count'],
                    $row['last_launch_at']?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function scormHandout(): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-scorm-handout', $this->scormOverviewData());
    }

    public function resetDemoData(): RedirectResponse
    {
        Gate::authorize('admin-write');

        $exitCode = Artisan::call('db:seed', ['--class' => PrototypeDemoSeeder::class, '--force' => true]);
        $status = $exitCode === 0
            ? 'SCORM demo data reset completed.'
            : sprintf('SCORM demo data reset failed (exit code %d).', $exitCode);
        $resetState = [
            'status' => $exitCode === 0 ? 'completed' : 'failed',
            'message' => $status,
            'completed_at' => now(),
        ];

        $output = trim((string) Artisan::output());
        if ($output !== '') {
            $status .= ' '.$output;
            $resetState['message'] .= ' '.$output;
        }

        $resetState['counts'] = $this->demoResetCounts();

        $this->recordDemoResetAuditEvent($resetState);

        return redirect()
            ->route('app.admin.scorm.index')
            ->with('status', $status)
            ->with('scormDemoReset', $resetState);
    }

    private function scormOverviewData(): array
    {
        $modules = LearningModule::query()
            ->with(['assets'])
            ->where('source_type', 'scorm')
            ->orderBy('title')
            ->get();

        $moduleIds = $modules->pluck('id');
        $progressRows = ModuleProgress::query()
            ->whereIn('learning_module_id', $moduleIds)
            ->get();
        $runtimeEvents = LearningEvent::query()
            ->with('user')
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->whereIn('entity_id', $moduleIds)
            ->latest()
            ->get();
        $launchEvents = LearningEvent::query()
            ->with('user')
            ->where('event_type', 'scorm_launched')
            ->where('entity_type', 'learning_module')
            ->whereIn('entity_id', $moduleIds)
            ->latest()
            ->get();
        $averageSessionSeconds = (int) round((float) ($runtimeEvents
            ->map(fn (LearningEvent $event) => isset($event->metadata['session_seconds'])
                ? (int) $event->metadata['session_seconds']
                : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null))
            ->filter(fn ($value) => $value !== null)
            ->avg() ?? 0));
        $learnerCount = $progressRows->pluck('user_id')->unique()->count();
        $completedCount = $progressRows->where('status', 'completed')->count();
        $totalEnrollments = $progressRows->count();
        $completionRate = $totalEnrollments > 0
            ? (int) round(($completedCount / $totalEnrollments) * 100)
            : 0;

        $moduleRows = $modules->map(function (LearningModule $module) use ($progressRows, $runtimeEvents, $launchEvents) {
            $moduleProgressRows = $progressRows->where('learning_module_id', $module->id);
            $moduleRuntimeEvents = $runtimeEvents->where('entity_id', $module->id);
            $moduleLaunchEvents = $launchEvents->where('entity_id', $module->id);
            $moduleLearnerCount = $moduleProgressRows->pluck('user_id')->unique()->count();
            $moduleCompletedCount = $moduleProgressRows->where('status', 'completed')->count();
            $moduleAverageSessionSeconds = (int) round((float) ($moduleRuntimeEvents
                ->map(fn (LearningEvent $event) => isset($event->metadata['session_seconds'])
                    ? (int) $event->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null))
                ->filter(fn ($value) => $value !== null)
                ->avg() ?? 0));

            return [
                'id' => $module->id,
                'title' => $module->title,
                'status' => $module->status,
                'compliance_area' => $module->compliance_area ?: 'unscoped',
                'summary' => $this->scormSummary($module),
                'learner_count' => $moduleLearnerCount,
                'completed_count' => $moduleCompletedCount,
                'in_progress_count' => $moduleProgressRows->where('status', 'in_progress')->count(),
                'average_score' => (int) round((float) ($moduleRuntimeEvents->pluck('metadata.score_raw')->filter(fn ($value) => $value !== null)->avg() ?? 0)),
                'average_session_label' => ScormRuntimeMetrics::formatSeconds($moduleAverageSessionSeconds),
                'completion_rate' => $moduleLearnerCount > 0
                    ? (int) round(($moduleCompletedCount / $moduleLearnerCount) * 100)
                    : 0,
                'launch_count' => $moduleLaunchEvents->count(),
                'last_launch_at' => $moduleLaunchEvents->first()?->created_at,
            ];
        })->values();

        $recentAttempts = $runtimeEvents
            ->take(15)
            ->map(function (LearningEvent $event) use ($modules) {
                $sessionSeconds = isset($event->metadata['session_seconds'])
                    ? (int) $event->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null);

                return [
                    'when' => $event->created_at,
                    'module_id' => (int) $event->entity_id,
                    'user_id' => (int) $event->user_id,
                    'module_title' => $modules->firstWhere('id', (int) $event->entity_id)?->title ?? ('Module #'.$event->entity_id),
                    'learner_name' => $event->user?->name ?? 'Unknown learner',
                    'status' => $event->metadata['status'] ?? ($event->metadata['lesson_status'] ?? 'n/a'),
                    'score_raw' => $event->metadata['score_raw'] ?? null,
                    'session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                    'percent_complete' => $event->metadata['percent_complete'] ?? null,
                ];
            })
            ->values();

        $latestRuntimeByUserAndModule = $runtimeEvents
            ->unique(fn (LearningEvent $event) => $event->user_id.':'.$event->entity_id)
            ->keyBy(fn (LearningEvent $event) => $event->user_id.':'.$event->entity_id);

        $recentCompletions = ModuleProgress::query()
            ->with('user:id,name')
            ->whereIn('learning_module_id', $moduleIds)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(12)
            ->get()
            ->map(function (ModuleProgress $progress) use ($latestRuntimeByUserAndModule, $modules) {
                $runtimeEvent = $latestRuntimeByUserAndModule->get($progress->user_id.':'.$progress->learning_module_id);
                $sessionSeconds = isset($runtimeEvent?->metadata['session_seconds'])
                    ? (int) $runtimeEvent->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($runtimeEvent?->metadata['session_time'] ?? null);

                return [
                    'completed_at' => $progress->completed_at,
                    'module_id' => (int) $progress->learning_module_id,
                    'user_id' => (int) $progress->user_id,
                    'learner_id' => (int) $progress->user_id,
                    'module_title' => $modules->firstWhere('id', (int) $progress->learning_module_id)?->title ?? ('Module #'.$progress->learning_module_id),
                    'learner_name' => $progress->user?->name ?? 'Unknown learner',
                    'learner_email' => $progress->user?->email ?? '',
                    'percent_complete' => (int) $progress->percent_complete,
                    'scorm_status' => $runtimeEvent?->metadata['status'] ?? ($runtimeEvent?->metadata['lesson_status'] ?? 'completed'),
                    'scorm_score_raw' => $runtimeEvent?->metadata['score_raw'] ?? null,
                    'scorm_session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                    'score_raw' => $runtimeEvent?->metadata['score_raw'] ?? null,
                    'session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                    'lesson_location' => $runtimeEvent?->metadata['lesson_location'] ?? null,
                ];
            })
            ->values();

        $reinforcementCompletedCount = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->whereIn('learning_module_id', $moduleIds)
                ->whereNotNull('completed_at')
                ->count()
            : 0;

        $recentReinforcementProof = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['user:id,name,email', 'module:id,title,source_type'])
                ->whereIn('learning_module_id', $moduleIds)
                ->whereNotNull('completed_at')
                ->latest('completed_at')
                ->limit(10)
                ->get()
                ->map(function (ReinforcementTouchpoint $touchpoint) {
                    return [
                        'id' => $touchpoint->id,
                        'completed_at' => $touchpoint->completed_at,
                        'due_on' => $touchpoint->due_on,
                        'module_id' => (int) $touchpoint->learning_module_id,
                        'user_id' => (int) $touchpoint->user_id,
                        'learner_name' => $touchpoint->user?->name ?? 'Unknown learner',
                        'learner_email' => $touchpoint->user?->email ?? '',
                        'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                        'interval_days' => (int) $touchpoint->interval_days,
                        'proof_type' => $touchpoint->proof_type,
                        'proof_summary' => $touchpoint->proof_summary ?: 'Proof recorded',
                    ];
                })
                ->values()
            : collect();

        $recentReinforcementFailures = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['user:id,name,email', 'module:id,title,source_type'])
                ->whereIn('learning_module_id', $moduleIds)
                ->where('status', 'needs_retry')
                ->latest('updated_at')
                ->limit(10)
                ->get()
                ->map(function (ReinforcementTouchpoint $touchpoint) {
                    $remediationModuleIds = collect($touchpoint->metadata['last_remediation_module_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->values();

                    return [
                        'id' => $touchpoint->id,
                        'updated_at' => $touchpoint->updated_at,
                        'due_on' => $touchpoint->due_on,
                        'module_id' => (int) $touchpoint->learning_module_id,
                        'user_id' => (int) $touchpoint->user_id,
                        'learner_name' => $touchpoint->user?->name ?? 'Unknown learner',
                        'learner_email' => $touchpoint->user?->email ?? '',
                        'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                        'interval_days' => (int) $touchpoint->interval_days,
                        'proof_type' => $touchpoint->proof_type,
                        'proof_summary' => $touchpoint->proof_summary ?: 'Incorrect reinforcement answer recorded.',
                        'remediation_count' => $remediationModuleIds->count(),
                        'remediation_titles' => LearningModule::query()
                            ->whereIn('id', $remediationModuleIds->all())
                            ->pluck('title')
                            ->values(),
                    ];
                })
                ->values()
            : collect();

        $reinforcementDueCount = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->whereIn('learning_module_id', $moduleIds)
                ->whereNull('completed_at')
                ->whereDate('due_on', '<=', today())
                ->count()
            : 0;

        $recentLaunches = $launchEvents
            ->take(15)
            ->map(function (LearningEvent $event) use ($modules) {
                return [
                    'when' => $event->created_at,
                    'module_id' => (int) $event->entity_id,
                    'user_id' => (int) $event->user_id,
                    'module_title' => $modules->firstWhere('id', (int) $event->entity_id)?->title ?? ('Module #'.$event->entity_id),
                    'learner_name' => $event->user?->name ?? 'Unknown learner',
                    'launch_path' => $event->metadata['launch_path'] ?? 'n/a',
                ];
            })
            ->values();

        $topScores = $runtimeEvents
            ->filter(fn (LearningEvent $event) => $event->metadata['score_raw'] ?? null)
            ->sortByDesc(fn (LearningEvent $event) => (float) ($event->metadata['score_raw'] ?? 0))
            ->take(10)
            ->map(function (LearningEvent $event) use ($modules) {
                return [
                    'module_id' => (int) $event->entity_id,
                    'user_id' => (int) $event->user_id,
                    'module_title' => $modules->firstWhere('id', (int) $event->entity_id)?->title ?? ('Module #'.$event->entity_id),
                    'learner_name' => $event->user?->name ?? 'Unknown learner',
                    'score_raw' => $event->metadata['score_raw'] ?? null,
                    'when' => $event->created_at,
                ];
            })
            ->values();

        $learnerLeaderboard = $runtimeEvents
            ->groupBy('user_id')
            ->map(function ($events, $userId) {
                $scoredEvents = $events->filter(fn (LearningEvent $event) => ($event->metadata['score_raw'] ?? null) !== null);
                $averageSessionSeconds = (int) round((float) ($events
                    ->map(fn (LearningEvent $event) => isset($event->metadata['session_seconds'])
                        ? (int) $event->metadata['session_seconds']
                        : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null))
                    ->filter(fn ($value) => $value !== null)
                    ->avg() ?? 0));

                return [
                    'user_id' => (int) $userId,
                    'learner_name' => $events->first()?->user?->name ?? 'Unknown learner',
                    'attempt_count' => $events->count(),
                    'average_score' => (int) round((float) ($scoredEvents->pluck('metadata.score_raw')->avg() ?? 0)),
                    'best_score' => (int) round((float) ($scoredEvents->pluck('metadata.score_raw')->max() ?? 0)),
                    'average_session_label' => ScormRuntimeMetrics::formatSeconds($averageSessionSeconds),
                ];
            })
            ->sortByDesc(fn (array $row) => [$row['average_score'], $row['best_score'], $row['attempt_count']])
            ->take(10)
            ->values();

        $mostActiveLearners = $launchEvents
            ->groupBy('user_id')
            ->map(function ($events, $userId) use ($runtimeEvents) {
                $learnerRuntimeEvents = $runtimeEvents->where('user_id', (int) $userId);

                return [
                    'user_id' => (int) $userId,
                    'learner_name' => $events->first()?->user?->name ?? 'Unknown learner',
                    'launch_count' => $events->count(),
                    'attempt_count' => $learnerRuntimeEvents->count(),
                    'last_launch_at' => $events->first()?->created_at,
                ];
            })
            ->sortByDesc(fn (array $row) => [$row['launch_count'], $row['attempt_count']])
            ->take(10)
            ->values();

        $primaryDemoModule = $modules->first(fn (LearningModule $module) => ScormDemoScenario::isPrimaryDemoCourse($module));
        $liveDemoState = $this->recentScormActivityState($launchEvents, $runtimeEvents, $modules, $moduleIds);

        return [
            'summary' => [
                'modules' => $modules->count(),
                'learners' => $learnerCount,
                'completed' => $completedCount,
                'in_progress' => $progressRows->where('status', 'in_progress')->count(),
                'completion_rate' => $completionRate,
                'launches' => $launchEvents->count(),
                'attempts' => $runtimeEvents->count(),
                'average_session_label' => ScormRuntimeMetrics::formatSeconds($averageSessionSeconds),
                'last_launch_at' => $launchEvents->first()?->created_at,
                'reinforcement_due' => $reinforcementDueCount,
                'reinforcement_completed' => $reinforcementCompletedCount,
                'reinforcement_failed' => $recentReinforcementFailures->count(),
                'reinforcement_remediation_assigned' => $recentReinforcementFailures->sum('remediation_count'),
            ],
            'moduleRows' => $moduleRows,
            'recentAttempts' => $recentAttempts,
            'recentCompletions' => $recentCompletions,
            'recentLaunches' => $recentLaunches,
            'topScores' => $topScores,
            'recentReinforcementProof' => $recentReinforcementProof,
            'recentReinforcementFailures' => $recentReinforcementFailures,
            'learnerLeaderboard' => $learnerLeaderboard,
            'mostActiveLearners' => $mostActiveLearners,
            'primaryDemoModule' => $primaryDemoModule,
            'liveDemoState' => $liveDemoState,
            'demoResetStatus' => $this->latestDemoResetStatus(),
        ];
    }

    private function recentScormActivityState(Collection $launchEvents, Collection $runtimeEvents, Collection $modules, Collection $moduleIds): ?array
    {
        if ($moduleIds->isEmpty()) {
            return null;
        }

        $latestLaunch = $launchEvents->first();
        $latestRuntime = $runtimeEvents->first();

        $freshThreshold = now()->subHours(24);
        $hasFreshLaunch = $latestLaunch?->created_at?->gte($freshThreshold) ?? false;
        $hasFreshRuntime = $latestRuntime?->created_at?->gte($freshThreshold) ?? false;
        $latestCompletedAt = ModuleProgress::query()
            ->whereIn('learning_module_id', $moduleIds)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->value('completed_at');

        return [
            'status' => $hasFreshLaunch && $hasFreshRuntime ? 'primed' : 'stale',
            'stale_reason' => $this->liveDemoStaleReason($hasFreshLaunch, $hasFreshRuntime),
            'fresh_window_label' => '24h',
            'latest_launch_at' => $latestLaunch?->created_at,
            'latest_launch_age_label' => $this->relativeAgeLabel($latestLaunch?->created_at),
            'latest_launch_user' => $latestLaunch?->user?->name,
            'latest_launch_user_email' => $latestLaunch?->user?->email,
            'latest_launch_module' => $modules->firstWhere('id', (int) ($latestLaunch?->entity_id ?? 0))?->title,
            'latest_runtime_at' => $latestRuntime?->created_at,
            'latest_runtime_age_label' => $this->relativeAgeLabel($latestRuntime?->created_at),
            'latest_runtime_user' => $latestRuntime?->user?->name,
            'latest_runtime_user_email' => $latestRuntime?->user?->email,
            'latest_runtime_module' => $modules->firstWhere('id', (int) ($latestRuntime?->entity_id ?? 0))?->title,
            'latest_runtime_status' => $latestRuntime?->metadata['status'] ?? ($latestRuntime?->metadata['lesson_status'] ?? null),
            'latest_runtime_score' => $latestRuntime?->metadata['score_raw'] ?? null,
            'latest_runtime_percent' => $latestRuntime?->metadata['percent_complete'] ?? null,
            'latest_runtime_lesson_location' => $latestRuntime?->metadata['lesson_location'] ?? null,
            'latest_completed_at' => $latestCompletedAt,
            'has_fresh_launch' => $hasFreshLaunch,
            'has_fresh_runtime' => $hasFreshRuntime,
        ];
    }

    private function liveDemoStaleReason(bool $hasFreshLaunch, bool $hasFreshRuntime): ?string
    {
        if ($hasFreshLaunch && $hasFreshRuntime) {
            return null;
        }

        if (! $hasFreshLaunch && ! $hasFreshRuntime) {
            return 'Demo is stale because both the learner launch and runtime commit are older than the freshness window.';
        }

        if (! $hasFreshLaunch) {
            return 'Demo is stale because the learner launch is older than the freshness window.';
        }

        return 'Demo is stale because the runtime commit is older than the freshness window.';
    }

    private function relativeAgeLabel(?Carbon $timestamp): ?string
    {
        if (! $timestamp) {
            return null;
        }

        $seconds = max(0, now()->diffInSeconds($timestamp));

        if ($seconds < 60) {
            return sprintf('%ds ago', $seconds);
        }

        $minutes = (int) ceil($seconds / 60);
        if ($minutes < 60) {
            return sprintf('%dm ago', $minutes);
        }

        $hours = (int) ceil($minutes / 60);
        if ($hours < 24) {
            return sprintf('%dh ago', $hours);
        }

        $days = (int) ceil($hours / 24);

        return sprintf('%dd ago', $days);
    }

    private function latestDemoResetStatus(): ?array
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return null;
        }

        $event = AssignmentAuditEvent::query()
            ->with('actor:id,name')
            ->where('entity_type', 'scorm_demo')
            ->where('action', 'scorm_demo_reset')
            ->latest()
            ->first();

        if (! $event) {
            return null;
        }

        return [
            'status' => $event->meta['status'] ?? 'completed',
            'message' => $event->meta['message'] ?? 'SCORM demo data reset completed.',
            'completed_at' => $event->created_at,
            'actor_name' => $event->actor?->name,
            'counts' => is_array($event->meta['counts'] ?? null) ? $event->meta['counts'] : [],
        ];
    }

    private function demoResetCounts(): array
    {
        $demoLearnerEmails = collect(PrototypeDemoSeeder::demoLearners())
            ->pluck('email')
            ->all();
        $moduleTitles = [
            PrototypeDemoSeeder::DEMO_SCORM_MODULE_TITLE,
            PrototypeDemoSeeder::DEMO_SECONDARY_SCORM_MODULE_TITLE,
            PrototypeDemoSeeder::DEMO_MANUAL_MODULE_TITLE,
        ];

        $learnerIds = User::query()
            ->whereIn('email', $demoLearnerEmails)
            ->pluck('id');
        $moduleIds = LearningModule::query()
            ->whereIn('title', $moduleTitles)
            ->pluck('id');

        return [
            'learners' => $learnerIds->count(),
            'modules' => $moduleIds->count(),
            'progress_rows' => ModuleProgress::query()
                ->whereIn('user_id', $learnerIds)
                ->whereIn('learning_module_id', $moduleIds)
                ->count(),
            'reminders' => AssignmentReminder::query()
                ->whereIn('learning_module_id', $moduleIds)
                ->count(),
            'learning_events' => LearningEvent::query()
                ->whereIn('user_id', $learnerIds)
                ->where('entity_type', 'learning_module')
                ->whereIn('entity_id', $moduleIds)
                ->count(),
            'scorm_assets' => LearningAsset::query()
                ->whereIn('learning_module_id', $moduleIds)
                ->where('asset_type', 'scorm_package')
                ->where('status', 'processed')
                ->count(),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $this->validatedData($request);

        if ($request->hasFile('cover_image')) {
            $validated['attributes']['cover_image'] = $request->file('cover_image')
                ->store('module-covers', 'public');
        }

        $module = LearningModule::query()->create($validated['attributes']);
        $module->prerequisites()->sync($validated['prerequisite_ids']);
        app(LearningModuleRevisionService::class)->record($module->fresh(), 'created');

        return redirect()
            ->to(($validated['attributes']['source_type'] ?? 'manual') === 'scorm'
                ? route('app.admin.modules.edit', ['module' => $module->id]).'#scorm-package'
                : route('app.admin.modules.edit', ['module' => $module->id]))
            ->with('status', ($validated['attributes']['source_type'] ?? 'manual') === 'scorm'
                ? 'SCORM module created. Upload a package to continue.'
                : 'Module created.');
    }

    public function edit(LearningModule $module): View
    {
        Gate::authorize('admin-write');

        $recentScormAttempts = LearningEvent::query()
            ->with('user')
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->where('entity_id', $module->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (LearningEvent $event) {
                $sessionSeconds = isset($event->metadata['session_seconds'])
                    ? (int) $event->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null);

                return [
                    'when' => $event->created_at,
                    'learner_name' => $event->user?->name ?? 'Unknown learner',
                    'learner_email' => $event->user?->email ?? null,
                    'status' => $event->metadata['status'] ?? ($event->metadata['lesson_status'] ?? 'n/a'),
                    'score_raw' => $event->metadata['score_raw'] ?? null,
                    'session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                    'percent_complete' => $event->metadata['percent_complete'] ?? null,
                    'lesson_location' => $event->metadata['lesson_location'] ?? null,
                ];
            })
            ->values();
        $currentScormAsset = $module->latestScormAsset();
        $scormAssetHistory = $module->assets()
            ->where('asset_type', 'scorm_package')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function (LearningAsset $asset) use ($currentScormAsset) {
                return [
                    'id' => $asset->id,
                    'original_filename' => $asset->original_filename,
                    'status' => $asset->status,
                    'launch_path' => $asset->launch_path,
                    'size_label' => $asset->size_bytes !== null
                        ? sprintf('%d MB', max(1, (int) ceil(((int) $asset->size_bytes) / 1024 / 1024)))
                        : 'n/a',
                    'uploaded_at' => $asset->created_at,
                    'activated_at' => isset($asset->processing_metadata['activated_at'])
                        ? Carbon::parse($asset->processing_metadata['activated_at'])
                        : null,
                    'error_message' => $asset->error_message,
                    'is_current' => $currentScormAsset?->id === $asset->id,
                ];
            })
            ->values();

        return view('app.admin-learning-modules-form', [
            'module' => $module,
            'owners' => \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'availablePrerequisites' => LearningModule::query()
                ->excludeModule($module->id)
                ->orderBy('title')
                ->get(['id', 'title', 'status']),
            'revisions' => $module->revisions()
                ->with('user')
                ->latest('revision_number')
                ->limit(10)
                ->get(),
            'formAction' => route('app.admin.modules.update', ['module' => $module->id]),
            'formMethod' => 'PATCH',
            'pageTitle' => 'Edit Module',
            'submitLabel' => 'Save Module',
            'formIntro' => null,
            'latestScormAsset' => $currentScormAsset,
            'scormSummary' => $scormSummary = $this->scormSummary($module),
            'scormAssetHistory' => $scormAssetHistory,
            'recentScormAttempts' => $recentScormAttempts,
            'moduleReadinessState' => $this->moduleReadinessState($module, $scormSummary),
            'moduleVisibilityState' => $this->moduleVisibilityState($module),
            'moduleVisibilityImpact' => $this->moduleVisibilityImpact($module),
            'reinforcementQuestionSet' => $this->latestReinforcementQuestionSet($module),
            'remediationModules' => LearningModule::query()->orderBy('title')->get(['id', 'title', 'status']),
            'topicOptions' => \App\Models\Topic::ordered()->pluck('name')->all(),
            'roleOptions' => \App\Models\Role::ordered()->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, LearningModule $module): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $this->validatedData($request, $module);

        if ($request->hasFile('cover_image')) {
            if ($module->cover_image) {
                Storage::disk('public')->delete($module->cover_image);
            }
            $validated['attributes']['cover_image'] = $request->file('cover_image')
                ->store('module-covers', 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            if ($module->cover_image) {
                Storage::disk('public')->delete($module->cover_image);
            }
            $validated['attributes']['cover_image'] = null;
        }

        $module->update($validated['attributes']);
        $module->prerequisites()->sync($validated['prerequisite_ids']);
        app(LearningModuleRevisionService::class)->record($module->fresh(), 'updated');

        return redirect()
            ->route('app.admin.modules.edit', ['module' => $module->id])
            ->with('status', 'Module updated.');
    }

    public function draftReinforcementQuestions(LearningModule $module, ReinforcementQuestionDraftService $drafts): RedirectResponse
    {
        Gate::authorize('admin-write');

        $questionSet = $drafts->draftForModule($module);

        return redirect()
            ->route('app.admin.modules.edit', ['module' => $module->id])
            ->with('status', sprintf(
                'Reinforcement draft created with %d question%s. Review and approve before learner use.',
                $questionSet->questions->count(),
                $questionSet->questions->count() === 1 ? '' : 's'
            ));
    }

    public function updateReinforcementQuestions(Request $request, LearningModule $module): RedirectResponse
    {
        Gate::authorize('admin-write');

        $questionSet = $this->latestReinforcementQuestionSet($module);
        abort_unless($questionSet !== null, 404);

        $validated = $request->validate([
            'set_title' => ['required', 'string', 'max:255'],
            'set_summary' => ['nullable', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['required', 'integer'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.option_a' => ['required', 'string'],
            'questions.*.option_b' => ['required', 'string'],
            'questions.*.option_c' => ['required', 'string'],
            'questions.*.option_d' => ['required', 'string'],
            'questions.*.correct_answer' => ['required', 'in:A,B,C,D'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.remediation_learning_module_id' => ['nullable', 'integer', 'exists:learning_modules,id'],
        ]);

        $questionSet->update([
            'title' => trim($validated['set_title']),
            'summary' => $this->nullableTrim($validated['set_summary'] ?? null),
            'status' => 'in_review',
        ]);

        foreach ($validated['questions'] as $index => $questionInput) {
            $question = $questionSet->questions()->findOrFail((int) $questionInput['id']);
            $question->update([
                'position' => $index + 1,
                'question_text' => trim($questionInput['question_text']),
                'options' => [
                    'A' => trim($questionInput['option_a']),
                    'B' => trim($questionInput['option_b']),
                    'C' => trim($questionInput['option_c']),
                    'D' => trim($questionInput['option_d']),
                ],
                'correct_answer' => $questionInput['correct_answer'],
                'explanation' => $this->nullableTrim($questionInput['explanation'] ?? null),
                'remediation_learning_module_id' => $questionInput['remediation_learning_module_id'] ?? null,
                'status' => 'in_review',
            ]);
        }

        return redirect()
            ->route('app.admin.modules.edit', ['module' => $module->id])
            ->with('status', 'Reinforcement draft updated and moved into review.');
    }

    public function approveReinforcementQuestions(LearningModule $module): RedirectResponse
    {
        Gate::authorize('admin-write');

        $questionSet = $this->latestReinforcementQuestionSet($module);
        abort_unless($questionSet !== null, 404);

        $questionSet->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $questionSet->questions()->update([
            'status' => 'approved',
        ]);

        ReinforcementTouchpoint::query()
            ->where('learning_module_id', $module->id)
            ->whereNull('completed_at')
            ->get()
            ->each(function (ReinforcementTouchpoint $touchpoint) use ($module, $questionSet): void {
                $metadata = $touchpoint->metadata ?? [];
                $metadata['question_set_id'] = $questionSet->id;
                $metadata['question_set_status'] = 'approved';
                $metadata['question_count'] = $questionSet->questions()->count();

                $touchpoint->update([
                    'reinforcement_question_set_id' => $questionSet->id,
                    'prompt' => sprintf(
                        'Answer %d approved reinforcement question%s for %s.',
                        $questionSet->questions()->count(),
                        $questionSet->questions()->count() === 1 ? '' : 's',
                        $module->title
                    ),
                    'metadata' => $metadata,
                ]);
            });

        return redirect()
            ->route('app.admin.modules.edit', ['module' => $module->id])
            ->with('status', 'Reinforcement question set approved for learner follow-up.');
    }

    public function uploadScorm(Request $request, LearningModule $module, ScormPackageService $scorm): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $request->validate([
            'scorm_package' => ['required', 'file', 'extensions:zip', 'max:51200'],
        ]);

        try {
            $asset = $scorm->uploadAndProcess($module, $validated['scorm_package']);
            app(LearningModuleRevisionService::class)->record($module->fresh(), 'updated');

            return redirect($this->scormEditUrl($module))
                ->with('status', 'SCORM package uploaded and processed.')
                ->with('scormUploadStatus', [
                    'state' => 'completed',
                    'title' => 'SCORM package uploaded and processed.',
                    'message' => sprintf(
                        'Package `%s` is ready. Launch path: `%s`.',
                        $asset->original_filename,
                        $asset->launch_path ?? 'n/a'
                    ),
                ]);
        } catch (Throwable $exception) {
            return redirect($this->scormEditUrl($module))
                ->withInput()
                ->with('status', 'SCORM package upload failed.')
                ->with('scormUploadStatus', [
                    'state' => 'failed',
                    'title' => 'SCORM package upload failed.',
                    'message' => $exception->getMessage(),
                ]);
        }
    }

    public function activateScormPackage(LearningModule $module, LearningAsset $asset): RedirectResponse
    {
        Gate::authorize('admin-write');

        abort_unless($asset->learning_module_id === $module->id, 404);
        abort_unless($asset->asset_type === 'scorm_package', 404);
        abort_unless($asset->status === 'processed', 422);

        $metadata = $asset->processing_metadata ?? [];
        $metadata['activated_at'] = now()->toIso8601String();
        $metadata['activated_by'] = auth()->id();

        $asset->forceFill([
            'processing_metadata' => $metadata,
        ])->save();

        $module->forceFill([
            'source_type' => 'scorm',
            'source_uri' => $asset->launch_path,
        ])->save();

        app(LearningModuleRevisionService::class)->record($module->fresh(), 'updated');

        return redirect($this->scormEditUrl($module))
            ->with('status', 'SCORM package activated as current.')
            ->with('scormUploadStatus', [
                'state' => 'completed',
                'title' => 'SCORM package activated as current.',
                'message' => sprintf(
                    'Package `%s` is now the current SCORM package for this module.',
                    $asset->original_filename
                ),
            ]);
    }

    private function scormEditUrl(LearningModule $module): string
    {
        return route('app.admin.modules.edit', ['module' => $module->id]).'#scorm-package';
    }

    public function transition(Request $request, LearningModule $module): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $request->validate([
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        if ($validated['status'] === 'published' && $module->review_status !== 'approved') {
            return redirect()
                ->route('app.admin.modules.index')
                ->with('status', 'Module must be approved before publishing.');
        }

        $module->update([
            'status' => $validated['status'],
        ]);
        app(LearningModuleRevisionService::class)->record($module->fresh(), 'status_changed');

        return redirect()
            ->route('app.admin.modules.index')
            ->with('status', "Module status updated to {$validated['status']}.");
    }

    public function bulkTransition(Request $request): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $request->validate([
            'status' => ['required', 'in:draft,published,archived'],
            'module_ids' => ['required', 'array', 'min:1'],
            'module_ids.*' => ['integer', 'exists:learning_modules,id'],
        ]);

        $targetStatus = $validated['status'];
        $moduleIds = collect($validated['module_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $modules = LearningModule::query()
            ->whereIn('id', $moduleIds)
            ->get();

        $updated = 0;
        $skippedUnapproved = 0;
        $unchanged = 0;

        foreach ($modules as $module) {
            if ($targetStatus === 'published' && $module->review_status !== 'approved') {
                $skippedUnapproved++;
                continue;
            }

            if ($module->status === $targetStatus) {
                $unchanged++;
                continue;
            }

            $module->update(['status' => $targetStatus]);
            app(LearningModuleRevisionService::class)->record($module->fresh(), 'status_changed');
            $updated++;
        }

        $messageParts = ["Bulk status update complete: {$updated} updated."];
        if ($skippedUnapproved > 0) {
            $messageParts[] = "{$skippedUnapproved} skipped (not approved for publish).";
        }
        if ($unchanged > 0) {
            $messageParts[] = "{$unchanged} unchanged.";
        }

        return redirect()
            ->route('app.admin.modules.index')
            ->with('status', implode(' ', $messageParts));
    }

    private function validatedData(Request $request, ?LearningModule $module = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'topic' => ['nullable', 'string', 'max:100'],
            'difficulty' => ['nullable', 'in:beginner,intermediate,advanced,any'],
            'status' => ['required', 'in:draft,published,archived'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'review_status' => ['nullable', 'in:draft,in_review,approved'],
            'compliance_area' => ['nullable', 'string', 'max:100'],
            'refresh_interval_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'reinforcement_intervals_days' => ['nullable', 'string', 'max:255'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'source_type' => ['nullable', 'string', 'max:100'],
            'source_uri' => ['nullable', 'string', 'max:500'],
            'content_text' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'target_roles' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'requires_acknowledgement' => ['nullable', 'boolean'],
            'prerequisite_ids' => ['nullable', 'array'],
            'prerequisite_ids.*' => ['integer', 'exists:learning_modules,id'],
        ]);

        $prerequisiteIds = collect($validated['prerequisite_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => ! $module || $id !== $module->id)
            ->unique()
            ->values()
            ->all();

        $reviewStatus = $validated['review_status'] ?? 'draft';

        return [
            'attributes' => [
                'title' => trim($validated['title']),
                'description' => trim($validated['description'] ?? ''),
                'topic' => $this->nullableTrim($validated['topic'] ?? null),
                'difficulty' => $validated['difficulty'] ?? null,
                'status' => $validated['status'],
                'owner_user_id' => $validated['owner_user_id'] ?? null,
                'review_status' => $reviewStatus,
                'approved_by' => $reviewStatus === 'approved' ? auth()->id() : null,
                'approved_at' => $reviewStatus === 'approved' ? now() : null,
                'compliance_area' => $this->nullableTrim($validated['compliance_area'] ?? null),
                'refresh_interval_days' => $validated['refresh_interval_days'] ?? null,
                'reinforcement_intervals_days' => $this->parseReinforcementIntervals($validated['reinforcement_intervals_days'] ?? ''),
                'available_from' => $this->nullableDateTime($validated['available_from'] ?? null),
                'available_until' => $this->nullableDateTime($validated['available_until'] ?? null),
                'source_type' => $this->nullableTrim($validated['source_type'] ?? null) ?? 'manual',
                'source_uri' => $this->nullableTrim($validated['source_uri'] ?? null),
                'content_text' => $this->nullableTrim($validated['content_text'] ?? null),
                'target_roles' => $this->parseTargetRoles($validated['target_roles'] ?? ''),
                'is_required' => $request->boolean('is_required'),
                'requires_acknowledgement' => $request->boolean('requires_acknowledgement'),
            ],
            'prerequisite_ids' => $prerequisiteIds,
        ];
    }

    private function createFormData(LearningModule $module, string $pageTitle, string $submitLabel, ?string $formIntro): array
    {
        return [
            'module' => $module,
            'owners' => \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'availablePrerequisites' => $allModules = LearningModule::query()
                ->orderBy('title')
                ->get(['id', 'title', 'status']),
            'formAction' => route('app.admin.modules.store'),
            'formMethod' => 'POST',
            'pageTitle' => $pageTitle,
            'submitLabel' => $submitLabel,
            'formIntro' => $formIntro,
            'latestScormAsset' => null,
            'scormSummary' => null,
            'scormAssetHistory' => collect(),
            'recentScormAttempts' => collect(),
            'moduleReadinessState' => $this->moduleReadinessState($module, null),
            'moduleVisibilityState' => $this->moduleVisibilityState($module),
            'moduleVisibilityImpact' => $this->moduleVisibilityImpact($module),
            'reinforcementQuestionSet' => null,
            'remediationModules' => $allModules,
            'topicOptions' => \App\Models\Topic::ordered()->pluck('name')->all(),
            'roleOptions' => \App\Models\Role::ordered()->pluck('name')->all(),
        ];
    }

    private function latestReinforcementQuestionSet(LearningModule $module): ?ReinforcementQuestionSet
    {
        if (! Schema::hasTable('reinforcement_question_sets')) {
            return null;
        }

        return ReinforcementQuestionSet::query()
            ->with(['questions', 'reviewer:id,name', 'learningAsset:id,learning_module_id,original_filename'])
            ->where('learning_module_id', $module->id)
            ->latest('id')
            ->first();
    }

    private function moduleReadinessState(LearningModule $module, ?array $scormSummary): array
    {
        $blockers = collect();

        if (($module->review_status ?? 'draft') !== 'approved') {
            $blockers->push('Module review status must be approved before publishing.');
        }

        if (! filled($module->title)) {
            $blockers->push('Module title is required.');
        }

        if (! filled($module->description)) {
            $blockers->push('Module description is required.');
        }

        if (($module->source_type ?? 'manual') === 'scorm') {
            if (! $scormSummary || ! ($scormSummary['has_package'] ?? false)) {
                $blockers->push('A SCORM package must be uploaded before learner launch can work.');
            } elseif (($scormSummary['package_status'] ?? null) !== 'processed') {
                $blockers->push('The current SCORM package must be processed successfully.');
            }

            if (! filled($scormSummary['launch_path'] ?? null)) {
                $blockers->push('A valid SCORM launch path must be detected from the manifest.');
            }
        }

        $checklist = collect([
            [
                'label' => 'Review approved',
                'passed' => ($module->review_status ?? 'draft') === 'approved',
                'detail' => ucfirst(str_replace('_', ' ', $module->review_status ?? 'draft')),
            ],
            [
                'label' => 'Core metadata complete',
                'passed' => filled($module->title) && filled($module->description),
                'detail' => filled($module->title) && filled($module->description) ? 'title and description present' : 'title or description missing',
            ],
            [
                'label' => 'Launch path ready',
                'passed' => ($module->source_type ?? 'manual') !== 'scorm' || (($scormSummary['package_status'] ?? null) === 'processed' && filled($scormSummary['launch_path'] ?? null)),
                'detail' => ($module->source_type ?? 'manual') === 'scorm'
                    ? (filled($scormSummary['launch_path'] ?? null) ? (string) $scormSummary['launch_path'] : 'launch path missing')
                    : 'manual/pdf flow',
            ],
        ])->values();

        return [
            'can_publish' => $blockers->isEmpty(),
            'blockers' => $blockers->values()->all(),
            'checklist' => $checklist->all(),
            'preferred_fix_href' => $this->moduleReadinessFixHref($module, $blockers->all()),
        ];
    }

    private function moduleReadinessFixHref(LearningModule $module, array $blockers): string
    {
        $blockerText = strtolower(implode(' ', $blockers));

        if (str_contains($blockerText, 'scorm package') || str_contains($blockerText, 'launch path') || str_contains($blockerText, 'manifest')) {
            return '#scorm-package';
        }

        if (str_contains($blockerText, 'review status')) {
            return '#field-review-status';
        }

        if (str_contains($blockerText, 'title')) {
            return '#field-title';
        }

        if (str_contains($blockerText, 'description')) {
            return '#field-description';
        }

        return ($module->source_type ?? 'manual') === 'scorm' ? '#scorm-package' : '#field-status';
    }

    private function moduleVisibilityState(LearningModule $module): array
    {
        $targetRoles = collect($module->target_roles ?? [])
            ->filter()
            ->values();

        $signals = collect();

        if (($module->status ?? 'draft') !== 'published') {
            $signals->push('Learners will not see this in their dashboard until the module status is published.');
        }

        if ($module->available_from && $module->available_from->isFuture()) {
            $signals->push('The availability window has not opened yet, so learners will not see it until '.$module->available_from->format('M d, Y H:i').'.');
        }

        if ($module->available_until && $module->available_until->isPast()) {
            $signals->push('The availability window has already closed, so learners will not see it.');
        }

        if ($targetRoles->isEmpty()) {
            $signals->push('No target roles are set, so this module can be shown to all eligible learners.');
        } else {
            $signals->push('Targeted roles: '.$targetRoles->join(', ').'. Only matching learners will see it.');
        }

        if (! filled($module->compliance_area)) {
            $signals->push('No compliance area is set, so compliance-driven assignment views may not group this module clearly.');
        }

        if (($module->prerequisites_count ?? $module->prerequisites()->count()) > 0) {
            $signals->push(($module->prerequisites_count ?? $module->prerequisites()->count()).' prerequisite module(s) must be completed before learners can open this module.');
        }

        $timingState = match (true) {
            $module->available_until && $module->available_until->isPast() => 'expired',
            $module->available_from && $module->available_from->isFuture() => 'scheduled',
            ($module->status ?? 'draft') !== 'published' => 'not live',
            default => 'live now',
        };

        return [
            'timing_state' => $timingState,
            'timing_label' => match ($timingState) {
                'expired' => 'expired',
                'scheduled' => 'scheduled',
                'not live' => 'not live',
                default => 'live now',
            },
            'audience_label' => $targetRoles->isEmpty() ? 'all roles' : 'role-targeted',
            'audience_detail' => $targetRoles->isEmpty() ? 'visible to every eligible role' : $targetRoles->join(', '),
            'window_label' => $module->available_from || $module->available_until
                ? (($module->available_from?->format('Y-m-d H:i') ?? 'now').' -> '.($module->available_until?->format('Y-m-d H:i') ?? 'open'))
                : 'always on',
            'signals' => $signals->values()->all(),
        ];
    }

    private function moduleVisibilityImpact(LearningModule $module): array
    {
        $assignmentService = app(AssignmentService::class);
        $learnerUsers = User::query()
            ->with('preference')
            ->where('is_admin', false)
            ->get();

        $schedule = $assignmentService->scheduleStatus($module);

        // Pre-compute target roles once (pure in-memory, no DB)
        $targetRoles = collect($module->target_roles ?? [])
            ->filter()
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->values()
            ->all();
        $hasTargetRoles = $targetRoles !== [];

        $roleConfiguredLearners = $learnerUsers
            ->filter(fn (User $user) => filled($user->preference?->role))
            ->values();

        // Role matching — pure in-memory string comparison
        $roleMatchedLearners = $roleConfiguredLearners
            ->filter(function (User $user) use ($hasTargetRoles, $targetRoles) {
                if (! $hasTargetRoles) {
                    return true;
                }
                $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
                return $userRole !== '' && in_array($userRole, $targetRoles, true);
            })
            ->values();

        // Compliance matching — bulk-load compliance_role_rules once
        $complianceArea = strtolower(trim((string) ($module->compliance_area ?? '')));
        $needsCompliance = $module->is_required && $complianceArea !== '';

        if ($needsCompliance && Schema::hasTable('compliance_role_rules')) {
            $uniqueRoles = $roleMatchedLearners
                ->map(fn (User $user) => strtolower(trim((string) ($user->preference?->role ?? ''))))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $complianceByRole = \App\Models\ComplianceRoleRule::query()
                ->whereIn('role', $uniqueRoles)
                ->get()
                ->groupBy(fn ($rule) => strtolower(trim($rule->role)))
                ->map(fn ($rules) => $rules->pluck('compliance_area')->map(fn ($a) => strtolower(trim((string) $a)))->filter()->unique()->values()->all())
                ->all();
        } else {
            $complianceByRole = [];
        }

        $complianceMatchedLearners = $roleMatchedLearners
            ->filter(function (User $user) use ($needsCompliance, $complianceArea, $complianceByRole) {
                if (! $needsCompliance) {
                    return true;
                }
                $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
                $inherited = $complianceByRole[$userRole]
                    ?? collect(config('learning_assignments.role_compliance_areas.' . $userRole, []))
                        ->filter()->map(fn ($a) => strtolower(trim((string) $a)))->unique()->values()->all();
                return in_array($complianceArea, $inherited, true);
            })
            ->values();

        // Prerequisites — bulk-load completed progress once
        $prerequisites = $module->relationLoaded('prerequisites')
            ? $module->prerequisites
            : $module->prerequisites()->get(['learning_modules.id']);
        $requiredIds = $prerequisites->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        if ($requiredIds !== [] && Schema::hasTable('learning_module_prerequisites')) {
            $userIdsWithAllPrereqs = ModuleProgress::query()
                ->whereIn('learning_module_id', $requiredIds)
                ->where('status', 'completed')
                ->select('user_id')
                ->selectRaw('COUNT(DISTINCT learning_module_id) as completed_count')
                ->groupBy('user_id')
                ->havingRaw('COUNT(DISTINCT learning_module_id) >= ?', [count($requiredIds)])
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $prereqReadySet = array_flip($userIdsWithAllPrereqs);
        } else {
            $prereqReadySet = null; // null = no prerequisites, all pass
        }

        $prerequisiteReadyLearners = $complianceMatchedLearners
            ->filter(fn (User $user) => $prereqReadySet === null || isset($prereqReadySet[$user->id]))
            ->values();

        // Waivers — bulk-load once
        if (Schema::hasTable('assignment_waivers')) {
            $waivedUserIds = \App\Models\AssignmentWaiver::query()
                ->where('learning_module_id', $module->id)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->flip()
                ->all();
        } else {
            $waivedUserIds = [];
        }

        $canSeeNow = (($module->status ?? 'draft') === 'published')
            ? $prerequisiteReadyLearners
                ->filter(fn (User $user) => $schedule['is_open'] && ! isset($waivedUserIds[$user->id]))
                ->values()
            : collect();

        $blockedByRole = $roleConfiguredLearners
            ->reject(function (User $user) use ($hasTargetRoles, $targetRoles) {
                if (! $hasTargetRoles) {
                    return true;
                }
                $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
                return $userRole !== '' && in_array($userRole, $targetRoles, true);
            })
            ->values();

        $blockedByCompliance = $roleMatchedLearners
            ->reject(function (User $user) use ($needsCompliance, $complianceArea, $complianceByRole) {
                if (! $needsCompliance) {
                    return true;
                }
                $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
                $inherited = $complianceByRole[$userRole]
                    ?? collect(config('learning_assignments.role_compliance_areas.' . $userRole, []))
                        ->filter()->map(fn ($a) => strtolower(trim((string) $a)))->unique()->values()->all();
                return in_array($complianceArea, $inherited, true);
            })
            ->values();

        $blockedByPrerequisites = $complianceMatchedLearners
            ->reject(fn (User $user) => $prereqReadySet === null || isset($prereqReadySet[$user->id]))
            ->values();

        $blockedByWaiver = $prerequisiteReadyLearners
            ->filter(fn (User $user) => isset($waivedUserIds[$user->id]))
            ->values();

        $blockedBySchedule = (($module->status ?? 'draft') === 'published' && ! $schedule['is_open'])
            ? $prerequisiteReadyLearners
                ->reject(fn (User $user) => isset($waivedUserIds[$user->id]))
                ->values()
            : collect();

        $counts = [
            'learners_total' => $roleConfiguredLearners->count(),
            'role_matched' => $roleMatchedLearners->count(),
            'compliance_matched' => $complianceMatchedLearners->count(),
            'prerequisite_ready' => $prerequisiteReadyLearners->count(),
            'visible_now' => $canSeeNow->count(),
            'blocked_role' => $blockedByRole->count(),
            'blocked_compliance' => $blockedByCompliance->count(),
            'blocked_prerequisites' => $blockedByPrerequisites->count(),
            'blocked_schedule' => $blockedBySchedule->count(),
            'blocked_waiver' => $blockedByWaiver->count(),
        ];

        $signals = collect();
        $signals->push(sprintf(
            '%d of %d learner accounts can see this module right now.',
            $canSeeNow->count(),
            $roleConfiguredLearners->count()
        ));

        if (($module->target_roles ?? []) !== []) {
            $signals->push(sprintf(
                '%d learner accounts match the targeted roles.',
                $roleMatchedLearners->count()
            ));
        }

        if ($module->is_required && filled($module->compliance_area)) {
            $signals->push(sprintf(
                '%d learner accounts match the compliance scope for %s.',
                $complianceMatchedLearners->count(),
                $module->compliance_area
            ));
        }

        if ($blockedByPrerequisites->isNotEmpty()) {
            $signals->push(sprintf(
                '%d learner accounts are still blocked by prerequisites.',
                $blockedByPrerequisites->count()
            ));
        }

        if ($blockedByRole->isNotEmpty()) {
            $signals->push(sprintf(
                '%d learner accounts are outside the targeted role scope.',
                $blockedByRole->count()
            ));
        }

        if ($blockedByCompliance->isNotEmpty()) {
            $signals->push(sprintf(
                '%d learner accounts do not inherit the required compliance area.',
                $blockedByCompliance->count()
            ));
        }

        if ($blockedBySchedule->isNotEmpty()) {
            $signals->push(sprintf(
                '%d learner accounts are ready but blocked by the current availability window.',
                $blockedBySchedule->count()
            ));
        }

        if ($blockedByWaiver->isNotEmpty()) {
            $signals->push(sprintf(
                '%d learner accounts are waived and will not see this module.',
                $blockedByWaiver->count()
            ));
        }

        $headline = match (true) {
            $canSeeNow->isNotEmpty() => sprintf('%d learners can see it now', $canSeeNow->count()),
            ($module->status ?? 'draft') !== 'published' => 'No learners can see it until it is published',
            $module->available_from && $module->available_from->isFuture() => 'No learners can see it until the start window opens',
            $module->available_until && $module->available_until->isPast() => 'No learners can see it because the window has closed',
            $roleMatchedLearners->isEmpty() && ($module->target_roles ?? []) !== [] => 'No learners currently match the targeted roles',
            $complianceMatchedLearners->isEmpty() && $module->is_required && filled($module->compliance_area) => 'No learners currently match the compliance scope',
            $blockedByPrerequisites->isNotEmpty() => 'Eligible learners are currently blocked by prerequisites',
            default => 'Learner visibility is limited by current assignment rules',
        };

        $primaryBlocker = collect([
            ['label' => 'Blocked by role targeting', 'count' => $counts['blocked_role']],
            ['label' => 'Blocked by compliance scope', 'count' => $counts['blocked_compliance']],
            ['label' => 'Blocked by prerequisites', 'count' => $counts['blocked_prerequisites']],
            ['label' => 'Blocked by schedule window', 'count' => $counts['blocked_schedule']],
            ['label' => 'Blocked by waivers', 'count' => $counts['blocked_waiver']],
        ])->sortByDesc('count')->firstWhere('count', '>', 0);

        $recommendedActions = collect();

        $preferredFixHref = '#field-target-roles';

        if (($module->status ?? 'draft') !== 'published') {
            $recommendedActions->push([
                'label' => 'Publish the module when review approval and launch readiness are complete so learners can actually see it.',
                'href' => '#field-status',
            ]);
            $preferredFixHref = '#field-status';
        }

        if ($counts['blocked_role'] > 0) {
            $recommendedActions->push([
                'label' => 'Adjust the target roles if this module should reach a wider learner group, or confirm the intended role scope.',
                'href' => '#field-target-roles',
            ]);
            $preferredFixHref = $preferredFixHref === '#field-status' ? $preferredFixHref : '#field-target-roles';
        }

        if ($counts['blocked_compliance'] > 0) {
            $recommendedActions->push([
                'label' => 'Align the compliance area with the assignment rules for the intended learner roles so required learners inherit it correctly.',
                'href' => '#field-compliance-area',
            ]);
            if ($preferredFixHref === '#field-target-roles') {
                $preferredFixHref = '#field-compliance-area';
            }
        }

        if ($counts['blocked_prerequisites'] > 0) {
            $recommendedActions->push([
                'label' => 'Review the prerequisite chain or complete the upstream modules first if this content is intentionally gated.',
                'href' => '#field-prerequisites',
            ]);
            if ($preferredFixHref === '#field-target-roles') {
                $preferredFixHref = '#field-prerequisites';
            }
        }

        if ($counts['blocked_schedule'] > 0) {
            $recommendedActions->push([
                'label' => 'Bring the availability window forward or remove the date restriction if learners should access this now.',
                'href' => '#field-availability-window',
            ]);
            if ($preferredFixHref === '#field-target-roles') {
                $preferredFixHref = '#field-availability-window';
            }
        }

        if ($counts['visible_now'] === 0 && $roleConfiguredLearners->isEmpty()) {
            $recommendedActions->push([
                'label' => 'No learner accounts have role preferences set yet, so the dashboard cannot target this module meaningfully.',
                'href' => '#field-target-roles',
            ]);
        }

        return [
            'headline' => $headline,
            'counts' => $counts,
            'primary_blocker' => $primaryBlocker,
            'example_names' => $canSeeNow->pluck('name')->take(3)->values()->all(),
            'preferred_fix_href' => $preferredFixHref,
            'recommended_actions' => $recommendedActions
                ->unique(fn (array $action) => $action['label'].'|'.$action['href'])
                ->values()
                ->all(),
            'signals' => $signals->values()->all(),
        ];
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseReinforcementIntervals(?string $value): array
    {
        $intervals = collect(explode(',', (string) $value))
            ->map(fn ($item) => (int) trim((string) $item))
            ->filter(fn (int $days) => $days > 0 && $days <= 3650)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $intervals !== [] ? $intervals : [7, 30];
    }

    private function parseTargetRoles(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $role) => strtolower(trim($role)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function nullableDateTime(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function scormSummary(LearningModule $module): ?array
    {
        $asset = $module->relationLoaded('assets')
            ? $module->assets
                ->where('asset_type', 'scorm_package')
                ->where('status', 'processed')
                ->sortByDesc('id')
                ->first()
            : $module->latestScormAsset();

        if (! $asset && $module->source_type !== 'scorm') {
            return null;
        }

        $progressQuery = ModuleProgress::query()->where('learning_module_id', $module->id);
        $lastRuntimeAt = LearningEvent::query()
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->where('entity_id', $module->id)
            ->latest('created_at')
            ->value('created_at');
        $runtimeEvents = LearningEvent::query()
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->where('entity_id', $module->id)
            ->get();

        return [
            'has_package' => $asset !== null,
            'package_status' => $asset?->status ?? 'not_uploaded',
            'launch_path' => $asset?->launch_path,
            'last_runtime_at' => $lastRuntimeAt,
            'learner_count' => (clone $progressQuery)->count(),
            'completed_count' => (clone $progressQuery)->where('status', 'completed')->count(),
            'in_progress_count' => (clone $progressQuery)->where('status', 'in_progress')->count(),
            'average_score' => (int) round((float) ($runtimeEvents->pluck('metadata.score_raw')->filter(fn ($value) => $value !== null)->avg() ?? 0)),
            'total_session_label' => ScormRuntimeMetrics::formatSeconds((int) $runtimeEvents->map(function (LearningEvent $event) {
                return isset($event->metadata['session_seconds'])
                    ? (int) $event->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null);
            })->filter(fn ($value) => $value !== null)->sum()),
        ];
    }

    private function recordDemoResetAuditEvent(array $resetState): void
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'entity_type' => 'scorm_demo',
            'entity_id' => 1,
            'action' => 'scorm_demo_reset',
            'meta' => [
                'status' => $resetState['status'] ?? 'completed',
                'message' => $resetState['message'] ?? 'SCORM demo data reset completed.',
                'counts' => $resetState['counts'] ?? [],
            ],
        ]);
    }
}
