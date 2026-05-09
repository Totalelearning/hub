<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use App\Services\GamificationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminGamificationController extends Controller
{
    public function index(GamificationService $gamification): View
    {
        Gate::authorize('admin-access');

        $admin = auth()->user();

        // Summary stats
        $totalXpAwarded = XpTransaction::sum('xp_amount');
        $activeStreaks = DB::table('streaks')
            ->where('current_streak', '>', 0)
            ->where('last_activity_date', '>=', now()->subDay()->toDateString())
            ->count();
        $badgesEarned = UserBadge::count();
        $learnersWithXp = User::where('total_xp', '>', 0)->whereNull('suspended_at')->count();
        $avgXp = $learnersWithXp > 0
            ? (int) round(User::where('total_xp', '>', 0)->whereNull('suspended_at')->avg('total_xp'))
            : 0;

        // Team leaderboard
        $teamLeaderboard = $gamification->teamLeaderboard();

        // Top earners — scoped for managers
        $topEarners = User::query()
            ->forManagedScope($admin)
            ->select('users.id', 'users.name', 'users.total_xp')
            ->leftJoin('user_preferences', 'user_preferences.user_id', '=', 'users.id')
            ->addSelect('user_preferences.team')
            ->where('users.total_xp', '>', 0)
            ->whereNull('users.suspended_at')
            ->orderByDesc('users.total_xp')
            ->limit(20)
            ->get();

        // Badge distribution
        $badgeDistribution = Badge::active()
            ->ordered()
            ->withCount('users')
            ->get();
        $totalLearners = User::whereNull('suspended_at')->count();

        // XP activity (last 30 days)
        $xpByDay = XpTransaction::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw("DATE(created_at) as day"), DB::raw('SUM(xp_amount) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        return view('app.admin-gamification', [
            'totalXpAwarded' => $totalXpAwarded,
            'activeStreaks' => $activeStreaks,
            'badgesEarned' => $badgesEarned,
            'learnersWithXp' => $learnersWithXp,
            'avgXp' => $avgXp,
            'teamLeaderboard' => $teamLeaderboard,
            'topEarners' => $topEarners,
            'badgeDistribution' => $badgeDistribution,
            'totalLearners' => $totalLearners,
            'xpByDay' => $xpByDay,
        ]);
    }

    public function settings(GamificationSettingsService $settingsService): View
    {
        Gate::authorize('admin-write');

        $settings = $settingsService->all();
        $defaults = $settingsService->defaults();

        return view('app.admin-gamification-settings', [
            'settings' => $settings,
            'defaults' => $defaults,
        ]);
    }

    public function updateSettings(Request $request, GamificationSettingsService $settingsService): RedirectResponse
    {
        Gate::authorize('admin-write');

        $settingsService->update($request->input('settings', []));

        return redirect()
            ->route('app.admin.gamification.settings')
            ->with('success', 'Gamification settings updated.');
    }

    public function resetSettings(GamificationSettingsService $settingsService): RedirectResponse
    {
        Gate::authorize('admin-write');

        $settingsService->resetToDefaults();

        return redirect()
            ->route('app.admin.gamification.settings')
            ->with('success', 'Settings reset to defaults.');
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        Gate::authorize('admin-access');

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            $csv = fn (array $row) => fputcsv($handle, $row, ',', '"', '');

            $csv(['Learner', 'Email', 'Team', 'Total XP', 'Level', 'Current Streak', 'Longest Streak', 'Badges Earned']);

            $gamification = app(GamificationService::class);

            User::query()
                ->where('total_xp', '>', 0)
                ->whereNull('suspended_at')
                ->with(['preference', 'streak'])
                ->withCount('badges')
                ->orderByDesc('total_xp')
                ->chunk(100, function ($users) use ($csv, $gamification) {
                    foreach ($users as $user) {
                        $level = $gamification->calculateLevel($user->total_xp);
                        $csv([
                            $user->name,
                            $user->email,
                            $user->preference?->team ?? '',
                            $user->total_xp,
                            $level['level'].' - '.$level['name'],
                            $user->streak?->current_streak ?? 0,
                            $user->streak?->longest_streak ?? 0,
                            $user->badges_count,
                        ]);
                    }
                });

            fclose($handle);
        }, 'gamification-report-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
