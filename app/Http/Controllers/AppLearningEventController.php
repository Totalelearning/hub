<?php

namespace App\Http\Controllers;

use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\LearningPath;
use App\Models\UserPreference;
use App\Services\AssignmentService;
use App\Services\LearningPathService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppLearningEventController extends Controller
{
    public function store(Request $request, AssignmentService $assignments, LearningPathService $paths): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $validated = $request->validate([
            'event_type' => ['required', 'string', 'max:100'],
            'entity_type' => ['required', 'in:learning_module,learning_path,user_preference'],
            'entity_id' => ['required', 'integer', 'min:1'],
            'metadata' => ['nullable', 'array'],
        ]);

        if ($validated['entity_type'] === 'learning_module') {
            $request->validate([
                'entity_id' => ['required', 'integer', 'exists:learning_modules,id'],
            ]);

            $module = LearningModule::query()->findOrFail((int) $validated['entity_id']);
            abort_unless($assignments->isVisibleToUser($user, $module), 403);
        }

        if ($validated['entity_type'] === 'learning_path') {
            $request->validate([
                'entity_id' => ['required', 'integer', 'exists:learning_paths,id'],
            ]);

            $pathId = (int) $validated['entity_id'];
            $canAccess = $paths->visiblePathsForUser($user)->contains(fn (LearningPath $path) => (int) $path->id === $pathId);
            abort_unless($canAccess, 403);
        }

        if ($validated['entity_type'] === 'user_preference') {
            $request->validate([
                'entity_id' => ['required', 'integer', 'exists:user_preferences,id'],
            ]);

            $preference = UserPreference::query()->findOrFail((int) $validated['entity_id']);
            abort_unless((int) $preference->user_id === (int) $user->id, 403);
        }

        $event = LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => $validated['event_type'],
            'entity_type' => $validated['entity_type'],
            'entity_id' => (int) $validated['entity_id'],
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json([
            'id' => $event->id,
            'status' => 'recorded',
        ], 201);
    }
}
