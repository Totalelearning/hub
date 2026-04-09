<?php

namespace Tests\Unit;

use App\Models\LearningModule;
use App\Models\User;
use App\Models\UserPreference;
use App\Providers\Ranking\ExternalAiFeedRanker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalAiFeedRankerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_applies_external_ai_boost_from_http_response(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('ranking.external_ai.max_boost', 20);

        Http::fake([
            '*' => Http::response([
                'id' => 'resp_123',
                'model' => 'gpt-5-mini',
                'usage' => [
                    'prompt_tokens' => 87,
                    'completion_tokens' => 19,
                ],
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'boost' => 7,
                            'reason' => 'Goal alignment appears stronger semantically.',
                        ], JSON_THROW_ON_ERROR),
                    ],
                ]],
            ], 200, ['x-request-id' => 'req_123']),
        ]);

        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'goal' => 'improve compliance readiness',
            'topics' => [],
        ]);
        $module = LearningModule::query()->create([
            'title' => 'Compliance Readiness',
            'description' => 'compliance readiness control walkthrough',
            'status' => 'published',
        ]);

        $result = app(ExternalAiFeedRanker::class)->rank($user, $module, null);

        $this->assertSame(7, $result['breakdown']['ai_semantic_boost']);
        $this->assertSame('Goal alignment appears stronger semantically.', $result['explanations']['ai_provider_reason']);
        $this->assertSame('gpt-5-mini', $result['explanations']['ai_provider_model']);
        $this->assertSame('req_123', $result['explanations']['ai_provider_request_id']);
        $this->assertSame(87, $result['explanations']['ai_provider_input_tokens_est']);
        $this->assertSame(19, $result['explanations']['ai_provider_output_tokens_est']);
    }

    public function test_it_retries_and_parses_fenced_json_content(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('ranking.external_ai.max_boost', 20);
        config()->set('ranking.external_ai.attempts', 2);
        config()->set('ranking.external_ai.retry_sleep_ms', 1);

        Http::fakeSequence()
            ->pushStatus(500)
            ->push([
                'id' => 'resp_retry_123',
                'model' => 'gpt-5-mini',
                'choices' => [[
                    'message' => [
                        'content' => "```json\n{\"boost\": 9, \"reason\": \"Recovered after retry.\"}\n```",
                    ],
                ]],
            ], 200, ['x-request-id' => 'req_retry_123']);

        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);
        $module = LearningModule::query()->create([
            'title' => 'Retry Parsing Module',
            'description' => 'Retry parsing test',
            'status' => 'published',
        ]);

        $result = app(ExternalAiFeedRanker::class)->rank($user, $module, null);

        Http::assertSentCount(2);
        $this->assertSame(9, $result['breakdown']['ai_semantic_boost']);
        $this->assertSame('Recovered after retry.', $result['explanations']['ai_provider_reason']);
        $this->assertSame('req_retry_123', $result['explanations']['ai_provider_request_id']);
    }

    public function test_it_clamps_boost_to_configured_max(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('ranking.external_ai.max_boost', 5);

        Http::fake([
            '*' => Http::response([
                'id' => 'resp_456',
                'model' => 'gpt-5-mini',
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'boost' => 99,
                            'reason' => 'Model requested a very large boost.',
                        ], JSON_THROW_ON_ERROR),
                    ],
                ]],
            ], 200, ['x-request-id' => 'req_456']),
        ]);

        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);
        $module = LearningModule::query()->create([
            'title' => 'Clamp Module',
            'description' => 'Clamp test',
            'status' => 'published',
        ]);

        $result = app(ExternalAiFeedRanker::class)->rank($user, $module, null);

        $this->assertSame(5, $result['breakdown']['ai_semantic_boost']);
        $this->assertSame('Model requested a very large boost.', $result['explanations']['ai_provider_reason']);
    }

    public function test_it_throws_when_openai_key_is_missing(): void
    {
        config()->set('services.openai.api_key', '');

        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);
        $module = LearningModule::query()->create([
            'title' => 'Missing Key Module',
            'description' => 'External ranker key test',
            'status' => 'published',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OPENAI_API_KEY is not configured.');

        app(ExternalAiFeedRanker::class)->rank($user, $module, null);
    }
}
