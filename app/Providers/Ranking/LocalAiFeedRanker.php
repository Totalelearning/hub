<?php

namespace App\Providers\Ranking;

use App\Contracts\FeedRanker;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Services\FeedScoringService;
use App\Services\RankingSettingsService;

class LocalAiFeedRanker implements FeedRanker
{
    public function __construct(
        private readonly FeedScoringService $scoring,
        private readonly RankingSettingsService $settings,
    ) {
    }

    public function rank(User $user, LearningModule $module, ?ModuleProgress $progress): array
    {
        $result = $this->scoring->scoreWithBreakdown($user, $module, $progress);

        $goal = strtolower(trim((string) ($user->preference?->goal ?? '')));
        $moduleText = strtolower(implode(' ', array_filter([
            $module->title,
            $module->description,
            $module->topic,
            $module->compliance_area,
        ])));

        $boost = 0;
        if ($goal !== '' && $moduleText !== '') {
            $tokens = collect(preg_split('/[^a-z0-9]+/i', $goal) ?: [])
                ->map(fn ($token) => strtolower(trim((string) $token)))
                ->filter(fn ($token) => strlen($token) >= 5)
                ->unique()
                ->values()
                ->all();

            $matches = collect($tokens)->filter(fn (string $token) => str_contains($moduleText, $token))->count();
            if ($matches > 0) {
                $perMatch = (int) $this->settings->get('local_ai_goal_semantic_boost_per_match', config('ranking.local_ai.goal_semantic_boost_per_match', 4));
                $maxBoost = (int) $this->settings->get('local_ai_goal_semantic_boost_max', config('ranking.local_ai.goal_semantic_boost_max', 10));
                $boost = min(max(0, $maxBoost), $matches * max(0, $perMatch));
            }
        }

        if ($boost > 0) {
            $result['breakdown']['ai_semantic_boost'] = $boost;
            $result['score'] = (int) $result['score'] + $boost;
            $result['highlights'][] = [
                'key' => 'ai_semantic_boost',
                'score' => $boost,
                'label' => 'AI semantic goal match',
            ];
        }

        return $result;
    }

    public function providerName(): string
    {
        return 'local_ai';
    }
}
