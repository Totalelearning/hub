<?php

namespace Tests\Feature\Api;

use App\Models\LearningModule;
use App\Models\MentorThread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class MentorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_fetches_a_mentor_thread(): void
    {
        $module = $this->createModule('Thread Module');

        $createResponse = $this->postJson('/api/mentor/threads', [
            'learning_module_id' => $module->id,
            'title' => 'Week 1 Mentor Q&A',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.learning_module_id', $module->id)
            ->assertJsonPath('data.title', 'Week 1 Mentor Q&A')
            ->assertJsonPath('data.status', 'active');

        $threadId = (int) $createResponse->json('data.id');

        $showResponse = $this->getJson("/api/mentor/threads/{$threadId}");

        $showResponse->assertOk()
            ->assertJsonPath('data.id', $threadId)
            ->assertJsonPath('data.title', 'Week 1 Mentor Q&A')
            ->assertJsonPath('data.messages', []);
    }

    public function test_it_posts_message_generates_assistant_reply_and_writes_trace_and_events(): void
    {
        $module = $this->createModule('Mentor Module');

        $thread = MentorThread::query()->create([
            'learning_module_id' => $module->id,
            'title' => 'Mentor Thread',
            'status' => 'active',
        ]);

        $firstUnitId = $this->insertUnit($module->id, 1, 'Vectors are used to represent magnitude and direction in algebra and geometry.');
        $secondUnitId = $this->insertUnit($module->id, 2, 'Vectors can be added by combining components into a resultant vector.');

        $response = $this->postJson("/api/mentor/threads/{$thread->id}/messages", [
            'content' => 'Explain vectors in algebra',
        ]);

        $response->assertCreated()
            ->assertJsonPath('assistant_message.provider', 'local')
            ->assertJsonPath('assistant_message.model', 'heuristic-v1')
            ->assertJsonPath('retrieval.strategy_used', 'keyword_fallback');

        $retrievalUnitIds = array_column($response->json('retrieval.units'), 'id');
        $citations = $response->json('assistant_message.citations');

        $this->assertSame([$firstUnitId, $secondUnitId], $retrievalUnitIds);
        $this->assertSame([$firstUnitId, $secondUnitId], $citations);

        $assistantMessageId = (int) $response->json('assistant_message.id');
        $this->assertDatabaseHas('mentor_messages', [
            'id' => $assistantMessageId,
            'mentor_thread_id' => $thread->id,
            'role' => 'assistant',
        ]);

        $trace = DB::table('mentor_retrieval_traces')
            ->where('mentor_message_id', $assistantMessageId)
            ->first();

        $this->assertNotNull($trace);
        $this->assertSame('keyword_fallback', $trace->retrieval_strategy);
        $this->assertSame($retrievalUnitIds, json_decode((string) $trace->retrieved_unit_ids, true));

        $this->assertDatabaseHas('learning_events', [
            'event_type' => 'mentor_question_asked',
            'entity_type' => 'mentor_thread',
            'entity_id' => $thread->id,
        ]);
        $this->assertDatabaseHas('learning_events', [
            'event_type' => 'mentor_answer_generated',
            'entity_type' => 'mentor_thread',
            'entity_id' => $thread->id,
        ]);

        $this->assertDatabaseHas('ai_provider_usages', [
            'provider' => 'local',
            'capability' => 'mentor_answer',
            'success' => true,
        ]);
    }

    public function test_it_returns_503_when_mentor_provider_is_disabled(): void
    {
        config(['mentor.enabled' => false]);

        $thread = MentorThread::query()->create([
            'learning_module_id' => null,
            'title' => 'Disabled Provider Thread',
            'status' => 'active',
        ]);

        $this->postJson("/api/mentor/threads/{$thread->id}/messages", [
            'content' => 'Any question',
        ])->assertStatus(503)
            ->assertJsonPath('error.code', 'mentor_provider_disabled')
            ->assertJsonPath('error.message', 'Mentor provider is disabled.');
    }

    public function test_it_rate_limits_mentor_message_endpoint(): void
    {
        RateLimiter::clear('mentor-message:127.0.0.1');

        $thread = MentorThread::query()->create([
            'learning_module_id' => null,
            'title' => 'Rate Limited Thread',
            'status' => 'active',
        ]);

        for ($index = 0; $index < 30; $index++) {
            $this->postJson("/api/mentor/threads/{$thread->id}/messages", [
                'content' => 'Repeated question '.$index,
            ])->assertStatus(201);
        }

        $this->postJson("/api/mentor/threads/{$thread->id}/messages", [
            'content' => 'One too many',
        ])->assertStatus(429)
            ->assertJsonPath('error.code', 'rate_limited')
            ->assertJsonPath('error.message', 'Too many mentor requests. Please retry later.');

        RateLimiter::clear('mentor-message:127.0.0.1');
    }

    private function createModule(string $title): LearningModule
    {
        return LearningModule::query()->create([
            'title' => $title,
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'published',
            'embedding' => null,
        ]);
    }

    private function insertUnit(int $moduleId, int $position, string $content): int
    {
        return (int) DB::table('learning_units')->insertGetId([
            'learning_module_id' => $moduleId,
            'position' => $position,
            'content_text' => $content,
            'content_hash' => hash('sha256', $content.'|'.$position),
            'metadata' => null,
            'embedding' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
