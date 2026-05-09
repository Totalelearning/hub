<?php

namespace App\Http\Controllers;

use App\Models\LearningModule;
use App\Models\LearningPath;
use App\Services\LearningPathService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminLearningPathController extends Controller
{
    public function index(): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-learning-paths-index', [
            'paths' => LearningPath::query()->withCount('steps')->orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('admin-write');

        return view('app.admin-learning-paths-form', [
            'path' => new LearningPath([
                'status' => 'draft',
                'target_roles' => [],
            ]),
            'availableModules' => LearningModule::query()->orderBy('title')->get(['id', 'title', 'status']),
            'formAction' => route('app.admin.paths.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Create Learning Path',
            'submitLabel' => 'Create Path',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $this->validatedData($request);
        $path = LearningPath::query()->create($validated['attributes']);
        $this->syncSteps($path, $validated['module_ids'], $validated['step_delays']);

        return redirect()
            ->route('app.admin.paths.edit', ['path' => $path->id])
            ->with('status', 'Learning path created.');
    }

    public function edit(LearningPath $path): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-learning-paths-form', [
            'path' => $path->load('steps'),
            'availableModules' => LearningModule::query()->orderBy('title')->get(['id', 'title', 'status']),
            'formAction' => route('app.admin.paths.update', ['path' => $path->id]),
            'formMethod' => 'PATCH',
            'pageTitle' => 'Edit Learning Path',
            'submitLabel' => 'Save Path',
        ]);
    }

    public function show(LearningPath $path, LearningPathService $paths): View
    {
        Gate::authorize('admin-access');

        $path->load(['steps.module']);

        return view('app.admin-learning-paths-show', [
            'path' => $path,
            'learnerRows' => $paths->learnerRows($path),
        ]);
    }

    public function update(Request $request, LearningPath $path): RedirectResponse
    {
        Gate::authorize('admin-write');

        $validated = $this->validatedData($request);
        $path->update($validated['attributes']);
        $this->syncSteps($path, $validated['module_ids'], $validated['step_delays']);

        return redirect()
            ->route('app.admin.paths.edit', ['path' => $path->id])
            ->with('status', 'Learning path updated.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'target_roles' => ['nullable', 'string'],
            'module_ids' => ['nullable', 'array'],
            'module_ids.*' => ['integer', 'exists:learning_modules,id'],
            'step_delays' => ['nullable', 'array'],
            'step_delays.*' => ['nullable', 'integer', 'min:0', 'max:3650'],
        ]);

        $moduleIds = collect($validated['module_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return [
            'attributes' => [
                'title' => trim($validated['title']),
                'description' => trim($validated['description']),
                'status' => $validated['status'],
                'target_roles' => collect(explode(',', (string) ($validated['target_roles'] ?? '')))
                    ->map(fn ($role) => strtolower(trim($role)))
                    ->filter()
                    ->values()
                    ->all(),
            ],
            'module_ids' => $moduleIds,
            'step_delays' => collect($validated['step_delays'] ?? [])
                ->mapWithKeys(function ($value, $key) {
                    if (! is_numeric((string) $key)) {
                        return [];
                    }

                    return [(int) $key => max(0, (int) ($value ?? 0))];
                })
                ->all(),
        ];
    }

    private function syncSteps(LearningPath $path, array $moduleIds, array $stepDelays): void
    {
        $path->steps()->delete();

        foreach ($moduleIds as $index => $moduleId) {
            $path->steps()->create([
                'learning_module_id' => $moduleId,
                'position' => $index + 1,
                'delay_days' => $stepDelays[$moduleId] ?? 0,
            ]);
        }
    }
}
