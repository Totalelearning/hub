<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\UserBadge;
use App\Services\GamificationService;
use Illuminate\View\View;

class AppBadgesController extends Controller
{
    public function index(GamificationService $gamification): View
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $allBadges = Badge::active()->ordered()->get();

        $earnedBadgeIds = UserBadge::where('user_id', $user->id)
            ->pluck('badge_id')
            ->all();

        $earnedAt = UserBadge::where('user_id', $user->id)
            ->pluck('earned_at', 'badge_id')
            ->all();

        // Group by category
        $categories = $allBadges->groupBy('category')->map(function ($badges, $category) use ($earnedBadgeIds, $earnedAt) {
            return $badges->map(function ($badge) use ($earnedBadgeIds, $earnedAt) {
                $badge->is_earned = in_array($badge->id, $earnedBadgeIds, true);
                $badge->earned_at_date = $earnedAt[$badge->id] ?? null;
                return $badge;
            });
        });

        $userSummary = $gamification->userSummary($user);

        return view('app.badges', [
            'categories' => $categories,
            'earnedCount' => count($earnedBadgeIds),
            'totalCount' => $allBadges->count(),
            'userSummary' => $userSummary,
        ]);
    }
}
