<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Location;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLocationComparisonController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('admin-access');

        $admin = $request->user();
        $sort = $request->query('sort', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $validSorts = ['name', 'user_count', 'completion_rate', 'overdue_count', 'avg_xp'];
        $sort = in_array($sort, $validSorts, true) ? $sort : 'name';
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $locations = Location::ordered()->get();

        // Build per-location stats
        $locationStats = $locations->map(function (Location $location) use ($admin) {
            $userIds = UserPreference::where('location_id', $location->id)
                ->pluck('user_id')
                ->toArray();

            // Scope by admin's managed teams/locations
            if (! $admin->isSiteAdmin()) {
                $scopedIds = User::managedScopeUserIds($admin);
                if ($scopedIds !== null) {
                    $userIds = array_values(array_intersect($userIds, $scopedIds));
                }
            }

            $userCount = count($userIds);

            if ($userCount === 0) {
                return [
                    'location' => $location,
                    'user_count' => 0,
                    'active_count' => 0,
                    'suspended_count' => 0,
                    'verified_count' => 0,
                    'unverified_count' => 0,
                    'course_assigned' => 0,
                    'course_completed' => 0,
                    'completion_rate' => 0,
                    'overdue_count' => 0,
                    'avg_xp' => 0,
                    'active_streaks' => 0,
                    'never_logged_in' => 0,
                    'inactive_30' => 0,
                    'roles' => [],
                    'teams' => [],
                    'employees' => [],
                ];
            }

            // Account status breakdown
            $activeCount = User::whereIn('id', $userIds)->whereNull('suspended_at')->count();
            $suspendedCount = $userCount - $activeCount;

            // Verification breakdown
            $verifiedCount = User::whereIn('id', $userIds)->whereNotNull('email_verified_at')->count();
            $unverifiedCount = $userCount - $verifiedCount;

            // Course completion stats
            $courseAssigned = DB::table('course_user')
                ->whereIn('user_id', $userIds)
                ->count();

            $courseCompleted = DB::table('course_user')
                ->whereIn('user_id', $userIds)
                ->where('status', 'completed')
                ->count();

            $completionRate = $courseAssigned > 0
                ? round(($courseCompleted / $courseAssigned) * 100, 1)
                : 0;

            // Overdue assignments
            $overdueCount = DB::table('assignment_reminders')
                ->whereIn('user_id', $userIds)
                ->whereIn('status', ['pending', 'sent'])
                ->whereDate('due_on', '<', Carbon::today()->toDateString())
                ->distinct('user_id')
                ->count('user_id');

            // Gamification
            $avgXp = (int) User::whereIn('id', $userIds)
                ->where('total_xp', '>', 0)
                ->avg('total_xp');

            $activeStreaks = DB::table('streaks')
                ->whereIn('user_id', $userIds)
                ->where('current_streak', '>', 0)
                ->where('last_activity_date', '>=', Carbon::today()->subDay()->toDateString())
                ->count();

            // Activity
            $neverLoggedIn = User::whereIn('id', $userIds)->whereNull('last_login_at')->count();
            $inactive30 = User::whereIn('id', $userIds)
                ->whereNotNull('last_login_at')
                ->where('last_login_at', '<', now()->subDays(30))
                ->count();

            // Roles, teams, and employee names at this location
            $roles = UserPreference::whereIn('user_id', $userIds)
                ->whereNotNull('role')->where('role', '!=', '')
                ->distinct()->pluck('role')->sort()->values()->toArray();

            $teams = UserPreference::whereIn('user_id', $userIds)
                ->whereNotNull('team')->where('team', '!=', '')
                ->distinct()->pluck('team')->sort()->values()->toArray();

            $employees = User::whereIn('id', $userIds)
                ->orderBy('name')->pluck('name')->toArray();

            return [
                'location' => $location,
                'user_count' => $userCount,
                'active_count' => $activeCount,
                'suspended_count' => $suspendedCount,
                'verified_count' => $verifiedCount,
                'unverified_count' => $unverifiedCount,
                'course_assigned' => $courseAssigned,
                'course_completed' => $courseCompleted,
                'completion_rate' => $completionRate,
                'overdue_count' => $overdueCount,
                'avg_xp' => $avgXp,
                'active_streaks' => $activeStreaks,
                'never_logged_in' => $neverLoggedIn,
                'inactive_30' => $inactive30,
                'roles' => $roles,
                'teams' => $teams,
                'employees' => $employees,
            ];
        });

        // Sort
        $locationStats = match ($sort) {
            'name' => $locationStats->sortBy(fn ($s) => $s['location']->name, SORT_NATURAL, $sortDir === 'desc'),
            'user_count' => $locationStats->sortBy('user_count', SORT_NUMERIC, $sortDir === 'desc'),
            'completion_rate' => $locationStats->sortBy('completion_rate', SORT_NUMERIC, $sortDir === 'desc'),
            'overdue_count' => $locationStats->sortBy('overdue_count', SORT_NUMERIC, $sortDir === 'desc'),
            'avg_xp' => $locationStats->sortBy('avg_xp', SORT_NUMERIC, $sortDir === 'desc'),
            default => $locationStats,
        };

        // Totals
        $totals = [
            'locations' => $locations->count(),
            'users' => $locationStats->sum('user_count'),
            'completion_rate' => $locationStats->sum('course_assigned') > 0
                ? round(($locationStats->sum('course_completed') / $locationStats->sum('course_assigned')) * 100, 1)
                : 0,
            'overdue' => $locationStats->sum('overdue_count'),
        ];

        // JSON-serializable stats for Alpine.js client-side filtering
        $statsJson = $locationStats->values()->map(fn (array $stat) => [
            'slug' => $stat['location']->slug,
            'name' => $stat['location']->name,
            'user_count' => $stat['user_count'],
            'active_count' => $stat['active_count'],
            'suspended_count' => $stat['suspended_count'],
            'verified_count' => $stat['verified_count'],
            'unverified_count' => $stat['unverified_count'],
            'course_assigned' => $stat['course_assigned'],
            'course_completed' => $stat['course_completed'],
            'completion_rate' => $stat['completion_rate'],
            'overdue_count' => $stat['overdue_count'],
            'avg_xp' => $stat['avg_xp'],
            'active_streaks' => $stat['active_streaks'],
            'never_logged_in' => $stat['never_logged_in'],
            'inactive_30' => $stat['inactive_30'],
            'roles' => $stat['roles'],
            'teams' => $stat['teams'],
            'employees' => $stat['employees'],
            'users_url' => route('app.admin.users.index', ['location' => $stat['location']->slug]),
            'compliance_url' => route('app.admin.compliance', ['location' => $stat['location']->slug]),
            'analytics_url' => route('app.admin.course-analytics'),
        ]);

        return view('app.admin-location-comparison', [
            'statsJson' => $statsJson,
            'totals' => $totals,
            'exportUrl' => route('app.admin.locations.export'),
            'manageUrl' => route('app.admin.roles-teams.index'),
            'dashboardUrl' => route('app.admin.assignments'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('admin-access');

        $admin = $request->user();
        $locations = Location::ordered()->get();

        $filename = 'location-comparison-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($locations, $admin): void {
            $handle = fopen('php://output', 'wb');

            $csv = fn (array $row) => fputcsv($handle, $row, ',', '"', '');
            $csv([
                'location', 'users', 'active', 'suspended',
                'verified', 'unverified', 'courses_assigned', 'courses_completed',
                'completion_rate', 'overdue_users', 'avg_xp', 'active_streaks',
                'never_logged_in', 'inactive_30_days',
            ]);

            foreach ($locations as $location) {
                $userIds = UserPreference::where('location_id', $location->id)
                    ->pluck('user_id')
                    ->toArray();

                if (! $admin->isSiteAdmin()) {
                    $scopedIds = User::managedScopeUserIds($admin);
                    if ($scopedIds !== null) {
                        $userIds = array_values(array_intersect($userIds, $scopedIds));
                    }
                }

                $userCount = count($userIds);
                if ($userCount === 0) {
                    $csv([$location->name, 0, 0, 0, 0, 0, 0, 0, '0%', 0, 0, 0, 0, 0]);
                    continue;
                }

                $activeCount = User::whereIn('id', $userIds)->whereNull('suspended_at')->count();
                $verifiedCount = User::whereIn('id', $userIds)->whereNotNull('email_verified_at')->count();
                $courseAssigned = DB::table('course_user')->whereIn('user_id', $userIds)->count();
                $courseCompleted = DB::table('course_user')->whereIn('user_id', $userIds)->where('status', 'completed')->count();
                $completionRate = $courseAssigned > 0 ? round(($courseCompleted / $courseAssigned) * 100, 1) : 0;
                $overdueCount = DB::table('assignment_reminders')
                    ->whereIn('user_id', $userIds)
                    ->whereIn('status', ['pending', 'sent'])
                    ->whereDate('due_on', '<', Carbon::today()->toDateString())
                    ->distinct('user_id')
                    ->count('user_id');
                $avgXp = (int) User::whereIn('id', $userIds)->where('total_xp', '>', 0)->avg('total_xp');
                $activeStreaks = DB::table('streaks')
                    ->whereIn('user_id', $userIds)
                    ->where('current_streak', '>', 0)
                    ->where('last_activity_date', '>=', Carbon::today()->subDay()->toDateString())
                    ->count();
                $neverLoggedIn = User::whereIn('id', $userIds)->whereNull('last_login_at')->count();
                $inactive30 = User::whereIn('id', $userIds)->whereNotNull('last_login_at')->where('last_login_at', '<', now()->subDays(30))->count();

                $csv([
                    $location->name,
                    $userCount,
                    $activeCount,
                    $userCount - $activeCount,
                    $verifiedCount,
                    $userCount - $verifiedCount,
                    $courseAssigned,
                    $courseCompleted,
                    $completionRate . '%',
                    $overdueCount,
                    $avgXp,
                    $activeStreaks,
                    $neverLoggedIn,
                    $inactive30,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
