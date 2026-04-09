<?php

namespace App\Services;

use App\Models\Course;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;
use Illuminate\Support\Carbon;

class ProgressService
{
    public function getProgress(User $user, LearningModule $module): ?ModuleProgress
    {
        return ModuleProgress::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->id)
            ->first();
    }

    public function updateProgress(User $user, LearningModule $module, int $percent, ?array $lastPosition = null): ModuleProgress
    {
        $clampedPercent = max(0, min(100, $percent));
        $now = Carbon::now();

        $progress = ModuleProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
        ]);

        if (! $progress->exists && $clampedPercent > 0) {
            $progress->started_at = $now;
        }

        if ($clampedPercent === 100) {
            $progress->status = 'completed';
            $progress->completed_at = $progress->completed_at ?? $now;
            $progress->started_at = $progress->started_at ?? $now;
        } else {
            $progress->status = $clampedPercent > 0 ? 'in_progress' : 'not_started';
            $progress->completed_at = null;
            if ($clampedPercent > 0 && $progress->started_at === null) {
                $progress->started_at = $now;
            }
        }

        $progress->percent_complete = $clampedPercent;
        $progress->last_position = $lastPosition;
        $progress->last_activity_at = $now;
        $progress->save();

        // When a module is completed, check if any assigned courses are now fully complete
        if ($clampedPercent === 100) {
            $this->checkCourseCompletions($user, $module);
        }

        return $progress;
    }

    public function markCompleted(User $user, LearningModule $module): ModuleProgress
    {
        return $this->updateProgress($user, $module, 100, [
            'event' => 'completed',
            'at' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Check all courses containing this module and mark complete if all modules done.
     */
    private function checkCourseCompletions(User $user, LearningModule $module): void
    {
        $courses = Course::query()
            ->whereHas('modules', fn ($q) => $q->where('learning_modules.id', $module->id))
            ->whereHas('assignedUsers', fn ($q) => $q->where('user_id', $user->id))
            ->get();

        $completionService = app(CourseCompletionService::class);

        foreach ($courses as $course) {
            $completionService->checkAndMarkComplete($course, $user);
        }
    }
}

