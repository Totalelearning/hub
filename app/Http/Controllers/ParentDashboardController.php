<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ModuleProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $userId = $user->id;

        // Fetch all courses assigned to this parent
        $courses = Course::query()
            ->where('status', 'published')
            ->whereHas('assignedUsers', fn ($q) => $q->where('user_id', $userId))
            ->withCount('modules')
            ->with('modules:id,title')
            ->get();

        // Get pivot statuses
        $pivotStatuses = DB::table('course_user')
            ->where('user_id', $userId)
            ->pluck('status', 'course_id');

        // Attach per-course module progress
        $allModuleIds = $courses->flatMap(fn ($c) => $c->modules->pluck('id'))->unique()->values()->all();
        $moduleProgressMap = [];

        if (! empty($allModuleIds)) {
            $moduleProgressMap = ModuleProgress::where('user_id', $userId)
                ->whereIn('learning_module_id', $allModuleIds)
                ->get()
                ->keyBy('learning_module_id');
        }

        foreach ($courses as $course) {
            $courseModuleIds = $course->modules->pluck('id');
            $completedCount = 0;
            $totalPercent = 0;

            foreach ($courseModuleIds as $mid) {
                $progress = $moduleProgressMap[$mid] ?? null;
                if ($progress) {
                    $totalPercent += $progress->percent_complete;
                    if ($progress->status === 'completed') {
                        $completedCount++;
                    }
                }
            }

            $modCount = $courseModuleIds->count();
            $course->course_progress_percent = $modCount > 0 ? (int) round($totalPercent / $modCount) : 0;
            $course->course_completed_modules = $completedCount;
            $course->enrolment_status = $pivotStatuses[$course->id] ?? 'assigned';
        }

        // Sort: incomplete first, completed last
        $courses = $courses->sortBy(fn ($c) => match ($c->enrolment_status) {
            'assigned' => 0,
            'in_progress' => 1,
            'completed' => 2,
            default => 1,
        })->values();

        // Summary stats
        $total = $courses->count();
        $completed = $courses->where('enrolment_status', 'completed')->count();
        $inProgress = $courses->where('enrolment_status', 'in_progress')->count();
        $outstanding = $total - $completed;

        return view('app.parent-dashboard', [
            'user' => $user,
            'courses' => $courses,
            'summary' => [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'outstanding' => $outstanding,
                'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            ],
        ]);
    }
}
