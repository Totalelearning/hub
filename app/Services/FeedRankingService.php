<?php

namespace App\Services;

use App\Contracts\FeedRanker;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;
use Throwable;

class FeedRankingService
{
    public function __construct(
        private readonly FeedScoringService $deterministic,
        private readonly FeedRanker $ranker,
        private readonly AiOpsLogger $aiOpsLogger,
        private readonly RankingSettingsService $settings,
    ) {
    }

    /**
     * @return array{
     *   result: array<string,mixed>,
     *   meta: array<string,mixed>
     * }
     */
    public function rank(User $user, LearningModule $module, ?ModuleProgress $progress): array
    {
        $enabled = (bool) $this->settings->get('enabled', config('ranking.enabled', false));
        $provider = $this->ranker->providerName();

        if (! $enabled || $provider === 'deterministic') {
            return [
                'result' => $this->deterministic->scoreWithBreakdown($user, $module, $progress),
                'meta' => [
                    'provider' => 'deterministic',
                    'fallback_from' => null,
                ],
            ];
        }

        try {
            $result = $this->ranker->rank($user, $module, $progress);
            $this->aiOpsLogger->recordProviderUsage($provider, 'feed_ranking', true, [
                'model' => $result['explanations']['ai_provider_model'] ?? null,
                'request_id' => $result['explanations']['ai_provider_request_id'] ?? null,
                'input_tokens_est' => $result['explanations']['ai_provider_input_tokens_est'] ?? null,
                'output_tokens_est' => $result['explanations']['ai_provider_output_tokens_est'] ?? null,
                'metadata' => [
                    'user_id' => $user->id,
                    'module_id' => $module->id,
                    'score' => (int) ($result['score'] ?? 0),
                    'boost' => (int) ($result['breakdown']['ai_semantic_boost'] ?? 0),
                    'reason' => $result['explanations']['ai_provider_reason'] ?? null,
                ],
            ]);

            return [
                'result' => $result,
                'meta' => [
                    'provider' => $provider,
                    'fallback_from' => null,
                ],
            ];
        } catch (Throwable $exception) {
            $this->aiOpsLogger->recordFailure($provider, 'feed_ranking', $exception, [
                'user_id' => $user->id,
                'module_id' => $module->id,
            ]);

            return [
                'result' => $this->deterministic->scoreWithBreakdown($user, $module, $progress),
                'meta' => [
                    'provider' => 'deterministic',
                    'fallback_from' => $provider,
                ],
            ];
        }
    }

    public function isVisibleToUser(User $user, LearningModule $module): bool
    {
        return $this->deterministic->isVisibleToUser($user, $module);
    }
}
