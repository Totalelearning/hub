<?php

namespace Tests\Feature\Services;

use App\Services\VectorSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VectorSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_nearest_modules_and_excludes_the_target_module(): void
    {
        $targetId = $this->createModule('Target', [1.0, 0.0, 0.0]);
        $closestId = $this->createModule('Closest', [0.95, 0.05, 0.0]);
        $secondId = $this->createModule('Second', [0.75, 0.25, 0.0]);
        $this->createModule('Far', [0.0, 1.0, 0.0]);

        $results = app(VectorSearchService::class)->similarToModuleId($targetId, 2);

        $this->assertCount(2, $results);
        $this->assertSame([$closestId, $secondId], $results->pluck('id')->all());
        $this->assertFalse(in_array($targetId, $results->pluck('id')->all(), true));
    }

    private function createModule(string $title, array $values): int
    {
        return (int) DB::table('learning_modules')->insertGetId([
            'title' => $title,
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'published',
            'embedding' => $this->vectorLiteral($values),
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
