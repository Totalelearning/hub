<?php

return [
    'feed_scoring' => [
        'required_module' => 75,
        'renewal_due' => 90,
        'renewal_due_soon' => 25,
        'role_match' => 40,
        'compliance_match' => 35,
        'topic_match' => 50,
        'difficulty_match' => 20,
        'goal_affinity_per_keyword' => 5,
        'goal_affinity_max' => 10,
        'path_next_step' => 30,
        'not_completed' => 15,
        'recent_module_reengagement' => 12,
        'recent_module_reengagement_full_days' => 3,
        'recent_module_reengagement_mid_days' => 7,
        'recent_module_reengagement_window_days' => 14,
        'recent_topic_activity' => 5,
        'recent_topic_activity_window_days' => 7,
        'prerequisites_unlocked' => 10,
        'acknowledgement_required' => 20,
    ],
    'feed_scoring_presets' => [
        'balanced' => [
            'label' => 'Balanced',
            'weights' => [],
        ],
        'compliance_first' => [
            'label' => 'Compliance First',
            'weights' => [
                'required_module' => 120,
                'renewal_due' => 120,
                'renewal_due_soon' => 45,
                'compliance_match' => 60,
                'path_next_step' => 20,
                'topic_match' => 30,
                'goal_affinity_per_keyword' => 4,
                'goal_affinity_max' => 8,
            ],
        ],
        'engagement_first' => [
            'label' => 'Engagement First',
            'weights' => [
                'required_module' => 70,
                'renewal_due' => 70,
                'renewal_due_soon' => 25,
                'topic_match' => 70,
                'goal_affinity_per_keyword' => 6,
                'goal_affinity_max' => 14,
                'recent_module_reengagement' => 18,
                'recent_topic_activity' => 12,
                'path_next_step' => 40,
            ],
        ],
    ],

    'inactive_nudge_after_days' => 7,
    'inactive_nudge_cooldown_days' => 3,
    'not_started_nudge_after_days' => 10,
    'not_started_nudge_cooldown_days' => 5,

    'role_compliance_areas' => [
        'manager' => ['ai-safety', 'data-privacy', 'security-awareness', 'workplace-safety'],
        'specialist' => ['data-privacy', 'security-awareness'],
        'new-starter' => ['data-privacy', 'security-awareness', 'workplace-safety'],
        'operator' => ['workplace-safety', 'data-privacy'],
    ],
];
