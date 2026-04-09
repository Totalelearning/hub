<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LearningModule;
use App\Models\ReinforcementQuestionSet;
use App\Models\Role;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::withCount('modules')
            ->with('owner')
            ->orderByDesc('updated_at')
            ->get();

        return view('app.admin-courses-index', compact('courses'));
    }

    public function create(): View
    {
        $modules = LearningModule::orderBy('title')->get(['id', 'title', 'topic', 'source_type']);

        return view('app.admin-courses-form', [
            'course' => null,
            'modules' => $modules,
            'selectedModuleIds' => [],
            'topicOptions' => Topic::ordered()->pluck('name')->all(),
            'roleOptions' => Role::ordered()->pluck('name')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topic' => ['nullable', 'string', 'max:100'],
            'target_roles' => ['nullable', 'array'],
            'target_roles.*' => ['string'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:999'],
            'reinforcement_delay_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'status' => ['required', 'in:draft,published,archived'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['integer', 'exists:learning_modules,id'],
        ]);

        $targetRoles = $validated['target_roles'] ?? [];

        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'topic' => $validated['topic'] ?? null,
            'target_roles' => $targetRoles,
            'estimated_minutes' => $validated['estimated_minutes'] ?? null,
            'reinforcement_delay_days' => $validated['reinforcement_delay_days'] ?? 30,
            'status' => $validated['status'],
            'owner_user_id' => $request->user()->id,
        ]);

        $this->syncModules($course, $validated['modules'] ?? []);
        $this->autoAssignUsers($course, $targetRoles);

        return redirect()
            ->route('app.admin.modules.index')
            ->with('status', "Course \"{$course->title}\" created.");
    }

    public function edit(Course $course): View
    {
        $course->load('modules');
        $modules = LearningModule::orderBy('title')->get(['id', 'title', 'topic', 'source_type']);
        $selectedModuleIds = $course->modules->pluck('id')->all();

        $topicOptions = Topic::ordered()->pluck('name')->all();
        $roleOptions = Role::ordered()->pluck('name')->all();

        // Build reinforcement readiness data for each course module
        $moduleQuestionReadiness = $course->modules->map(function ($module) {
            $set = ReinforcementQuestionSet::where('learning_module_id', $module->id)
                ->orderByDesc('id')
                ->with('questions')
                ->first();

            return [
                'module' => $module,
                'question_set' => $set,
                'status' => $set?->status ?? 'none',
                'question_count' => $set?->questions->count() ?? 0,
            ];
        });

        return view('app.admin-courses-form', compact(
            'course', 'modules', 'selectedModuleIds', 'topicOptions', 'roleOptions', 'moduleQuestionReadiness'
        ));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topic' => ['nullable', 'string', 'max:100'],
            'target_roles' => ['nullable', 'array'],
            'target_roles.*' => ['string'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:999'],
            'reinforcement_delay_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'status' => ['required', 'in:draft,published,archived'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['integer', 'exists:learning_modules,id'],
        ]);

        $targetRoles = $validated['target_roles'] ?? [];

        $course->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'target_roles' => $targetRoles,
            'estimated_minutes' => $validated['estimated_minutes'] ?? null,
            'reinforcement_delay_days' => $validated['reinforcement_delay_days'] ?? 30,
            'status' => $validated['status'],
        ]);

        $this->syncModules($course, $validated['modules'] ?? []);
        $this->autoAssignUsers($course, $targetRoles);

        return redirect()
            ->route('app.admin.modules.index')
            ->with('status', "Course \"{$course->title}\" updated.");
    }

    public function bulkTransition(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,published,archived'],
            'course_ids' => ['required', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $count = Course::whereIn('id', $validated['course_ids'])
            ->update(['status' => $validated['status']]);

        return redirect()
            ->route('app.admin.modules.index')
            ->with('status', "{$count} course(s) set to {$validated['status']}.");
    }

    public function destroy(Course $course): RedirectResponse
    {
        $title = $course->title;
        $course->delete();

        return redirect()
            ->route('app.admin.modules.index')
            ->with('status', "Course \"{$title}\" deleted.");
    }

    private function syncModules(Course $course, array $moduleIds): void
    {
        $syncData = [];
        foreach (array_values($moduleIds) as $index => $id) {
            $syncData[$id] = ['sort_order' => $index];
        }
        $course->modules()->sync($syncData);
    }

    private function autoAssignUsers(Course $course, array $targetRoles): void
    {
        if (empty($targetRoles)) {
            $course->assignedUsers()->sync([]);
            return;
        }

        if (in_array('all', $targetRoles)) {
            $userIds = User::pluck('id')->all();
        } else {
            $userIds = UserPreference::whereIn('role', $targetRoles)
                ->pluck('user_id')
                ->all();
        }

        $course->assignedUsers()->sync($userIds);
    }
}
