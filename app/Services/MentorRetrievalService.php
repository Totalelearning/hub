<?php

namespace App\Services;

use App\Models\LearningUnit;

class MentorRetrievalService
{
    public function __construct(private AiOpsLogger $aiOpsLogger)
    {
    }

    public function retrieveContext(string $query, ?int $learningModuleId, int $limit = 8): array
    {
        $startedAt = microtime(true);
        $limit = max(1, min($limit, 20));

        $baseQuery = LearningUnit::query();

        if ($learningModuleId !== null) {
            $baseQuery->where('learning_module_id', $learningModuleId);
        }

        $hasEmbeddings = (clone $baseQuery)->whereNotNull('embedding')->exists();

        if ($hasEmbeddings) {
            $queryEmbedding = $this->buildQueryEmbeddingLiteral($query);

            $units = (clone $baseQuery)
                ->select(['id', 'learning_module_id', 'position', 'content_text'])
                ->selectRaw('1 - (embedding <=> ?::vector) as score', [$queryEmbedding])
                ->whereNotNull('embedding')
                ->orderByRaw('embedding <=> ?::vector asc', [$queryEmbedding])
                ->limit($limit)
                ->get()
                ->map(static fn (LearningUnit $unit): array => [
                    'id' => (int) $unit->id,
                    'learning_module_id' => (int) $unit->learning_module_id,
                    'position' => (int) $unit->position,
                    'content_text' => (string) $unit->content_text,
                    'score' => isset($unit->score) ? (float) $unit->score : 0.0,
                ])
                ->all();

            $result = [
                'units' => $units,
                'strategy_used' => 'vector_cosine',
            ];

            $this->aiOpsLogger->recordProviderUsage('local', 'retrieval_context', true, [
                'model' => 'deterministic-hash-embedding-v1',
                'input_tokens_est' => max(1, intdiv(mb_strlen($query), 4)),
                'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'metadata' => [
                    'strategy_used' => 'vector_cosine',
                    'top_k' => count($units),
                    'learning_module_id' => $learningModuleId,
                    'environment' => app()->environment(),
                ],
            ]);

            return $result;
        }

        $terms = $this->extractTerms($query);
        $fallbackQuery = (clone $baseQuery);

        $fallbackQuery->where(function ($query) use ($terms): void {
            foreach ($terms as $term) {
                $query->orWhere('content_text', 'ILIKE', '%'.$term.'%');
            }
        });

        $units = $fallbackQuery
            ->select(['id', 'learning_module_id', 'position', 'content_text'])
            ->orderBy('position')
            ->limit($limit * 3)
            ->get()
            ->map(function (LearningUnit $unit) use ($terms): array {
                $content = mb_strtolower($unit->content_text);
                $score = 0;

                foreach ($terms as $term) {
                    if (str_contains($content, mb_strtolower($term))) {
                        $score++;
                    }
                }

                return [
                    'id' => (int) $unit->id,
                    'learning_module_id' => (int) $unit->learning_module_id,
                    'position' => (int) $unit->position,
                    'content_text' => (string) $unit->content_text,
                    'score' => (float) $score,
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();

        $result = [
            'units' => $units,
            'strategy_used' => 'keyword_fallback',
        ];

        $this->aiOpsLogger->recordProviderUsage('local', 'retrieval_context', true, [
            'model' => null,
            'input_tokens_est' => max(1, intdiv(mb_strlen($query), 4)),
            'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'metadata' => [
                'strategy_used' => 'keyword_fallback',
                'top_k' => count($units),
                'learning_module_id' => $learningModuleId,
                'environment' => app()->environment(),
            ],
        ]);

        return $result;
    }

    private function extractTerms(string $query): array
    {
        $parts = preg_split('/\s+/u', mb_strtolower(trim($query))) ?: [];
        $parts = array_values(array_filter($parts, static fn (string $part): bool => mb_strlen($part) >= 3));

        if ($parts === []) {
            return ['course'];
        }

        return array_slice(array_unique($parts), 0, 6);
    }

    private function buildQueryEmbeddingLiteral(string $query): string
    {
        $vector = array_fill(0, 1536, 0.0);

        foreach ($this->extractTerms($query) as $term) {
            $index = abs(crc32($term)) % 1536;
            $vector[$index] += 1.0;
        }

        $norm = sqrt(array_reduce($vector, static fn (float $carry, float $value): float => $carry + ($value ** 2), 0.0));

        if ($norm > 0.0) {
            foreach ($vector as $index => $value) {
                $vector[$index] = $value / $norm;
            }
        }

        $literal = '['.implode(',', $vector).']';

        $this->aiOpsLogger->recordProviderUsage('local', 'embeddings', true, [
            'model' => 'deterministic-hash-embedding-v1',
            'input_tokens_est' => max(1, intdiv(mb_strlen($query), 4)),
            'output_tokens_est' => 1536,
            'metadata' => [
                'dimension' => 1536,
                'environment' => app()->environment(),
            ],
        ]);

        return $literal;
    }
}
