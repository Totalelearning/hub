<?php

namespace Tests\Feature\Api;

use App\Models\SocialPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FeedApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_ranked_feed_response_with_expected_fields(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-13 12:00:00'));

        $mostRelevant = SocialPost::query()->create([
            'body' => 'Most relevant',
            'likes_count' => 6,
            'comments_count' => 3,
            'shares_count' => 1,
            'is_published' => true,
            'published_at' => now()->subHours(6),
        ]);

        $second = SocialPost::query()->create([
            'body' => 'Second relevant',
            'likes_count' => 1,
            'comments_count' => 0,
            'shares_count' => 0,
            'is_published' => true,
            'published_at' => now()->subHours(1),
        ]);

        SocialPost::query()->create([
            'body' => 'Not published',
            'likes_count' => 999,
            'comments_count' => 999,
            'shares_count' => 999,
            'is_published' => false,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/feed?limit=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $mostRelevant->id)
            ->assertJsonPath('data.1.id', $second->id)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'learning_module_id',
                        'body',
                        'likes_count',
                        'comments_count',
                        'shares_count',
                        'published_at',
                        'ranking_score',
                    ],
                ],
            ]);

        Carbon::setTestNow();
    }

    public function test_it_validates_feed_limit_parameter(): void
    {
        $this->getJson('/api/feed?limit=0')
            ->assertStatus(422)
            ->assertJsonValidationErrors('limit');
    }
}
