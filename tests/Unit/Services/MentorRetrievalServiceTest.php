<?php

namespace Tests\Unit\Services;

use App\Models\LearningModule;
use App\Services\MentorRetrievalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MentorRetrievalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_keyword_fallback_when_embeddings_are_missing(): void
    {
        $module = LearningModule::query()->create([
            'title' => 'Keyword Module',
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'published',
            'embedding' => null,
        ]);

        $firstId = $this->insertUnit($module->id, 1, 'Algebra basics and equations', null);
        $secondId = $this->insertUnit($module->id, 2, 'Algebra practice examples', null);
        $this->insertUnit($module->id, 3, 'Unrelated chemistry lesson', null);

        $result = app(MentorRetrievalService::class)->retrieveContext('algebra equations', $module->id, 5);

        $this->assertSame('keyword_fallback', $result['strategy_used']);
        $this->assertSame([$firstId, $secondId], array_column($result['units'], 'id'));
        $this->assertTrue($result['units'][0]['score'] >= $result['units'][1]['score']);
    }

    public function test_it_uses_vector_cosine_when_embeddings_exist(): void
    {
        $module = LearningModule::query()->create([
            'title' => 'Vector Module',
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'published',
            'embedding' => null,
        ]);

        $targetLiteral = $this->vectorLiteralFromTerms(['algebra', 'mastery']);
        $otherLiteral = $this->vectorLiteralFromTerms(['history', 'timeline']);

        $closestId = $this->insertUnit($module->id, 1, 'Algebra mastery context', $targetLiteral);
        $this->insertUnit($module->id, 2, 'History timeline context', $otherLiteral);

        $result = app(MentorRetrievalService::class)->retrieveContext('algebra mastery', $module->id, 2);

        $this->assertSame('vector_cosine', $result['strategy_used']);
        $this->assertSame($closestId, $result['units'][0]['id']);
        $this->assertTrue($result['units'][0]['score'] >= $result['units'][1]['score']);
    }

    private function insertUnit(int $moduleId, int $position, string $content, ?string $embedding): int
    {
        return (int) DB::table('learning_units')->insertGetId([
            'learning_module_id' => $moduleId,
            'position' => $position,
            'content_text' => $content,
            'content_hash' => hash('sha256', $content.'|'.$position),
            'metadata' => null,
            'embedding' => $embedding,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function vectorLiteralFromTerms(array $terms): string
    {
        $vector = array_fill(0, 1536, 0.0);

        foreach ($terms as $term) {
            $index = abs(crc32(mb_strtolower($term))) % 1536;
            $vector[$index] += 1.0;
        }

        $norm = sqrt(array_reduce($vector, static fn (float $carry, float $value): float => $carry + ($value ** 2), 0.0));

        if ($norm > 0.0) {
            foreach ($vector as $index => $value) {
                $vector[$index] = $value / $norm;
            }
        }

        return '['.implode(',', $vector).']';
    }
}

