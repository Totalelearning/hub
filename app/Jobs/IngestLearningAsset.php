<?php

namespace App\Jobs;

use App\Contracts\PdfTextExtractor;
use App\Models\LearningAsset;
use App\Models\LearningUnit;
use App\Services\AiOpsLogger;
use App\Services\Chunking\TextChunker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class IngestLearningAsset implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $assetId)
    {
    }

    public function handle(PdfTextExtractor $pdfTextExtractor, TextChunker $textChunker): void
    {
        $asset = LearningAsset::query()->with('module')->find($this->assetId);

        if ($asset === null || $asset->module === null) {
            return;
        }

        $asset->update([
            'status' => 'ingesting',
            'error_message' => null,
        ]);

        try {
            $disk = Storage::disk($asset->storage_disk);

            if (! $disk->exists($asset->storage_path)) {
                throw new RuntimeException('Stored asset file was not found.');
            }

            $binaryPdf = $disk->get($asset->storage_path);
            $text = $pdfTextExtractor->extractText($binaryPdf);

            if (trim($text) === '') {
                throw new RuntimeException('No extractable text was found in the PDF.');
            }

            $chunks = $textChunker->chunk($text);

            if ($chunks === []) {
                throw new RuntimeException('Chunking produced no usable text units.');
            }

            DB::transaction(function () use ($asset, $chunks): void {
                foreach ($chunks as $index => $chunkText) {
                    $normalizedChunkText = trim($chunkText);
                    $hash = hash('sha256', preg_replace('/\s+/u', ' ', $normalizedChunkText) ?? $normalizedChunkText);

                    LearningUnit::query()->updateOrCreate(
                        [
                            'learning_module_id' => $asset->learning_module_id,
                            'content_hash' => $hash,
                        ],
                        [
                            'position' => $index + 1,
                            'content_text' => $normalizedChunkText,
                            'metadata' => [
                                'asset_id' => $asset->id,
                                'chunk_length' => mb_strlen($normalizedChunkText),
                            ],
                        ]
                    );
                }
            });

            $asset->update([
                'status' => 'ingested',
                'error_message' => null,
            ]);

            app(AiOpsLogger::class)->recordProviderUsage('local', 'content_ingestion', true, [
                'model' => 'pdf-text-extraction',
                'metadata' => [
                    'asset_id' => $asset->id,
                    'learning_module_id' => $asset->learning_module_id,
                    'chunks_written' => count($chunks),
                    'environment' => app()->environment(),
                ],
            ]);
        } catch (Throwable $exception) {
            $asset->update([
                'status' => 'extraction_failed',
                'error_message' => Str::limit($exception->getMessage(), 1000, ''),
            ]);

            app(AiOpsLogger::class)->recordFailure('local', 'content_ingestion', $exception, [
                'model' => 'pdf-text-extraction',
                'metadata' => [
                    'asset_id' => $asset->id,
                    'learning_module_id' => $asset->learning_module_id,
                    'environment' => app()->environment(),
                ],
            ]);

            report($exception);
        }
    }
}
