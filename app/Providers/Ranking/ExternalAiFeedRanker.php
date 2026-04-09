<?php

namespace App\Providers\Ranking;

use App\Contracts\FeedRanker;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Services\ExternalAiRankingClient;
use App\Services\FeedScoringService;
use App\Services\RankingSettingsService;

class ExternalAiFeedRanker implements FeedRanker
{
    public function __construct(
        private readonly FeedScoringService $scoring,
        private readonly RankingSettingsService $settings,
        private readonly ExternalAiRankingClient $client,
    ) {
    }

    public function rank(User $user, LearningModule $module, ?ModuleProgress $progress): array
    {
        $base = $this->scoring->scoreWithBreakdown($user, $module, $progress);
        $maxBoost = max(0, (int) $this->settings->get('external_ai_max_boost', config('ranking.external_ai.max_boost', 20)));
        $response = $this->client->rank($this->rankingContext($user, $module, $progress, $base));
        $boost = min($maxBoost, max(0, (int) ($response['boost'] ?? 0)));
        $reason = trim((string) ($response['reason'] ?? ''));

        if ($boost > 0) {
            $base['score'] += $boost;
            $base['breakdown']['ai_semantic_boost'] = $boost;
            $base['explanations']['ai_provider_reason'] = $reason !== '' ? $reason : null;
            $base['highlights'][] = [
                'key' => 'ai_semantic_boost',
                'score' => $boost,
                'label' => 'AI ranker boost',
            ];
        }

        $base['explanations']['ai_provider_reason'] = $reason !== '' ? $reason : ($base['explanations']['ai_provider_reason'] ?? null);
        $base['explanations']['ai_provider_model'] = (string) ($response['model'] ?? config('ranking.external_ai.model', 'gpt-5-mini'));
        $base['explanations']['ai_provider_request_id'] = $response['request_id'] ?? null;
        $base['explanations']['ai_provider_input_tokens_est'] = $response['input_tokens_est'] ?? null;
        $base['explanations']['ai_provider_output_tokens_est'] = $response['output_tokens_est'] ?? null;

        return $base;
    }

    public function providerName(): string
    {
        return 'external_ai';
    }

    private function rankingContext(User $user, LearningModule $module, ?ModuleProgress $progress, array $base): array
    {
        return [
            'user' => [
                'role' => $user->preference?->role,
                'goal' => $user->preference?->goal,
                'topics' => $user->preference?->topics ?? [],
                'difficulty' => $user->preference?->difficulty,
            ],
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'description' => $module->description,
                'topic' => $module->topic,
                'difficulty' => $module->difficulty,
                'compliance_area' => $module->compliance_area,
            ],
            'progress' => [
                'status' => $progress?->status ?? 'not_started',
                'percent_complete' => (int) ($progress?->percent_complete ?? 0),
            ],
            'deterministic_score' => [
                'score' => (int) ($base['score'] ?? 0),
                'breakdown' => $base['breakdown'] ?? [],
                'highlights' => $base['highlights'] ?? [],
            ],
        ];
    }
}
