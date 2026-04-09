<?php

namespace Tests\Feature\Jobs;

use App\Contracts\PdfTextExtractor;
use App\Jobs\IngestLearningAsset;
use App\Models\LearningAsset;
use App\Models\LearningModule;
use App\Services\Chunking\TextChunker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IngestLearningAssetTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ingests_chunks_using_the_injected_pdf_extractor(): void
    {
        Storage::fake('local');

        $module = LearningModule::query()->create([
            'title' => 'Ingestion Module',
            'description' => null,
            'source_type' => 'manual',
            'source_uri' => null,
            'content_text' => null,
            'status' => 'draft',
            'embedding' => null,
        ]);

        $path = 'learning-assets/ingestion/sample.pdf';
        Storage::disk('local')->put($path, 'fake-pdf-content');

        $asset = LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'original_filename' => 'sample.pdf',
            'storage_disk' => 'local',
            'storage_path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => 16,
            'status' => 'uploaded',
        ]);

        $this->app->instance(PdfTextExtractor::class, new class implements PdfTextExtractor
        {
            public function extractText(string $binaryPdf): string
            {
                return "Paragraph one for ingestion.\n\nParagraph two for ingestion.";
            }
        });

        $job = new IngestLearningAsset($asset->id);
        $job->handle($this->app->make(PdfTextExtractor::class), $this->app->make(TextChunker::class));

        $this->assertDatabaseHas('learning_assets', [
            'id' => $asset->id,
            'status' => 'ingested',
        ]);

        $this->assertDatabaseCount('learning_units', 1);
        $this->assertDatabaseHas('learning_units', [
            'learning_module_id' => $module->id,
            'position' => 1,
        ]);
    }
}
