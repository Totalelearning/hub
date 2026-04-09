<?php

return [
    'enabled' => (bool) env('RANKING_AI_ENABLED', false),
    'provider' => env('RANKING_PROVIDER', 'deterministic'),

    'local_ai' => [
        'goal_semantic_boost_per_match' => (int) env('RANKING_LOCAL_AI_GOAL_BOOST_PER_MATCH', 4),
        'goal_semantic_boost_max' => (int) env('RANKING_LOCAL_AI_GOAL_BOOST_MAX', 10),
    ],

    'external_ai' => [
        'model' => env('RANKING_EXTERNAL_AI_MODEL', 'gpt-5-mini'),
        'endpoint' => env('RANKING_EXTERNAL_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'timeout' => (int) env('RANKING_EXTERNAL_AI_TIMEOUT', 15),
        'attempts' => (int) env('RANKING_EXTERNAL_AI_ATTEMPTS', 2),
        'retry_sleep_ms' => (int) env('RANKING_EXTERNAL_AI_RETRY_SLEEP_MS', 250),
        'max_boost' => (int) env('RANKING_EXTERNAL_AI_MAX_BOOST', 20),
    ],
];
