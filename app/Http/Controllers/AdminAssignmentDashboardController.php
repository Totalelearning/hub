<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAuditEvent;
use App\Models\AssignmentReminder;
use App\Models\AssignmentWaiver;
use App\Models\ComplianceRoleRule;
use App\Models\Course;
use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\ModuleAcknowledgement;
use App\Models\ModuleProgress;
use App\Models\ReinforcementTouchpoint;
use App\Models\User;
use App\Services\AssignmentSettingsService;
use App\Services\AssignmentService;
use App\Services\FeedScoringSettingsService;
use App\Services\RankingHealthService;
use App\Services\ReminderService;
use App\Support\RankingHealthCopy;
use App\Support\RankingHealthLatencyFormatter;
use App\Support\RankingHealthMessages;
use App\Support\RankingHealthOptions;
use App\Support\ScormRuntimeMetrics;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminAssignmentDashboardController extends Controller
{
    public function __invoke(Request $request, AssignmentService $assignments): View
    {
        Gate::authorize('admin-access');

        $focus = $this->normalizeFocus($request->query('focus'));
        $rankingProvider = $this->normalizeRankingProvider($request->query('ranking_provider'));
        $rankingSeverityTrigger = $this->normalizeRankingSeverityTrigger($request->query('ranking_severity_trigger'));
        $rankingExportFrom = $this->normalizeRankingExportDate($request->query('ranking_export_from'));
        $rankingExportTo = $this->normalizeRankingExportDate($request->query('ranking_export_to'));
        $providerOptions = RankingHealthOptions::providerOptions();
        $triggerOptions = RankingHealthOptions::severityTriggerOptions();
        $dashboardData = $this->dashboardData($assignments, app(ReminderService::class), $focus, $rankingProvider, $rankingSeverityTrigger);

        return view('app.admin-assignment-dashboard', [
            'currentFocus' => $focus,
            'selectedRankingProvider' => $rankingProvider ?? 'all',
            'rankingProbeProviderOptions' => $providerOptions,
            'selectedRankingSeverityTrigger' => $rankingSeverityTrigger ?? 'all',
            'rankingSeverityTriggerOptions' => $triggerOptions,
            'selectedRankingExportFrom' => $rankingExportFrom?->toDateString(),
            'selectedRankingExportTo' => $rankingExportTo?->toDateString(),
            'rankingHealthCopy' => RankingHealthCopy::dashboardPage(),
        ] + $dashboardData);
    }

    public function export(Request $request, AssignmentService $assignments): StreamedResponse
    {
        Gate::authorize('admin-access');

        $focus = $this->normalizeFocus($request->query('focus'));
        $dashboardData = $this->dashboardData($assignments, app(ReminderService::class), $focus);
        $focusRows = $dashboardData['focusRows'];
        $latestScormProof = $dashboardData['recentScormCompletions']->first();
        $latestReinforcementProof = $dashboardData['recentReinforcementProof']->first();
        $filename = sprintf('assignments-%s-%s.csv', $focus, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($focusRows, $latestScormProof, $latestReinforcementProof): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['role', 'learner_name', 'learner_email', 'module_title', 'compliance_area', 'urgency', 'due_date', 'notes']);

            if ($latestScormProof !== null) {
                fputcsv($handle, [
                    'latest_scorm_proof',
                    $latestScormProof['learner_name'] ?? '',
                    $latestScormProof['learner_email'] ?? '',
                    $latestScormProof['module_title'] ?? '',
                    '',
                    $latestScormProof['scorm_status'] ?? '',
                    $latestScormProof['completed_at']?->toDateString() ?? '',
                    'score=' . ($latestScormProof['score_raw'] ?? 'n/a')
                        . '; session=' . ($latestScormProof['session_label'] ?? 'n/a')
                        . '; percent=' . ($latestScormProof['percent_complete'] ?? 'n/a')
                        . '; location=' . ($latestScormProof['lesson_location'] ?? 'n/a'),
                ]);
            }

            if ($latestReinforcementProof !== null) {
                fputcsv($handle, [
                    'latest_reinforcement_proof',
                    $latestReinforcementProof['learner_name'] ?? '',
                    $latestReinforcementProof['learner_email'] ?? '',
                    $latestReinforcementProof['module_title'] ?? '',
                    '',
                    ($latestReinforcementProof['interval_days'] ?? 'n/a').'-day '.($latestReinforcementProof['proof_type'] ?? 'follow_up'),
                    $latestReinforcementProof['completed_at']?->toDateString() ?? '',
                    ($latestReinforcementProof['proof_summary'] ?? 'Proof recorded')
                        . '; due=' . ($latestReinforcementProof['due_on']?->toDateString() ?? 'n/a'),
                ]);
            }

            foreach ($focusRows as $row) {
                fputcsv($handle, [
                    $row['role'],
                    $row['learner_name'],
                    $row['learner_email'],
                    $row['module_title'],
                    $row['compliance_area'],
                    $row['urgency'],
                    $row['renewal_due_at']?->toDateString() ?? '',
                    $row['waiver_reason'] ?: ('Progress: ' . $row['progress_status']),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSettings(
        AssignmentSettingsService $assignmentSettings,
        FeedScoringSettingsService $feedScoringSettings,
    ): StreamedResponse {
        Gate::authorize('admin-access');

        $filename = sprintf('assignment-settings-%s.csv', now()->format('Ymd-His'));
        $reminder = $assignmentSettings->all();
        $scoring = $feedScoringSettings->allWeights();

        return response()->streamDownload(function () use ($reminder, $scoring): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['category', 'key', 'value']);

            foreach ($reminder as $key => $value) {
                fputcsv($handle, ['reminder', $key, (int) $value]);
            }

            foreach ($scoring as $key => $value) {
                fputcsv($handle, ['feed_scoring', $key, (int) $value]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function syncReminders(ReminderService $reminders): RedirectResponse
    {
        Gate::authorize('admin-access');

        $created = $reminders->syncPending();

        return redirect()
            ->route('app.admin.assignments')
            ->with('status', sprintf('Reminder queue synced. %d pending reminders available.', $created->count()));
    }

    public function runReminders(Request $request): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'mode' => ['nullable', 'in:sync_and_send,sync_only,send_only'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'types' => ['nullable', 'string', 'max:255'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $mode = (string) ($validated['mode'] ?? 'sync_and_send');
        $options = [
            '--limit' => (int) ($validated['limit'] ?? 100),
        ];
        if ($mode === 'sync_only') {
            $options['--sync-only'] = true;
        }
        if ($mode === 'send_only') {
            $options['--send-only'] = true;
        }
        if (filled($validated['types'] ?? null)) {
            $options['--types'] = strtolower(trim((string) $validated['types']));
        }
        if ((bool) ($validated['dry_run'] ?? false)) {
            $options['--dry-run'] = true;
        }

        $exitCode = Artisan::call('assignments:run-reminders', $options);
        $commandOutput = trim((string) Artisan::output());
        $status = $exitCode === 0
            ? 'Reminder run completed.'
            : sprintf('Reminder run failed (exit code %d).', $exitCode);

        if ($commandOutput !== '') {
            $status .= ' '.$commandOutput;
        }

        return redirect()
            ->route('app.admin.assignments')
            ->with('status', $status);
    }

    public function markReminderSent(AssignmentReminder $reminder, ReminderService $reminders): RedirectResponse
    {
        Gate::authorize('admin-access');

        $reminders->markSent($reminder->loadMissing(['user.preference', 'module']));

        return redirect()
            ->route('app.admin.assignments')
            ->with('status', 'Reminder marked as sent.');
    }

    public function audit(Request $request): View
    {
        Gate::authorize('admin-access');

        $action = $this->normalizeAuditAction($request->query('action'));
        $search = trim((string) $request->query('q', ''));
        $scope = $this->normalizeAuditScope(
            $request->query('actor'),
            $request->query('target'),
            $request->query('module'),
        );
        $dateRange = $this->normalizeAuditDateRange(
            $request->query('from'),
            $request->query('to'),
        );
        $auditEvents = $this->auditEvents($action, $search, $dateRange, $scope);

        return view('app.admin-assignment-audit', [
            'currentAction' => $action,
            'search' => $search,
            'scope' => $scope,
            'dateRange' => $dateRange,
            'auditEvents' => $auditEvents,
            'summary' => $this->auditSummary($auditEvents),
            'datePresets' => $this->auditDatePresets(),
        ]);
    }

    public function auditExport(Request $request): StreamedResponse
    {
        Gate::authorize('admin-access');

        $action = $this->normalizeAuditAction($request->query('action'));
        $search = trim((string) $request->query('q', ''));
        $scope = $this->normalizeAuditScope(
            $request->query('actor'),
            $request->query('target'),
            $request->query('module'),
        );
        $dateRange = $this->normalizeAuditDateRange(
            $request->query('from'),
            $request->query('to'),
        );
        $auditEvents = $this->auditEvents($action, $search, $dateRange, $scope);
        $filename = sprintf('assignment-audit-%s-%s.csv', $action, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($auditEvents): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['when', 'actor', 'action', 'target_user', 'module', 'details']);

            foreach ($auditEvents as $event) {
                fputcsv($handle, [
                    $event->created_at?->toDateTimeString() ?? '',
                    $event->actor?->name ?? 'system',
                    $event->action,
                    $event->targetUser?->email ?? '',
                    $event->module?->title ?? ($event->meta['module_title'] ?? ''),
                    $this->auditEventDetails($event),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function role(string $role, AssignmentService $assignments): View
    {
        Gate::authorize('admin-access');

        $normalizedRole = strtolower(trim($role));

        $users = User::query()
            ->with('preference')
            ->forManagedScope(auth()->user())
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => strtolower((string) $user->preference?->role) === $normalizedRole)
            ->values();

        $requiredModules = LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true)
            ->orderBy('compliance_area')
            ->orderBy('title')
            ->get();

        $progressByUserAndModule = ModuleProgress::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('learning_module_id', $requiredModules->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $rows) => $rows->keyBy('learning_module_id'));

        $userRows = $users->map(function (User $user) use ($requiredModules, $progressByUserAndModule, $assignments) {
            $userProgress = $progressByUserAndModule->get($user->id, collect());

            $assignmentRows = $requiredModules
                ->map(function (LearningModule $module) use ($assignments, $user, $userProgress) {
                    $assignment = $assignments->forUser($user, $module, $userProgress->get($module->id));

                    if (! $assignment['is_required'] && ! $assignment['is_waived']) {
                        return null;
                    }

                    return [
                        'title' => $module->title,
                        'compliance_area' => $module->compliance_area ?: 'unscoped',
                        'urgency' => $assignment['urgency'],
                    ];
                })
                ->filter()
                ->values();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'assigned_required_count' => $assignmentRows->where('urgency', '!=', 'waived')->count(),
                'overdue_count' => $assignmentRows->where('urgency', 'overdue')->count(),
                'due_soon_count' => $assignmentRows->where('urgency', 'due_soon')->count(),
                'waived_count' => $assignmentRows->where('urgency', 'waived')->count(),
                'modules' => $assignmentRows,
            ];
        });

        return view('app.admin-assignment-role-detail', [
            'role' => $normalizedRole,
            'userRows' => $userRows,
        ]);
    }

    public function complianceArea(string $area, AssignmentService $assignments): View
    {
        Gate::authorize('admin-access');

        $normalizedArea = strtolower(trim($area));

        $modules = LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true)
            ->where('compliance_area', $normalizedArea)
            ->orderBy('title')
            ->get();

        $users = User::query()
            ->with('preference')
            ->forManagedScope(auth()->user())
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => filled($user->preference?->role))
            ->values();

        $progressByUserAndModule = ModuleProgress::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('learning_module_id', $modules->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $rows) => $rows->keyBy('learning_module_id'));

        $moduleRows = $modules->map(function (LearningModule $module) use ($users, $progressByUserAndModule, $assignments) {
            $assignedUsers = $users->map(function (User $user) use ($module, $progressByUserAndModule, $assignments) {
                $assignment = $assignments->forUser($user, $module, $progressByUserAndModule->get($user->id, collect())->get($module->id));

                if (! $assignment['is_required'] && ! $assignment['is_waived']) {
                    return null;
                }

                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => strtolower((string) $user->preference?->role),
                    'urgency' => $assignment['urgency'],
                ];
            })->filter()->values();

            return [
                'title' => $module->title,
                'refresh_interval_days' => $module->refresh_interval_days,
                'assigned_user_count' => $assignedUsers->where('urgency', '!=', 'waived')->count(),
                'overdue_count' => $assignedUsers->where('urgency', 'overdue')->count(),
                'due_soon_count' => $assignedUsers->where('urgency', 'due_soon')->count(),
                'waived_count' => $assignedUsers->where('urgency', 'waived')->count(),
                'assigned_users' => $assignedUsers,
            ];
        });

        return view('app.admin-assignment-compliance-detail', [
            'complianceArea' => $normalizedArea,
            'moduleRows' => $moduleRows,
        ]);
    }

    public function user(User $user, AssignmentService $assignments): View
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $detailData = $this->learnerDetailData($user, $assignments);

        return view('app.admin-assignment-user-detail', [
            'learner' => $user,
        ] + $detailData);
    }

    public function userExport(User $user, AssignmentService $assignments): StreamedResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $detailData = $this->learnerDetailData($user, $assignments);
        $latestScormProof = $this->latestLearnerScormProof($user);
        $latestReinforcementProof = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->latest('completed_at')
                ->first()
            : null;
        $filename = sprintf('learner-assignments-%s-%s.csv', $user->id, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($detailData, $latestScormProof, $latestReinforcementProof): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'record_type',
                'learner_name',
                'learner_email',
                'module_title',
                'compliance_area',
                'urgency_or_action',
                'due_or_when',
                'details',
            ]);

            if ($latestScormProof !== null) {

                fputcsv($handle, [
                    'latest_scorm_proof',
                    $detailData['learner']->name,
                    $detailData['learner']->email,
                    $latestScormProof['module_title'],
                    '',
                    $latestScormProof['status'],
                    $latestScormProof['when']?->toDateTimeString() ?? '',
                    'score=' . ($latestScormProof['score_raw'] ?? 'n/a')
                        . '; session=' . $latestScormProof['session_label']
                        . '; percent=' . ($latestScormProof['percent_complete'] ?? 'n/a')
                        . '; location=' . ($latestScormProof['lesson_location'] ?? 'n/a'),
                ]);
            }

            if ($latestReinforcementProof !== null) {
                fputcsv($handle, [
                    'latest_reinforcement_proof',
                    $detailData['learner']->name,
                    $detailData['learner']->email,
                    $latestReinforcementProof->module?->title ?? ('Module #'.$latestReinforcementProof->learning_module_id),
                    '',
                    $latestReinforcementProof->interval_days.'-day '.$latestReinforcementProof->proof_type,
                    $latestReinforcementProof->completed_at?->toDateTimeString() ?? '',
                    ($latestReinforcementProof->proof_summary ?: 'Proof recorded')
                        . '; due=' . ($latestReinforcementProof->due_on?->toDateString() ?? 'n/a'),
                ]);
            }

            foreach ($detailData['moduleRows'] as $row) {
                $details = !empty($row['role_targeting']['target_roles'])
                    ? 'role=' . implode(', ', $row['role_targeting']['target_roles'])
                    : 'role=all';

                if (! empty($row['compliance_targeting']['compliance_area'])) {
                    $details .= '; compliance=' . $row['compliance_targeting']['compliance_area'];
                }

                if ($row['waiver']) {
                    $details .= '; waiver=' . $row['waiver']['reason'];
                }

                $details .= '; progress=' . $row['progress_status'];

                fputcsv($handle, [
                    'assignment',
                    $detailData['learner']->name,
                    $detailData['learner']->email,
                    $row['title'],
                    $row['compliance_area'],
                    $row['urgency'],
                    $row['renewal_due_at']?->toDateString() ?? '',
                    $details,
                ]);
            }

            foreach ($detailData['recentAuditEvents'] as $event) {
                fputcsv($handle, [
                    'audit_event',
                    $detailData['learner']->name,
                    $detailData['learner']->email,
                    $event->module?->title ?? ($event->meta['module_title'] ?? ''),
                    $event->module?->compliance_area ?? '',
                    $event->action,
                    $event->created_at?->toDateTimeString() ?? '',
                    $this->auditEventDetails($event),
                ]);
            }

            foreach ($detailData['recentScormAttempts'] as $attempt) {
                fputcsv($handle, [
                    'scorm_attempt',
                    $detailData['learner']->name,
                    $detailData['learner']->email,
                    $attempt['module_title'],
                    '',
                    $attempt['status'],
                    $attempt['when']?->toDateTimeString() ?? '',
                    'score=' . ($attempt['score_raw'] ?? 'n/a') . '; session=' . $attempt['session_label'] . '; percent=' . ($attempt['percent_complete'] ?? 'n/a') . '; location=' . ($attempt['lesson_location'] ?? 'n/a'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function userEvents(User $user, Request $request): View
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $eventType = trim((string) $request->query('event_type', ''));
        $entityType = strtolower(trim((string) $request->query('entity_type', '')));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        if (! in_array($entityType, ['', 'learning_module', 'learning_path', 'user_preference'], true)) {
            $entityType = '';
        }

        $events = LearningEvent::query()
            ->where('user_id', $user->id)
            ->when($eventType !== '', fn ($query) => $query->where('event_type', $eventType))
            ->when($entityType !== '', fn ($query) => $query->where('entity_type', $entityType))
            ->when($from !== '', fn ($query) => $query->where('created_at', '>=', $from.' 00:00:00'))
            ->when($to !== '', fn ($query) => $query->where('created_at', '<=', $to.' 23:59:59'))
            ->latest()
            ->limit(300)
            ->get();
        $reinforcementTouchpoints = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with('module:id,title')
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->get()
            : collect();
        $moduleTitles = LearningModule::query()
            ->whereIn('id', $events->where('entity_type', 'learning_module')->pluck('entity_id')->map(fn ($id) => (int) $id)->unique()->values())
            ->pluck('title', 'id')
            ->toArray();
        $latestScormRuntimeEvent = $events->first(fn (LearningEvent $event) => $event->event_type === 'scorm_runtime_committed');
        $latestCompletedScormRuntimeEvent = $events->first(function (LearningEvent $event): bool {
            if ($event->event_type !== 'scorm_runtime_committed') {
                return false;
            }

            $status = Str::lower((string) ($event->metadata['status'] ?? $event->metadata['lesson_status'] ?? ''));
            $percentComplete = isset($event->metadata['percent_complete']) ? (int) $event->metadata['percent_complete'] : null;

            return in_array($status, ['completed', 'passed'], true) || ($percentComplete !== null && $percentComplete >= 100);
        });
        $latestScormLaunchEvent = $events->first(fn (LearningEvent $event) => $event->event_type === 'scorm_launched');
        $latestReinforcementProof = $reinforcementTouchpoints
            ->where('status', 'completed')
            ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => optional($touchpoint->completed_at)->getTimestamp() ?? 0)
            ->map(function (ReinforcementTouchpoint $touchpoint) {
                return [
                    'module_id' => (int) $touchpoint->learning_module_id,
                    'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                    'completed_at' => $touchpoint->completed_at,
                    'due_on' => $touchpoint->due_on,
                    'interval_days' => (int) $touchpoint->interval_days,
                    'proof_type' => $touchpoint->proof_type,
                    'proof_summary' => $touchpoint->proof_summary ?: 'Reinforcement proof recorded.',
                ];
            })
            ->first();
        $latestReinforcementFailure = $reinforcementTouchpoints
            ->where('status', 'needs_retry')
            ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => optional($touchpoint->updated_at)->getTimestamp() ?? 0)
            ->map(function (ReinforcementTouchpoint $touchpoint) {
                $remediationModuleIds = collect($touchpoint->metadata['last_remediation_module_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values();

                return [
                    'module_id' => (int) $touchpoint->learning_module_id,
                    'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                    'updated_at' => $touchpoint->updated_at,
                    'due_on' => $touchpoint->due_on,
                    'interval_days' => (int) $touchpoint->interval_days,
                    'proof_type' => $touchpoint->proof_type,
                    'proof_summary' => $touchpoint->proof_summary ?: 'Incorrect reinforcement answer recorded.',
                    'incorrect_count' => (int) ($touchpoint->metadata['last_incorrect_count'] ?? 0),
                    'remediation_count' => $remediationModuleIds->count(),
                    'remediation_titles' => LearningModule::query()->whereIn('id', $remediationModuleIds->all())->pluck('title')->values(),
                ];
            })
            ->first();

        return view('app.admin-assignment-user-events', [
            'learner' => $user,
            'events' => $events,
            'moduleTitles' => $moduleTitles,
            'eventMetadataSummary' => $events
                ->mapWithKeys(fn (LearningEvent $event) => [$event->id => $this->userEventMetadataSummary($event)])
                ->all(),
            'eventTypes' => LearningEvent::query()
                ->where('user_id', $user->id)
                ->distinct()
                ->orderBy('event_type')
                ->pluck('event_type'),
            'summary' => [
                'rows' => $events->count(),
                'scorm_launches' => $events->where('event_type', 'scorm_launched')->count(),
                'scorm_runtime_commits' => $events->where('event_type', 'scorm_runtime_committed')->count(),
                'reinforcement_completed' => $reinforcementTouchpoints->where('status', 'completed')->count(),
                'reinforcement_failed' => $reinforcementTouchpoints->where('status', 'needs_retry')->count(),
                'scorm_completed_commits' => $events->filter(function (LearningEvent $event): bool {
                    if ($event->event_type !== 'scorm_runtime_committed') {
                        return false;
                    }

                    $status = Str::lower((string) ($event->metadata['status'] ?? $event->metadata['lesson_status'] ?? ''));
                    $percentComplete = isset($event->metadata['percent_complete']) ? (int) $event->metadata['percent_complete'] : null;

                    return in_array($status, ['completed', 'passed'], true) || ($percentComplete !== null && $percentComplete >= 100);
                })->count(),
            ],
            'latestScormRuntimeProof' => $latestScormRuntimeEvent
                ? $this->buildUserEventProof($latestScormRuntimeEvent, $moduleTitles)
                : null,
            'latestCompletedScormProof' => $latestCompletedScormRuntimeEvent
                ? $this->buildUserEventProof($latestCompletedScormRuntimeEvent, $moduleTitles)
                : null,
            'latestScormLaunchProof' => $latestScormLaunchEvent
                ? $this->buildUserEventProof($latestScormLaunchEvent, $moduleTitles)
                : null,
            'latestReinforcementProof' => $latestReinforcementProof,
            'latestReinforcementFailure' => $latestReinforcementFailure,
            'filters' => [
                'event_type' => $eventType !== '' ? $eventType : null,
                'entity_type' => $entityType !== '' ? $entityType : null,
                'from' => $from !== '' ? $from : null,
                'to' => $to !== '' ? $to : null,
            ],
        ]);
    }

    public function userEventsExport(User $user, Request $request): StreamedResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $eventType = trim((string) $request->query('event_type', ''));
        $entityType = strtolower(trim((string) $request->query('entity_type', '')));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        if (! in_array($entityType, ['', 'learning_module', 'learning_path', 'user_preference'], true)) {
            $entityType = '';
        }

        $events = LearningEvent::query()
            ->where('user_id', $user->id)
            ->when($eventType !== '', fn ($query) => $query->where('event_type', $eventType))
            ->when($entityType !== '', fn ($query) => $query->where('entity_type', $entityType))
            ->when($from !== '', fn ($query) => $query->where('created_at', '>=', $from.' 00:00:00'))
            ->when($to !== '', fn ($query) => $query->where('created_at', '<=', $to.' 23:59:59'))
            ->latest()
            ->limit(1000)
            ->get();
        $moduleTitles = LearningModule::query()
            ->whereIn('id', $events->where('entity_type', 'learning_module')->pluck('entity_id')->map(fn ($id) => (int) $id)->unique()->values())
            ->pluck('title', 'id')
            ->toArray();

        $filename = sprintf('learner-events-%s-%s.csv', $user->id, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($events, $user, $moduleTitles): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'learner_name',
                'learner_email',
                'event_type',
                'entity_type',
                'entity_id',
                'entity_label',
                'status',
                'score_raw',
                'session',
                'percent_complete',
                'lesson_location',
                'launch_path',
                'asset_id',
                'metadata',
                'created_at',
            ]);
            foreach ($events as $event) {
                $entityLabel = $event->entity_type === 'learning_module'
                    ? ($moduleTitles[(int) $event->entity_id] ?? '')
                    : '';
                $proof = in_array($event->event_type, ['scorm_runtime_committed', 'scorm_launched'], true)
                    ? $this->buildUserEventProof($event, $moduleTitles)
                    : null;

                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $event->event_type,
                    $event->entity_type,
                    $event->entity_id,
                    $entityLabel,
                    $proof['status'] ?? '',
                    $proof['score_raw'] ?? '',
                    $proof['session_label'] ?? '',
                    $proof['percent_complete'] ?? '',
                    $proof['lesson_location'] ?? '',
                    $proof['launch_path'] ?? '',
                    $proof['asset_id'] ?? '',
                    $this->userEventMetadataSummary($event),
                    $event->created_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeRule(Request $request): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'role' => ['required', 'string', 'max:100'],
            'compliance_area' => ['required', 'string', 'max:100'],
        ]);

        ComplianceRoleRule::query()->updateOrCreate(
            [
                'role' => strtolower(trim($validated['role'])),
                'compliance_area' => strtolower(trim($validated['compliance_area'])),
            ],
            [],
        );

        $this->recordAuditEvent(
            action: 'rule_saved',
            entityType: 'compliance_role_rule',
            entityId: null,
            meta: [
                'role' => strtolower(trim($validated['role'])),
                'compliance_area' => strtolower(trim($validated['compliance_area'])),
            ],
        );

        return redirect()->route('app.admin.assignments')->with('status', 'Assignment rule added.');
    }

    public function destroyRule(ComplianceRoleRule $rule): RedirectResponse
    {
        Gate::authorize('admin-access');

        $this->recordAuditEvent(
            action: 'rule_removed',
            entityType: 'compliance_role_rule',
            entityId: $rule->id,
            meta: [
                'role' => $rule->role,
                'compliance_area' => $rule->compliance_area,
            ],
        );

        $rule->delete();

        return redirect()->route('app.admin.assignments')->with('status', 'Assignment rule removed.');
    }

    public function storeWaiver(Request $request, User $user, LearningModule $module): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        AssignmentWaiver::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'learning_module_id' => $module->id,
            ],
            [
                'created_by' => (int) auth()->id(),
                'reason' => trim((string) ($validated['reason'] ?? '')) ?: 'Admin waiver',
            ],
        );

        $this->recordAuditEvent(
            action: 'waiver_saved',
            entityType: 'assignment_waiver',
            entityId: null,
            targetUserId: $user->id,
            learningModuleId: $module->id,
            meta: [
                'reason' => trim((string) ($validated['reason'] ?? '')) ?: 'Admin waiver',
                'learner_email' => $user->email,
                'module_title' => $module->title,
            ],
        );

        return redirect()
            ->route('app.admin.assignments.user', ['user' => $user->id])
            ->with('status', 'Assignment waived for learner.');
    }

    public function destroyWaiver(User $user, LearningModule $module): RedirectResponse
    {
        Gate::authorize('admin-access');

        $existingWaiver = AssignmentWaiver::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->id)
            ->first();

        $this->recordAuditEvent(
            action: 'waiver_removed',
            entityType: 'assignment_waiver',
            entityId: $existingWaiver?->id,
            targetUserId: $user->id,
            learningModuleId: $module->id,
            meta: [
                'reason' => $existingWaiver?->reason,
                'learner_email' => $user->email,
                'module_title' => $module->title,
            ],
        );

        AssignmentWaiver::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->id)
            ->delete();

        return redirect()
            ->route('app.admin.assignments.user', ['user' => $user->id])
            ->with('status', 'Assignment waiver removed.');
    }

    private function normalizeFocus(?string $focus): string
    {
        $focus = strtolower(trim((string) $focus));

        return in_array($focus, ['all', 'overdue', 'due_soon', 'inactive', 'waived'], true)
            ? $focus
            : 'all';
    }

    private function normalizeAuditAction(?string $action): string
    {
        $action = strtolower(trim((string) $action));

        return in_array($action, [
            'all',
            'rule_saved',
            'rule_removed',
            'waiver_saved',
            'waiver_removed',
            'acknowledgement_recorded',
            'reminder_marked_sent',
            'reminder_batch_run',
            'feed_scoring_settings_updated',
            'feed_scoring_settings_reset',
            'feed_scoring_preset_applied',
            'ranking_settings_updated',
            'ranking_settings_reset',
            'ranking_provider_tested',
            'ranking_probe_history_exported',
            'ranking_severity_transitions_exported',
            'ranking_incident_bundle_exported',
            'scorm_demo_reset',
            'ranking_severity_changed',
            'reminder_settings_updated',
            'reminder_settings_reset',
            'user_created',
            'user_updated',
                    'user_password_reset',
                    'user_password_reset_link_sent',
                    'user_suspended',
                    'user_restored',
                    'user_verification_link_sent',
                    'user_verification_marked',
            'user_verification_cleared',
        ], true)
            ? $action
            : 'all';
    }

    private function normalizeAuditDateRange(mixed $from, mixed $to): array
    {
        return [
            'from' => $this->normalizeAuditDateBoundary($from, false),
            'to' => $this->normalizeAuditDateBoundary($to, true),
        ];
    }

    private function normalizeAuditScope(mixed $actor, mixed $target, mixed $module): array
    {
        return [
            'actor' => $this->normalizeAuditScopeId($actor),
            'target' => $this->normalizeAuditScopeId($target),
            'module' => $this->normalizeAuditScopeId($module),
        ];
    }

    private function dashboardData(AssignmentService $assignments, ReminderService $reminders, string $focus, ?string $rankingProvider = null, ?string $rankingSeverityTrigger = null): array
    {
        $rules = ComplianceRoleRule::query()
            ->orderBy('role')
            ->orderBy('compliance_area')
            ->get();

        $rulesByRole = $rules
            ->groupBy('role')
            ->map(fn (Collection $rules) => $rules->values());

        // --- Analytics snapshot (matches analytics page — all users in scope) ---
        $teamScope = User::managedScopeUserIds(auth()->user());
        $analyticsPivot = DB::table('course_user')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('courses.status', 'published')
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_user.user_id', $teamScope))
            ->selectRaw("count(*) as total_assigned")
            ->selectRaw("count(case when course_user.status = 'completed' then 1 end) as total_completed")
            ->selectRaw("count(case when course_user.status = 'in_progress' then 1 end) as total_in_progress")
            ->selectRaw("count(case when course_user.status = 'assigned' then 1 end) as total_not_started")
            ->selectRaw("count(distinct course_user.user_id) as total_learners")
            ->first();
        $analyticsCompletionRate = ($analyticsPivot->total_assigned ?? 0) > 0
            ? (int) round(($analyticsPivot->total_completed / $analyticsPivot->total_assigned) * 100)
            : 0;

        // --- Location completion ranking (for trustee dashboard) ---
        $locationCompletionRows = [];
        if (auth()->user()->hasUnrestrictedView()) {
            $locationCompletionRows = DB::table('course_user')
                ->join('user_preferences', 'user_preferences.user_id', '=', 'course_user.user_id')
                ->join('locations', 'locations.id', '=', 'user_preferences.location_id')
                ->join('courses', 'courses.id', '=', 'course_user.course_id')
                ->where('courses.status', 'published')
                ->where('locations.is_active', true)
                ->select(
                    'locations.name as location',
                    DB::raw("count(*) as enrolled"),
                    DB::raw("count(case when course_user.status = 'completed' then 1 end) as completed"),
                )
                ->groupBy('locations.name')
                ->having(DB::raw('count(*)'), '>', 0)
                ->orderByRaw("round(count(case when course_user.status = 'completed' then 1 end)::numeric / count(*) * 100) asc")
                ->get()
                ->map(fn ($row) => [
                    'location' => $row->location,
                    'enrolled' => $row->enrolled,
                    'completed' => $row->completed,
                    'completion_rate' => (int) round($row->completed / $row->enrolled * 100),
                ])
                ->all();
        }

        // --- Course completion data (from course_user pivot) ---
        $users = User::query()
            ->with('preference')
            ->forManagedScope(auth()->user())
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => filled($user->preference?->role))
            ->values();
        $usersById = $users->keyBy('id');

        $courseEnrollments = DB::table('course_user')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->where('courses.status', 'published')
            ->whereIn('course_user.user_id', $users->pluck('id'))
            ->select(
                'course_user.user_id',
                'course_user.course_id',
                'course_user.status',
                'course_user.completed_at',
                'course_user.updated_at',
                'users.name as user_name',
                'users.email as user_email',
                'courses.title as course_title'
            )
            ->get()
            ->map(function ($row) use ($usersById) {
                $user = $usersById->get($row->user_id);
                $row->role = $user ? (strtolower((string) $user->preference?->role) ?: 'unassigned') : 'unassigned';
                return $row;
            });

        $courseCompletionTotalAssignments = $courseEnrollments->count();
        $courseCompletionCompletedCount = $courseEnrollments->where('status', 'completed')->count();
        $courseCompletionInProgressCount = $courseEnrollments->where('status', 'in_progress')->count();
        $courseCompletionNotStartedCount = $courseEnrollments->where('status', 'assigned')->count();
        $courseCompletionRate = $courseCompletionTotalAssignments > 0
            ? (int) round(($courseCompletionCompletedCount / $courseCompletionTotalAssignments) * 100)
            : 0;
        $courseCompletionAveragePercent = $courseCompletionTotalAssignments > 0
            ? (int) round(($courseCompletionCompletedCount / $courseCompletionTotalAssignments) * 100)
            : 0;

        $courseCompletionUserRows = $courseEnrollments
            ->groupBy('user_id')
            ->map(function (Collection $rows, int|string $userId) {
                $assignedCount = $rows->count();
                $completedCount = $rows->where('status', 'completed')->count();
                $inProgressCount = $rows->where('status', 'in_progress')->count();
                $notStartedCount = $rows->where('status', 'assigned')->count();

                return [
                    'user_id' => (int) $userId,
                    'name' => $rows->first()->user_name ?? 'Unknown user',
                    'email' => $rows->first()->user_email ?? '',
                    'role' => $rows->first()->role ?? 'unassigned',
                    'assigned_count' => $assignedCount,
                    'completed_count' => $completedCount,
                    'in_progress_count' => $inProgressCount,
                    'not_started_count' => $notStartedCount,
                    'completion_rate' => $assignedCount > 0 ? (int) round(($completedCount / $assignedCount) * 100) : 0,
                    'average_percent' => $assignedCount > 0 ? (int) round(($completedCount / $assignedCount) * 100) : 0,
                    'last_activity_at' => ($latestUpdate = $rows->pluck('updated_at')->filter()->sortDesc()->first())
                        ? \Carbon\Carbon::parse($latestUpdate)
                        : null,
                ];
            })
            ->sortByDesc(fn (array $row) => [$row['completion_rate'], $row['completed_count'], $row['assigned_count']])
            ->values();

        // Module-level data still needed for overdue/waiver/acknowledgement sections below
        $requiredModules = LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true)
            ->orderBy('compliance_area')
            ->orderBy('title')
            ->get();

        $requiredModulesByComplianceArea = $requiredModules
            ->groupBy(fn (LearningModule $module) => $module->compliance_area ?: 'unscoped')
            ->map(function (Collection $modules, string $area) {
                return [
                    'compliance_area' => $area,
                    'module_count' => $modules->count(),
                    'modules' => $modules->pluck('title')->values(),
                ];
            })
            ->values();
        $scormRequiredModules = $requiredModules
            ->where('source_type', 'scorm')
            ->values();

        $progressByUserAndModule = ModuleProgress::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('learning_module_id', $requiredModules->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $rows) => $rows->keyBy('learning_module_id'));

        $waiverPairs = Schema::hasTable('assignment_waivers')
            ? AssignmentWaiver::query()
                ->whereIn('user_id', $users->pluck('id'))
                ->whereIn('learning_module_id', $requiredModules->pluck('id'))
                ->get()
                ->mapWithKeys(fn (AssignmentWaiver $w) => [$w->user_id . '-' . $w->learning_module_id => $w])
            : collect();

        $uniqueRoles = $users->map(fn (User $u) => strtolower(trim((string) ($u->preference?->role ?? ''))))->filter()->unique()->values()->all();
        $complianceByRole = ComplianceRoleRule::query()
            ->whereIn('role', $uniqueRoles)
            ->get()
            ->groupBy(fn ($rule) => strtolower(trim($rule->role)))
            ->map(fn ($rules) => $rules->pluck('compliance_area')->map(fn ($a) => strtolower(trim((string) $a)))->filter()->unique()->values()->all())
            ->all();

        $modulePrereqIds = [];
        foreach ($requiredModules as $module) {
            $prereqs = $module->relationLoaded('prerequisites')
                ? $module->prerequisites->pluck('id')
                : $module->prerequisites()->pluck('learning_modules.id');
            $modulePrereqIds[$module->id] = $prereqs->map(fn ($id) => (int) $id)->values()->all();
        }
        $allPrereqIds = collect($modulePrereqIds)->flatten()->unique()->values()->all();
        $completedPrereqs = $allPrereqIds !== []
            ? ModuleProgress::query()
                ->whereIn('user_id', $users->pluck('id'))
                ->whereIn('learning_module_id', $allPrereqIds)
                ->where('status', 'completed')
                ->get(['user_id', 'learning_module_id'])
                ->groupBy('user_id')
                ->map(fn (Collection $rows) => $rows->pluck('learning_module_id')->map(fn ($id) => (int) $id)->all())
            : collect();

        $feedScoringService = app(\App\Services\FeedScoringService::class);
        $overdueByRole = $users
            ->groupBy(fn (User $user) => strtolower((string) $user->preference?->role))
            ->map(function (Collection $roleUsers, string $role) use ($requiredModules, $progressByUserAndModule, $assignments, $waiverPairs, $complianceByRole, $modulePrereqIds, $completedPrereqs, $feedScoringService) {
                $overdueCount = 0;

                foreach ($roleUsers as $user) {
                    $userProgress = $progressByUserAndModule->get($user->id, collect());
                    $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
                    $userCompletedPrereqs = $completedPrereqs->get($user->id, []);

                    foreach ($requiredModules as $module) {
                        $progress = $userProgress->get($module->id);

                        // Inline targeting checks (no DB)
                        $targetRoles = collect($module->target_roles ?? [])->filter()->map(fn ($r) => strtolower(trim((string) $r)))->values()->all();
                        $roleMatches = $targetRoles === [] || ($userRole !== '' && in_array($userRole, $targetRoles, true));
                        if (! $roleMatches) continue;

                        $complianceArea = strtolower(trim((string) ($module->compliance_area ?? '')));
                        $needsCompliance = $module->is_required && $complianceArea !== '';
                        $complianceMatches = ! $needsCompliance || in_array($complianceArea, $complianceByRole[$userRole] ?? collect(config('learning_assignments.role_compliance_areas.' . $userRole, []))->filter()->map(fn ($a) => strtolower(trim((string) $a)))->unique()->values()->all(), true);
                        if (! $complianceMatches) continue;

                        $reqIds = $modulePrereqIds[$module->id] ?? [];
                        $prereqUnlocked = $reqIds === [] || count(array_intersect($reqIds, $userCompletedPrereqs)) >= count($reqIds);
                        if (! $prereqUnlocked) continue;

                        $schedule = $assignments->scheduleStatus($module);
                        if (! $schedule['is_open']) continue;

                        $isWaived = $waiverPairs->has($user->id . '-' . $module->id);
                        if ($isWaived) continue;

                        $renewal = $feedScoringService->renewalStatus($module, $progress);
                        if ($renewal['is_due']) {
                            $overdueCount++;
                        }
                    }
                }

                return [
                    'role' => $role,
                    'user_count' => $roleUsers->count(),
                    'overdue_count' => $overdueCount,
                ];
            })
            ->sortByDesc('overdue_count')
            ->values();

        $waivers = AssignmentWaiver::query()
            ->with(['user.preference', 'module'])
            ->latest()
            ->get();

        $acknowledgements = Schema::hasTable('module_acknowledgements')
            ? ModuleAcknowledgement::query()
                ->with(['user.preference', 'module'])
                ->latest('acknowledged_at')
                ->get()
            : collect();

        $waiverByRole = $waivers
            ->groupBy(fn (AssignmentWaiver $waiver) => strtolower((string) $waiver->user?->preference?->role) ?: 'unassigned')
            ->map(function (Collection $roleWaivers, string $role) {
                return [
                    'role' => $role,
                    'waiver_count' => $roleWaivers->count(),
                    'users' => $roleWaivers->pluck('user.name')->filter()->unique()->values(),
                    'modules' => $roleWaivers->pluck('module.title')->filter()->unique()->values(),
                ];
            })
            ->sortByDesc('waiver_count')
            ->values();

        $acknowledgementsByRole = $acknowledgements
            ->groupBy(fn (ModuleAcknowledgement $acknowledgement) => strtolower((string) $acknowledgement->user?->preference?->role) ?: 'unassigned')
            ->map(function (Collection $roleAcknowledgements, string $role) {
                return [
                    'role' => $role,
                    'acknowledgement_count' => $roleAcknowledgements->count(),
                    'users' => $roleAcknowledgements->pluck('user.name')->filter()->unique()->values(),
                    'modules' => $roleAcknowledgements->pluck('module.title')->filter()->unique()->values(),
                ];
            })
            ->sortByDesc('acknowledgement_count')
            ->values();

        $recentAuditEvents = $this->auditTableExists()
            ? AssignmentAuditEvent::query()
                ->with(['actor', 'targetUser', 'module'])
                ->latest()
                ->limit(12)
                ->get()
            : collect();
        $recentTuningEvents = $this->auditTableExists()
            ? AssignmentAuditEvent::query()
                ->with('actor')
                ->whereIn('action', [
                    'feed_scoring_settings_updated',
                    'feed_scoring_settings_reset',
                    'feed_scoring_preset_applied',
                    'ranking_settings_updated',
                    'ranking_settings_reset',
                    'ranking_provider_tested',
                    'ranking_probe_history_exported',
                    'ranking_severity_transitions_exported',
                    'ranking_incident_bundle_exported',
                    'reminder_settings_updated',
                    'reminder_settings_reset',
                ])
                ->latest()
                ->limit(8)
                ->get()
            : collect();
        $latestTuningEvent = $recentTuningEvents->first();
        $lastRankingExport = $this->latestRankingExportSummary();

        $pendingReminders = Schema::hasTable('assignment_reminders')
            ? $reminders->pendingQueue()
            : collect();
        $assignmentSettingsService = app(AssignmentSettingsService::class);
        $feedScoringSettingsService = app(FeedScoringSettingsService::class);
        $rankingHealthService = app(RankingHealthService::class);
        $reminderSettings = $assignmentSettingsService->all();
        $reminderDefaults = $assignmentSettingsService->defaults();
        $scoringWeights = $feedScoringSettingsService->allWeights();
        $scoringDefaults = $feedScoringSettingsService->defaults();
        $rankingHealth = $rankingHealthService->snapshot(5, $rankingProvider, $rankingSeverityTrigger);
        $rankingSettings = $rankingHealth['settings'];
        $rankingStatus = $rankingHealth['provider_status'];
        $reminderOverrideKeys = collect($reminderSettings)
            ->filter(fn ($value, $key) => (int) $value !== (int) ($reminderDefaults[$key] ?? $value))
            ->keys()
            ->values()
            ->all();
        $scoringOverrideKeys = collect($scoringWeights)
            ->filter(fn ($value, $key) => (int) $value !== (int) ($scoringDefaults[$key] ?? $value))
            ->keys()
            ->values()
            ->all();
        $reminderBatches24hCount = $this->auditTableExists()
            ? AssignmentAuditEvent::query()
                ->where('action', 'reminder_batch_run')
                ->where('created_at', '>=', now()->subDay())
                ->count()
            : 0;
        $recentRankingProbes = collect($rankingHealth['recent_probes']);
        $latestRankingProbe = $rankingHealth['last_probe'];
        $lastSuccessfulRankingProbe = $rankingHealth['last_successful_probe'];
        $providerOptions = RankingHealthOptions::providerOptions();
        $triggerOptions = RankingHealthOptions::severityTriggerOptions();
        $selectedRankingProviderKey = $rankingProvider ?? 'all';
        $selectedRankingTriggerKey = $rankingSeverityTrigger ?? 'all';
        $selectedRankingProviderLabel = $providerOptions[$selectedRankingProviderKey] ?? 'All providers';
        $selectedRankingTriggerLabel = $triggerOptions[$selectedRankingTriggerKey] ?? 'All triggers';
        $scormProgressRows = $users->flatMap(function (User $user) use ($scormRequiredModules, $progressByUserAndModule, $assignments, $waiverPairs, $complianceByRole, $modulePrereqIds, $completedPrereqs) {
            $userProgress = $progressByUserAndModule->get($user->id, collect());
            $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
            $userCompletedPrereqs = $completedPrereqs->get($user->id, []);

            return $scormRequiredModules->map(function (LearningModule $module) use ($assignments, $user, $userProgress, $waiverPairs, $complianceByRole, $userRole, $modulePrereqIds, $userCompletedPrereqs) {
                $progress = $userProgress->get($module->id);

                // Inline targeting (no DB)
                $targetRoles = collect($module->target_roles ?? [])->filter()->map(fn ($r) => strtolower(trim((string) $r)))->values()->all();
                $roleMatches = $targetRoles === [] || ($userRole !== '' && in_array($userRole, $targetRoles, true));

                $complianceArea = strtolower(trim((string) ($module->compliance_area ?? '')));
                $needsCompliance = $module->is_required && $complianceArea !== '';
                $complianceMatches = ! $needsCompliance || in_array($complianceArea, $complianceByRole[$userRole] ?? collect(config('learning_assignments.role_compliance_areas.' . $userRole, []))->filter()->map(fn ($a) => strtolower(trim((string) $a)))->unique()->values()->all(), true);

                $reqIds = $modulePrereqIds[$module->id] ?? [];
                $prereqUnlocked = $reqIds === [] || count(array_intersect($reqIds, $userCompletedPrereqs)) >= count($reqIds);

                $schedule = $assignments->scheduleStatus($module);
                $isWaived = $waiverPairs->has($user->id . '-' . $module->id);

                $isAssigned = $roleMatches && $complianceMatches && $prereqUnlocked && $schedule['is_open'];
                $isRequired = $isAssigned && $module->is_required && ! $isWaived;

                if (! $isRequired && ! $isWaived) {
                    return null;
                }

                return [
                    'user_id' => $user->id,
                    'module_id' => $module->id,
                    'progress_status' => $progress?->status ?? 'not_started',
                ];
            })->filter()->values();
        })->values();
        $scormRuntimeEvents = LearningEvent::query()
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->whereIn('entity_id', $scormRequiredModules->pluck('id'))
            ->latest('created_at')
            ->get();
        $latestScormRuntimeByUserAndModule = $scormRuntimeEvents
            ->unique(fn (LearningEvent $event) => $event->user_id.':'.$event->entity_id)
            ->keyBy(fn (LearningEvent $event) => $event->user_id.':'.$event->entity_id);
        $recentScormCompletions = ModuleProgress::query()
            ->with('user:id,name')
            ->whereIn('learning_module_id', $scormRequiredModules->pluck('id'))
            ->whereIn('user_id', $users->pluck('id'))
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(5)
            ->get()
            ->map(function (ModuleProgress $progress) use ($latestScormRuntimeByUserAndModule, $scormRequiredModules) {
                $runtimeEvent = $latestScormRuntimeByUserAndModule->get($progress->user_id.':'.$progress->learning_module_id);
                $sessionSeconds = isset($runtimeEvent?->metadata['session_seconds'])
                    ? (int) $runtimeEvent->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($runtimeEvent?->metadata['session_time'] ?? null);

                return [
                    'completed_at' => $progress->completed_at,
                    'user_id' => (int) $progress->user_id,
                    'module_id' => (int) $progress->learning_module_id,
                    'learner_name' => $progress->user?->name ?? 'Unknown learner',
                    'learner_email' => $progress->user?->email ?? '',
                    'module_title' => $scormRequiredModules->firstWhere('id', (int) $progress->learning_module_id)?->title ?? ('Module #'.$progress->learning_module_id),
                    'percent_complete' => (int) $progress->percent_complete,
                    'scorm_status' => $runtimeEvent?->metadata['status'] ?? ($runtimeEvent?->metadata['lesson_status'] ?? 'completed'),
                    'score_raw' => $runtimeEvent?->metadata['score_raw'] ?? null,
                    'session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                    'lesson_location' => $runtimeEvent?->metadata['lesson_location'] ?? null,
                ];
            })
            ->values();
        $reinforcementTouchpoints = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['user:id,name,email', 'module:id,title'])
                ->whereIn('user_id', $users->pluck('id'))
                ->latest('due_on')
                ->get()
            : collect();
        $recentReinforcementProof = $reinforcementTouchpoints
            ->where('status', 'completed')
            ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => optional($touchpoint->completed_at)->getTimestamp() ?? 0)
            ->take(5)
            ->map(function (ReinforcementTouchpoint $touchpoint) {
                return [
                    'completed_at' => $touchpoint->completed_at,
                    'due_on' => $touchpoint->due_on,
                    'learner_name' => $touchpoint->user?->name ?? 'Unknown learner',
                    'learner_email' => $touchpoint->user?->email ?? '',
                    'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                    'interval_days' => (int) $touchpoint->interval_days,
                    'proof_type' => $touchpoint->proof_type,
                    'proof_summary' => $touchpoint->proof_summary,
                ];
            })
            ->values();

        $recentReinforcementFailures = $reinforcementTouchpoints
            ->where('status', 'needs_retry')
            ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => optional($touchpoint->updated_at)->getTimestamp() ?? 0)
            ->take(5)
            ->map(function (ReinforcementTouchpoint $touchpoint) {
                $remediationModuleIds = collect($touchpoint->metadata['last_remediation_module_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values();

                return [
                    'updated_at' => $touchpoint->updated_at,
                    'due_on' => $touchpoint->due_on,
                    'learner_name' => $touchpoint->user?->name ?? 'Unknown learner',
                    'learner_email' => $touchpoint->user?->email ?? '',
                    'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                    'interval_days' => (int) $touchpoint->interval_days,
                    'proof_type' => $touchpoint->proof_type,
                    'proof_summary' => $touchpoint->proof_summary ?: 'Incorrect reinforcement answer recorded.',
                    'remediation_count' => $remediationModuleIds->count(),
                    'remediation_titles' => LearningModule::query()->whereIn('id', $remediationModuleIds->all())->pluck('title')->values(),
                ];
            })
            ->values();

        return [
            'summary' => [
                'rules_count' => ComplianceRoleRule::query()->count(),
                'roles_count' => $rulesByRole->count(),
                'required_modules_count' => $requiredModules->count(),
                'compliance_areas_count' => $requiredModulesByComplianceArea->count(),
                'waivers_count' => $waivers->count(),
                'acknowledgements_count' => $acknowledgements->count(),
                'pending_reminders_count' => $pendingReminders->count(),
                'inactive_nudge_count' => $pendingReminders->where('reminder_type', 'inactive_nudge')->count(),
                'not_started_nudge_count' => $pendingReminders->where('reminder_type', 'not_started_nudge')->count(),
                'audit_events_count' => $this->auditTableExists() ? AssignmentAuditEvent::query()->count() : 0,
                'reminder_batches_24h_count' => $reminderBatches24hCount,
                'inactive_nudge_after_days' => (int) ($reminderSettings['inactive_nudge_after_days'] ?? 0),
                'inactive_nudge_cooldown_days' => (int) ($reminderSettings['inactive_nudge_cooldown_days'] ?? 0),
                'not_started_nudge_after_days' => (int) ($reminderSettings['not_started_nudge_after_days'] ?? 0),
                'not_started_nudge_cooldown_days' => (int) ($reminderSettings['not_started_nudge_cooldown_days'] ?? 0),
                'score_required_module_weight' => (int) ($scoringWeights['required_module'] ?? 0),
                'score_topic_match_weight' => (int) ($scoringWeights['topic_match'] ?? 0),
                'score_goal_affinity_max' => (int) ($scoringWeights['goal_affinity_max'] ?? 0),
                'reminder_overrides_count' => count($reminderOverrideKeys),
                'scoring_overrides_count' => count($scoringOverrideKeys),
                'ranking_enabled' => (bool) ($rankingSettings['enabled'] ?? false),
                'ranking_provider' => (string) ($rankingSettings['provider'] ?? 'deterministic'),
                'ranking_provider_ready' => (bool) ($rankingStatus['active_provider_ready'] ?? false),
                'ranking_overrides_count' => (int) $rankingHealth['override_count'],
                'ranking_probe_success_count' => (int) $rankingHealth['probe_summary']['successes'],
                'ranking_probe_failure_count' => (int) $rankingHealth['probe_summary']['failures'],
                'ranking_last_probe_at' => !empty($latestRankingProbe['created_at']) ? CarbonImmutable::parse($latestRankingProbe['created_at']) : null,
                'ranking_last_probe_success' => $latestRankingProbe['success'] ?? null,
                'ranking_last_probe_message' => $latestRankingProbe['message'] ?? null,
                'ranking_last_successful_probe_at' => !empty($lastSuccessfulRankingProbe['created_at']) ? CarbonImmutable::parse($lastSuccessfulRankingProbe['created_at']) : null,
                'ranking_last_successful_probe_provider' => $lastSuccessfulRankingProbe['provider'] ?? null,
                'ranking_last_successful_probe_latency_ms' => $lastSuccessfulRankingProbe['latency_ms'] ?? null,
                'ranking_success_gap' => $rankingHealth['success_gap'],
                'ranking_severity' => $rankingHealth['severity'],
                'ranking_last_export' => $lastRankingExport,
                'scorm_required_modules_count' => $scormRequiredModules->count(),
                'scorm_required_assignments_count' => $scormProgressRows->count(),
                'scorm_completed_count' => $scormProgressRows->where('progress_status', 'completed')->count(),
                'scorm_in_progress_count' => $scormProgressRows->where('progress_status', 'in_progress')->count(),
                'scorm_average_score' => (int) round((float) ($scormRuntimeEvents->pluck('metadata.score_raw')->filter(fn ($value) => $value !== null)->avg() ?? 0)),
                'scorm_total_session_label' => ScormRuntimeMetrics::formatSeconds((int) $scormRuntimeEvents->map(function (LearningEvent $event) {
                    return isset($event->metadata['session_seconds'])
                        ? (int) $event->metadata['session_seconds']
                        : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null);
                })->filter(fn ($value) => $value !== null)->sum()),
                'scorm_completed_last_24h_count' => $recentScormCompletions
                    ->filter(fn (array $row) => $row['completed_at'] && $row['completed_at']->gte(now()->subDay()))
                    ->count(),
                'scorm_latest_completion_at' => $recentScormCompletions->first()['completed_at'] ?? null,
                'last_tuning_at' => $latestTuningEvent?->created_at,
                'last_tuning_action' => $latestTuningEvent?->action,
                'last_tuning_actor' => $latestTuningEvent?->actor?->name,
                'analytics_total_assigned' => (int) ($analyticsPivot->total_assigned ?? 0),
                'analytics_total_completed' => (int) ($analyticsPivot->total_completed ?? 0),
                'analytics_total_in_progress' => (int) ($analyticsPivot->total_in_progress ?? 0),
                'analytics_total_not_started' => (int) ($analyticsPivot->total_not_started ?? 0),
                'analytics_total_learners' => (int) ($analyticsPivot->total_learners ?? 0),
                'analytics_completion_rate' => $analyticsCompletionRate,
                'location_completion_rows' => $locationCompletionRows,
                'course_completion_total_assignments' => $courseCompletionTotalAssignments,
                'course_completion_completed_count' => $courseCompletionCompletedCount,
                'course_completion_in_progress_count' => $courseCompletionInProgressCount,
                'course_completion_not_started_count' => $courseCompletionNotStartedCount,
                'course_completion_average_percent' => $courseCompletionAveragePercent,
                'course_completion_rate' => $courseCompletionRate,
                'course_completion_learners_count' => $courseCompletionUserRows->count(),
                'reinforcement_total_count' => $reinforcementTouchpoints->count(),
                'reinforcement_due_count' => $reinforcementTouchpoints->filter(fn (ReinforcementTouchpoint $touchpoint) => in_array($touchpoint->status, ['pending', 'due'], true) && optional($touchpoint->due_on)?->isPast())->count(),
                'reinforcement_completed_count' => $reinforcementTouchpoints->where('status', 'completed')->count(),
                'reinforcement_failed_count' => $reinforcementTouchpoints->where('status', 'needs_retry')->count(),
                'reinforcement_remediation_assigned_count' => $recentReinforcementFailures->sum('remediation_count'),
            ],
            'courseCompletionUserRows' => $courseCompletionUserRows,
            'rulesByRole' => $rulesByRole,
            'requiredModulesByComplianceArea' => $requiredModulesByComplianceArea,
            'overdueByRole' => $overdueByRole,
            'waiverByRole' => $waiverByRole,
            'acknowledgementsByRole' => $acknowledgementsByRole,
            'pendingReminders' => $pendingReminders,
            'focusRows' => $this->buildFocusRows($users, $requiredModules, $progressByUserAndModule, $assignments, $reminders, $focus, $waiverPairs, $complianceByRole, $modulePrereqIds, $completedPrereqs),
            'recentAuditEvents' => $recentAuditEvents,
            'recentTuningEvents' => $recentTuningEvents,
            'recentSeverityTransitions' => collect($rankingHealth['recent_severity_transitions']),
            'severityTriggerSummary' => collect($rankingHealth['severity_trigger_summary']),
            'recentRankingProbes' => $recentRankingProbes,
            'recentRankingProbeLatencySummary' => RankingHealthLatencyFormatter::summarize($recentRankingProbes),
            'recentRankingProbeEmptyMessage' => RankingHealthMessages::probeHistoryEmpty($rankingProvider, $selectedRankingProviderLabel, true),
            'recentSeverityTransitionsEmptyMessage' => RankingHealthMessages::severityTransitionsEmpty($rankingSeverityTrigger, $selectedRankingTriggerLabel),
            'rankingProviderMismatchMessage' => $rankingProvider !== null && $rankingProvider !== ($rankingSettings['provider'] ?? 'deterministic')
                ? RankingHealthMessages::providerMismatch($selectedRankingProviderLabel, $providerOptions[$rankingSettings['provider'] ?? 'deterministic'] ?? (string) ($rankingSettings['provider'] ?? 'deterministic'))
                : null,
            'recentRankingFailures' => collect($rankingHealth['failure_summary']),
            'recentRankingLiveFailures' => collect($rankingHealth['recent_live_failures']),
            'reminderOverrideKeys' => $reminderOverrideKeys,
            'rankingOverrideKeys' => $rankingHealth['override_keys'],
            'scoringOverrideKeys' => $scoringOverrideKeys,
            'recentScormCompletions' => $recentScormCompletions,
            'recentReinforcementProof' => $recentReinforcementProof,
            'recentReinforcementFailures' => $recentReinforcementFailures,
        ];
    }

    private function normalizeRankingProvider(mixed $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        return in_array($normalized, RankingHealthService::PROVIDER_FILTERS, true) ? $normalized : null;
    }

    private function normalizeRankingSeverityTrigger(mixed $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        return in_array($normalized, RankingHealthService::SEVERITY_TRIGGER_FILTERS, true) ? $normalized : null;
    }

    private function normalizeRankingExportDate(mixed $value): ?CarbonImmutable
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function latestRankingExportSummary(): ?array
    {
        if (! $this->auditTableExists()) {
            return null;
        }

        $event = AssignmentAuditEvent::query()
            ->whereIn('action', [
                'ranking_probe_history_exported',
                'ranking_severity_transitions_exported',
                'ranking_incident_bundle_exported',
            ])
            ->latest()
            ->first();

        if (! $event) {
            return null;
        }

        $labels = [
            'ranking_probe_history_exported' => 'Probe CSV',
            'ranking_severity_transitions_exported' => 'Severity CSV',
            'ranking_incident_bundle_exported' => 'Incident Bundle',
            'scorm_demo_reset' => 'SCORM Demo Reset',
        ];
        $meta = $event->meta ?? [];

        return [
            'action' => $event->action,
            'label' => $labels[$event->action] ?? $event->action,
            'created_at' => $event->created_at,
            'bundle_id' => $meta['bundle_id'] ?? null,
            'provider' => $meta['provider'] ?? null,
            'trigger' => $meta['trigger'] ?? null,
        ];
    }

    private function learnerDetailData(User $user, AssignmentService $assignments): array
    {
        $user->load('preference');

        $requiredModules = LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true)
            ->orderBy('compliance_area')
            ->orderBy('title')
            ->get();

        $progressByModuleId = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('learning_module_id', $requiredModules->pluck('id'))
            ->get()
            ->keyBy('learning_module_id');

        $acknowledgementsByModuleId = Schema::hasTable('module_acknowledgements')
            ? ModuleAcknowledgement::query()
                ->where('user_id', $user->id)
                ->whereIn('learning_module_id', $requiredModules->pluck('id'))
                ->get()
                ->keyBy('learning_module_id')
            : collect();
        $latestScormEventsByModuleId = LearningEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->whereIn('entity_id', $requiredModules->pluck('id'))
            ->latest('id')
            ->get()
            ->unique('entity_id')
            ->keyBy(fn (LearningEvent $event) => (int) $event->entity_id);

        $moduleRows = $requiredModules
            ->map(function (LearningModule $module) use ($assignments, $progressByModuleId, $acknowledgementsByModuleId, $latestScormEventsByModuleId, $user) {
                $progress = $progressByModuleId->get($module->id);
                $assignment = $assignments->forUser($user, $module, $progress);
                $latestScormEvent = $latestScormEventsByModuleId->get($module->id);
                $scormSessionSeconds = isset($latestScormEvent?->metadata['session_seconds'])
                    ? (int) $latestScormEvent->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($latestScormEvent?->metadata['session_time'] ?? null);

                if (! $assignment['is_required'] && ! $assignment['is_waived']) {
                    return null;
                }

                return [
                    'module_id' => $module->id,
                    'title' => $module->title,
                    'source_type' => $module->source_type ?: 'manual',
                    'compliance_area' => $module->compliance_area ?: 'unscoped',
                    'refresh_interval_days' => $module->refresh_interval_days,
                    'urgency' => $assignment['urgency'],
                    'renewal_due_at' => $assignment['renewal']['due_at'] ?? null,
                    'progress_status' => $progress?->status ?? 'not_started',
                    'percent_complete' => (int) ($progress?->percent_complete ?? 0),
                    'completed_at' => $progress?->completed_at,
                    'requires_acknowledgement' => (bool) $module->requires_acknowledgement,
                    'acknowledged_at' => $acknowledgementsByModuleId->get($module->id)?->acknowledged_at,
                    'acknowledgement' => $assignment['acknowledgement'],
                    'role_targeting' => $assignment['role_targeting'],
                    'compliance_targeting' => $assignment['compliance_targeting'],
                    'waiver' => $assignment['waiver'],
                    'latest_scorm_status' => $latestScormEvent?->metadata['status'] ?? ($latestScormEvent?->metadata['lesson_status'] ?? null),
                    'latest_scorm_score_raw' => $latestScormEvent?->metadata['score_raw'] ?? null,
                    'latest_scorm_session_label' => ScormRuntimeMetrics::formatSeconds($scormSessionSeconds),
                    'latest_scorm_lesson_location' => $latestScormEvent?->metadata['lesson_location'] ?? null,
                    'latest_scorm_runtime_at' => $latestScormEvent?->created_at,
                ];
            })
            ->filter()
            ->values();

        $summary = [
            'required_total' => $moduleRows->count(),
            'overdue_total' => $moduleRows->where('urgency', 'overdue')->count(),
            'due_soon_total' => $moduleRows->where('urgency', 'due_soon')->count(),
            'completed_total' => $moduleRows->where('progress_status', 'completed')->count(),
            'waived_total' => $moduleRows->where('urgency', 'waived')->count(),
            'acknowledged_total' => $moduleRows->filter(fn (array $row) => (bool) ($row['acknowledgement']['is_acknowledged'] ?? false))->count(),
            'pending_acknowledgement_total' => $moduleRows->filter(fn (array $row) => (bool) ($row['acknowledgement']['is_required'] ?? false) && ! ($row['acknowledgement']['is_acknowledged'] ?? false))->count(),
            'scorm_completed_total' => $moduleRows->where('source_type', 'scorm')->where('progress_status', 'completed')->count(),
        ];

        $recentAuditEvents = $this->auditTableExists()
            ? AssignmentAuditEvent::query()
                ->with(['actor', 'module'])
                ->where('target_user_id', $user->id)
                ->latest()
                ->limit(12)
                ->get()
            : collect();
        $moduleTitlesById = $requiredModules->pluck('title', 'id');
        $recentScormAttempts = LearningEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->latest()
            ->limit(12)
            ->get()
            ->map(function (LearningEvent $event) use ($moduleTitlesById) {
                $sessionSeconds = isset($event->metadata['session_seconds'])
                    ? (int) $event->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($event->metadata['session_time'] ?? null);

                return [
                    'module_id' => (int) $event->entity_id,
                    'when' => $event->created_at,
                    'module_title' => $moduleTitlesById[(int) $event->entity_id] ?? ('Module #'.$event->entity_id),
                    'status' => $event->metadata['status'] ?? ($event->metadata['lesson_status'] ?? 'n/a'),
                    'score_raw' => $event->metadata['score_raw'] ?? null,
                    'session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                    'percent_complete' => $event->metadata['percent_complete'] ?? null,
                    'lesson_location' => $event->metadata['lesson_location'] ?? null,
                    'metadata_summary' => $this->userEventMetadataSummary($event),
                ];
            })
            ->values();

        $reinforcementTouchpoints = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['module:id,title'])
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->get()
            : collect();
        $recentReinforcementProof = $reinforcementTouchpoints
            ->where('status', 'completed')
            ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => optional($touchpoint->completed_at)->getTimestamp() ?? 0)
            ->take(5)
            ->map(function (ReinforcementTouchpoint $touchpoint) {
                return [
                    'module_id' => (int) $touchpoint->learning_module_id,
                    'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                    'completed_at' => $touchpoint->completed_at,
                    'due_on' => $touchpoint->due_on,
                    'interval_days' => (int) $touchpoint->interval_days,
                    'proof_type' => $touchpoint->proof_type,
                    'proof_summary' => $touchpoint->proof_summary ?: 'Reinforcement proof recorded.',
                ];
            })
            ->values();
        $recentReinforcementFailures = $reinforcementTouchpoints
            ->where('status', 'needs_retry')
            ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => optional($touchpoint->updated_at)->getTimestamp() ?? 0)
            ->take(5)
            ->map(function (ReinforcementTouchpoint $touchpoint) {
                $remediationModuleIds = collect($touchpoint->metadata['last_remediation_module_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values();

                return [
                    'module_id' => (int) $touchpoint->learning_module_id,
                    'module_title' => $touchpoint->module?->title ?? ('Module #'.$touchpoint->learning_module_id),
                    'updated_at' => $touchpoint->updated_at,
                    'due_on' => $touchpoint->due_on,
                    'interval_days' => (int) $touchpoint->interval_days,
                    'proof_type' => $touchpoint->proof_type,
                    'proof_summary' => $touchpoint->proof_summary ?: 'Incorrect reinforcement answer recorded.',
                    'incorrect_count' => (int) ($touchpoint->metadata['last_incorrect_count'] ?? 0),
                    'remediation_count' => $remediationModuleIds->count(),
                    'remediation_titles' => LearningModule::query()->whereIn('id', $remediationModuleIds->all())->pluck('title')->values(),
                ];
            })
            ->values();

        return [
            'learner' => $user,
            'moduleRows' => $moduleRows,
            'summary' => $summary + [
                'reinforcement_completed_total' => $reinforcementTouchpoints->where('status', 'completed')->count(),
                'reinforcement_failed_total' => $reinforcementTouchpoints->where('status', 'needs_retry')->count(),
                'reinforcement_remediation_assigned_total' => $recentReinforcementFailures->sum('remediation_count'),
            ],
            'recentAuditEvents' => $recentAuditEvents,
            'recentScormAttempts' => $recentScormAttempts,
            'recentReinforcementProof' => $recentReinforcementProof,
            'recentReinforcementFailures' => $recentReinforcementFailures,
            'latestReinforcementProof' => $recentReinforcementProof->first(),
            'latestReinforcementFailure' => $recentReinforcementFailures->first(),
        ];
    }

    private function userEventMetadataSummary(LearningEvent $event): string
    {
        $metadata = $event->metadata ?? [];

        if ($event->event_type === 'scorm_runtime_committed') {
            $sessionSeconds = isset($metadata['session_seconds'])
                ? (int) $metadata['session_seconds']
                : ScormRuntimeMetrics::parseSessionSeconds($metadata['session_time'] ?? null);

            return sprintf(
                'status=%s; score=%s; session=%s; percent=%s; location=%s',
                $metadata['status'] ?? ($metadata['lesson_status'] ?? 'n/a'),
                $metadata['score_raw'] ?? 'n/a',
                ScormRuntimeMetrics::formatSeconds($sessionSeconds),
                $metadata['percent_complete'] ?? 'n/a',
                $metadata['lesson_location'] ?? 'n/a',
            );
        }

        if ($event->event_type === 'scorm_launched') {
            return sprintf(
                'asset_id=%s; launch_path=%s',
                $metadata['asset_id'] ?? 'n/a',
                $metadata['launch_path'] ?? 'n/a',
            );
        }

        if ($event->event_type === 'reinforcement_completed') {
            return sprintf(
                'module_id=%s; interval_days=%s; proof_type=%s',
                $metadata['module_id'] ?? 'n/a',
                $metadata['interval_days'] ?? 'n/a',
                $metadata['proof_type'] ?? 'n/a',
            );
        }

        if ($event->event_type === 'reinforcement_failed') {
            return sprintf(
                'module_id=%s; incorrect=%s; remediation=%s',
                $metadata['module_id'] ?? 'n/a',
                $metadata['incorrect_count'] ?? 'n/a',
                collect($metadata['remediation_module_ids'] ?? [])->filter()->count(),
            );
        }

        return json_encode($metadata) ?: '{}';
    }

    private function buildUserEventProof(LearningEvent $event, array $moduleTitles): array
    {
        $metadata = $event->metadata ?? [];
        $sessionSeconds = isset($metadata['session_seconds'])
            ? (int) $metadata['session_seconds']
            : ScormRuntimeMetrics::parseSessionSeconds($metadata['session_time'] ?? null);
        $status = $metadata['status'] ?? ($metadata['lesson_status'] ?? null);

        return [
            'event_type' => $event->event_type,
            'module_id' => (int) $event->entity_id,
            'module_title' => $moduleTitles[(int) $event->entity_id] ?? ('Module #'.$event->entity_id),
            'when' => $event->created_at,
            'status' => $status,
            'score_raw' => $metadata['score_raw'] ?? null,
            'session_label' => ScormRuntimeMetrics::formatSeconds($sessionSeconds),
            'percent_complete' => $metadata['percent_complete'] ?? null,
            'lesson_location' => $metadata['lesson_location'] ?? null,
            'launch_path' => $metadata['launch_path'] ?? null,
            'asset_id' => $metadata['asset_id'] ?? null,
        ];
    }

    private function latestLearnerScormProof(User $user): ?array
    {
        $event = LearningEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->latest()
            ->first();

        if (! $event instanceof LearningEvent) {
            return null;
        }

        $moduleTitles = LearningModule::query()
            ->where('id', (int) $event->entity_id)
            ->pluck('title', 'id')
            ->toArray();

        return $this->buildUserEventProof($event, $moduleTitles);
    }

    private function auditEvents(string $action, string $search = '', array $dateRange = [], array $scope = []): Collection
    {
        if (! $this->auditTableExists()) {
            return collect();
        }

        $events = AssignmentAuditEvent::query()
            ->with(['actor', 'targetUser', 'module'])
            ->when($action !== 'all', fn ($query) => $query->where('action', $action))
            ->when($scope['actor'] ?? null, fn ($query, int $actorId) => $query->where('actor_user_id', $actorId))
            ->when($scope['target'] ?? null, fn ($query, int $targetId) => $query->where('target_user_id', $targetId))
            ->when($scope['module'] ?? null, fn ($query, int $moduleId) => $query->where('learning_module_id', $moduleId))
            ->when(
                $dateRange['from'] ?? null,
                fn ($query, CarbonImmutable $from) => $query->where('created_at', '>=', $from),
            )
            ->when(
                $dateRange['to'] ?? null,
                fn ($query, CarbonImmutable $to) => $query->where('created_at', '<=', $to),
            )
            ->latest()
            ->limit($search !== '' ? 250 : 100)
            ->get();

        if ($search === '') {
            return $events->take(100)->values();
        }

        $needle = Str::lower($search);

        return $events
            ->filter(function (AssignmentAuditEvent $event) use ($needle) {
                $meta = $event->meta ?? [];

                $haystack = collect([
                    $event->action,
                    $event->actor?->name,
                    $event->actor?->email,
                    $event->targetUser?->name,
                    $event->targetUser?->email,
                    $event->module?->title,
                    $event->module?->compliance_area,
                    $meta['reason'] ?? null,
                    $meta['trigger'] ?? null,
                    $meta['bundle_id'] ?? null,
                    $meta['role'] ?? null,
                    $meta['compliance_area'] ?? null,
                    $meta['module_title'] ?? null,
                    $meta['changed_keys'] ?? null,
                    $meta['reminder_type'] ?? null,
                    $meta['mode'] ?? null,
                    isset($meta['types']) ? implode(' ', (array) $meta['types']) : null,
                    isset($meta['synced_total']) ? (string) $meta['synced_total'] : null,
                    isset($meta['sent_total']) ? (string) $meta['sent_total'] : null,
                    isset($meta['remaining_pending']) ? (string) $meta['remaining_pending'] : null,
                ])
                    ->filter()
                    ->map(fn ($value) => Str::lower((string) $value))
                    ->implode(' ');

                return Str::contains($haystack, $needle);
            })
            ->take(100)
            ->values();
    }

    private function auditSummary(Collection $auditEvents): array
    {
        return [
            'total' => $auditEvents->count(),
            'rule_saved' => $auditEvents->where('action', 'rule_saved')->count(),
            'rule_removed' => $auditEvents->where('action', 'rule_removed')->count(),
            'waiver_saved' => $auditEvents->where('action', 'waiver_saved')->count(),
            'waiver_removed' => $auditEvents->where('action', 'waiver_removed')->count(),
            'acknowledgement_recorded' => $auditEvents->where('action', 'acknowledgement_recorded')->count(),
            'reminder_marked_sent' => $auditEvents->where('action', 'reminder_marked_sent')->count(),
            'reminder_batch_run' => $auditEvents->where('action', 'reminder_batch_run')->count(),
            'feed_scoring_settings_updated' => $auditEvents->where('action', 'feed_scoring_settings_updated')->count(),
            'feed_scoring_settings_reset' => $auditEvents->where('action', 'feed_scoring_settings_reset')->count(),
            'feed_scoring_preset_applied' => $auditEvents->where('action', 'feed_scoring_preset_applied')->count(),
            'ranking_settings_updated' => $auditEvents->where('action', 'ranking_settings_updated')->count(),
            'ranking_settings_reset' => $auditEvents->where('action', 'ranking_settings_reset')->count(),
            'ranking_provider_tested' => $auditEvents->where('action', 'ranking_provider_tested')->count(),
            'ranking_probe_history_exported' => $auditEvents->where('action', 'ranking_probe_history_exported')->count(),
            'ranking_severity_transitions_exported' => $auditEvents->where('action', 'ranking_severity_transitions_exported')->count(),
            'ranking_incident_bundle_exported' => $auditEvents->where('action', 'ranking_incident_bundle_exported')->count(),
            'scorm_demo_reset' => $auditEvents->where('action', 'scorm_demo_reset')->count(),
            'ranking_severity_changed' => $auditEvents->where('action', 'ranking_severity_changed')->count(),
            'reminder_settings_updated' => $auditEvents->where('action', 'reminder_settings_updated')->count(),
            'reminder_settings_reset' => $auditEvents->where('action', 'reminder_settings_reset')->count(),
            'user_created' => $auditEvents->where('action', 'user_created')->count(),
            'user_updated' => $auditEvents->where('action', 'user_updated')->count(),
            'user_password_reset' => $auditEvents->where('action', 'user_password_reset')->count(),
            'user_password_reset_link_sent' => $auditEvents->where('action', 'user_password_reset_link_sent')->count(),
            'user_suspended' => $auditEvents->where('action', 'user_suspended')->count(),
            'user_restored' => $auditEvents->where('action', 'user_restored')->count(),
            'user_verification_link_sent' => $auditEvents->where('action', 'user_verification_link_sent')->count(),
            'user_verification_marked' => $auditEvents->where('action', 'user_verification_marked')->count(),
            'user_verification_cleared' => $auditEvents->where('action', 'user_verification_cleared')->count(),
        ];
    }

    private function auditEventDetails(AssignmentAuditEvent $event): string
    {
        $meta = $event->meta ?? [];

        if (($meta['reason'] ?? null) !== null) {
            return (string) $meta['reason'];
        }

        if ($event->action === 'reminder_batch_run') {
            $base = sprintf(
                'synced %d, sent %d, remaining total %d',
                (int) ($meta['synced_total'] ?? 0),
                (int) ($meta['sent_total'] ?? 0),
                (int) ($meta['remaining_pending'] ?? 0),
            );
            $filtered = isset($meta['remaining_pending_filtered'])
                ? 'remaining filtered '.(int) $meta['remaining_pending_filtered']
                : null;

            $mode = filled($meta['mode'] ?? null) ? 'mode '.(string) $meta['mode'] : null;
            $types = collect($meta['types'] ?? [])
                ->filter()
                ->implode('|');
            $typesFragment = $types !== '' ? 'types '.$types : null;

            return collect([$base, $filtered, $mode, $typesFragment])
                ->filter()
                ->implode('; ');
        }

        if (($meta['reminder_type'] ?? null) !== null) {
            return (string) $meta['reminder_type'];
        }

        if (($meta['preset_label'] ?? null) !== null) {
            $changed = ! empty($meta['changed_keys']) && is_array($meta['changed_keys'])
                ? '; changed '.implode(', ', $meta['changed_keys'])
                : '';

            return 'preset '.(string) $meta['preset_label'].$changed;
        }

        if ($event->action === 'ranking_severity_changed') {
            $before = (string) ($meta['before_label'] ?? ($meta['before_level'] ?? 'unknown'));
            $after = (string) ($meta['after_label'] ?? ($meta['after_level'] ?? 'unknown'));
            $trigger = filled($meta['trigger'] ?? null) ? ' via '.(string) $meta['trigger'] : '';

            return trim(sprintf('%s -> %s%s', $before, $after, $trigger));
        }

        if ($event->action === 'ranking_incident_bundle_exported') {
            return collect([
                filled($meta['bundle_id'] ?? null) ? 'bundle '.(string) $meta['bundle_id'] : null,
                'provider '.(string) ($meta['provider'] ?? 'all'),
                'trigger '.(string) ($meta['trigger'] ?? 'all'),
                isset($meta['probe_count']) ? 'probes '.(int) $meta['probe_count'] : null,
                isset($meta['severity_transition_count']) ? 'transitions '.(int) $meta['severity_transition_count'] : null,
            ])->filter()->implode('; ');
        }

        if ($event->action === 'scorm_demo_reset') {
            return collect([
                (string) ($meta['message'] ?? 'SCORM demo data reset completed.'),
                filled($meta['status'] ?? null) ? 'status '.(string) $meta['status'] : null,
            ])->filter()->implode('; ');
        }

        if ($event->action === 'ranking_probe_history_exported') {
            return collect([
                'provider '.(string) ($meta['provider'] ?? 'all'),
                isset($meta['probe_count']) ? 'probes '.(int) $meta['probe_count'] : null,
                filled($meta['export_from'] ?? null) ? 'from '.(string) $meta['export_from'] : null,
                filled($meta['export_to'] ?? null) ? 'to '.(string) $meta['export_to'] : null,
            ])->filter()->implode('; ');
        }

        if ($event->action === 'ranking_severity_transitions_exported') {
            return collect([
                'trigger '.(string) ($meta['trigger'] ?? 'all'),
                isset($meta['severity_transition_count']) ? 'transitions '.(int) $meta['severity_transition_count'] : null,
                filled($meta['export_from'] ?? null) ? 'from '.(string) $meta['export_from'] : null,
                filled($meta['export_to'] ?? null) ? 'to '.(string) $meta['export_to'] : null,
            ])->filter()->implode('; ');
        }

        if (in_array($event->action, ['user_created', 'user_updated'], true)) {
            return ! empty($meta['changed_keys']) && is_array($meta['changed_keys'])
                ? 'changed '.implode(', ', $meta['changed_keys'])
                : 'user fields updated';
        }

        if ($event->action === 'user_password_reset') {
            return (string) ($meta['reason'] ?? 'Password reset via user management.');
        }

        if ($event->action === 'user_password_reset_link_sent') {
            return (string) ($meta['reason'] ?? 'Password reset link sent.');
        }

        if ($event->action === 'user_suspended') {
            return (string) ($meta['reason'] ?? 'Account suspended.');
        }

        if ($event->action === 'user_restored') {
            return (string) ($meta['reason'] ?? 'Account restored.');
        }

        if ($event->action === 'user_verification_link_sent') {
            return (string) ($meta['reason'] ?? 'Email verification link sent.');
        }

        if ($event->action === 'user_verification_marked') {
            return (string) ($meta['reason'] ?? 'Email marked verified.');
        }

        if ($event->action === 'user_verification_cleared') {
            return (string) ($meta['reason'] ?? 'Email verification cleared.');
        }

        if (! empty($meta['changed_keys']) && is_array($meta['changed_keys'])) {
            return 'changed '.implode(', ', $meta['changed_keys']);
        }

        $scope = trim((string) (($meta['role'] ?? '') . (($meta['compliance_area'] ?? '') ? ' / ' . $meta['compliance_area'] : '')));

        return $scope !== '' ? $scope : 'n/a';
    }

    private function auditDatePresets(): array
    {
        return [
            [
                'label' => 'Today',
                'from' => now()->toDateString(),
                'to' => now()->toDateString(),
            ],
            [
                'label' => 'Last 7 Days',
                'from' => now()->subDays(6)->toDateString(),
                'to' => now()->toDateString(),
            ],
            [
                'label' => 'Last 30 Days',
                'from' => now()->subDays(29)->toDateString(),
                'to' => now()->toDateString(),
            ],
        ];
    }

    private function normalizeAuditDateBoundary(mixed $value, bool $endOfDay): ?CarbonImmutable
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }

    private function normalizeAuditScopeId(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }

    private function buildFocusRows(
        Collection $users,
        Collection $requiredModules,
        Collection $progressByUserAndModule,
        AssignmentService $assignments,
        ReminderService $reminders,
        string $focus,
        Collection $waiverPairs,
        array $complianceByRole,
        array $modulePrereqIds,
        Collection $completedPrereqs,
    ): Collection {
        $feedScoringService = app(\App\Services\FeedScoringService::class);
        return $users
            ->flatMap(function (User $user) use ($requiredModules, $progressByUserAndModule, $assignments, $reminders, $waiverPairs, $complianceByRole, $modulePrereqIds, $completedPrereqs, $feedScoringService) {
                $userProgress = $progressByUserAndModule->get($user->id, collect());
                $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
                $userCompletedPrereqs = $completedPrereqs->get($user->id, []);

                return $requiredModules->map(function (LearningModule $module) use ($assignments, $reminders, $user, $userProgress, $waiverPairs, $complianceByRole, $userRole, $modulePrereqIds, $userCompletedPrereqs, $feedScoringService) {
                    $progress = $userProgress->get($module->id);

                    // Inline targeting (no DB)
                    $targetRoles = collect($module->target_roles ?? [])->filter()->map(fn ($r) => strtolower(trim((string) $r)))->values()->all();
                    $roleMatches = $targetRoles === [] || ($userRole !== '' && in_array($userRole, $targetRoles, true));

                    $complianceArea = strtolower(trim((string) ($module->compliance_area ?? '')));
                    $needsCompliance = $module->is_required && $complianceArea !== '';
                    $complianceMatches = ! $needsCompliance || in_array($complianceArea, $complianceByRole[$userRole] ?? collect(config('learning_assignments.role_compliance_areas.' . $userRole, []))->filter()->map(fn ($a) => strtolower(trim((string) $a)))->unique()->values()->all(), true);

                    $reqIds = $modulePrereqIds[$module->id] ?? [];
                    $prereqUnlocked = $reqIds === [] || count(array_intersect($reqIds, $userCompletedPrereqs)) >= count($reqIds);

                    $schedule = $assignments->scheduleStatus($module);
                    $isWaived = $waiverPairs->has($user->id . '-' . $module->id);
                    $waiver = $isWaived ? $waiverPairs->get($user->id . '-' . $module->id) : null;

                    $isAssigned = $roleMatches && $complianceMatches && $prereqUnlocked && $schedule['is_open'];
                    $isRequired = $isAssigned && $module->is_required && ! $isWaived;

                    if (! $isRequired && ! $isWaived) {
                        return null;
                    }

                    $renewal = $feedScoringService->renewalStatus($module, $progress);
                    $isOverdue = $isRequired && ($renewal['is_due'] ?? false);
                    $isDueSoon = $isRequired && ! $isOverdue && ($renewal['is_due_soon'] ?? false);
                    $urgencyBase = $isWaived ? 'waived' : ($isOverdue ? 'overdue' : ($isDueSoon ? 'due_soon' : 'required'));

                    $isIncompleteRequired = $isRequired && (($progress?->status ?? 'not_started') !== 'completed');

                    $assignment = [
                        'is_required' => $isRequired,
                        'is_incomplete_required' => $isIncompleteRequired,
                        'is_waived' => $isWaived,
                        'urgency' => $urgencyBase,
                        'renewal' => $renewal,
                        'waiver' => $waiver ? ['reason' => $waiver->reason] : null,
                    ];

                    $nudgeType = $reminders->classifyNudgeType($module, $progress, $assignment);
                    $urgency = $nudgeType ?? $urgencyBase;

                    return [
                        'learner_id' => $user->id,
                        'learner_name' => $user->name,
                        'learner_email' => $user->email,
                        'role' => strtolower((string) $user->preference?->role) ?: 'unassigned',
                        'module_title' => $module->title,
                        'module_id' => $module->id,
                        'compliance_area' => $module->compliance_area ?: 'unscoped',
                        'urgency' => $urgency,
                        'renewal_due_at' => $renewal['due_at'] ?? null,
                        'waiver_reason' => $waiver?->reason,
                        'progress_status' => $progress?->status ?? 'not_started',
                    ];
                })->filter()->values();
            })
            ->filter(function (array $row) use ($focus) {
                return match ($focus) {
                    'overdue' => $row['urgency'] === 'overdue',
                    'due_soon' => $row['urgency'] === 'due_soon',
                    'inactive' => in_array($row['urgency'], ['inactive_nudge', 'not_started_nudge'], true),
                    'waived' => $row['urgency'] === 'waived',
                    default => true,
                };
            })
            ->sortBy([
                fn (array $row) => match ($row['urgency']) {
                    'overdue' => 0,
                    'due_soon' => 1,
                    'inactive_nudge' => 2,
                    'not_started_nudge' => 2,
                    'required' => 3,
                    'waived' => 4,
                    default => 5,
                },
                ['role', 'asc'],
                ['learner_name', 'asc'],
                ['module_title', 'asc'],
            ])
            ->values();
    }

    private function recordAuditEvent(
        string $action,
        string $entityType,
        ?int $entityId,
        array $meta = [],
        ?int $targetUserId = null,
        ?int $learningModuleId = null,
    ): void {
        if (! $this->auditTableExists()) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => $targetUserId,
            'learning_module_id' => $learningModuleId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'meta' => $meta,
        ]);
    }

    private function authorizeTeamAccess(User $user): void
    {
        $admin = auth()->user();
        if (! $admin->isSiteAdmin() && ! $admin->canManageTeam($user->preference?->team)) {
            abort(403);
        }
    }

    private function auditTableExists(): bool
    {
        return Schema::hasTable('assignment_audit_events');
    }
}
