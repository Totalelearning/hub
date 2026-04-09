<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiProviderUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAiUsagesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['nullable', 'string', 'max:255'],
            'capability' => ['nullable', 'string', 'max:255'],
            'request_id' => ['nullable', 'string', 'max:255'],
            'success' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($validated['limit'] ?? 20);
        $query = AiProviderUsage::query();

        if (! empty($validated['provider'])) {
            $query->where('provider', $validated['provider']);
        }

        if (! empty($validated['capability'])) {
            $query->where('capability', $validated['capability']);
        }

        if (! empty($validated['request_id'])) {
            $query->where('request_id', $validated['request_id']);
        }

        if (array_key_exists('success', $validated)) {
            $query->where('success', (bool) $validated['success']);
        }

        if (! empty($validated['from'])) {
            $query->where('created_at', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->where('created_at', '<=', $validated['to']);
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate($limit)
            ->appends($request->query());

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
