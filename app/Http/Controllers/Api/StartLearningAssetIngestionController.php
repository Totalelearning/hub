<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\IngestLearningAsset;
use App\Models\LearningAsset;
use Illuminate\Http\JsonResponse;

class StartLearningAssetIngestionController extends Controller
{
    public function __invoke(int $id): JsonResponse
    {
        $asset = LearningAsset::query()->find($id);

        if ($asset === null) {
            return response()->json([
                'message' => 'Learning asset not found.',
            ], 404);
        }

        $asset->update(['status' => 'ingest_queued']);

        IngestLearningAsset::dispatch($asset->id);

        return response()->json([
            'data' => [
                'id' => $asset->id,
                'status' => 'ingest_queued',
            ],
        ], 202);
    }
}

