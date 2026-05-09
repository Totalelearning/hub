<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppLeaderboardController extends Controller
{
    public function index(Request $request, GamificationService $gamification): View
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $teamFilter = $request->query('team');

        // Available teams for filter dropdown
        $teams = DB::table('user_preferences')
            ->whereNotNull('team')
            ->where('team', '!=', '')
            ->distinct()
            ->pluck('team')
            ->sort()
            ->values();

        $individuals = $gamification->individualLeaderboard($teamFilter, 50);
        $teamLeaderboard = $gamification->teamLeaderboard();
        $userSummary = $gamification->userSummary($user);

        return view('app.leaderboard', [
            'individuals' => $individuals,
            'teamLeaderboard' => $teamLeaderboard,
            'teams' => $teams,
            'teamFilter' => $teamFilter,
            'currentUserId' => $user->id,
            'userSummary' => $userSummary,
        ]);
    }
}
