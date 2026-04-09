<?php

namespace App\Services;

class RankingProviderStatusService
{
    public function snapshot(RankingSettingsService $settings): array
    {
        $current = $settings->all();
        $providers = [
            'deterministic' => [
                'label' => 'Deterministic',
                'ready' => true,
                'issues' => [],
                'details' => [
                    'mode' => 'Always available',
                ],
            ],
            'local_ai' => [
                'label' => 'Local AI Heuristic',
                'ready' => true,
                'issues' => [],
                'details' => [
                    'goal_boost_per_match' => $current['local_ai_goal_semantic_boost_per_match'] ?? null,
                    'goal_boost_max' => $current['local_ai_goal_semantic_boost_max'] ?? null,
                ],
            ],
            'external_ai' => $this->externalAiStatus($current),
        ];

        $activeProvider = (string) ($current['provider'] ?? 'deterministic');

        return [
            'enabled' => (bool) ($current['enabled'] ?? false),
            'active_provider' => $activeProvider,
            'active_provider_label' => $providers[$activeProvider]['label'] ?? ucfirst(str_replace('_', ' ', $activeProvider)),
            'active_provider_ready' => $providers[$activeProvider]['ready'] ?? false,
            'providers' => $providers,
        ];
    }

    private function externalAiStatus(array $current): array
    {
        $apiKeyConfigured = trim((string) config('services.openai.api_key', '')) !== '';
        $endpoint = trim((string) config('ranking.external_ai.endpoint', ''));
        $model = trim((string) config('ranking.external_ai.model', ''));
        $timeout = (int) config('ranking.external_ai.timeout', 15);
        $attempts = (int) ($current['external_ai_attempts'] ?? config('ranking.external_ai.attempts', 2));
        $retrySleepMs = (int) ($current['external_ai_retry_sleep_ms'] ?? config('ranking.external_ai.retry_sleep_ms', 250));
        $maxBoost = (int) ($current['external_ai_max_boost'] ?? config('ranking.external_ai.max_boost', 20));

        $issues = [];

        if (! $apiKeyConfigured) {
            $issues[] = 'OPENAI_API_KEY is not configured.';
        }

        if ($endpoint === '') {
            $issues[] = 'External endpoint is empty.';
        }

        if ($model === '') {
            $issues[] = 'External model is empty.';
        }

        return [
            'label' => 'External AI Provider',
            'ready' => $issues === [],
            'issues' => $issues,
            'details' => [
                'api_key_configured' => $apiKeyConfigured,
                'endpoint' => $endpoint,
                'model' => $model,
                'timeout' => max(1, $timeout),
                'attempts' => max(1, $attempts),
                'retry_sleep_ms' => max(0, $retrySleepMs),
                'max_boost' => max(0, $maxBoost),
            ],
        ];
    }
}
