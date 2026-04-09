<?php

namespace Tests\Unit\Services;

use App\Contracts\FeedRanker;
use App\Models\LearningModule;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\FeedRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedRankingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_ai_ranker_and_logs_success_when_enabled(): void
    {
        config()->set('ranking.enabled', true);
        config()->set('ranking.provider', 'local_ai');

        $this->app->bind(FeedRanker::class, function () {
            return new class implements FeedRanker {
                public function rank(User $user, LearningModule $module, ?\App\Models\ModuleProgress $progress): array
                {
                    return [
                        'score' => 321,
                        'breakdown' => ['test_ai' => 321],
                        'renewal' => [],
                        'role_targeting' => [],
                        'compliance_targeting' => [],
                        'prerequisites' => [],
                        'acknowledgement' => [],
                        'assignment' => [],
                        'explanations' => [],
                        'highlights' => [],
                    ];
                }

                public function providerName(): string
                {
                    return 'test_ai';
                }
            };
        });

        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'AI Ranked Module',
            'description' => 'AI ranking test',
            'status' => 'published',
        ]);
        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => [],
            'difficulty' => 'any',
            'role' => 'manager',
        ]);

        $result = app(FeedRankingService::class)->rank($user, $module, null);

        $this->assertSame(321, $result['result']['score']);
        $this->assertSame('test_ai', $result['meta']['provider']);
        $this->assertNull($result['meta']['fallback_from']);

        $this->assertDatabaseHas('ai_provider_usages', [
            'provider' => 'test_ai',
            'capability' => 'feed_ranking',
            'success' => true,
        ]);
    }

    public function test_it_falls_back_to_deterministic_and_logs_failure_when_ai_errors(): void
    {
        config()->set('ranking.enabled', true);
        config()->set('ranking.provider', 'local_ai');

        $this->app->bind(FeedRanker::class, function () {
            return new class implements FeedRanker {
                public function rank(User $user, LearningModule $module, ?\App\Models\ModuleProgress $progress): array
                {
                    throw new \RuntimeException('simulated ai failure');
                }

                public function providerName(): string
                {
                    return 'test_ai';
                }
            };
        });

        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Fallback Ranked Module',
            'description' => 'Fallback test',
            'status' => 'published',
        ]);
        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => [],
            'difficulty' => 'any',
            'role' => 'manager',
        ]);

        $result = app(FeedRankingService::class)->rank($user, $module, null);

        $this->assertSame('deterministic', $result['meta']['provider']);
        $this->assertSame('test_ai', $result['meta']['fallback_from']);
        $this->assertArrayHasKey('score', $result['result']);
        $this->assertArrayHasKey('breakdown', $result['result']);

        $this->assertDatabaseHas('ai_provider_usages', [
            'provider' => 'test_ai',
            'capability' => 'feed_ranking',
            'success' => false,
        ]);
    }
}

