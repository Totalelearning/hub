<?php

namespace App\Services;

use RuntimeException;
use Throwable;

class RankingProviderProbeService
{
    public function __construct(
        private readonly RankingSettingsService $settings,
        private readonly RankingProviderStatusService $status,
        private readonly AiOpsLogger $opsLogger,
        private readonly ExternalAiRankingClient $client,
    ) {
    }

    public function probe(): array
    {
        $provider = (string) $this->settings->get('provider', 'deterministic');
        $startedAt = microtime(true);

        try {
            $result = match ($provider) {
                'local_ai' => $this->probeLocalAi(),
                'external_ai' => $this->probeExternalAi(),
                default => $this->probeDeterministic(),
            };

            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            $this->opsLogger->recordProviderUsage($provider, 'feed_ranking_probe', true, [
                'model' => $result['model'] ?? null,
                'request_id' => $result['request_id'] ?? null,
                'latency_ms' => $latencyMs,
                'metadata' => [
                    'message' => $result['message'],
                    'provider' => $provider,
                ],
            ]);

            return [
                'success' => true,
                'provider' => $provider,
                'message' => $result['message'],
                'request_id' => $result['request_id'] ?? null,
                'model' => $result['model'] ?? null,
                'latency_ms' => $latencyMs,
                'meta' => $result['meta'] ?? [],
            ];
        } catch (Throwable $exception) {
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            $this->opsLogger->recordFailure($provider, 'feed_ranking_probe', $exception, [
                'latency_ms' => $latencyMs,
                'metadata' => [
                    'provider' => $provider,
                ],
            ]);

            return [
                'success' => false,
                'provider' => $provider,
                'message' => $exception->getMessage(),
                'latency_ms' => $latencyMs,
                'error_type' => $exception::class,
            ];
        }
    }

    private function probeDeterministic(): array
    {
        return [
            'message' => 'Deterministic ranking is available without external dependencies.',
            'meta' => [
                'mode' => 'deterministic',
            ],
        ];
    }

    private function probeLocalAi(): array
    {
        return [
            'message' => 'Local AI heuristic ranking is configured and available.',
            'meta' => [
                'goal_boost_per_match' => (int) $this->settings->get('local_ai_goal_semantic_boost_per_match', 0),
                'goal_boost_max' => (int) $this->settings->get('local_ai_goal_semantic_boost_max', 0),
            ],
        ];
    }

    private function probeExternalAi(): array
    {
        $snapshot = $this->status->snapshot($this->settings);
        $provider = $snapshot['providers']['external_ai'] ?? null;

        if (! is_array($provider) || ! ($provider['ready'] ?? false)) {
            throw new RuntimeException($provider['issues'][0] ?? 'External AI provider is not ready.');
        }

        $response = $this->client->probe([
            'type' => 'feed_ranking_probe',
            'goal' => 'Validate external feed ranking provider connectivity.',
        ]);

        return [
            'message' => (string) (($response['reason'] ?? '') !== '' ? $response['reason'] : 'External AI provider responded successfully.'),
            'request_id' => $response['request_id'] ?? null,
            'model' => (string) ($response['model'] ?? config('ranking.external_ai.model', 'gpt-5-mini')),
            'meta' => [
                'status' => (string) ($response['status'] ?? 'ok'),
            ],
        ];
    }
}
