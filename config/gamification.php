<?php

return [
    /*
    |--------------------------------------------------------------------------
    | XP Awards
    |--------------------------------------------------------------------------
    | Points awarded for each qualifying action. Admins can override these
    | via the gamification_settings table; these values are the defaults.
    */
    'xp' => [
        'course_completed'          => 100,
        'reinforcement_completed'   => 25,
        'perfect_score'             => 50,
        'streak_3_day'              => 15,
        'streak_5_day'              => 30,
        'streak_7_day'              => 50,
        'streak_14_day'             => 100,
        'streak_30_day'             => 250,
    ],

    /*
    |--------------------------------------------------------------------------
    | Level Thresholds
    |--------------------------------------------------------------------------
    | Minimum total XP required to reach each level.
    */
    'levels' => [
        1  => 0,
        2  => 100,
        3  => 250,
        4  => 500,
        5  => 1000,
        6  => 1750,
        7  => 2750,
        8  => 4000,
        9  => 5500,
        10 => 7500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Streak Settings
    |--------------------------------------------------------------------------
    */
    'streak_type' => 'daily', // 'daily' — counts distinct calendar days with activity
];
