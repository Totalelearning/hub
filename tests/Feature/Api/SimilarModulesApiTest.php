<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SimilarModulesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_similar_modules_with_similarity_score(): void
    {
        $targetId = $this->createModule('Target', [1.0, 0.0, 0.0]);
        $closestId = $this->createModule('Closest', [0.9, 0.1, 0.0]);
        $secondId = $this->createModule('Second', [0.8, 0.2, 0.0]);
        $this->createModule('Far', [0.0, 1.0, 0.0]);

        $response = $this->getJson("/api/modules/similar/{$targetId}?limit=2");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $closestId)
            ->assertJsonPath('data.1.id', $secondId)
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title', 'description', 'similarity_score'],
                ],
            ]);
    }

    public function test_it_returns_not_found_when_module_does_not_exist(): void
    {
        $this->getJson('/api/modules/similar/999999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Learning module not found.');
    }

    public function test_it_returns_unprocessable_entity_when_module_embedding_is_missing(): void
    {
        $targetId = $this->createModule('No Embedding');

        $this->getJson("/api/modules/similar/{$targetId}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Learning module embedding is not available.');
    }

    public function test_it_validates_limit_query_parameter(): void
    {
        $targetId = $this->createModule('Target', [1.0, 0.0, 0.0]);

        $this->getJson("/api/modules/similar/{$targetId}?limit=0")
            ->assertStatus(422)
            ->assertJsonValidationErrors('limit');
    }

    private function createModule(string $title, ?array $values = null): int
    {
        return (int) DB::table('learning_modules')->insertGetId([
            'title' => $title,
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'published',
            'embedding' => $values === null ? null : $this->vectorLiteral($values),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function vectorLiteral(array $values): string
    {
        $dimensions = array_fill(0, 1536, 0.0);

        foreach ($values as $index => $value) {
            $dimensions[$index] = $value;
        }

        return '['.implode(',', $dimensions).']';
    }
}