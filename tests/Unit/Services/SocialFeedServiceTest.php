<?php

namespace Tests\Unit\Services;

use App\Models\SocialPost;
use App\Services\SocialFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SocialFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ranks_posts_by_engagement_and_recency(): void
    {
        $asOf = Carbon::parse('2026-02-13 12:00:00');

        $highEngagementOld = SocialPost::query()->create([
            'body' => 'High engagement old',
            'likes_count' => 20,
            'comments_count' => 6,
            'shares_count' => 3,
            'is_published' => true,
            'published_at' => $asOf->copy()->subHours(80),
        ]);

        $highRecencyLowEngagement = SocialPost::query()->create([
            'body' => 'Fresh low engagement',
            'likes_count' => 1,
            'comments_count' => 0,
            'shares_count' => 0,
            'is_published' => true,
            'published_at' => $asOf->copy()->subHours(1),
        ]);

        $balanced = SocialPost::query()->create([
            'body' => 'Balanced post',
            'likes_count' => 4,
            'comments_count' => 2,
            'shares_count' => 1,
            'is_published' => true,
            'published_at' => $asOf->copy()->subHours(12),
        ]);

        SocialPost::query()->create([
            'body' => 'Draft post',
            'likes_count' => 100,
            'comments_count' => 100,
            'shares_count' => 100,
            'is_published' => false,
            'published_at' => $asOf->copy()->subHour(),
        ]);

        $results = app(SocialFeedService::class)->rankedFeed(10, $asOf);

        $this->assertSame(
            [$highEngagementOld->id, $balanced->id, $highRecencyLowEngagement->id],
            $results->pluck('id')->all()
        );
        $this->assertCount(3, $results);
        $this->assertTrue((float) $results->first()->ranking_score > (float) $results->last()->ranking_score);
    }
}
