<?php

namespace Tests\Feature\Api;

use App\Jobs\IngestLearningAsset;
use App\Models\LearningAsset;
use App\Models\LearningModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LearningAssetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uploads_pdf_assets_for_a_module(): void
    {
        Storage::fake('local');
        $module = $this->createModule();

        $response = $this->post(
            "/api/modules/{$module->id}/assets",
            ['file' => UploadedFile::fake()->create('module.pdf', 32, 'application/pdf')],
            ['Accept' => 'application/json']
        );

        $response->assertCreated()
            ->assertJsonPath('data.status', 'uploaded');

        $asset = LearningAsset::query()->findOrFail($response->json('data.id'));

        $this->assertSame($module->id, $asset->learning_module_id);
        $this->assertSame('uploaded', $asset->status);
        Storage::disk('local')->assertExists($asset->storage_path);
    }

    public function test_it_dispatches_ingestion_job_for_an_asset(): void
    {
        Queue::fake();
        $module = $this->createModule();
        $asset = LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'original_filename' => 'module.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/test/module.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'status' => 'uploaded',
        ]);

        $response = $this->postJson("/api/assets/{$asset->id}/ingest");

        $response->assertAccepted()
            ->assertJsonPath('data.id', $asset->id)
            ->assertJsonPath('data.status', 'ingest_queued');

        Queue::assertPushed(IngestLearningAsset::class, function (IngestLearningAsset $job) use ($asset): bool {
            return $job->assetId === $asset->id;
        });

        $this->assertDatabaseHas('learning_assets', [
            'id' => $asset->id,
            'status' => 'ingest_queued',
        ]);
    }

    private function createModule(): LearningModule
    {
        return LearningModule::query()->create([
            'title' => 'Test Module',
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'draft',
            'embedding' => null,
        ]);
    }
}

