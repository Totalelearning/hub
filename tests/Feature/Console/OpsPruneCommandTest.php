<?php

namespace Tests\Feature\Console;

use App\Models\LearningModule;
use App\Models\MentorMessage;
use App\Models\MentorThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OpsPruneCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prunes_only_rows_older_than_retention_windows(): void
    {
        config([
            'ops.ai_ops_retention_days' => 30,
            'ops.learning_events_retention_days' => 90,
            'ops.mentor_traces_retention_days' => 90,
            'ops.assignment_audit_retention_days' => 180,
            'ops.assignment_reminders_retention_days' => 180,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-01 12:00:00'));

        DB::table('ai_provider_usages')->insert([
            [
                'provider' => 'local',
                'capability' => 'mentor_answer',
                'model' => 'heuristic-v1',
                'request_id' => null,
                'input_tokens_est' => 10,
                'output_tokens_est' => 20,
                'latency_ms' => 5,
                'success' => true,
                'error_type' => null,
                'error_message' => null,
                'metadata' => null,
                'created_at' => now()->subDays(31),
            ],
            [
                'provider' => 'local',
                'capability' => 'mentor_answer',
                'model' => 'heuristic-v1',
                'request_id' => null,
                'input_tokens_est' => 10,
                'output_tokens_est' => 20,
                'latency_ms' => 5,
                'success' => true,
                'error_type' => null,
                'error_message' => null,
                'metadata' => null,
                'created_at' => now()->subDays(5),
            ],
        ]);

        DB::table('learning_events')->insert([
            [
                'user_id' => null,
                'event_type' => 'mentor_question_asked',
                'entity_type' => 'mentor_thread',
                'entity_id' => 1,
                'metadata' => null,
                'created_at' => now()->subDays(91),
            ],
            [
                'user_id' => null,
                'event_type' => 'mentor_answer_generated',
                'entity_type' => 'mentor_thread',
                'entity_id' => 1,
                'metadata' => null,
                'created_at' => now()->subDays(10),
            ],
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Prune Module',
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'published',
            'embedding' => null,
        ]);

        $thread = MentorThread::query()->create([
            'learning_module_id' => $module->id,
            'title' => 'Prune Thread',
            'status' => 'active',
        ]);

        $message = MentorMessage::query()->create([
            'mentor_thread_id' => $thread->id,
            'role' => 'assistant',
            'content' => 'Prune test message',
            'metadata' => ['citations' => []],
        ]);

        DB::table('mentor_retrieval_traces')->insert([
            [
                'mentor_message_id' => $message->id,
                'query_text' => 'old',
                'retrieved_unit_ids' => json_encode([]),
                'retrieval_scores' => json_encode([]),
                'retrieval_strategy' => 'keyword_fallback',
                'created_at' => now()->subDays(91),
                'updated_at' => now()->subDays(91),
            ],
            [
                'mentor_message_id' => $message->id,
                'query_text' => 'new',
                'retrieved_unit_ids' => json_encode([]),
                'retrieval_scores' => json_encode([]),
                'retrieval_strategy' => 'keyword_fallback',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ]);

        $learner = User::factory()->create();

        DB::table('assignment_audit_events')->insert([
            [
                'actor_user_id' => $learner->id,
                'target_user_id' => $learner->id,
                'learning_module_id' => $module->id,
                'entity_type' => 'assignment_waiver',
                'entity_id' => 1,
                'action' => 'waiver_saved',
                'meta' => json_encode(['source' => 'old']),
                'created_at' => now()->subDays(181),
                'updated_at' => now()->subDays(181),
            ],
            [
                'actor_user_id' => $learner->id,
                'target_user_id' => $learner->id,
                'learning_module_id' => $module->id,
                'entity_type' => 'assignment_waiver',
                'entity_id' => 1,
                'action' => 'waiver_saved',
                'meta' => json_encode(['source' => 'new']),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
        ]);

        DB::table('assignment_reminders')->insert([
            [
                'user_id' => $learner->id,
                'learning_module_id' => $module->id,
                'reminder_type' => 'due_soon',
                'due_on' => now()->toDateString(),
                'sent_at' => null,
                'status' => 'pending',
                'created_at' => now()->subDays(181),
                'updated_at' => now()->subDays(181),
            ],
            [
                'user_id' => $learner->id,
                'learning_module_id' => $module->id,
                'reminder_type' => 'inactive_nudge',
                'due_on' => now()->toDateString(),
                'sent_at' => null,
                'status' => 'pending',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
        ]);

        $this->artisan('ops:prune')->assertSuccessful();

        $this->assertSame(1, DB::table('ai_provider_usages')->count());
        $this->assertSame(1, DB::table('learning_events')->count());
        $this->assertSame(1, DB::table('mentor_retrieval_traces')->count());
        $this->assertSame(1, DB::table('assignment_audit_events')->count());
        $this->assertSame(1, DB::table('assignment_reminders')->count());

        Carbon::setTestNow();
    }

    public function test_it_supports_dry_run_without_deleting_rows(): void
    {
        config([
            'ops.ai_ops_retention_days' => 30,
            'ops.learning_events_retention_days' => 90,
            'ops.mentor_traces_retention_days' => 90,
            'ops.assignment_audit_retention_days' => 180,
            'ops.assignment_reminders_retention_days' => 180,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-01 12:00:00'));

        DB::table('ai_provider_usages')->insert([
            'provider' => 'local',
            'capability' => 'mentor_answer',
            'model' => 'heuristic-v1',
            'request_id' => null,
            'input_tokens_est' => 10,
            'output_tokens_est' => 20,
            'latency_ms' => 5,
            'success' => true,
            'error_type' => null,
            'error_message' => null,
            'metadata' => null,
            'created_at' => now()->subDays(31),
        ]);

        DB::table('learning_events')->insert([
            'user_id' => null,
            'event_type' => 'mentor_question_asked',
            'entity_type' => 'mentor_thread',
            'entity_id' => 1,
            'metadata' => null,
            'created_at' => now()->subDays(91),
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Dry Run Module',
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'published',
            'embedding' => null,
        ]);
        $thread = MentorThread::query()->create([
            'learning_module_id' => $module->id,
            'title' => 'Dry Run Thread',
            'status' => 'active',
        ]);
        $message = MentorMessage::query()->create([
            'mentor_thread_id' => $thread->id,
            'role' => 'assistant',
            'content' => 'Dry run message',
            'metadata' => ['citations' => []],
        ]);
        DB::table('mentor_retrieval_traces')->insert([
            'mentor_message_id' => $message->id,
            'query_text' => 'old',
            'retrieved_unit_ids' => json_encode([]),
            'retrieval_scores' => json_encode([]),
            'retrieval_strategy' => 'keyword_fallback',
            'created_at' => now()->subDays(91),
            'updated_at' => now()->subDays(91),
        ]);

        $user = User::factory()->create();
        DB::table('assignment_audit_events')->insert([
            'actor_user_id' => $user->id,
            'target_user_id' => $user->id,
            'learning_module_id' => $module->id,
            'entity_type' => 'assignment_waiver',
            'entity_id' => 1,
            'action' => 'waiver_saved',
            'meta' => json_encode(['source' => 'dry-run']),
            'created_at' => now()->subDays(181),
            'updated_at' => now()->subDays(181),
        ]);
        DB::table('assignment_reminders')->insert([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'due_soon',
            'due_on' => now()->toDateString(),
            'sent_at' => null,
            'status' => 'pending',
            'created_at' => now()->subDays(181),
            'updated_at' => now()->subDays(181),
        ]);

        $this->artisan('ops:prune --dry-run')
            ->expectsOutput('Dry run mode enabled. No rows were deleted.')
            ->assertSuccessful();

        $this->assertSame(1, DB::table('ai_provider_usages')->count());
        $this->assertSame(1, DB::table('learning_events')->count());
        $this->assertSame(1, DB::table('mentor_retrieval_traces')->count());
        $this->assertSame(1, DB::table('assignment_audit_events')->count());
        $this->assertSame(1, DB::table('assignment_reminders')->count());

        Carbon::setTestNow();
    }
}
