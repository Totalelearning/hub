<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMentorTracesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'thread_id' => ['nullable', 'integer', 'exists:mentor_threads,id'],
            'module_id' => ['nullable', 'integer', 'exists:learning_modules,id'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($validated['limit'] ?? 20);
        $query = MentorMessage::query()
            ->where('role', 'assistant')
            ->with(['thread', 'retrievalTrace']);

        if (! empty($validated['thread_id'])) {
            $query->where('mentor_thread_id', (int) $validated['thread_id']);
        }

        if (! empty($validated['module_id'])) {
            $moduleId = (int) $validated['module_id'];
            $query->whereHas('thread', static function ($threadQuery) use ($moduleId): void {
                $threadQuery->where('learning_module_id', $moduleId);
            });
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate($limit)
            ->appends($request->query());

        $items = collect($paginator->items())->map(static function (MentorMessage $message): array {
            return [
                'mentor_message_id' => (int) $message->id,
                'mentor_thread_id' => (int) $message->mentor_thread_id,
                'learning_module_id' => $message->thread?->learning_module_id !== null ? (int) $message->thread->learning_module_id : null,
                'content' => (string) $message->content,
                'citations' => array_values(array_map('intval', (array) ($message->metadata['citations'] ?? []))),
                'retrieval_trace' => $message->retrievalTrace === null ? null : [
                    'query_text' => (string) $message->retrievalTrace->query_text,
                    'retrieved_unit_ids' => $message->retrievalTrace->retrieved_unit_ids ?? [],
                    'retrieval_scores' => $message->retrievalTrace->retrieval_scores ?? [],
                    'retrieval_strategy' => (string) $message->retrievalTrace->retrieval_strategy,
                ],
                'created_at' => $message->created_at?->toISOString(),
            ];
        })->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}

