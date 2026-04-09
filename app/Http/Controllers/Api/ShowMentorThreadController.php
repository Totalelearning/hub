<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorMessage;
use App\Models\MentorThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowMentorThreadController extends Controller
{
    public function __invoke(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $thread = MentorThread::query()->find($id);

        if ($thread === null) {
            return response()->json([
                'message' => 'Mentor thread not found.',
            ], 404);
        }

        $limit = (int) ($validated['limit'] ?? 50);
        $messages = $thread->messages()
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->map(static function (MentorMessage $message): array {
                return [
                    'id' => (int) $message->id,
                    'role' => (string) $message->role,
                    'content' => (string) $message->content,
                    'metadata' => $message->metadata,
                    'created_at' => $message->created_at?->toISOString(),
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'id' => (int) $thread->id,
                'user_id' => $thread->user_id,
                'learning_module_id' => $thread->learning_module_id !== null ? (int) $thread->learning_module_id : null,
                'title' => $thread->title,
                'status' => $thread->status,
                'messages' => $messages,
            ],
        ]);
    }
}

