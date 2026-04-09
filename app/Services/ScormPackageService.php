<?php

namespace App\Services;

use App\Models\LearningAsset;
use App\Models\LearningModule;
use App\Support\ScormManifestParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class ScormPackageService
{
    public function uploadAndProcess(LearningModule $module, UploadedFile $file): LearningAsset
    {
        $storedPath = Storage::disk('local')->putFile("learning-assets/{$module->id}/packages", $file);

        $asset = LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => $file->getClientOriginalName(),
            'storage_disk' => 'local',
            'storage_path' => $storedPath,
            'mime_type' => $file->getClientMimeType() ?: 'application/zip',
            'size_bytes' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        try {
            return $this->process($asset);
        } catch (\Throwable $e) {
            $asset->forceFill([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    public function process(LearningAsset $asset): LearningAsset
    {
        $zipFile = Storage::disk($asset->storage_disk)->path($asset->storage_path);
        $extractRelativePath = "learning-assets/{$asset->learning_module_id}/scorm/{$asset->id}";
        $extractAbsolutePath = Storage::disk('local')->path($extractRelativePath);

        if (! is_dir($extractAbsolutePath) && ! mkdir($extractAbsolutePath, 0777, true) && ! is_dir($extractAbsolutePath)) {
            throw new RuntimeException('Failed to create SCORM extraction directory.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException('SCORM package could not be opened.');
        }

        if (! $zip->extractTo($extractAbsolutePath)) {
            $zip->close();
            throw new RuntimeException('SCORM package could not be extracted.');
        }

        $zip->close();

        $manifestAbsolutePath = $extractAbsolutePath.DIRECTORY_SEPARATOR.'imsmanifest.xml';
        if (! file_exists($manifestAbsolutePath)) {
            throw new RuntimeException('SCORM package is missing imsmanifest.xml.');
        }

        $manifestXml = file_get_contents($manifestAbsolutePath);
        if ($manifestXml === false) {
            throw new RuntimeException('SCORM manifest could not be read.');
        }

        $manifest = ScormManifestParser::parse($manifestXml);

        $asset->forceFill([
            'extracted_disk' => 'local',
            'extracted_path' => $extractRelativePath,
            'launch_path' => $manifest['launch_path'],
            'manifest' => $manifest,
            'processing_metadata' => [
                'processed_at' => now()->toIso8601String(),
                'activated_at' => now()->toIso8601String(),
                'manifest_path' => 'imsmanifest.xml',
            ],
            'status' => 'processed',
            'error_message' => null,
        ])->save();

        $module = $asset->module;
        if ($module) {
            $module->forceFill([
                'source_type' => 'scorm',
                'source_uri' => $manifest['launch_path'],
            ])->save();
        }

        return $asset->fresh();
    }
}
