<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\CourseReinforcementResponse;
use App\Models\Location;
use App\Models\ModuleProgress;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCourseAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('admin-access');

        $data = $this->analyticsData();
        $data['filterLocations'] = Location::where('is_active', true)->orderBy('sort_order')->pluck('name')->all();
        $data['filterRoles'] = Role::ordered()->pluck('name')->all();
        $data['filterTeams'] = Team::ordered()->pluck('name')->all();
        $admin = auth()->user();
        $data['showLocationComparison'] = Gate::allows('trustee-view');
        $data['adminScope'] = null;
        if (! $admin->hasUnrestrictedView()) {
            $teams = $admin->managed_teams ?? [];
            $locations = $admin->managed_locations ?? [];
            $locationNames = $locations
                ? Location::where('is_active', true)->whereIn('slug', $locations)->pluck('name')->all()
                : [];
            $data['adminScope'] = [
                'teams' => $teams,
                'locations' => $locationNames,
                'role_label' => $admin->systemRoleLabel(),
            ];
        }

        return view('app.admin-course-analytics', $data);
    }

    public function summaryJson(Request $request): JsonResponse
    {
        Gate::authorize('admin-access');

        $teamScope = $this->resolveFilteredUserIds($request);

        $pivotData = DB::table('course_user')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('courses.status', 'published')
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_user.user_id', $teamScope))
            ->selectRaw("count(*) as total_assigned")
            ->selectRaw("count(case when course_user.status = 'completed' then 1 end) as total_completed")
            ->selectRaw("count(case when course_user.status = 'in_progress' then 1 end) as total_in_progress")
            ->selectRaw("count(case when course_user.status = 'assigned' then 1 end) as total_not_started")
            ->first();

        $totalAssigned = $pivotData->total_assigned ?? 0;
        $totalCompleted = $pivotData->total_completed ?? 0;

        $reinforcementAgg = CourseReinforcementAttempt::whereIn('status', ['completed', 'gaps_found'])
            ->when($teamScope !== null, fn ($q) => $q->whereIn('user_id', $teamScope))
            ->selectRaw("count(*) as total_attempts")
            ->selectRaw("count(case when status = 'completed' then 1 end) as total_passed")
            ->selectRaw("count(case when status = 'gaps_found' then 1 end) as total_failed")
            ->selectRaw("round(avg(score_percent), 1) as overall_avg_score")
            ->first();

        $totalCourses = Course::where('status', 'published')->count();

        return response()->json([
            'total_courses' => $totalCourses,
            'total_assigned' => $totalAssigned,
            'total_completed' => $totalCompleted,
            'total_in_progress' => $pivotData->total_in_progress ?? 0,
            'total_not_started' => $pivotData->total_not_started ?? 0,
            'overall_completion_rate' => $totalAssigned > 0 ? round($totalCompleted / $totalAssigned * 100) : 0,
            'total_reinforcement_attempts' => (int) ($reinforcementAgg->total_attempts ?? 0),
            'total_passed' => (int) ($reinforcementAgg->total_passed ?? 0),
            'total_failed' => (int) ($reinforcementAgg->total_failed ?? 0),
            'overall_avg_score' => $reinforcementAgg->overall_avg_score !== null ? round((float) $reinforcementAgg->overall_avg_score, 1) : null,
        ]);
    }

    public function locationComparisonJson(Request $request): JsonResponse
    {
        Gate::authorize('trustee-view');

        $role = $request->query('role');
        $team = $request->query('team');
        $employee = $request->query('employee');
        $locationFilter = $request->query('location');

        // Shared closure to apply role/team/employee filters on a query that already joins user_preferences + users
        $applyUserFilters = function ($query) use ($role, $team, $employee) {
            if ($role) {
                $query->where('user_preferences.role', $role);
            }
            if ($team) {
                $query->where('user_preferences.team', $team);
            }
            if ($employee) {
                $query->join('users as filter_users', 'filter_users.id', '=', 'user_preferences.user_id')
                    ->where('filter_users.name', 'ilike', '%' . $employee . '%');
            }
            return $query;
        };

        $locations = Location::where('is_active', true)
            ->when($locationFilter, fn ($q) => $q->where('name', $locationFilter))
            ->orderBy('sort_order')
            ->get(['id', 'name']);
        $locationIds = $locations->pluck('id');

        // Pivot data per location
        $pivotQuery = DB::table('course_user')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('courses.status', 'published')
            ->whereIn('user_preferences.location_id', $locationIds);
        if ($role || $team || $employee) {
            $applyUserFilters($pivotQuery);
        }
        $pivotRows = $pivotQuery->select(
                'user_preferences.location_id',
                DB::raw("count(*) as total_enrolled"),
                DB::raw("count(case when course_user.status = 'completed' then 1 end) as total_completed"),
                DB::raw("count(case when course_user.status = 'in_progress' then 1 end) as total_in_progress"),
            )
            ->groupBy('user_preferences.location_id')
            ->get()
            ->keyBy('location_id');

        // Reinforcement stats per location
        $reinfQuery = DB::table('course_reinforcement_attempts')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'course_reinforcement_attempts.user_id')
            ->whereIn('course_reinforcement_attempts.status', ['completed', 'gaps_found'])
            ->whereIn('user_preferences.location_id', $locationIds);
        if ($role || $team || $employee) {
            $applyUserFilters($reinfQuery);
        }
        $reinfRows = $reinfQuery->select(
                'user_preferences.location_id',
                DB::raw("count(*) as total_quizzes"),
                DB::raw("count(case when course_reinforcement_attempts.status = 'completed' then 1 end) as passed"),
                DB::raw("round(avg(course_reinforcement_attempts.score_percent), 1) as avg_score"),
            )
            ->groupBy('user_preferences.location_id')
            ->get()
            ->keyBy('location_id');

        // Distinct learner counts per location (filtered)
        $learnerQuery = DB::table('user_preferences')
            ->join('users', 'users.id', '=', 'user_preferences.user_id')
            ->whereIn('user_preferences.location_id', $locationIds)
            ->where('users.system_role', 'learner');
        if ($role) {
            $learnerQuery->where('user_preferences.role', $role);
        }
        if ($team) {
            $learnerQuery->where('user_preferences.team', $team);
        }
        if ($employee) {
            $learnerQuery->where('users.name', 'ilike', '%' . $employee . '%');
        }
        $learnerCounts = $learnerQuery
            ->select('user_preferences.location_id', DB::raw('count(distinct user_preferences.user_id) as learner_count'))
            ->groupBy('user_preferences.location_id')
            ->get()
            ->keyBy('location_id');

        $items = $locations->map(function ($loc) use ($pivotRows, $reinfRows, $learnerCounts) {
            $pivot = $pivotRows->get($loc->id);
            $reinf = $reinfRows->get($loc->id);
            $enrolled = $pivot->total_enrolled ?? 0;
            $completed = $pivot->total_completed ?? 0;
            $quizzes = $reinf->total_quizzes ?? 0;
            $passed = $reinf->passed ?? 0;

            return [
                'location' => $loc->name,
                'learners' => (int) ($learnerCounts->get($loc->id)?->learner_count ?? 0),
                'enrolled' => $enrolled,
                'completed' => $completed,
                'in_progress' => $pivot->total_in_progress ?? 0,
                'completion_rate' => $enrolled > 0 ? round($completed / $enrolled * 100) : 0,
                'quizzes' => $quizzes,
                'pass_rate' => $quizzes > 0 ? round($passed / $quizzes * 100) : null,
                'avg_score' => $reinf?->avg_score !== null ? round((float) $reinf->avg_score, 1) : null,
            ];
        });

        $items = $items->filter(fn ($row) => $row['enrolled'] > 0)->values();

        return response()->json([
            'data' => $items,
            'current_page' => 1,
            'last_page' => 1,
            'from' => $items->count() > 0 ? 1 : 0,
            'to' => $items->count(),
            'total' => $items->count(),
        ]);
    }

    public function teamComparisonJson(Request $request): JsonResponse
    {
        Gate::authorize('trustee-view');

        $role = $request->query('role');
        $team = $request->query('team');
        $employee = $request->query('employee');
        $locationFilter = $request->query('location');

        // Get all teams from the teams table
        $teams = Team::ordered()->get(['id', 'name']);
        $teamNames = $teams->pluck('name');

        // If a specific team filter is set, only show that team
        if ($team) {
            $teamNames = $teamNames->filter(fn ($t) => $t === $team)->values();
        }

        // Shared closure to apply location/role/employee filters on queries that join user_preferences
        $applyFilters = function ($query) use ($role, $locationFilter, $employee) {
            if ($locationFilter) {
                $query->join('locations', 'locations.id', '=', 'user_preferences.location_id')
                    ->where('locations.name', $locationFilter);
            }
            if ($role) {
                $query->where('user_preferences.role', $role);
            }
            if ($employee) {
                $query->join('users as filter_users', 'filter_users.id', '=', 'user_preferences.user_id')
                    ->where('filter_users.name', 'ilike', '%' . $employee . '%');
            }
            return $query;
        };

        // Pivot data per team
        $pivotQuery = DB::table('course_user')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('courses.status', 'published')
            ->whereIn('user_preferences.team', $teamNames);
        $applyFilters($pivotQuery);
        $pivotRows = $pivotQuery->select(
                'user_preferences.team',
                DB::raw("count(*) as total_enrolled"),
                DB::raw("count(case when course_user.status = 'completed' then 1 end) as total_completed"),
                DB::raw("count(case when course_user.status = 'in_progress' then 1 end) as total_in_progress"),
            )
            ->groupBy('user_preferences.team')
            ->get()
            ->keyBy('team');

        // Reinforcement stats per team
        $reinfQuery = DB::table('course_reinforcement_attempts')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'course_reinforcement_attempts.user_id')
            ->whereIn('course_reinforcement_attempts.status', ['completed', 'gaps_found'])
            ->whereIn('user_preferences.team', $teamNames);
        $applyFilters($reinfQuery);
        $reinfRows = $reinfQuery->select(
                'user_preferences.team',
                DB::raw("count(*) as total_quizzes"),
                DB::raw("count(case when course_reinforcement_attempts.status = 'completed' then 1 end) as passed"),
                DB::raw("round(avg(course_reinforcement_attempts.score_percent), 1) as avg_score"),
            )
            ->groupBy('user_preferences.team')
            ->get()
            ->keyBy('team');

        // Distinct learner counts per team
        $learnerQuery = DB::table('user_preferences')
            ->join('users', 'users.id', '=', 'user_preferences.user_id')
            ->whereIn('user_preferences.team', $teamNames)
            ->where('users.system_role', 'learner');
        if ($locationFilter) {
            $learnerQuery->join('locations', 'locations.id', '=', 'user_preferences.location_id')
                ->where('locations.name', $locationFilter);
        }
        if ($role) {
            $learnerQuery->where('user_preferences.role', $role);
        }
        if ($employee) {
            $learnerQuery->where('users.name', 'ilike', '%' . $employee . '%');
        }
        $learnerCounts = $learnerQuery
            ->select('user_preferences.team', DB::raw('count(distinct user_preferences.user_id) as learner_count'))
            ->groupBy('user_preferences.team')
            ->get()
            ->keyBy('team');

        $items = $teamNames->map(function ($teamName) use ($pivotRows, $reinfRows, $learnerCounts) {
            $pivot = $pivotRows->get($teamName);
            $reinf = $reinfRows->get($teamName);
            $enrolled = $pivot->total_enrolled ?? 0;
            $completed = $pivot->total_completed ?? 0;
            $quizzes = $reinf->total_quizzes ?? 0;
            $passed = $reinf->passed ?? 0;

            return [
                'team' => $teamName,
                'learners' => (int) ($learnerCounts->get($teamName)?->learner_count ?? 0),
                'enrolled' => $enrolled,
                'completed' => $completed,
                'in_progress' => $pivot->total_in_progress ?? 0,
                'completion_rate' => $enrolled > 0 ? round($completed / $enrolled * 100) : 0,
                'quizzes' => $quizzes,
                'pass_rate' => $quizzes > 0 ? round($passed / $quizzes * 100) : null,
                'avg_score' => $reinf?->avg_score !== null ? round((float) $reinf->avg_score, 1) : null,
            ];
        });

        $items = $items->filter(fn ($row) => $row['enrolled'] > 0)->values();

        return response()->json([
            'data' => $items,
            'current_page' => 1,
            'last_page' => 1,
            'from' => $items->count() > 0 ? 1 : 0,
            'to' => $items->count(),
            'total' => $items->count(),
        ]);
    }

    public function roleComparisonJson(Request $request): JsonResponse
    {
        Gate::authorize('trustee-view');

        $roleFilter = $request->query('role');
        $team = $request->query('team');
        $employee = $request->query('employee');
        $locationFilter = $request->query('location');

        // Get all roles from the roles table
        $roles = Role::ordered()->pluck('name');

        if ($roleFilter) {
            $roles = $roles->filter(fn ($r) => $r === $roleFilter)->values();
        }

        // Shared closure to apply location/team/employee filters
        $applyFilters = function ($query) use ($team, $locationFilter, $employee) {
            if ($locationFilter) {
                $query->join('locations', 'locations.id', '=', 'user_preferences.location_id')
                    ->where('locations.name', $locationFilter);
            }
            if ($team) {
                $query->where('user_preferences.team', $team);
            }
            if ($employee) {
                $query->join('users as filter_users', 'filter_users.id', '=', 'user_preferences.user_id')
                    ->where('filter_users.name', 'ilike', '%' . $employee . '%');
            }
            return $query;
        };

        // Pivot data per role
        $pivotQuery = DB::table('course_user')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('courses.status', 'published')
            ->whereIn('user_preferences.role', $roles);
        $applyFilters($pivotQuery);
        $pivotRows = $pivotQuery->select(
                'user_preferences.role',
                DB::raw("count(*) as total_enrolled"),
                DB::raw("count(case when course_user.status = 'completed' then 1 end) as total_completed"),
                DB::raw("count(case when course_user.status = 'in_progress' then 1 end) as total_in_progress"),
            )
            ->groupBy('user_preferences.role')
            ->get()
            ->keyBy('role');

        // Reinforcement stats per role
        $reinfQuery = DB::table('course_reinforcement_attempts')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'course_reinforcement_attempts.user_id')
            ->whereIn('course_reinforcement_attempts.status', ['completed', 'gaps_found'])
            ->whereIn('user_preferences.role', $roles);
        $applyFilters($reinfQuery);
        $reinfRows = $reinfQuery->select(
                'user_preferences.role',
                DB::raw("count(*) as total_quizzes"),
                DB::raw("count(case when course_reinforcement_attempts.status = 'completed' then 1 end) as passed"),
                DB::raw("round(avg(course_reinforcement_attempts.score_percent), 1) as avg_score"),
            )
            ->groupBy('user_preferences.role')
            ->get()
            ->keyBy('role');

        // Distinct learner counts per role
        $learnerQuery = DB::table('user_preferences')
            ->join('users', 'users.id', '=', 'user_preferences.user_id')
            ->whereIn('user_preferences.role', $roles)
            ->where('users.system_role', 'learner');
        if ($locationFilter) {
            $learnerQuery->join('locations', 'locations.id', '=', 'user_preferences.location_id')
                ->where('locations.name', $locationFilter);
        }
        if ($team) {
            $learnerQuery->where('user_preferences.team', $team);
        }
        if ($employee) {
            $learnerQuery->where('users.name', 'ilike', '%' . $employee . '%');
        }
        $learnerCounts = $learnerQuery
            ->select('user_preferences.role', DB::raw('count(distinct user_preferences.user_id) as learner_count'))
            ->groupBy('user_preferences.role')
            ->get()
            ->keyBy('role');

        $items = $roles->map(function ($roleName) use ($pivotRows, $reinfRows, $learnerCounts) {
            $pivot = $pivotRows->get($roleName);
            $reinf = $reinfRows->get($roleName);
            $enrolled = $pivot->total_enrolled ?? 0;
            $completed = $pivot->total_completed ?? 0;
            $quizzes = $reinf->total_quizzes ?? 0;
            $passed = $reinf->passed ?? 0;

            return [
                'role' => $roleName,
                'learners' => (int) ($learnerCounts->get($roleName)?->learner_count ?? 0),
                'enrolled' => $enrolled,
                'completed' => $completed,
                'in_progress' => $pivot->total_in_progress ?? 0,
                'completion_rate' => $enrolled > 0 ? round($completed / $enrolled * 100) : 0,
                'quizzes' => $quizzes,
                'pass_rate' => $quizzes > 0 ? round($passed / $quizzes * 100) : null,
                'avg_score' => $reinf?->avg_score !== null ? round((float) $reinf->avg_score, 1) : null,
            ];
        });

        $items = $items->filter(fn ($row) => $row['enrolled'] > 0)->values();

        return response()->json([
            'data' => $items,
            'current_page' => 1,
            'last_page' => 1,
            'from' => $items->count() > 0 ? 1 : 0,
            'to' => $items->count(),
            'total' => $items->count(),
        ]);
    }

    public function export(): StreamedResponse
    {
        Gate::authorize('admin-access');

        $data = $this->analyticsData(paginateGaps: false);
        $filename = 'course-analytics-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            $csv = fn (array $row) => fputcsv($handle, $row, ',', '"', '');

            // Summary section
            $csv(['COURSE ANALYTICS REPORT', 'Generated ' . now()->format('Y-m-d H:i')]);
            $csv([]);
            $csv(['SUMMARY']);
            $csv(['Metric', 'Value']);
            $csv(['Published Courses', $data['summary']['total_courses']]);
            $csv(['Total Enrolments', $data['summary']['total_assigned']]);
            $csv(['Total Completed', $data['summary']['total_completed']]);
            $csv(['Overall Completion Rate', $data['summary']['overall_completion_rate'] . '%']);
            $csv(['Knowledge Check Attempts', $data['summary']['total_reinforcement_attempts']]);
            $csv(['Knowledge Checks Passed', $data['summary']['total_passed']]);
            $csv(['Knowledge Checks Failed', $data['summary']['total_failed']]);
            $csv(['Overall Avg Score', ($data['summary']['overall_avg_score'] ?? 'N/A') . ($data['summary']['overall_avg_score'] !== null ? '%' : '')]);

            // Comparison sections (trustee + site_admin only)
            $admin = auth()->user();
            if ($admin->hasUnrestrictedView()) {
                $comparisonHeader = ['Name', 'Learners', 'Enrolled', 'Completed', 'In Progress', 'Completion %', 'Quizzes', 'Pass Rate %', 'Avg Score %'];
                $comparisonRow = fn (array $item, string $nameKey) => [
                    $item[$nameKey],
                    $item['learners'],
                    $item['enrolled'],
                    $item['completed'],
                    $item['in_progress'],
                    $item['enrolled'] > 0 ? $item['completion_rate'] . '%' : 'N/A',
                    $item['quizzes'],
                    $item['pass_rate'] !== null ? $item['pass_rate'] . '%' : 'N/A',
                    $item['avg_score'] !== null ? $item['avg_score'] . '%' : 'N/A',
                ];

                $fakeRequest = new Request();
                $locationData = json_decode($this->locationComparisonJson($fakeRequest)->getContent(), true);
                $csv([]);
                $csv(['LOCATION COMPARISON']);
                $csv($comparisonHeader);
                foreach ($locationData['data'] as $row) {
                    $csv($comparisonRow($row, 'location'));
                }

                $teamData = json_decode($this->teamComparisonJson($fakeRequest)->getContent(), true);
                $csv([]);
                $csv(['TEAM COMPARISON']);
                $csv($comparisonHeader);
                foreach ($teamData['data'] as $row) {
                    $csv($comparisonRow($row, 'team'));
                }

                $roleData = json_decode($this->roleComparisonJson($fakeRequest)->getContent(), true);
                $csv([]);
                $csv(['ROLE COMPARISON']);
                $csv($comparisonHeader);
                foreach ($roleData['data'] as $row) {
                    $csv($comparisonRow($row, 'role'));
                }
            }

            // Course performance section
            $csv([]);
            $csv(['COURSE PERFORMANCE']);
            $csv(['Course', 'Modules', 'Enrolled', 'Completed', 'In Progress', 'Not Started', 'Completion %', 'Quiz Attempts', 'Passed', 'Failed', 'Pass Rate %', 'Avg Score %']);
            foreach ($data['courses'] as $course) {
                $stats = $course->stats;
                $reinf = $course->reinforcement;
                $csv([
                    $course->title,
                    $course->modules_count,
                    $stats['assigned'],
                    $stats['completed'],
                    $stats['in_progress'],
                    $stats['not_started'],
                    $stats['completion_rate'] . '%',
                    $reinf['total_attempts'],
                    $reinf['passed'],
                    $reinf['failed'],
                    $reinf['pass_rate'] !== null ? $reinf['pass_rate'] . '%' : 'N/A',
                    $reinf['avg_score'] !== null ? $reinf['avg_score'] . '%' : 'N/A',
                ]);
            }

            // Knowledge gap hotspots
            $csv([]);
            $csv(['KNOWLEDGE GAP HOTSPOTS']);
            $csv(['Module', 'Incorrect Answers']);
            foreach ($data['topGapModules'] as $gap) {
                $csv([$gap['module_title'], $gap['incorrect_count']]);
            }

            // Learners needing attention
            $csv([]);
            $csv(['LEARNERS NEEDING ATTENTION']);
            $csv(['Learner', 'Email', 'Course', 'Course Status']);
            foreach ($data['learnersWithGaps'] as $learner) {
                $csv([
                    $learner->name,
                    $learner->email,
                    $learner->course_title,
                    $learner->course_status ?? 'gaps_found',
                ]);
            }

            // Recent knowledge check results
            $csv([]);
            $csv(['RECENT KNOWLEDGE CHECK RESULTS']);
            $csv(['Learner', 'Email', 'Course', 'Score %', 'Result', 'Completed At']);
            foreach ($data['recentAttempts'] as $attempt) {
                $csv([
                    $attempt->user?->name ?? 'Unknown',
                    $attempt->user?->email ?? '',
                    $attempt->course?->title ?? 'Unknown',
                    $attempt->score_percent,
                    $attempt->status === 'completed' ? 'Passed' : 'Gaps Found',
                    $attempt->completed_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            // Per-course learner detail
            $csv([]);
            $csv(['ENROLMENT DETAIL']);
            $csv(['Course', 'Learner', 'Email', 'Status', 'Completed At', 'Reinforcement Status']);

            $enrolments = DB::table('course_user')
                ->join('users', 'users.id', '=', 'course_user.user_id')
                ->join('courses', 'courses.id', '=', 'course_user.course_id')
                ->where('courses.status', 'published')
                ->select(
                    'courses.title as course_title',
                    'users.name',
                    'users.email',
                    'course_user.status',
                    'course_user.completed_at',
                    'course_user.reinforcement_status'
                )
                ->orderBy('courses.title')
                ->orderBy('users.name')
                ->get();

            foreach ($enrolments as $row) {
                $csv([
                    $row->course_title,
                    $row->name,
                    $row->email,
                    $row->status,
                    $row->completed_at ?? '',
                    $row->reinforcement_status ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function coursesJson(Request $request): JsonResponse
    {
        Gate::authorize('admin-access');

        $teamScope = $this->resolveFilteredUserIds($request);

        $courses = Course::where('status', 'published')
            ->withCount(['modules', 'assignedUsers'])
            ->orderBy('title')
            ->paginate(20, ['*'], 'page');

        $pivotData = DB::table('course_user')
            ->select(
                'course_id',
                DB::raw("count(*) as total_assigned"),
                DB::raw("count(case when status = 'completed' then 1 end) as total_completed"),
                DB::raw("count(case when status = 'in_progress' then 1 end) as total_in_progress"),
                DB::raw("count(case when status = 'assigned' then 1 end) as total_not_started"),
            )
            ->whereIn('course_id', $courses->pluck('id'))
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_user.user_id', $teamScope))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $reinforcementStats = CourseReinforcementAttempt::select(
                'course_id',
                DB::raw("count(*) as total_attempts"),
                DB::raw("count(case when status = 'completed' then 1 end) as passed"),
                DB::raw("count(case when status = 'gaps_found' then 1 end) as failed"),
                DB::raw("round(avg(case when status in ('completed','gaps_found') then score_percent end), 1) as avg_score"),
            )
            ->whereIn('course_id', $courses->pluck('id'))
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_reinforcement_attempts.user_id', $teamScope))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $items = $courses->getCollection()->map(function ($course) use ($pivotData, $reinforcementStats) {
            $pivot = $pivotData->get($course->id);
            $rStats = $reinforcementStats->get($course->id);
            $assigned = $pivot->total_assigned ?? 0;
            $completed = $pivot->total_completed ?? 0;
            $inProgress = $pivot->total_in_progress ?? 0;
            $completionRate = $assigned > 0 ? round($completed / $assigned * 100) : 0;
            $totalAttempts = $rStats->total_attempts ?? 0;
            $passed = $rStats->passed ?? 0;
            $failed = $rStats->failed ?? 0;
            $passRate = ($passed + $failed) > 0 ? round($passed / ($passed + $failed) * 100) : null;

            return [
                'id' => $course->id,
                'title' => $course->title,
                'modules_count' => $course->modules_count,
                'assigned' => $assigned,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'completion_rate' => $completionRate,
                'total_attempts' => $totalAttempts,
                'passed' => $passed,
                'failed' => $failed,
                'pass_rate' => $passRate,
                'avg_score' => $rStats->avg_score ?? null,
                'edit_url' => route('app.admin.courses.edit', $course->id),
            ];
        });

        return response()->json([
            'data' => $items,
            'current_page' => $courses->currentPage(),
            'last_page' => $courses->lastPage(),
            'from' => $courses->firstItem(),
            'to' => $courses->lastItem(),
            'total' => $courses->total(),
        ]);
    }

    public function hotspotsJson(Request $request): JsonResponse
    {
        Gate::authorize('admin-access');

        $teamScope = $this->resolveFilteredUserIds($request);

        $gapModules = CourseReinforcementResponse::where('is_correct', false)
            ->join('reinforcement_questions', 'reinforcement_questions.id', '=', 'course_reinforcement_responses.reinforcement_question_id')
            ->whereNotNull('reinforcement_questions.remediation_learning_module_id')
            ->select(
                'reinforcement_questions.remediation_learning_module_id',
                DB::raw('count(*) as incorrect_count')
            )
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_reinforcement_responses.course_reinforcement_attempt_id', function ($sub) use ($teamScope) {
                $sub->select('id')->from('course_reinforcement_attempts')->whereIn('user_id', $teamScope);
            }))
            ->groupBy('reinforcement_questions.remediation_learning_module_id')
            ->orderByDesc('incorrect_count')
            ->paginate(10, ['*'], 'page');

        $moduleIds = $gapModules->pluck('remediation_learning_module_id');

        $gapModuleNames = DB::table('learning_modules')
            ->whereIn('id', $moduleIds)
            ->pluck('title', 'id');

        // Get parent course names for each module
        $moduleCourses = DB::table('course_module')
            ->join('courses', 'courses.id', '=', 'course_module.course_id')
            ->where('courses.status', 'published')
            ->whereIn('course_module.learning_module_id', $moduleIds)
            ->select('course_module.learning_module_id', 'courses.title')
            ->get()
            ->groupBy('learning_module_id')
            ->map(fn ($rows) => $rows->pluck('title')->unique()->implode(', '));

        $items = $gapModules->getCollection()->map(fn ($row) => [
            'module_id' => $row->remediation_learning_module_id,
            'module_title' => $gapModuleNames[$row->remediation_learning_module_id] ?? 'Unknown',
            'course_title' => $moduleCourses[$row->remediation_learning_module_id] ?? null,
            'incorrect_count' => $row->incorrect_count,
        ]);

        return response()->json([
            'data' => $items,
            'current_page' => $gapModules->currentPage(),
            'last_page' => $gapModules->lastPage(),
            'from' => $gapModules->firstItem(),
            'to' => $gapModules->lastItem(),
            'total' => $gapModules->total(),
        ]);
    }

    public function attemptsJson(Request $request): JsonResponse
    {
        Gate::authorize('admin-access');

        $teamScope = $this->resolveFilteredUserIds($request);

        $attempts = CourseReinforcementAttempt::with(['course:id,title', 'user:id,name,email', 'user.preference:id,user_id,team'])
            ->whereIn('status', ['completed', 'gaps_found'])
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_reinforcement_attempts.user_id', $teamScope))
            ->latest('completed_at')
            ->paginate(20, ['*'], 'page');

        $items = $attempts->getCollection()->map(fn ($a) => [
            'id' => $a->id,
            'learner_name' => $a->user?->name ?? 'Unknown',
            'learner_email' => $a->user?->email ?? '',
            'team' => $a->user?->preference?->team ?? null,
            'course_title' => $a->course?->title ?? 'Unknown',
            'score_percent' => (int) round($a->score_percent),
            'status' => $a->status,
            'completed_at' => $a->completed_at?->diffForHumans() ?? '—',
            'detail_url' => route('app.admin.course-analytics.attempt', $a->id),
        ]);

        return response()->json([
            'data' => $items,
            'current_page' => $attempts->currentPage(),
            'last_page' => $attempts->lastPage(),
            'from' => $attempts->firstItem(),
            'to' => $attempts->lastItem(),
            'total' => $attempts->total(),
        ]);
    }

    public function gapsJson(Request $request): JsonResponse
    {
        Gate::authorize('admin-access');

        $teamScope = $this->resolveFilteredUserIds($request);

        $learnersWithGaps = DB::table('course_user')
            ->where(function ($q) {
                $q->where('reinforcement_status', 'gaps_found')
                  ->orWhere(function ($q2) {
                      $q2->where('course_user.status', 'in_progress')
                         ->whereExists(function ($sub) {
                             $sub->select(DB::raw(1))
                                 ->from('course_reinforcement_attempts')
                                 ->whereColumn('course_reinforcement_attempts.course_id', 'course_user.course_id')
                                 ->whereColumn('course_reinforcement_attempts.user_id', 'course_user.user_id')
                                 ->where('course_reinforcement_attempts.status', 'gaps_found');
                         });
                  });
            })
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_user.user_id', $teamScope))
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->leftJoin('user_preferences', 'user_preferences.user_id', '=', 'users.id')
            ->select('users.id as user_id', 'users.name', 'users.email', 'courses.title as course_title', 'course_user.course_id', 'course_user.status as course_status', 'user_preferences.team')
            ->orderBy('users.name')
            ->paginate(20);

        $gapAttempts = DB::table('course_reinforcement_attempts')
            ->select('user_id', 'course_id', DB::raw('MAX(id) as latest_attempt_id'), DB::raw('MAX(score_percent) as latest_score'))
            ->where('status', 'gaps_found')
            ->when($teamScope !== null, fn ($q) => $q->whereIn('user_id', $teamScope))
            ->groupBy('user_id', 'course_id')
            ->get()
            ->keyBy(fn ($r) => $r->user_id . '-' . $r->course_id);

        $items = $learnersWithGaps->getCollection()->map(function ($learner) use ($gapAttempts) {
            $gapAttempt = $gapAttempts[$learner->user_id . '-' . $learner->course_id] ?? null;
            return [
                'name' => $learner->name,
                'team' => $learner->team ?? null,
                'course_title' => $learner->course_title,
                'course_id' => $learner->course_id,
                'latest_score' => $gapAttempt ? (int) round($gapAttempt->latest_score) : null,
                'attempt_url' => $gapAttempt
                    ? route('app.admin.course-analytics.attempt', $gapAttempt->latest_attempt_id)
                    : null,
                'course_url' => route('app.admin.courses.edit', $learner->course_id),
            ];
        });

        return response()->json([
            'data' => $items,
            'current_page' => $learnersWithGaps->currentPage(),
            'last_page' => $learnersWithGaps->lastPage(),
            'from' => $learnersWithGaps->firstItem(),
            'to' => $learnersWithGaps->lastItem(),
            'total' => $learnersWithGaps->total(),
        ]);
    }

    public function showAttempt(CourseReinforcementAttempt $attempt): View
    {
        Gate::authorize('admin-access');

        abort_unless(in_array($attempt->status, ['completed', 'gaps_found']), 404);

        $admin = auth()->user();
        if (! $admin->hasUnrestrictedView()) {
            $inTeam = DB::table('user_preferences')
                ->where('user_id', $attempt->user_id)
                ->whereIn('team', $admin->managed_teams ?? [])
                ->exists();
            abort_unless($inTeam, 403);
        }

        $attempt->load([
            'course:id,title,topic',
            'user:id,name,email',
            'responses.question.remediationModule:id,title',
        ]);

        $responses = $attempt->responses->sortBy(fn ($r) => $r->question?->position ?? $r->id)->values();

        $totalQuestions = $attempt->metadata['total_questions'] ?? $responses->count();
        $correctCount = $attempt->metadata['correct_count'] ?? $responses->where('is_correct', true)->count();

        return view('app.admin-course-analytics-attempt', [
            'attempt' => $attempt,
            'course' => $attempt->course,
            'learner' => $attempt->user,
            'responses' => $responses,
            'totalQuestions' => $totalQuestions,
            'correctCount' => $correctCount,
        ]);
    }

    /**
     * Resolve user IDs from admin scope + request filter params.
     * Returns null when no filtering is needed (unrestricted), or an array of user IDs.
     */
    private function resolveFilteredUserIds(Request $request): ?array
    {
        $admin = auth()->user();
        $adminScope = User::managedScopeUserIds($admin);

        $location = $request->query('location');
        $role = $request->query('role');
        $team = $request->query('team');
        $employee = $request->query('employee');

        $hasFilters = $location || $role || $team || $employee;

        if (! $hasFilters) {
            return $adminScope;
        }

        // Build filtered user IDs from preferences + users
        $query = DB::table('user_preferences')
            ->join('users', 'users.id', '=', 'user_preferences.user_id');

        if ($location) {
            $query->join('locations', 'locations.id', '=', 'user_preferences.location_id')
                ->where('locations.name', $location);
        }

        if ($role) {
            $query->where('user_preferences.role', $role);
        }

        if ($team) {
            $query->where('user_preferences.team', $team);
        }

        if ($employee) {
            $query->where('users.name', 'ilike', '%' . $employee . '%');
        }

        $filteredIds = $query->pluck('user_preferences.user_id')->unique()->values()->all();

        // Intersect with admin scope if the admin has restrictions
        if ($adminScope === null) {
            return $filteredIds;
        }

        return array_values(array_intersect($adminScope, $filteredIds));
    }

    private function analyticsData(bool $paginateGaps = true): array
    {
        $admin = auth()->user();
        $teamScope = User::managedScopeUserIds($admin);

        $courses = Course::where('status', 'published')
            ->withCount(['modules', 'assignedUsers'])
            ->with('modules:id,title')
            ->orderBy('title')
            ->get();

        // Pivot data: completion stats per course
        $pivotData = DB::table('course_user')
            ->select(
                'course_id',
                DB::raw("count(*) as total_assigned"),
                DB::raw("count(case when status = 'completed' then 1 end) as total_completed"),
                DB::raw("count(case when status = 'in_progress' then 1 end) as total_in_progress"),
                DB::raw("count(case when status = 'assigned' then 1 end) as total_not_started"),
            )
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_user.user_id', $teamScope))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        // Reinforcement stats per course
        $reinforcementStats = CourseReinforcementAttempt::select(
                'course_id',
                DB::raw("count(*) as total_attempts"),
                DB::raw("count(case when status = 'completed' then 1 end) as passed"),
                DB::raw("count(case when status = 'gaps_found' then 1 end) as failed"),
                DB::raw("round(avg(case when status in ('completed','gaps_found') then score_percent end), 1) as avg_score"),
            )
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_reinforcement_attempts.user_id', $teamScope))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        // Gap frequency: which modules get the most wrong answers
        $gapModules = CourseReinforcementResponse::where('is_correct', false)
            ->join('reinforcement_questions', 'reinforcement_questions.id', '=', 'course_reinforcement_responses.reinforcement_question_id')
            ->whereNotNull('reinforcement_questions.remediation_learning_module_id')
            ->select(
                'reinforcement_questions.remediation_learning_module_id',
                DB::raw('count(*) as incorrect_count')
            )
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_reinforcement_responses.course_reinforcement_attempt_id', function ($sub) use ($teamScope) {
                $sub->select('id')->from('course_reinforcement_attempts')->whereIn('user_id', $teamScope);
            }))
            ->groupBy('reinforcement_questions.remediation_learning_module_id')
            ->orderByDesc('incorrect_count')
            ->limit(10)
            ->get();

        $gapModuleIds = $gapModules->pluck('remediation_learning_module_id');
        $gapModuleNames = DB::table('learning_modules')
            ->whereIn('id', $gapModuleIds)
            ->pluck('title', 'id');

        $topGapModules = $gapModules->map(fn ($row) => [
            'module_id' => $row->remediation_learning_module_id,
            'module_title' => $gapModuleNames[$row->remediation_learning_module_id] ?? 'Unknown',
            'incorrect_count' => $row->incorrect_count,
        ]);

        // Attach stats to courses
        $courses->each(function ($course) use ($pivotData, $reinforcementStats) {
            $pivot = $pivotData->get($course->id);
            $course->setAttribute('stats', [
                'assigned' => $pivot->total_assigned ?? 0,
                'completed' => $pivot->total_completed ?? 0,
                'in_progress' => $pivot->total_in_progress ?? 0,
                'not_started' => $pivot->total_not_started ?? 0,
                'completion_rate' => ($pivot->total_assigned ?? 0) > 0
                    ? round(($pivot->total_completed ?? 0) / $pivot->total_assigned * 100)
                    : 0,
            ]);

            $rStats = $reinforcementStats->get($course->id);
            $course->setAttribute('reinforcement', [
                'total_attempts' => $rStats->total_attempts ?? 0,
                'passed' => $rStats->passed ?? 0,
                'failed' => $rStats->failed ?? 0,
                'avg_score' => $rStats->avg_score ?? null,
                'pass_rate' => ($rStats->total_attempts ?? 0) > 0 && (($rStats->passed ?? 0) + ($rStats->failed ?? 0)) > 0
                    ? round(($rStats->passed ?? 0) / (($rStats->passed ?? 0) + ($rStats->failed ?? 0)) * 100)
                    : null,
            ]);
        });

        // Global summary
        $totalAssigned = $pivotData->sum('total_assigned');
        $totalCompleted = $pivotData->sum('total_completed');
        $avgScore = CourseReinforcementAttempt::whereIn('status', ['completed', 'gaps_found'])->when($teamScope !== null, fn ($q) => $q->whereIn('user_id', $teamScope))->avg('score_percent');

        $summary = [
            'total_courses' => $courses->count(),
            'total_assigned' => (int) $totalAssigned,
            'total_completed' => (int) $totalCompleted,
            'total_in_progress' => (int) $pivotData->sum('total_in_progress'),
            'total_not_started' => (int) $pivotData->sum('total_not_started'),
            'overall_completion_rate' => $totalAssigned > 0 ? round($totalCompleted / $totalAssigned * 100) : 0,
            'total_reinforcement_attempts' => (int) $reinforcementStats->sum('total_attempts'),
            'total_passed' => (int) $reinforcementStats->sum('passed'),
            'total_failed' => (int) $reinforcementStats->sum('failed'),
            'overall_avg_score' => $avgScore !== null ? round($avgScore, 1) : null,
        ];

        // Recent reinforcement attempts
        $recentAttempts = CourseReinforcementAttempt::with(['course:id,title', 'user:id,name,email'])
            ->whereIn('status', ['completed', 'gaps_found'])
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_reinforcement_attempts.user_id', $teamScope))
            ->latest('completed_at')
            ->limit(15)
            ->get();

        // Learners needing attention: have a gaps_found attempt and course is not yet re-completed
        $learnersWithGaps = DB::table('course_user')
            ->where(function ($q) {
                $q->where('reinforcement_status', 'gaps_found')
                  ->orWhere(function ($q2) {
                      $q2->where('course_user.status', 'in_progress')
                         ->whereExists(function ($sub) {
                             $sub->select(DB::raw(1))
                                 ->from('course_reinforcement_attempts')
                                 ->whereColumn('course_reinforcement_attempts.course_id', 'course_user.course_id')
                                 ->whereColumn('course_reinforcement_attempts.user_id', 'course_user.user_id')
                                 ->where('course_reinforcement_attempts.status', 'gaps_found');
                         });
                  });
            })
            ->when($teamScope !== null, fn ($q) => $q->whereIn('course_user.user_id', $teamScope))
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->select('users.id as user_id', 'users.name', 'users.email', 'courses.title as course_title', 'course_user.course_id', 'course_user.status as course_status')
            ->orderBy('users.name');

        $learnersWithGaps = $paginateGaps
            ? $learnersWithGaps->paginate(20)
            : $learnersWithGaps->get();

        // Latest gaps_found attempt per user+course for "View Attempt" links
        $gapAttemptIds = DB::table('course_reinforcement_attempts')
            ->select('user_id', 'course_id', DB::raw('MAX(id) as latest_attempt_id'))
            ->where('status', 'gaps_found')
            ->when($teamScope !== null, fn ($q) => $q->whereIn('user_id', $teamScope))
            ->groupBy('user_id', 'course_id')
            ->get()
            ->keyBy(fn ($r) => $r->user_id . '-' . $r->course_id);

        return [
            'courses' => $courses,
            'summary' => $summary,
            'topGapModules' => $topGapModules,
            'recentAttempts' => $recentAttempts,
            'learnersWithGaps' => $learnersWithGaps,
            'gapAttemptIds' => $gapAttemptIds,
        ];
    }
}
