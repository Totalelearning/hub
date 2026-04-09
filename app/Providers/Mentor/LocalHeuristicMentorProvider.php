<?php

namespace App\Providers\Mentor;

use App\Contracts\MentorProvider;
use App\Services\AiOpsLogger;

class LocalHeuristicMentorProvider implements MentorProvider
{
    public function answer(string $question, array $contextUnits, array $options = []): array
    {
        $startedAt = microtime(true);

        if ($contextUnits === []) {
            $response = [
                'answer_text' => 'I do not have enough course context to answer that yet. Try ingesting content or asking a more specific question.',
                'citations' => [],
                'provider' => 'local',
                'model' => 'heuristic-v1',
                'confidence' => 'low',
            ];

            app(AiOpsLogger::class)->recordProviderUsage('local', 'mentor_answer', true, [
                'model' => 'heuristic-v1',
                'input_tokens_est' => max(1, intdiv(mb_strlen($question), 4)),
                'output_tokens_est' => max(1, intdiv(mb_strlen($response['answer_text']), 4)),
                'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'metadata' => [
                    'context_unit_count' => count($contextUnits),
                    'feature_flags' => ['mentor_provider_enabled' => (bool) config('mentor.enabled', true)],
                    'environment' => app()->environment(),
                ],
            ]);

            return $response;
        }

        $topUnits = array_slice($contextUnits, 0, 3);
        $citations = array_values(array_map(static fn (array $unit): int => (int) $unit['id'], $topUnits));

        $summaryParts = array_map(
            static function (array $unit): string {
                $snippet = trim((string) ($unit['content_text'] ?? ''));
                $snippet = preg_replace('/\s+/u', ' ', $snippet) ?? $snippet;
                $snippet = mb_substr($snippet, 0, 220);

                return "From unit {$unit['id']}: {$snippet}";
            },
            $topUnits
        );

        $response = [
            'answer_text' => "Based on the available course context:\n- ".implode("\n- ", $summaryParts),
            'citations' => $citations,
            'provider' => 'local',
            'model' => 'heuristic-v1',
            'confidence' => count($contextUnits) >= 2 ? 'medium' : 'low',
        ];

        app(AiOpsLogger::class)->recordProviderUsage('local', 'mentor_answer', true, [
            'model' => 'heuristic-v1',
            'input_tokens_est' => max(1, intdiv(mb_strlen($question), 4)),
            'output_tokens_est' => max(1, intdiv(mb_strlen($response['answer_text']), 4)),
            'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'metadata' => [
                'context_unit_count' => count($contextUnits),
                'feature_flags' => ['mentor_provider_enabled' => (bool) config('mentor.enabled', true)],
                'environment' => app()->environment(),
            ],
        ]);

        return $response;
    }
}
