<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\Streak;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\XpTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public function __construct(
        private GamificationSettingsService $settings,
    ) {}

    // ── XP Awarding ──────────────────────────────────────────────

    /**
     * Award XP for completing a course.
     */
    public function awardCourseCompletion(User $user, Course $course): ?XpTransaction
    {
        // Idempotency: don't double-award for the same course completion
        if ($this->alreadyAwarded($user, XpTransaction::REASON_COURSE_COMPLETED, 'course', $course->id)) {
            return null;
        }

        $amount = (int) $this->settings->get('course_completed', 100);

        return $this->recordXp($user, $amount, XpTransaction::REASON_COURSE_COMPLETED, 'course', $course->id, [
            'course_title' => $course->title,
        ]);
    }

    /**
     * Award XP for completing a reinforcement attempt (pass or fail).
     */
    public function awardReinforcementCompletion(User $user, CourseReinforcementAttempt $attempt): ?XpTransaction
    {
        if ($this->alreadyAwarded($user, XpTransaction::REASON_REINFORCEMENT_COMPLETED, 'course_reinforcement_attempt', $attempt->id)) {
            return null;
        }

        $amount = (int) $this->settings->get('reinforcement_completed', 25);

        return $this->recordXp($user, $amount, XpTransaction::REASON_REINFORCEMENT_COMPLETED, 'course_reinforcement_attempt', $attempt->id, [
            'score_percent' => $attempt->score_percent,
        ]);
    }

    /**
     * Award bonus XP for a perfect reinforcement score (100%).
     */
    public function awardPerfectScore(User $user, CourseReinforcementAttempt $attempt): ?XpTransaction
    {
        if ($this->alreadyAwarded($user, XpTransaction::REASON_PERFECT_SCORE, 'course_reinforcement_attempt', $attempt->id)) {
            return null;
        }

        $amount = (int) $this->settings->get('perfect_score', 50);

        return $this->recordXp($user, $amount, XpTransaction::REASON_PERFECT_SCORE, 'course_reinforcement_attempt', $attempt->id, [
            'score_percent' => 100,
        ]);
    }

    /**
     * Internal: create an XP transaction and increment the user's total.
     */
    private function recordXp(User $user, int $amount, string $reason, ?string $sourceType, ?int $sourceId, array $metadata = []): XpTransaction
    {
        $transaction = XpTransaction::create([
            'user_id'     => $user->id,
            'xp_amount'   => $amount,
            'reason'      => $reason,
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
            'metadata'    => $metadata,
        ]);

        // Atomic increment avoids race conditions
        User::where('id', $user->id)->increment('total_xp', $amount);
        $user->refresh();

        return $transaction;
    }

    /**
     * Check if XP has already been awarded for a specific source.
     */
    private function alreadyAwarded(User $user, string $reason, string $sourceType, int $sourceId): bool
    {
        return XpTransaction::where('user_id', $user->id)
            ->where('reason', $reason)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->exists();
    }

    // ── Streaks ──────────────────────────────────────────────────

    /**
     * Record a learning activity and update the user's streak.
     * Call this on course completion and reinforcement completion.
     */
    public function recordActivity(User $user): void
    {
        $today = Carbon::today();
        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_activity_date' => null],
        );

        // Already logged today — no change
        if ($streak->last_activity_date?->isSameDay($today)) {
            return;
        }

        $yesterday = $today->copy()->subDay();

        if ($streak->last_activity_date?->isSameDay($yesterday)) {
            // Consecutive day — extend streak
            $streak->current_streak++;
        } else {
            // Gap — reset to 1
            $streak->current_streak = 1;
        }

        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_activity_date = $today;
        $streak->save();

        // Award milestone XP
        $milestones = [
            3  => 'streak_3_day',
            5  => 'streak_5_day',
            7  => 'streak_7_day',
            14 => 'streak_14_day',
            30 => 'streak_30_day',
        ];

        $days = $streak->current_streak;
        if (isset($milestones[$days])) {
            $key = $milestones[$days];
            $amount = (int) $this->settings->get($key, 0);

            if ($amount > 0) {
                // Check we haven't already awarded for this specific streak milestone+length
                $existing = XpTransaction::where('user_id', $user->id)
                    ->where('reason', XpTransaction::REASON_STREAK_BONUS)
                    ->where('source_type', 'streak')
                    ->whereJsonContains('metadata->milestone', $key)
                    ->whereJsonContains('metadata->streak_length', $days)
                    ->exists();

                if (! $existing) {
                    $this->recordXp($user, $amount, XpTransaction::REASON_STREAK_BONUS, 'streak', $streak->id, [
                        'milestone' => $key,
                        'streak_length' => $days,
                    ]);
                }
            }
        }
    }

    /**
     * Get the current streak status for a user.
     */
    public function getStreakStatus(User $user): array
    {
        $streak = $user->streak;

        if (! $streak) {
            return ['current' => 0, 'longest' => 0, 'last_date' => null, 'is_active_today' => false];
        }

        return [
            'current' => $streak->current_streak,
            'longest' => $streak->longest_streak,
            'last_date' => $streak->last_activity_date,
            'is_active_today' => $streak->last_activity_date?->isSameDay(Carbon::today()) ?? false,
        ];
    }

    // ── Badges ───────────────────────────────────────────────────

    /**
     * Evaluate all active badges and award any newly earned ones.
     * Returns collection of newly earned Badge models.
     */
    public function evaluateBadges(User $user): Collection
    {
        $earnedBadgeIds = UserBadge::where('user_id', $user->id)->pluck('badge_id');

        $candidates = Badge::active()
            ->whereNotIn('id', $earnedBadgeIds)
            ->ordered()
            ->get();

        $newlyEarned = collect();

        foreach ($candidates as $badge) {
            if ($this->checkCriteria($badge, $user)) {
                UserBadge::create([
                    'user_id'     => $user->id,
                    'badge_id'    => $badge->id,
                    'earned_at'   => now(),
                    'source_type' => $badge->criteria_type,
                ]);

                // Award bonus XP for the badge itself
                if ($badge->xp_reward > 0) {
                    $this->recordXp($user, $badge->xp_reward, XpTransaction::REASON_BADGE_BONUS, 'badge', $badge->id, [
                        'badge_slug' => $badge->slug,
                        'badge_name' => $badge->name,
                    ]);
                }

                $newlyEarned->push($badge);
            }
        }

        return $newlyEarned;
    }

    /**
     * Check whether a user meets a badge's criteria.
     */
    private function checkCriteria(Badge $badge, User $user): bool
    {
        return match ($badge->criteria_type) {
            'courses_completed' => $this->countCompletedCourses($user) >= $badge->criteria_value,

            'perfect_scores' => XpTransaction::where('user_id', $user->id)
                ->where('reason', XpTransaction::REASON_PERFECT_SCORE)
                ->count() >= $badge->criteria_value,

            'streak_days' => ($user->streak?->current_streak ?? 0) >= $badge->criteria_value,

            'topic_courses_completed' => $this->countCompletedCoursesForTopic(
                $user,
                $badge->criteria_meta['topic'] ?? '',
            ) >= $badge->criteria_value,

            'total_xp' => ($user->total_xp ?? 0) >= $badge->criteria_value,

            default => false,
        };
    }

    private function countCompletedCourses(User $user): int
    {
        return DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
    }

    private function countCompletedCoursesForTopic(User $user, string $topic): int
    {
        if (! $topic) {
            return 0;
        }

        return DB::table('course_user')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('course_user.user_id', $user->id)
            ->where('course_user.status', 'completed')
            ->whereRaw('LOWER(courses.topic) = ?', [strtolower($topic)])
            ->count();
    }

    // ── Leaderboard ──────────────────────────────────────────────

    /**
     * Individual leaderboard ranked by total XP.
     */
    public function individualLeaderboard(?string $team = null, int $limit = 50): Collection
    {
        $query = User::query()
            ->select('users.id', 'users.name', 'users.total_xp')
            ->leftJoin('user_preferences', 'user_preferences.user_id', '=', 'users.id')
            ->leftJoin('streaks', 'streaks.user_id', '=', 'users.id')
            ->addSelect('user_preferences.team')
            ->addSelect('streaks.current_streak')
            ->where('users.total_xp', '>', 0)
            ->whereNull('users.suspended_at');

        if ($team) {
            $query->where('user_preferences.team', $team);
        }

        return $query
            ->orderByDesc('users.total_xp')
            ->limit($limit)
            ->get()
            ->values()
            ->map(function ($row, int $index) {
                $row->rank = $index + 1;
                $row->level = $this->calculateLevel($row->total_xp)['level'];
                $row->badge_count = UserBadge::where('user_id', $row->id)->count();
                return $row;
            });
    }

    /**
     * Team leaderboard aggregating XP across team members.
     */
    public function teamLeaderboard(): Collection
    {
        return DB::table('users')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'users.id')
            ->whereNotNull('user_preferences.team')
            ->where('user_preferences.team', '!=', '')
            ->whereNull('users.suspended_at')
            ->groupBy('user_preferences.team')
            ->select(
                'user_preferences.team',
                DB::raw('SUM(users.total_xp) as total_xp'),
                DB::raw('COUNT(users.id) as member_count'),
                DB::raw('ROUND(AVG(users.total_xp)) as avg_xp'),
            )
            ->orderByDesc('total_xp')
            ->get()
            ->values()
            ->map(function ($row, int $index) {
                $row->rank = $index + 1;
                return $row;
            });
    }

    // ── Dashboard Summary ────────────────────────────────────────

    /**
     * Everything needed for the learner gamification widget.
     */
    public function userSummary(User $user): array
    {
        $user->loadMissing(['streak', 'badges']);

        $level = $this->calculateLevel($user->total_xp ?? 0);
        $streak = $this->getStreakStatus($user);

        // User's rank (1-based position in XP leaderboard)
        $rank = User::where('total_xp', '>', $user->total_xp ?? 0)
            ->whereNull('suspended_at')
            ->count() + 1;

        // Team rank
        $userTeam = $user->preference?->team;
        $teamRank = null;
        if ($userTeam) {
            $teamLeaderboard = $this->teamLeaderboard();
            $teamEntry = $teamLeaderboard->firstWhere('team', $userTeam);
            $teamRank = $teamEntry?->rank;
        }

        // Recent XP (last 7 days)
        $recentXp = XpTransaction::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->sum('xp_amount');

        return [
            'total_xp' => $user->total_xp ?? 0,
            'level' => $level['level'],
            'level_name' => $level['name'],
            'xp_in_level' => $level['xp_in_level'],
            'xp_for_level' => $level['xp_for_level'],
            'progress_percent' => $level['progress_percent'],
            'streak' => $streak,
            'badges' => $user->badges->take(5),
            'badge_count' => $user->badges->count(),
            'rank' => $rank,
            'team_rank' => $teamRank,
            'recent_xp' => (int) $recentXp,
        ];
    }

    /**
     * Calculate level from total XP.
     */
    public function calculateLevel(int $totalXp): array
    {
        $levels = config('gamification.levels', []);
        ksort($levels);

        $currentLevel = 1;
        $currentThreshold = 0;
        $nextThreshold = null;

        foreach ($levels as $level => $threshold) {
            if ($totalXp >= $threshold) {
                $currentLevel = $level;
                $currentThreshold = $threshold;
            } else {
                $nextThreshold = $threshold;
                break;
            }
        }

        // If we're at max level
        if ($nextThreshold === null) {
            $nextThreshold = $currentThreshold;
        }

        $xpInLevel = $totalXp - $currentThreshold;
        $xpForLevel = max(1, $nextThreshold - $currentThreshold);
        $progressPercent = $nextThreshold > $currentThreshold
            ? (int) min(100, round(($xpInLevel / $xpForLevel) * 100))
            : 100;

        $names = [
            1  => 'Newcomer',
            2  => 'Explorer',
            3  => 'Learner',
            4  => 'Achiever',
            5  => 'Scholar',
            6  => 'Expert',
            7  => 'Master',
            8  => 'Champion',
            9  => 'Legend',
            10 => 'Grandmaster',
        ];

        return [
            'level' => $currentLevel,
            'name' => $names[$currentLevel] ?? 'Level '.$currentLevel,
            'xp_in_level' => $xpInLevel,
            'xp_for_level' => $xpForLevel,
            'progress_percent' => $progressPercent,
        ];
    }
}
