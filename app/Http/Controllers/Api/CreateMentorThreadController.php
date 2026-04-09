<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateMentorThreadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'learning_module_id' => ['nullable', 'integer', 'exists:learning_modules,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $thread = MentorThread::query()->create([
            'user_id' => null,
            'learning_module_id' => $validated['learning_module_id'] ?? null,
            'title' => $validated['title'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'data' => [
                'id' => (int) $thread->id,
                'user_id' => $thread->user_id,
                'learning_module_id' => $thread->learning_module_id !== null ? (int) $thread->learning_module_id : null,
                'title' => $thread->title,
                'status' => $thread->status,
                'created_at' => $thread->created_at?->toISOString(),
            ],
        ], 201);
    }
}

