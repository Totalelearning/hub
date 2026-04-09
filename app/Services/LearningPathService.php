<?php

namespace App\Services;

use App\Models\LearningPath;
use App\Models\ModuleProgress;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class LearningPathService
{
    public function __construct(
        private readonly AssignmentService $assignments,
    ) {
    }

    public function visiblePathsForUser(User $user): Collection
    {
        $role = strtolower(trim((string) ($user->preference?->role ?? '')));

        return LearningPath::query()
            ->with(['steps.module'])
            ->where('status', 'published')
            ->get()
            ->filter(function (LearningPath $path) use ($role) {
                $targetRoles = collect($path->target_roles ?? [])
                    ->map(fn ($value) => strtolower(trim((string) $value)))
                    ->filter()
                    ->values()
                    ->all();

                return $targetRoles === [] || ($role !== '' && in_array($role, $targetRoles, true));
            })
            ->values();
    }

    public function progressSummary(User $user, LearningPath $path): array
    {
        $path->loadMissing(['steps.module']);
        $steps = $path->steps->filter(fn ($step) => $step->module !== null)->values();
        $progressByModuleId = $this->progressByModuleId($user, $steps->pluck('learning_module_id'));
        $stepStates = $this->stepStatesFromProgress($user, $steps, $progressByModuleId);

        $completed = $stepStates->where('is_completed', true)->count();
        $total = $steps->count();
        $overdue = 0;
        $dueSoon = 0;

        foreach ($stepStates as $state) {
            if (! $state['is_unlocked'] && ! $state['is_completed']) {
                continue;
            }

            $assignment = $this->assignments->forUser($user, $state['module'], $progressByModuleId->get($state['module']->id));
            if ($assignment['is_overdue']) {
                $overdue++;
            } elseif ($assignment['is_due_soon']) {
                $dueSoon++;
            }
        }

        return [
            'total_steps' => $total,
            'completed_steps' => $completed,
            'percent_complete' => $total > 0 ? (int) floor(($completed / $total) * 100) : 0,
            'overdue_steps' => $overdue,
            'due_soon_steps' => $dueSoon,
        ];
    }

    public function eligibleUsers(LearningPath $path): Collection
    {
        $targetRoles = collect($path->target_roles ?? [])
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter()
            ->values()
            ->all();

        return User::query()
            ->with('preference')
            ->orderBy('name')
            ->get()
            ->filter(function (User $user) use ($targetRoles) {
                $role = strtolower(trim((string) ($user->preference?->role ?? '')));

                return $targetRoles === [] || ($role !== '' && in_array($role, $targetRoles, true));
            })
            ->values();
    }

    public function learnerRows(LearningPath $path): Collection
    {
        $path->loadMissing(['steps.module']);
        $users = $this->eligibleUsers($path);

        return $users->map(function (User $user) use ($path) {
            $summary = $this->progressSummary($user, $path);
            $progressByModuleId = $this->progressByModuleId($user, $path->steps->pluck('learning_module_id'));
            $stepStates = $this->stepStatesFromProgress($user, $path->steps, $progressByModuleId);

            $nextIncomplete = $stepStates
                ->first(function (array $state) {
                    return ! $state['is_completed'] && $state['is_unlocked'];
                });

            return [
                'user' => $user,
                'summary' => $summary,
                'next_step_title' => $nextIncomplete['module']->title ?? null,
            ];
        })->values();
    }

    public function stepStates(User $user, LearningPath $path): Collection
    {
        $path->loadMissing(['steps.module']);
        $steps = $path->steps->filter(fn ($step) => $step->module !== null)->values();
        $progressByModuleId = $this->progressByModuleId($user, $steps->pluck('learning_module_id'));

        return $this->stepStatesFromProgress($user, $steps, $progressByModuleId);
    }

    private function progressByModuleId(User $user, Collection $moduleIds): Collection
    {
        return ModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('learning_module_id', $moduleIds->values())
            ->get()
            ->keyBy('learning_module_id');
    }

    private function stepStatesFromProgress(User $user, Collection $steps, Collection $progressByModuleId): Collection
    {
        $lastCompletedAt = null;

        return $steps->map(function ($step) use ($progressByModuleId, &$lastCompletedAt, $user) {
            $progress = $progressByModuleId->get($step->learning_module_id);
            $isCompleted = ($progress?->status ?? 'not_started') === 'completed';
            $delayDays = max(0, (int) ($step->delay_days ?? 0));

            $lockedUntil = null;
            $isUnlocked = true;

            if ($step->position > 1 && $delayDays > 0 && $lastCompletedAt instanceof CarbonInterface) {
                $lockedUntil = $lastCompletedAt->copy()->addDays($delayDays);
                $isUnlocked = now()->greaterThanOrEqualTo($lockedUntil);
            } elseif ($step->position > 1 && $lastCompletedAt === null) {
                $isUnlocked = false;
            }

            if ($isCompleted) {
                $completedAt = $progress?->completed_at ?: $progress?->last_activity_at;
                if ($completedAt instanceof CarbonInterface) {
                    $lastCompletedAt = $completedAt->copy();
                }
                $isUnlocked = true;
            }

            return [
                'step' => $step,
                'module' => $step->module,
                'progress' => $progress,
                'is_completed' => $isCompleted,
                'is_unlocked' => $isUnlocked,
                'locked_until' => $lockedUntil,
                'delay_days' => $delayDays,
                'assignment' => $this->assignments->forUser($user, $step->module, $progress),
            ];
        })->values();
    }
}
