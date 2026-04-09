<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningModule;
use App\Services\VectorSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimilarModulesController extends Controller
{
    public function __invoke(int $id, Request $request, VectorSearchService $vectorSearchService): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $module = LearningModule::query()->find($id);

        if ($module === null) {
            return response()->json([
                'message' => 'Learning module not found.',
            ], 404);
        }

        if (blank($module->embedding)) {
            return response()->json([
                'message' => 'Learning module embedding is not available.',
            ], 422);
        }

        $limit = (int) ($validated['limit'] ?? 10);
        $similarModules = $vectorSearchService->similarModules($id, $limit);

        return response()->json([
            'data' => $similarModules->map(static function (LearningModule $module): array {
                return [
                    'id' => (int) $module->id,
                    'title' => (string) $module->title,
                    'description' => $module->description,
                    'similarity_score' => isset($module->similarity_score) ? (float) $module->similarity_score : null,
                ];
            })->values(),
        ]);
    }
}