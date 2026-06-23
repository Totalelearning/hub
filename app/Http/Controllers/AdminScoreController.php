<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminScoreController extends Controller
{
    public function userScores(User $user): View
    {
        Gate::authorize('admin-access');

        $courseEnrolments = DB::table('course_user')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('course_user.user_id', $user->id)
            ->select(
                'courses.id as course_id',
                'courses.title as course_title',
                'courses.status as course_status',
                'course_user.status',
                'course_user.completed_at',
                'course_user.reinforcement_status',
                'course_user.created_at as enrolled_at',
            )
            ->orderBy('courses.title')
            ->get();

        $courseIds = $courseEnrolments->pluck('course_id')->toArray();

        $modulesByCourse = DB::table('course_module')
            ->join('learning_modules', 'learning_modules.id', '=', 'course_module.learning_module_id')
            ->whereIn('course_module.course_id', $courseIds)
            ->select('course_module.course_id', 'learning_modules.id as module_id', 'learning_modules.title as module_title', 'course_module.sort_order')
            ->orderBy('course_module.sort_order')
            ->get()
            ->groupBy('course_id');

        $moduleProgress = ModuleProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('learning_module_id');

        $reinforcementAttempts = CourseReinforcementAttempt::where('user_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->orderByDesc('completed_at')
            ->get()
            ->groupBy('course_id');

        return view('app.admin-scores-user', compact(
            'user',
            'courseEnrolments',
            'modulesByCourse',
            'moduleProgress',
            'reinforcementAttempts',
        ));
    }

    public function courseScores(Course $course): View
    {
        Gate::authorize('admin-access');

        $enrolments = DB::table('course_user')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->leftJoin('user_preferences', 'user_preferences.user_id', '=', 'users.id')
            ->where('course_user.course_id', $course->id)
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'user_preferences.team',
                'course_user.status',
                'course_user.completed_at',
                'course_user.reinforcement_status',
                'course_user.created_at as enrolled_at',
            )
            ->orderBy('users.name')
            ->get();

        $userIds = $enrolments->pluck('user_id')->toArray();

        $modules = $course->modules()->get(['learning_modules.id', 'learning_modules.title']);

        $moduleProgress = ModuleProgress::whereIn('user_id', $userIds)
            ->whereIn('learning_module_id', $modules->pluck('id'))
            ->get()
            ->groupBy('user_id');

        $reinforcementAttempts = CourseReinforcementAttempt::where('course_id', $course->id)
            ->whereIn('user_id', $userIds)
            ->orderByDesc('completed_at')
            ->get()
            ->groupBy('user_id');

        return view('app.admin-scores-course', compact(
            'course',
            'enrolments',
            'modules',
            'moduleProgress',
            'reinforcementAttempts',
        ));
    }

    public function updateCourseEnrolment(Request $request, User $user, Course $course): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $request->validate([
            'status' => 'required|in:assigned,in_progress,completed',
            'completed_at' => 'nullable|date',
        ]);

        $update = ['status' => $validated['status']];

        if ($validated['status'] === 'completed') {
            $update['completed_at'] = $validated['completed_at'] ?? now();
        } else {
            $update['completed_at'] = null;
        }

        DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->update(array_merge($update, ['updated_at' => now()]));

        return back()->with('status', "Course enrolment for \"{$course->title}\" updated.");
    }

    public function updateModuleProgress(Request $request, User $user, LearningModule $learning_module): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $request->validate([
            'status' => 'required|in:not_started,in_progress,completed',
            'percent_complete' => 'required|integer|min:0|max:100',
        ]);

        $data = [
            'status' => $validated['status'],
            'percent_complete' => $validated['percent_complete'],
        ];

        if ($validated['status'] === 'completed') {
            $data['completed_at'] = now();
            $data['percent_complete'] = 100;
        } elseif ($validated['status'] === 'not_started') {
            $data['completed_at'] = null;
            $data['percent_complete'] = 0;
        } else {
            $data['completed_at'] = null;
        }

        ModuleProgress::updateOrCreate(
            ['user_id' => $user->id, 'learning_module_id' => $learning_module->id],
            array_merge($data, ['last_activity_at' => now()]),
        );

        return back()->with('status', "Module progress for \"{$learning_module->title}\" updated.");
    }

    public function updateReinforcement(Request $request, CourseReinforcementAttempt $attempt): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $request->validate([
            'score_percent' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:pending,sent,completed,gaps_found',
        ]);

        $attempt->update([
            'score_percent' => $validated['score_percent'],
            'status' => $validated['status'],
        ]);

        DB::table('course_user')
            ->where('user_id', $attempt->user_id)
            ->where('course_id', $attempt->course_id)
            ->update([
                'reinforcement_status' => $validated['status'] === 'completed' ? 'completed' : ($validated['status'] === 'gaps_found' ? 'gaps_found' : null),
                'updated_at' => now(),
            ]);

        return back()->with('status', 'Reinforcement score updated.');
    }
}
