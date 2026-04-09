<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\ModuleProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppCourseController extends Controller
{
    public function show(Course $course): View
    {
        $user = Auth::user();
        abort_unless($user, 403);

        // Ensure course is published
        abort_unless($course->status === 'published', 404);

        // Load course modules in sort order
        $course->load(['modules' => fn ($q) => $q->orderBy('course_module.sort_order')]);

        // Get the user's assignment pivot data
        $pivot = DB::table('course_user')
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        // Get progress for each module
        $moduleProgressMap = ModuleProgress::where('user_id', $user->id)
            ->whereIn('learning_module_id', $course->modules->pluck('id'))
            ->get()
            ->keyBy('learning_module_id');

        // Build per-module status
        $modulesWithProgress = $course->modules->map(function ($module) use ($moduleProgressMap) {
            $progress = $moduleProgressMap->get($module->id);

            return [
                'module' => $module,
                'status' => $progress?->status ?? 'not_started',
                'percent_complete' => (int) ($progress?->percent_complete ?? 0),
                'started_at' => $progress?->started_at,
                'completed_at' => $progress?->completed_at,
            ];
        });

        // Overall course progress
        $totalModules = $modulesWithProgress->count();
        $completedModules = $modulesWithProgress->where('status', 'completed')->count();
        $inProgressModules = $modulesWithProgress->where('status', 'in_progress')->count();
        $overallPercent = $totalModules > 0
            ? (int) round($modulesWithProgress->avg('percent_complete'))
            : 0;

        // Determine the next module to work on
        $nextModule = $modulesWithProgress
            ->first(fn ($item) => $item['status'] === 'in_progress')
            ?? $modulesWithProgress->first(fn ($item) => $item['status'] === 'not_started');

        // Course status from pivot
        $courseStatus = $pivot?->status ?? 'not_assigned';
        $courseCompletedAt = $pivot?->completed_at ? \Carbon\Carbon::parse($pivot->completed_at) : null;

        // Check for reinforcement attempts
        $latestAttempt = CourseReinforcementAttempt::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $reinforcementStatus = $pivot?->reinforcement_status;

        return view('app.course-show', [
            'course' => $course,
            'modulesWithProgress' => $modulesWithProgress,
            'totalModules' => $totalModules,
            'completedModules' => $completedModules,
            'inProgressModules' => $inProgressModules,
            'overallPercent' => $overallPercent,
            'nextModule' => $nextModule,
            'courseStatus' => $courseStatus,
            'courseCompletedAt' => $courseCompletedAt,
            'latestAttempt' => $latestAttempt,
            'reinforcementStatus' => $reinforcementStatus,
        ]);
    }
}
