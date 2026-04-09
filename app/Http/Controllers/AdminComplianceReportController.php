<?php

namespace App\Http\Controllers;

use App\Models\AssignmentReminder;
use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\LearningPath;
use App\Models\ModuleAcknowledgement;
use App\Models\ModuleProgress;
use App\Models\ReinforcementTouchpoint;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\LearningPathService;
use App\Support\ScormRuntimeMetrics;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminComplianceReportController extends Controller
{
    public function __invoke(Request $request, LearningPathService $paths): View
    {
        return view('app.admin-compliance-report', $this->reportData(
            $paths,
            $request->query('role'),
            $request->query('team'),
            $request->query('status'),
        ));
    }

    public function export(Request $request, LearningPathService $paths): StreamedResponse
    {
        Gate::authorize('admin-access');

        $report = $this->reportData(
            $paths,
            $request->query('role'),
            $request->query('team'),
            $request->query('status'),
        );
        $filename = 'compliance-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // Summary
            fputcsv($handle, ['Section', 'Label', 'Value'], ',', '"', '');
            foreach ($report['summary'] as $label => $value) {
                fputcsv($handle, ['summary', $label, (string) $value], ',', '"', '');
            }

            // By Course
            fputcsv($handle, [], ',', '"', '');
            fputcsv($handle, ['Section', 'Course', 'Enrollments', 'Completed', 'In Progress', 'Not Started'], ',', '"', '');
            foreach ($report['byCourse'] as $row) {
                fputcsv($handle, [
                    'course',
                    $row['course_title'],
                    $row['total_enrollments'],
                    $row['completed'],
                    $row['in_progress'],
                    $row['not_started'],
                ], ',', '"', '');
            }

            // By Role
            fputcsv($handle, [], ',', '"', '');
            fputcsv($handle, ['Section', 'Role', 'Enrollments', 'Completed', 'In Progress', 'Not Started'], ',', '"', '');
            foreach ($report['byRole'] as $row) {
                fputcsv($handle, [
                    'role',
                    $row['role'],
                    $row['total_enrollments'],
                    $row['completed'],
                    $row['in_progress'],
                    $row['not_started'],
                ], ',', '"', '');
            }

            // By Team
            fputcsv($handle, [], ',', '"', '');
            fputcsv($handle, ['Section', 'Team', 'Learners', 'Enrollments', 'Completed', 'In Progress', 'Not Started'], ',', '"', '');
            foreach ($report['byTeam'] as $row) {
                fputcsv($handle, [
                    'team',
                    $row['team'],
                    $row['learners'],
                    $row['total_enrollments'],
                    $row['completed'],
                    $row['in_progress'],
                    $row['not_started'],
                ], ',', '"', '');
            }

            // Recent Knowledge Check Results
            fputcsv($handle, [], ',', '"', '');
            fputcsv($handle, ['Section', 'Learner', 'Email', 'Course', 'Score', 'Result', 'Completed At'], ',', '"', '');
            foreach ($report['recentReinforcementResults'] as $attempt) {
                fputcsv($handle, [
                    'recent_knowledge_check',
                    $attempt->user?->name ?? 'Unknown',
                    $attempt->user?->email ?? '',
                    $attempt->course?->title ?? '',
                    $attempt->score ?? '',
                    $attempt->status ?? '',
                    $attempt->completed_at?->format('Y-m-d H:i:s') ?? '',
                ], ',', '"', '');
            }

            // Learning Paths
            fputcsv($handle, [], ',', '"', '');
            fputcsv($handle, ['Section', 'Path', 'Target Roles', 'Eligible Users', 'Total Steps', 'Fully Completed Users', 'Overdue Users', 'Average Completion'], ',', '"', '');
            foreach ($report['pathRows'] as $row) {
                fputcsv($handle, [
                    'learning_path',
                    $row['title'],
                    $row['target_roles']->join(', ') ?: 'all',
                    $row['eligible_users'],
                    $row['total_steps'],
                    $row['fully_completed_users'],
                    $row['overdue_users'],
                    $row['average_completion'],
                ], ',', '"', '');
            }

            // Path Coverage by Role
            fputcsv($handle, [], ',', '"', '');
            fputcsv($handle, ['Section', 'Role', 'Paths', 'Eligible Users', 'Fully Completed Paths', 'Overdue Paths', 'Average Completion'], ',', '"', '');
            foreach ($report['pathCoverageByRole'] as $row) {
                fputcsv($handle, [
                    'path_role',
                    $row['role'],
                    $row['paths'],
                    $row['eligible_users'],
                    $row['fully_completed_paths'],
                    $row['overdue_paths'],
                    $row['average_completion'],
                ], ',', '"', '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function learners(Request $request, AssignmentService $assignments): View
    {
        Gate::authorize('admin-access');

        $rows = $this->learnerRows(
            $assignments,
            $request->query('role'),
            $request->query('team'),
            $request->query('compliance_area'),
            $request->query('status'),
            $request->query('source_type'),
        );
        $latestScormProof = $rows
            ->filter(fn (array $row) => $row['source_type'] === 'scorm' && ! empty($row['scorm_runtime_at']))
            ->sortByDesc(fn (array $row) => $row['scorm_runtime_at']?->getTimestamp() ?? 0)
            ->first();
        $rowKeySet = $rows
            ->mapWithKeys(fn (array $row) => [$row['user_id'].':'.$row['module_id'] => true]);
        $recentReinforcementProof = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['user:id,name,email', 'module:id,title,source_type'])
                ->whereIn('user_id', $rows->pluck('user_id')->unique())
                ->whereIn('learning_module_id', $rows->pluck('module_id')->unique())
                ->whereNotNull('completed_at')
                ->get()
                ->filter(fn (ReinforcementTouchpoint $touchpoint) => $rowKeySet->has($touchpoint->user_id.':'.$touchpoint->learning_module_id))
                ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => $touchpoint->completed_at?->getTimestamp() ?? 0)
                ->map(function (ReinforcementTouchpoint $touchpoint) use ($rows) {
                    $row = $rows->first(fn (array $item) => $item['user_id'] === (int) $touchpoint->user_id && $item['module_id'] === (int) $touchpoint->learning_module_id);

                    return [
                        'id' => $touchpoint->id,
                        'user_id' => (int) $touchpoint->user_id,
                        'module_id' => (int) $touchpoint->learning_module_id,
                        'learner_name' => $touchpoint->user?->name ?? ($row['learner_name'] ?? 'Unknown learner'),
                        'learner_email' => $touchpoint->user?->email ?? ($row['learner_email'] ?? ''),
                        'role' => $row['role'] ?? 'unassigned',
                        'module_title' => $touchpoint->module?->title ?? ($row['module_title'] ?? ('Module #'.$touchpoint->learning_module_id)),
                        'source_type' => $touchpoint->module?->source_type ?? ($row['source_type'] ?? 'manual'),
                        'interval_days' => (int) $touchpoint->interval_days,
                        'proof_type' => $touchpoint->proof_type,
                        'proof_summary' => $touchpoint->proof_summary ?: 'Proof recorded',
                        'completed_at' => $touchpoint->completed_at,
                        'due_on' => $touchpoint->due_on,
                    ];
                })
                ->take(8)
                ->values()
            : collect();

        $recentReinforcementFailures = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['user:id,name,email', 'module:id,title,source_type'])
                ->whereIn('user_id', $rows->pluck('user_id')->unique())
                ->whereIn('learning_module_id', $rows->pluck('module_id')->unique())
                ->where('status', 'needs_retry')
                ->get()
                ->filter(fn (ReinforcementTouchpoint $touchpoint) => $rowKeySet->has($touchpoint->user_id.':'.$touchpoint->learning_module_id))
                ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => $touchpoint->updated_at?->getTimestamp() ?? 0)
                ->map(function (ReinforcementTouchpoint $touchpoint) use ($rows) {
                    $row = $rows->first(fn (array $item) => $item['user_id'] === (int) $touchpoint->user_id && $item['module_id'] === (int) $touchpoint->learning_module_id);
                    $remediationModuleIds = collect($touchpoint->metadata['last_remediation_module_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->values();

                    return [
                        'id' => $touchpoint->id,
                        'user_id' => (int) $touchpoint->user_id,
                        'module_id' => (int) $touchpoint->learning_module_id,
                        'learner_name' => $touchpoint->user?->name ?? ($row['learner_name'] ?? 'Unknown learner'),
                        'learner_email' => $touchpoint->user?->email ?? ($row['learner_email'] ?? ''),
                        'role' => $row['role'] ?? 'unassigned',
                        'module_title' => $touchpoint->module?->title ?? ($row['module_title'] ?? ('Module #'.$touchpoint->learning_module_id)),
                        'interval_days' => (int) $touchpoint->interval_days,
                        'proof_type' => $touchpoint->proof_type,
                        'proof_summary' => $touchpoint->proof_summary ?: 'Incorrect reinforcement answer recorded.',
                        'updated_at' => $touchpoint->updated_at,
                        'due_on' => $touchpoint->due_on,
                        'remediation_count' => $remediationModuleIds->count(),
                        'remediation_titles' => LearningModule::query()->whereIn('id', $remediationModuleIds->all())->pluck('title')->values(),
                    ];
                })
                ->take(8)
                ->values()
            : collect();

        return view('app.admin-compliance-learners', [
            'rows' => $rows,
            'latestScormProof' => $latestScormProof,
            'latestReinforcementProof' => $recentReinforcementProof->first(),
            'filters' => [
                'role' => filled($request->query('role')) ? strtolower(trim((string) $request->query('role'))) : null,
                'team' => filled($request->query('team')) ? trim((string) $request->query('team')) : null,
                'compliance_area' => filled($request->query('compliance_area')) ? strtolower(trim((string) $request->query('compliance_area'))) : null,
                'status' => $this->normalizeLearnerStatus($request->query('status')),
                'source_type' => $this->normalizeSourceType($request->query('source_type')),
            ],
            'availableTeams' => User::query()
                ->with('preference')
                ->get()
                ->map(fn (User $user) => trim((string) $user->preference?->team))
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'availableRoles' => User::query()
                ->with('preference')
                ->get()
                ->map(fn (User $user) => strtolower((string) $user->preference?->role))
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'availableComplianceAreas' => LearningModule::query()
                ->where('status', 'published')
                ->where('is_required', true)
                ->pluck('compliance_area')
                ->map(fn ($value) => strtolower((string) ($value ?: 'unscoped')))
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'availableStatuses' => collect([
                'not_started',
                'in_progress',
                'completed',
                'overdue',
                'due_soon',
                'waived',
            ]),
            'availableSourceTypes' => $this->availableSourceTypes(),
            'summary' => [
                'rows' => $rows->count(),
                'learners' => $rows->pluck('user_id')->unique()->count(),
                'overdue' => $rows->where('urgency', 'overdue')->count(),
                'due_soon' => $rows->where('urgency', 'due_soon')->count(),
                'waived' => $rows->where('urgency', 'waived')->count(),
                'completed' => $rows->where('status_key', 'completed')->count(),
                'in_progress' => $rows->where('status_key', 'in_progress')->count(),
                'scorm_rows' => $rows->where('source_type', 'scorm')->count(),
                'scorm_completed' => $rows->filter(fn (array $row) => $row['source_type'] === 'scorm' && $row['status_key'] === 'completed')->count(),
                'reinforcement_completed' => $recentReinforcementProof->count(),
                'reinforcement_failed' => $recentReinforcementFailures->count(),
            ],
            'latestReinforcementFailure' => $recentReinforcementFailures->first(),
        ]);
    }

    public function learnersExport(Request $request, AssignmentService $assignments): StreamedResponse
    {
        Gate::authorize('admin-access');

        $rows = $this->learnerRows(
            $assignments,
            $request->query('role'),
            $request->query('team'),
            $request->query('compliance_area'),
            $request->query('status'),
            $request->query('source_type'),
        );
        $rowKeySet = $rows
            ->mapWithKeys(fn (array $row) => [$row['user_id'].':'.$row['module_id'] => true]);
        $latestReinforcementProof = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['user:id,name,email', 'module:id,title,source_type'])
                ->whereIn('user_id', $rows->pluck('user_id')->unique())
                ->whereIn('learning_module_id', $rows->pluck('module_id')->unique())
                ->whereNotNull('completed_at')
                ->get()
                ->filter(fn (ReinforcementTouchpoint $touchpoint) => $rowKeySet->has($touchpoint->user_id.':'.$touchpoint->learning_module_id))
                ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => $touchpoint->completed_at?->getTimestamp() ?? 0)
                ->map(function (ReinforcementTouchpoint $touchpoint) use ($rows) {
                    $row = $rows->first(fn (array $item) => $item['user_id'] === (int) $touchpoint->user_id && $item['module_id'] === (int) $touchpoint->learning_module_id);

                    return [
                        'learner_name' => $touchpoint->user?->name ?? ($row['learner_name'] ?? 'Unknown learner'),
                        'learner_email' => $touchpoint->user?->email ?? ($row['learner_email'] ?? ''),
                        'role' => $row['role'] ?? 'unassigned',
                        'module_title' => $touchpoint->module?->title ?? ($row['module_title'] ?? ('Module #'.$touchpoint->learning_module_id)),
                        'source_type' => $touchpoint->module?->source_type ?? ($row['source_type'] ?? 'manual'),
                        'proof_type' => $touchpoint->proof_type,
                        'interval_days' => (int) $touchpoint->interval_days,
                        'proof_summary' => $touchpoint->proof_summary ?: 'Proof recorded',
                        'completed_at' => $touchpoint->completed_at,
                        'due_on' => $touchpoint->due_on,
                    ];
                })
                ->first()
            : null;
        $latestReinforcementFailure = Schema::hasTable('reinforcement_touchpoints')
            ? ReinforcementTouchpoint::query()
                ->with(['user:id,name,email', 'module:id,title,source_type'])
                ->whereIn('user_id', $rows->pluck('user_id')->unique())
                ->whereIn('learning_module_id', $rows->pluck('module_id')->unique())
                ->where('status', 'needs_retry')
                ->get()
                ->filter(fn (ReinforcementTouchpoint $touchpoint) => $rowKeySet->has($touchpoint->user_id.':'.$touchpoint->learning_module_id))
                ->sortByDesc(fn (ReinforcementTouchpoint $touchpoint) => $touchpoint->updated_at?->getTimestamp() ?? 0)
                ->map(function (ReinforcementTouchpoint $touchpoint) use ($rows) {
                    $row = $rows->first(fn (array $item) => $item['user_id'] === (int) $touchpoint->user_id && $item['module_id'] === (int) $touchpoint->learning_module_id);
                    $remediationModuleIds = collect($touchpoint->metadata['last_remediation_module_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->values();

                    return [
                        'learner_name' => $touchpoint->user?->name ?? ($row['learner_name'] ?? 'Unknown learner'),
                        'learner_email' => $touchpoint->user?->email ?? ($row['learner_email'] ?? ''),
                        'role' => $row['role'] ?? 'unassigned',
                        'module_title' => $touchpoint->module?->title ?? ($row['module_title'] ?? ('Module #'.$touchpoint->learning_module_id)),
                        'source_type' => $touchpoint->module?->source_type ?? ($row['source_type'] ?? 'manual'),
                        'proof_type' => $touchpoint->proof_type,
                        'interval_days' => (int) $touchpoint->interval_days,
                        'proof_summary' => $touchpoint->proof_summary ?: 'Incorrect reinforcement answer recorded.',
                        'updated_at' => $touchpoint->updated_at,
                        'due_on' => $touchpoint->due_on,
                        'remediation_count' => $remediationModuleIds->count(),
                    ];
                })
                ->first()
            : null;
        $filename = 'compliance-learners-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows, $latestReinforcementProof, $latestReinforcementFailure): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            if ($latestReinforcementProof !== null) {
                fputcsv($handle, ['record_type', 'learner', 'email', 'role', 'module', 'source_type', 'proof_type', 'interval_days', 'proof_summary', 'completed_at', 'due_on'], ',', '"', '');
                fputcsv($handle, [
                    'latest_reinforcement_proof',
                    $latestReinforcementProof['learner_name'],
                    $latestReinforcementProof['learner_email'],
                    $latestReinforcementProof['role'],
                    $latestReinforcementProof['module_title'],
                    $latestReinforcementProof['source_type'],
                    $latestReinforcementProof['proof_type'],
                    $latestReinforcementProof['interval_days'],
                    $latestReinforcementProof['proof_summary'],
                    $latestReinforcementProof['completed_at']?->format('Y-m-d H:i:s') ?? '',
                    $latestReinforcementProof['due_on']?->toDateString() ?? '',
                ], ',', '"', '');
                fputcsv($handle, [], ',', '"', '');
            }

            if ($latestReinforcementFailure !== null) {
                fputcsv($handle, ['record_type', 'learner', 'email', 'role', 'module', 'source_type', 'proof_type', 'interval_days', 'proof_summary', 'updated_at', 'due_on', 'remediation_count'], ',', '"', '');
                fputcsv($handle, [
                    'latest_reinforcement_failure',
                    $latestReinforcementFailure['learner_name'],
                    $latestReinforcementFailure['learner_email'],
                    $latestReinforcementFailure['role'],
                    $latestReinforcementFailure['module_title'],
                    $latestReinforcementFailure['source_type'],
                    $latestReinforcementFailure['proof_type'],
                    $latestReinforcementFailure['interval_days'],
                    $latestReinforcementFailure['proof_summary'],
                    $latestReinforcementFailure['updated_at']?->format('Y-m-d H:i:s') ?? '',
                    $latestReinforcementFailure['due_on']?->toDateString() ?? '',
                    $latestReinforcementFailure['remediation_count'],
                ], ',', '"', '');
                fputcsv($handle, [], ',', '"', '');
            }

            fputcsv($handle, ['learner', 'email', 'role', 'module', 'source_type', 'scorm_status', 'scorm_progress_percent', 'scorm_score', 'scorm_session_time', 'scorm_runtime_at', 'lesson_location', 'compliance_area', 'status', 'urgency', 'progress_status', 'progress_percent', 'completed_at', 'due_on', 'acknowledged', 'requires_acknowledgement'], ',', '"', '');
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['learner_name'],
                    $row['learner_email'],
                    $row['role'],
                    $row['module_title'],
                    $row['source_type'],
                    $row['scorm_status'] ?? '',
                    $row['scorm_percent_complete'] ?? '',
                    $row['scorm_score_raw'] ?? '',
                    $row['scorm_session_label'] ?? '',
                    $row['scorm_runtime_at']?->format('Y-m-d H:i:s') ?? '',
                    $row['scorm_lesson_location'] ?? '',
                    $row['compliance_area'],
                    $row['status_key'],
                    $row['urgency'],
                    $row['progress_status'],
                    $row['percent_complete'],
                    $row['completed_at']?->format('Y-m-d H:i:s') ?? '',
                    $row['due_on']?->toDateString() ?? '',
                    $row['is_acknowledged'] ? 'yes' : 'no',
                    $row['requires_acknowledgement'] ? 'yes' : 'no',
                ], ',', '"', '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function reportData(
        LearningPathService $paths,
        ?string $roleFilter = null,
        ?string $teamFilter = null,
        ?string $statusFilter = null,
    ): array
    {
        Gate::authorize('admin-access');

        $normalizedRoleFilter = filled($roleFilter)
            ? strtolower(trim((string) $roleFilter))
            : null;
        $normalizedTeamFilter = filled($teamFilter)
            ? trim((string) $teamFilter)
            : null;
        $normalizedStatusFilter = filled($statusFilter)
            ? strtolower(trim((string) $statusFilter))
            : null;
        if ($normalizedStatusFilter !== null && ! in_array($normalizedStatusFilter, ['assigned', 'in_progress', 'completed'], true)) {
            $normalizedStatusFilter = null;
        }

        // --- Users ---
        $allUsers = User::query()
            ->with('preference')
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => filled($user->preference?->role))
            ->values();
        $availableRoles = $allUsers
            ->map(fn (User $user) => strtolower((string) $user->preference?->role))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $availableTeams = $allUsers
            ->map(fn (User $user) => trim((string) $user->preference?->team))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $users = $normalizedRoleFilter
            ? $allUsers
                ->filter(fn (User $user) => strtolower((string) $user->preference?->role) === $normalizedRoleFilter)
                ->values()
            : $allUsers;
        $users = $normalizedTeamFilter !== null
            ? $users
                ->filter(fn (User $user) => trim((string) $user->preference?->team) === $normalizedTeamFilter)
                ->values()
            : $users;

        // --- Courses ---
        $courses = Course::where('status', 'published')->orderBy('title')->get();

        // --- Enrollments ---
        $enrollments = DB::table('course_user')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('courses.status', 'published')
            ->whereIn('course_user.user_id', $users->pluck('id'))
            ->select('course_user.*', 'users.name as learner_name', 'users.email as learner_email', 'courses.title as course_title', 'courses.topic as course_topic')
            ->get();

        // Index users by id for fast lookup
        $usersById = $users->keyBy('id');

        // --- Build rows ---
        $rows = $enrollments->map(function ($enrollment) use ($usersById) {
            $user = $usersById->get($enrollment->user_id);
            $role = $user ? (strtolower((string) $user->preference?->role) ?: 'unassigned') : 'unassigned';
            $team = $user ? (trim((string) $user->preference?->team) ?: 'Unassigned') : 'Unassigned';

            return [
                'user_id' => (int) $enrollment->user_id,
                'course_id' => (int) $enrollment->course_id,
                'learner_name' => $enrollment->learner_name,
                'learner_email' => $enrollment->learner_email,
                'role' => $role,
                'team' => $team,
                'course_title' => $enrollment->course_title,
                'course_topic' => $enrollment->course_topic,
                'status' => $enrollment->status,
                'completed_at' => $enrollment->completed_at,
                'reinforcement_status' => $enrollment->reinforcement_status,
            ];
        });

        // --- Apply status filter ---
        if ($normalizedStatusFilter !== null) {
            $rows = $rows->filter(fn (array $row) => $row['status'] === $normalizedStatusFilter);
        }
        $rows = $rows->values();

        // --- Summary ---
        $summary = [
            'total_enrollments' => $rows->count(),
            'completed' => $rows->where('status', 'completed')->count(),
            'in_progress' => $rows->where('status', 'in_progress')->count(),
            'not_started' => $rows->where('status', 'assigned')->count(),
            'reinforcement_passed' => $rows->where('reinforcement_status', 'passed')->count(),
            'reinforcement_gaps_found' => $rows->where('reinforcement_status', 'gaps_found')->count(),
        ];

        // --- By Team ---
        $byTeam = $rows->groupBy('team')->map(function ($teamRows, $team) {
            return [
                'team' => $team,
                'learners' => $teamRows->pluck('user_id')->unique()->count(),
                'total_enrollments' => $teamRows->count(),
                'completed' => $teamRows->where('status', 'completed')->count(),
                'in_progress' => $teamRows->where('status', 'in_progress')->count(),
                'not_started' => $teamRows->where('status', 'assigned')->count(),
            ];
        })->sortByDesc('total_enrollments')->values();

        // --- By Role ---
        $byRole = $rows->groupBy('role')->map(function ($roleRows, $role) {
            return [
                'role' => $role,
                'total_enrollments' => $roleRows->count(),
                'completed' => $roleRows->where('status', 'completed')->count(),
                'in_progress' => $roleRows->where('status', 'in_progress')->count(),
                'not_started' => $roleRows->where('status', 'assigned')->count(),
            ];
        })->sortByDesc('total_enrollments')->values();

        // --- Employee Overview ---
        $employeeOverview = $rows->groupBy('user_id')->map(function ($empRows) {
            $first = $empRows->first();
            $enrollments = $empRows->count();
            $completed = $empRows->where('status', 'completed')->count();

            return [
                'user_id' => $first['user_id'],
                'learner_name' => $first['learner_name'],
                'learner_email' => $first['learner_email'],
                'team' => $first['team'],
                'role' => $first['role'],
                'trained_percent' => $enrollments > 0 ? (int) round(($completed / $enrollments) * 100) : 0,
                'enrollments' => $enrollments,
                'completed' => $completed,
                'in_progress' => $empRows->where('status', 'in_progress')->count(),
                'not_started' => $empRows->where('status', 'assigned')->count(),
            ];
        })->sortByDesc('trained_percent')->values();

        // --- By Course ---
        $byCourse = $rows->groupBy('course_id')->map(function ($courseRows, $courseId) {
            return [
                'course_id' => $courseId,
                'course_title' => $courseRows->first()['course_title'],
                'total_enrollments' => $courseRows->count(),
                'completed' => $courseRows->where('status', 'completed')->count(),
                'in_progress' => $courseRows->where('status', 'in_progress')->count(),
                'not_started' => $courseRows->where('status', 'assigned')->count(),
            ];
        })->sortByDesc('total_enrollments')->values();

        // --- Course Reinforcement Attempts ---
        $recentReinforcementResults = CourseReinforcementAttempt::with(['course:id,title', 'user:id,name,email'])
            ->whereIn('status', ['completed', 'gaps_found'])
            ->whereIn('user_id', $users->pluck('id'))
            ->latest('completed_at')
            ->limit(12)
            ->get();

        // --- Learning Paths (kept as-is) ---
        $publishedPaths = LearningPath::query()
            ->with(['steps.module'])
            ->where('status', 'published')
            ->orderBy('title')
            ->get();

        $pathRows = $publishedPaths->map(function (LearningPath $path) use ($users, $paths) {
            $eligibleUsers = $users->filter(function (User $user) use ($path) {
                $role = strtolower((string) $user->preference?->role);
                $targetRoles = collect($path->target_roles ?? [])
                    ->map(fn ($value) => strtolower(trim((string) $value)))
                    ->filter()
                    ->values()
                    ->all();

                return $targetRoles === [] || ($role !== '' && in_array($role, $targetRoles, true));
            })->values();

            $summaries = $eligibleUsers->map(fn (User $user) => $paths->progressSummary($user, $path));

            return [
                'id' => $path->id,
                'title' => $path->title,
                'target_roles' => collect($path->target_roles ?? [])->values(),
                'eligible_users' => $eligibleUsers->count(),
                'total_steps' => $path->steps->count(),
                'fully_completed_users' => $summaries->filter(fn (array $summary) => $summary['total_steps'] > 0 && $summary['completed_steps'] === $summary['total_steps'])->count(),
                'overdue_users' => $summaries->filter(fn (array $summary) => ($summary['overdue_steps'] ?? 0) > 0)->count(),
                'average_completion' => $summaries->count() > 0
                    ? (int) floor($summaries->avg('percent_complete'))
                    : 0,
            ];
        })->values();

        $pathCoverageByRole = $users
            ->groupBy(fn (User $user) => strtolower((string) $user->preference?->role) ?: 'unassigned')
            ->map(function (Collection $roleUsers, string $role) use ($publishedPaths, $paths) {
                $visiblePaths = $publishedPaths->filter(function (LearningPath $path) use ($role) {
                    $targetRoles = collect($path->target_roles ?? [])
                        ->map(fn ($value) => strtolower(trim((string) $value)))
                        ->filter()
                        ->values()
                        ->all();

                    return $targetRoles === [] || in_array($role, $targetRoles, true);
                })->values();

                $summaries = $roleUsers->flatMap(function (User $user) use ($visiblePaths, $paths) {
                    return $visiblePaths->map(fn (LearningPath $path) => $paths->progressSummary($user, $path));
                });

                return [
                    'role' => $role,
                    'paths' => $visiblePaths->count(),
                    'eligible_users' => $roleUsers->count(),
                    'fully_completed_paths' => $summaries->filter(fn (array $summary) => $summary['total_steps'] > 0 && $summary['completed_steps'] === $summary['total_steps'])->count(),
                    'overdue_paths' => $summaries->filter(fn (array $summary) => ($summary['overdue_steps'] ?? 0) > 0)->count(),
                    'average_completion' => $summaries->count() > 0
                        ? (int) floor($summaries->avg('percent_complete'))
                        : 0,
                ];
            })
            ->sortByDesc('paths')
            ->values();

        return [
            'summary' => $summary,
            'byCourse' => $byCourse,
            'byTeam' => $byTeam,
            'byRole' => $byRole,
            'employeeOverview' => $employeeOverview,
            'recentReinforcementResults' => $recentReinforcementResults,
            'pathRows' => $pathRows,
            'pathCoverageByRole' => $pathCoverageByRole,
            'filters' => [
                'role' => $normalizedRoleFilter,
                'team' => $normalizedTeamFilter,
                'status' => $normalizedStatusFilter,
            ],
            'availableTeams' => $availableTeams,
            'availableRoles' => $availableRoles,
            'availableStatuses' => collect(['assigned', 'in_progress', 'completed']),
        ];
    }

    private function learnerRows(
        AssignmentService $assignments,
        ?string $roleFilter = null,
        ?string $teamFilter = null,
        ?string $complianceAreaFilter = null,
        ?string $statusFilter = null,
        ?string $sourceTypeFilter = null,
    ): Collection {
        $normalizedRoleFilter = filled($roleFilter) ? strtolower(trim((string) $roleFilter)) : null;
        $normalizedTeamFilter = filled($teamFilter) ? trim((string) $teamFilter) : null;
        $normalizedAreaFilter = filled($complianceAreaFilter) ? strtolower(trim((string) $complianceAreaFilter)) : null;
        $normalizedStatusFilter = $this->normalizeLearnerStatus($statusFilter);
        $normalizedSourceTypeFilter = $this->normalizeSourceType($sourceTypeFilter);

        $users = User::query()
            ->with('preference')
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => filled($user->preference?->role))
            ->when($normalizedRoleFilter !== null, fn (Collection $items) => $items->filter(
                fn (User $user) => strtolower((string) $user->preference?->role) === $normalizedRoleFilter
            ))
            ->when($normalizedTeamFilter !== null, fn (Collection $items) => $items->filter(
                fn (User $user) => trim((string) $user->preference?->team) === $normalizedTeamFilter
            ))
            ->values();

        $requiredModulesQuery = LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true);
        if ($normalizedAreaFilter !== null) {
            $requiredModulesQuery->whereRaw('LOWER(COALESCE(compliance_area, ?)) = ?', ['unscoped', $normalizedAreaFilter]);
        }
        if ($normalizedSourceTypeFilter !== null) {
            $requiredModulesQuery->where('source_type', $normalizedSourceTypeFilter);
        }
        $requiredModules = $requiredModulesQuery
            ->orderBy('compliance_area')
            ->orderBy('title')
            ->get();

        $progressByUserAndModule = ModuleProgress::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('learning_module_id', $requiredModules->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $rows) => $rows->keyBy('learning_module_id'));

        $acknowledgementsByUserAndModule = Schema::hasTable('module_acknowledgements')
            ? ModuleAcknowledgement::query()
                ->whereIn('user_id', $users->pluck('id'))
                ->whereIn('learning_module_id', $requiredModules->pluck('id'))
                ->get()
                ->groupBy('user_id')
                ->map(fn (Collection $rows) => $rows->keyBy('learning_module_id'))
            : collect();
        $latestScormEvents = $this->latestScormRuntimeEvents($users->pluck('id'), $requiredModules->pluck('id'));

        return $users->flatMap(function (User $user) use ($requiredModules, $progressByUserAndModule, $acknowledgementsByUserAndModule, $assignments, $latestScormEvents) {
            $userProgress = $progressByUserAndModule->get($user->id, collect());
            $userAcknowledgements = $acknowledgementsByUserAndModule->get($user->id, collect());

            return $requiredModules->map(function (LearningModule $module) use ($assignments, $user, $userProgress, $userAcknowledgements, $latestScormEvents) {
                $progress = $userProgress->get($module->id);
                $assignment = $assignments->forUser($user, $module, $progress);
                if (! $assignment['is_required'] && ! $assignment['is_waived']) {
                    return null;
                }

                $acknowledgement = $userAcknowledgements->get($module->id);
                $scormEvent = $latestScormEvents->get($user->id.':'.$module->id);
                $scormScore = $scormEvent?->metadata['score_raw'] ?? null;
                $scormSessionSeconds = isset($scormEvent?->metadata['session_seconds'])
                    ? (int) $scormEvent->metadata['session_seconds']
                    : ScormRuntimeMetrics::parseSessionSeconds($scormEvent?->metadata['session_time'] ?? null);

                return [
                    'user_id' => $user->id,
                    'module_id' => $module->id,
                    'learner_name' => $user->name,
                    'learner_email' => $user->email,
                    'role' => strtolower((string) $user->preference?->role) ?: 'unassigned',
                    'team' => trim((string) $user->preference?->team) ?: 'Unassigned',
                    'module_title' => $module->title,
                    'source_type' => $module->source_type ?: 'manual',
                    'scorm_score_raw' => $scormScore,
                    'scorm_status' => $scormEvent?->metadata['status'] ?? ($scormEvent?->metadata['lesson_status'] ?? null),
                    'scorm_percent_complete' => $scormEvent?->metadata['percent_complete'] ?? ($progress?->percent_complete ?? 0),
                    'scorm_session_seconds' => $scormSessionSeconds,
                    'scorm_session_label' => ScormRuntimeMetrics::formatSeconds($scormSessionSeconds),
                    'scorm_lesson_location' => $scormEvent?->metadata['lesson_location'] ?? null,
                    'scorm_runtime_at' => $scormEvent?->created_at,
                    'compliance_area' => $module->compliance_area ?: 'unscoped',
                    'urgency' => $assignment['urgency'],
                    'progress_status' => $progress?->status ?? 'not_started',
                    'percent_complete' => (int) ($progress?->percent_complete ?? 0),
                    'completed_at' => $progress?->completed_at,
                    'status_key' => $this->deriveLearnerStatusKey($assignment['urgency'], $progress?->status ?? 'not_started'),
                    'due_on' => $assignment['renewal']['due_at'] ?? null,
                    'requires_acknowledgement' => (bool) $module->requires_acknowledgement,
                    'is_acknowledged' => $acknowledgement !== null,
                ];
            })->filter()->values();
        })
        ->when(
            $normalizedStatusFilter !== null,
            fn (Collection $items) => $items->filter(fn (array $row) => $row['status_key'] === $normalizedStatusFilter),
        )
        ->sortBy([
            ['team', 'asc'],
            ['role', 'asc'],
            ['learner_name', 'asc'],
            ['compliance_area', 'asc'],
            ['module_title', 'asc'],
        ])->values();
    }

    private function deriveLearnerStatusKey(string $urgency, string $progressStatus): string
    {
        return match ($urgency) {
            'waived' => 'waived',
            'overdue' => 'overdue',
            'due_soon' => 'due_soon',
            default => match ($progressStatus) {
                'completed' => 'completed',
                'in_progress' => 'in_progress',
                default => 'not_started',
            },
        };
    }

    private function normalizeLearnerStatus(mixed $status): ?string
    {
        $status = strtolower(trim((string) $status));
        $allowed = ['not_started', 'in_progress', 'completed', 'overdue', 'due_soon', 'waived'];

        return in_array($status, $allowed, true) ? $status : null;
    }

    private function normalizeSourceType(?string $sourceType): ?string
    {
        $normalized = strtolower(trim((string) $sourceType));

        return in_array($normalized, ['manual', 'pdf', 'scorm'], true) ? $normalized : null;
    }

    private function availableSourceTypes(): Collection
    {
        return LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true)
            ->pluck('source_type')
            ->map(fn ($value) => strtolower((string) ($value ?: 'manual')))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function latestScormRuntimeEvents(Collection $userIds, Collection $moduleIds): Collection
    {
        if ($userIds->isEmpty() || $moduleIds->isEmpty()) {
            return collect();
        }

        return LearningEvent::query()
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->whereIn('user_id', $userIds->all())
            ->whereIn('entity_id', $moduleIds->all())
            ->latest('created_at')
            ->get()
            ->unique(fn (LearningEvent $event) => $event->user_id.':'.$event->entity_id)
            ->keyBy(fn (LearningEvent $event) => $event->user_id.':'.$event->entity_id);
    }
}
