<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningAsset;
use App\Models\LearningModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadLearningAssetController extends Controller
{
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf'],
        ]);

        $module = LearningModule::query()->find($id);

        if ($module === null) {
            return response()->json([
                'message' => 'Learning module not found.',
            ], 404);
        }

        /** @var UploadedFile $file */
        $file = $validated['file'];
        $path = Storage::disk('local')->putFile("learning-assets/{$module->id}", $file);

        $asset = LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'original_filename' => $file->getClientOriginalName(),
            'storage_disk' => 'local',
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType() ?? 'application/pdf',
            'size_bytes' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        return response()->json([
            'data' => [
                'id' => $asset->id,
                'status' => $asset->status,
            ],
        ], 201);
    }
}

