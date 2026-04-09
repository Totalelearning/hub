<?php

namespace Tests\Feature\Api;

use App\Models\AiProviderUsage;
use App\Models\AssignmentAuditEvent;
use App\Models\LearningAsset;
use App\Models\LearningModule;
use App\Models\MentorMessage;
use App\Models\MentorRetrievalTrace;
use App\Models\MentorThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOpsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_admin_ai_usage_records(): void
    {
        AiProviderUsage::query()->create([
            'provider' => 'local',
            'capability' => 'mentor_answer',
            'success' => true,
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'local',
            'capability' => 'content_ingestion',
            'success' => false,
            'error_type' => 'RuntimeException',
            'error_message' => 'Failed',
        ]);

        $response = $this->getJson('/api/admin/ai/usages?provider=local&capability=mentor_answer&success=1&limit=10');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.capability', 'mentor_answer')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_filters_admin_ai_usage_records_by_request_id(): void
    {
        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_match',
            'success' => false,
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_other',
            'success' => false,
        ]);

        $response = $this->getJson('/api/admin/ai/usages?provider=external_ai&capability=feed_ranking&success=0&request_id=req_match&limit=10');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.request_id', 'req_match')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_filters_admin_mentor_traces_by_module_id(): void
    {
        $moduleA = $this->createModule('Module A');
        $moduleB = $this->createModule('Module B');

        $threadA = MentorThread::query()->create([
            'learning_module_id' => $moduleA->id,
            'title' => 'Thread A',
            'status' => 'active',
        ]);

        $threadB = MentorThread::query()->create([
            'learning_module_id' => $moduleB->id,
            'title' => 'Thread B',
            'status' => 'active',
        ]);

        $assistantA = MentorMessage::query()->create([
            'mentor_thread_id' => $threadA->id,
            'role' => 'assistant',
            'content' => 'Answer A',
            'metadata' => ['citations' => [11, 12]],
        ]);

        MentorRetrievalTrace::query()->create([
            'mentor_message_id' => $assistantA->id,
            'query_text' => 'question A',
            'retrieved_unit_ids' => [11, 12],
            'retrieval_scores' => [0.9, 0.8],
            'retrieval_strategy' => 'vector_cosine',
        ]);

        $assistantB = MentorMessage::query()->create([
            'mentor_thread_id' => $threadB->id,
            'role' => 'assistant',
            'content' => 'Answer B',
            'metadata' => ['citations' => [21]],
        ]);

        MentorRetrievalTrace::query()->create([
            'mentor_message_id' => $assistantB->id,
            'query_text' => 'question B',
            'retrieved_unit_ids' => [21],
            'retrieval_scores' => [0.7],
            'retrieval_strategy' => 'keyword_fallback',
        ]);

        $response = $this->getJson("/api/admin/mentor/traces?module_id={$moduleA->id}&limit=10");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.mentor_message_id', $assistantA->id)
            ->assertJsonPath('data.0.learning_module_id', $moduleA->id)
            ->assertJsonPath('data.0.citations.0', 11)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_filters_admin_ingestion_assets_by_status(): void
    {
        $module = $this->createModule('Asset Module');

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'original_filename' => 'a.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/a.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'status' => 'ingested',
        ]);

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'original_filename' => 'b.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/b.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'status' => 'extraction_failed',
        ]);

        $response = $this->getJson('/api/admin/ingestion/assets?status=ingested&limit=10');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'ingested')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_returns_admin_ranking_health_snapshot(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-09 12:00:00');

        config()->set('services.openai.api_key', '');

        $successfulProbe = AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 125,
            'success' => true,
            'metadata' => ['message' => 'Local probe ok.'],
        ]);
        \Illuminate\Support\Facades\DB::table('ai_provider_usages')
            ->where('id', $successfulProbe->id)
            ->update([
                'created_at' => now()->subMinutes(30),
            ]);

        $failedProbe = AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 420,
            'success' => false,
            'error_message' => 'OPENAI_API_KEY is not configured.',
            'metadata' => ['message' => 'OPENAI_API_KEY is not configured.'],
        ]);
        \Illuminate\Support\Facades\DB::table('ai_provider_usages')
            ->where('id', $failedProbe->id)
            ->update([
                'created_at' => now()->subMinutes(5),
            ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_runtime_fail_1',
            'latency_ms' => 390,
            'success' => false,
            'error_type' => 'RuntimeException',
            'error_message' => 'Ranking fallback triggered.',
            'metadata' => ['message' => 'Ranking fallback triggered.'],
        ]);

        \App\Models\RankingSetting::query()->create(['key' => 'enabled', 'value' => '1']);
        \App\Models\RankingSetting::query()->create(['key' => 'provider', 'value' => 'external_ai']);
        \App\Models\RankingSetting::query()->create(['key' => 'external_ai_max_boost', 'value' => '17']);
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'Active provider is not ready.',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'degraded',
                'before_label' => 'Degraded',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'Active provider is not ready.',
                'trigger' => 'ranking_settings_updated',
            ],
        ]);

        $response = $this->getJson('/api/admin/ai/ranking-health?limit=5');

        $response->assertOk()
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.provider', 'external_ai')
            ->assertJsonPath('data.override_count', 3)
            ->assertJsonPath('data.provider_status.active_provider_ready', false)
            ->assertJsonPath('data.severity.level', 'critical')
            ->assertJsonPath('data.severity.label', 'Critical')
            ->assertJsonPath('data.probe_summary.successes', 1)
            ->assertJsonPath('data.probe_summary.failures', 1)
            ->assertJsonPath('data.latency_summary.avg_ms', 273)
            ->assertJsonPath('data.latency_summary.min_ms', 125)
            ->assertJsonPath('data.latency_summary.max_ms', 420)
            ->assertJsonPath('data.last_probe.provider', 'external_ai')
            ->assertJsonPath('data.last_probe.message', 'OPENAI_API_KEY is not configured.')
            ->assertJsonPath('data.last_successful_probe.provider', 'local_ai')
            ->assertJsonPath('data.last_successful_probe.message', 'Local probe ok.')
            ->assertJsonPath('data.success_gap.minutes', 0)
            ->assertJsonPath('data.success_gap.label', '0 minutes')
            ->assertJsonPath('data.live_failure_summary.count', 1)
            ->assertJsonPath('data.recent_live_failures.0.request_id', 'req_runtime_fail_1')
            ->assertJsonPath('data.failure_summary.0.label', 'OPENAI_API_KEY is not configured.')
            ->assertJsonPath('data.failure_summary.0.count', 1)
            ->assertJsonPath('data.failure_summary.0.sources.0', 'probe')
            ->assertJsonPath('data.severity_trigger_summary.0.trigger', 'ranking_provider_tested')
            ->assertJsonPath('data.severity_trigger_summary.0.count', 1)
            ->assertJsonPath('data.severity_trigger_summary.1.trigger', 'ranking_settings_updated')
            ->assertJsonPath('data.severity_trigger_summary.1.count', 1)
            ->assertJsonPath('data.recent_severity_transitions.0.before_label', 'Degraded')
            ->assertJsonPath('data.recent_severity_transitions.0.after_label', 'Critical')
            ->assertJsonPath('data.recent_severity_transitions.0.trigger', 'ranking_settings_updated')
            ->assertJsonPath('data.recent_severity_transitions.0.actor_name', $admin->name)
            ->assertJsonPath('data.recent_severity_transitions.1.trigger', 'ranking_provider_tested')
            ->assertJsonPath('meta.probe_limit', 5);

        \Carbon\Carbon::setTestNow();
    }

    public function test_it_filters_admin_ranking_health_snapshot_by_provider(): void
    {
        AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 120,
            'success' => true,
            'metadata' => ['message' => 'Local probe ok.'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 420,
            'success' => false,
            'error_message' => 'External timeout.',
            'metadata' => ['message' => 'External timeout.'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_runtime_filtered',
            'latency_ms' => 390,
            'success' => false,
            'error_message' => 'Live ranking provider failed.',
            'metadata' => ['message' => 'Live ranking provider failed.'],
        ]);

        $response = $this->getJson('/api/admin/ai/ranking-health?limit=5&provider=external_ai');

        $response->assertOk()
            ->assertJsonPath('data.selected_provider', 'external_ai')
            ->assertJsonPath('data.probe_summary.successes', 0)
            ->assertJsonPath('data.probe_summary.failures', 1)
            ->assertJsonPath('data.severity.level', 'critical')
            ->assertJsonPath('data.last_probe.provider', 'external_ai')
            ->assertJsonPath('data.last_successful_probe', null)
            ->assertJsonPath('data.latency_summary.avg_ms', 420)
            ->assertJsonPath('data.failure_summary.0.label', 'External timeout.')
            ->assertJsonPath('data.recent_live_failures.0.request_id', 'req_runtime_filtered')
            ->assertJsonPath('meta.provider_filter', 'external_ai')
            ->assertJsonCount(1, 'data.recent_probes');
    }

    public function test_it_filters_admin_ranking_health_severity_transitions_by_trigger(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'degraded',
                'after_label' => 'Degraded',
                'trigger' => 'ranking_settings_updated',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'degraded',
                'before_label' => 'Degraded',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);

        $response = $this->getJson('/api/admin/ai/ranking-health?limit=5&trigger=ranking_settings_updated');

        $response->assertOk()
            ->assertJsonPath('data.selected_severity_trigger', 'ranking_settings_updated')
            ->assertJsonPath('data.recent_severity_transitions.0.trigger', 'ranking_settings_updated')
            ->assertJsonPath('meta.trigger_filter', 'ranking_settings_updated')
            ->assertJsonCount(1, 'data.recent_severity_transitions');
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
}
