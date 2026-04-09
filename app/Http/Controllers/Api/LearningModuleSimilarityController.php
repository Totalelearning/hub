<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VectorSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningModuleSimilarityController extends Controller
{
    public function __invoke(int $id, Request $request, VectorSearchService $vectorSearchService): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = (int) ($validated['limit'] ?? 10);
        $similarModules = $vectorSearchService->similarToModuleId($id, $limit);

        return response()->json([
            'data' => $similarModules,
        ]);
    }
}
