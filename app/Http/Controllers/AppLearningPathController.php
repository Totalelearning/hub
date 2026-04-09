<?php

namespace App\Http\Controllers;

use App\Models\ModuleProgress;
use App\Services\LearningPathService;
use Illuminate\View\View;

class AppLearningPathController extends Controller
{
    public function index(LearningPathService $paths): View
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $pathCollection = $paths->visiblePathsForUser($user)
            ->map(function ($path) use ($paths, $user) {
                $path->setAttribute('summary', $paths->progressSummary($user, $path));
                $path->setAttribute('step_states', $paths->stepStates($user, $path));
                $path->setAttribute(
                    'next_step',
                    collect($path->step_states)->first(fn (array $state) => ! $state['is_completed'] && $state['is_unlocked'])
                );

                return $path;
            });

        $latestCompletedProgress = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        $activePath = $pathCollection
            ->first(fn ($path) => ($path->summary['completed_steps'] ?? 0) < ($path->summary['total_steps'] ?? 0))
            ?? $pathCollection->first();

        return view('app.paths', [
            'paths' => $pathCollection,
            'latestCompletedProgress' => $latestCompletedProgress,
            'activePath' => $activePath,
        ]);
    }
}
