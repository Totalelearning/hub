<?php

namespace Database\Seeders;

use App\Models\CourseReinforcementAttempt;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use App\Services\GamificationSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamificationBackfillSeeder extends Seeder
{
    /**
     * Backfill XP for existing course completions and reinforcement attempts.
     * Idempotent — checks for existing transactions before inserting.
     */
    public function run(): void
    {
        $settings = app(GamificationSettingsService::class);
        $courseXp = (int) $settings->get('course_completed', 100);
        $reinforcementXp = (int) $settings->get('reinforcement_completed', 25);
        $perfectScoreXp = (int) $settings->get('perfect_score', 50);

        $this->command->info('Backfilling XP for existing course completions...');

        // Course completions
        $completions = DB::table('course_user')
            ->where('status', 'completed')
            ->get();

        $courseCount = 0;
        foreach ($completions as $completion) {
            $exists = XpTransaction::where('user_id', $completion->user_id)
                ->where('reason', XpTransaction::REASON_COURSE_COMPLETED)
                ->where('source_type', 'course')
                ->where('source_id', $completion->course_id)
                ->exists();

            if (! $exists) {
                XpTransaction::create([
                    'user_id' => $completion->user_id,
                    'xp_amount' => $courseXp,
                    'reason' => XpTransaction::REASON_COURSE_COMPLETED,
                    'source_type' => 'course',
                    'source_id' => $completion->course_id,
                    'metadata' => ['backfilled' => true],
                ]);
                $courseCount++;
            }
        }

        $this->command->info("  Awarded XP for {$courseCount} course completions.");

        // Reinforcement attempts
        $attempts = CourseReinforcementAttempt::whereIn('status', ['completed', 'gaps_found'])->get();

        $reinforcementCount = 0;
        $perfectCount = 0;

        foreach ($attempts as $attempt) {
            $exists = XpTransaction::where('user_id', $attempt->user_id)
                ->where('reason', XpTransaction::REASON_REINFORCEMENT_COMPLETED)
                ->where('source_type', 'course_reinforcement_attempt')
                ->where('source_id', $attempt->id)
                ->exists();

            if (! $exists) {
                XpTransaction::create([
                    'user_id' => $attempt->user_id,
                    'xp_amount' => $reinforcementXp,
                    'reason' => XpTransaction::REASON_REINFORCEMENT_COMPLETED,
                    'source_type' => 'course_reinforcement_attempt',
                    'source_id' => $attempt->id,
                    'metadata' => ['backfilled' => true, 'score_percent' => $attempt->score_percent],
                ]);
                $reinforcementCount++;
            }

            // Perfect score bonus
            if ($attempt->score_percent >= 100) {
                $perfectExists = XpTransaction::where('user_id', $attempt->user_id)
                    ->where('reason', XpTransaction::REASON_PERFECT_SCORE)
                    ->where('source_type', 'course_reinforcement_attempt')
                    ->where('source_id', $attempt->id)
                    ->exists();

                if (! $perfectExists) {
                    XpTransaction::create([
                        'user_id' => $attempt->user_id,
                        'xp_amount' => $perfectScoreXp,
                        'reason' => XpTransaction::REASON_PERFECT_SCORE,
                        'source_type' => 'course_reinforcement_attempt',
                        'source_id' => $attempt->id,
                        'metadata' => ['backfilled' => true, 'score_percent' => 100],
                    ]);
                    $perfectCount++;
                }
            }
        }

        $this->command->info("  Awarded XP for {$reinforcementCount} reinforcement completions.");
        $this->command->info("  Awarded XP for {$perfectCount} perfect scores.");

        // Recalculate total_xp for all users
        $this->command->info('Recalculating user totals...');

        $totals = XpTransaction::query()
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(xp_amount) as total'))
            ->get();

        foreach ($totals as $row) {
            User::where('id', $row->user_id)->update(['total_xp' => $row->total]);
        }

        $this->command->info("  Updated totals for {$totals->count()} users.");

        // Evaluate badges for all users with XP
        $this->command->info('Evaluating badges...');
        $gamification = app(GamificationService::class);
        $badgeCount = 0;

        $users = User::where('total_xp', '>', 0)->get();
        foreach ($users as $user) {
            $earned = $gamification->evaluateBadges($user);
            $badgeCount += $earned->count();
        }

        $this->command->info("  Awarded {$badgeCount} badges.");
        $this->command->info('Backfill complete!');
    }
}
