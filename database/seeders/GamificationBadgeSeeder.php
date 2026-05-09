<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class GamificationBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // ── Achievement badges ──────────────────────────────────
            [
                'slug' => 'first_course',
                'name' => 'First Course Complete',
                'description' => 'Completed your very first course.',
                'icon' => 'bi-mortarboard',
                'category' => 'achievement',
                'criteria_type' => 'courses_completed',
                'criteria_value' => 1,
                'xp_reward' => 25,
                'sort_order' => 1,
            ],
            [
                'slug' => 'five_courses',
                'name' => 'Five Courses Complete',
                'description' => 'Completed five courses — great progress!',
                'icon' => 'bi-star',
                'category' => 'achievement',
                'criteria_type' => 'courses_completed',
                'criteria_value' => 5,
                'xp_reward' => 50,
                'sort_order' => 2,
            ],
            [
                'slug' => 'ten_courses',
                'name' => 'Ten Courses Complete',
                'description' => 'Completed ten courses — impressive dedication.',
                'icon' => 'bi-star-fill',
                'category' => 'achievement',
                'criteria_type' => 'courses_completed',
                'criteria_value' => 10,
                'xp_reward' => 100,
                'sort_order' => 3,
            ],

            // ── Mastery badges ──────────────────────────────────────
            [
                'slug' => 'perfect_score',
                'name' => 'Perfect Score',
                'description' => 'Achieved 100% on a knowledge check.',
                'icon' => 'bi-bullseye',
                'category' => 'mastery',
                'criteria_type' => 'perfect_scores',
                'criteria_value' => 1,
                'xp_reward' => 25,
                'sort_order' => 10,
            ],
            [
                'slug' => 'five_perfect',
                'name' => 'Five Perfect Scores',
                'description' => 'Achieved five perfect knowledge check scores.',
                'icon' => 'bi-lightning-charge-fill',
                'category' => 'mastery',
                'criteria_type' => 'perfect_scores',
                'criteria_value' => 5,
                'xp_reward' => 75,
                'sort_order' => 11,
            ],

            // ── Streak badges ───────────────────────────────────────
            [
                'slug' => 'streak_3',
                'name' => '3-Day Streak',
                'description' => 'Learned three days in a row.',
                'icon' => 'bi-fire',
                'category' => 'streak',
                'criteria_type' => 'streak_days',
                'criteria_value' => 3,
                'xp_reward' => 0,
                'sort_order' => 20,
            ],
            [
                'slug' => 'streak_5',
                'name' => '5-Day Streak',
                'description' => 'Five consecutive days of learning.',
                'icon' => 'bi-fire',
                'category' => 'streak',
                'criteria_type' => 'streak_days',
                'criteria_value' => 5,
                'xp_reward' => 0,
                'sort_order' => 21,
            ],
            [
                'slug' => 'streak_7',
                'name' => 'Week-Long Streak',
                'description' => 'A full week of daily learning!',
                'icon' => 'bi-fire',
                'category' => 'streak',
                'criteria_type' => 'streak_days',
                'criteria_value' => 7,
                'xp_reward' => 0,
                'sort_order' => 22,
            ],
            [
                'slug' => 'streak_30',
                'name' => '30-Day Streak',
                'description' => 'An entire month of consistent learning!',
                'icon' => 'bi-fire',
                'category' => 'streak',
                'criteria_type' => 'streak_days',
                'criteria_value' => 30,
                'xp_reward' => 0,
                'sort_order' => 23,
            ],

            // ── Topic badges ────────────────────────────────────────
            [
                'slug' => 'safeguarding_complete',
                'name' => 'Safeguarding Champion',
                'description' => 'Completed all safeguarding courses.',
                'icon' => 'bi-shield-check',
                'category' => 'topic',
                'criteria_type' => 'topic_courses_completed',
                'criteria_value' => 1,
                'criteria_meta' => ['topic' => 'safeguarding'],
                'xp_reward' => 50,
                'sort_order' => 30,
            ],
            [
                'slug' => 'health_safety_complete',
                'name' => 'Health & Safety Pro',
                'description' => 'Completed a health and safety course.',
                'icon' => 'bi-heart-pulse',
                'category' => 'topic',
                'criteria_type' => 'topic_courses_completed',
                'criteria_value' => 1,
                'criteria_meta' => ['topic' => 'health & safety'],
                'xp_reward' => 50,
                'sort_order' => 31,
            ],

            // ── XP milestone badges ─────────────────────────────────
            [
                'slug' => 'xp_500',
                'name' => 'Rising Star',
                'description' => 'Earned 500 XP — you are on your way!',
                'icon' => 'bi-rocket-takeoff',
                'category' => 'achievement',
                'criteria_type' => 'total_xp',
                'criteria_value' => 500,
                'xp_reward' => 0,
                'sort_order' => 40,
            ],
            [
                'slug' => 'xp_2000',
                'name' => 'Knowledge Leader',
                'description' => 'Earned 2,000 XP — a true knowledge leader.',
                'icon' => 'bi-gem',
                'category' => 'achievement',
                'criteria_type' => 'total_xp',
                'criteria_value' => 2000,
                'xp_reward' => 0,
                'sort_order' => 41,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge,
            );
        }
    }
}
