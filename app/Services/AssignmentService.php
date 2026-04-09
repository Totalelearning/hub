<?php

namespace App\Services;

use App\Models\AssignmentWaiver;
use App\Models\ComplianceRoleRule;
use App\Models\LearningModule;
use App\Models\ModuleAcknowledgement;
use App\Models\ModuleProgress;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AssignmentService
{
    public function forUser(User $user, LearningModule $module, ?ModuleProgress $progress): array
    {
        $roleTargeting = $this->roleTargetingStatus($user, $module);
        $complianceTargeting = $this->complianceTargetingStatus($user, $module);
        $prerequisites = $this->prerequisiteStatus($user, $module);
        $schedule = $this->scheduleStatus($module);
        $renewal = app(FeedScoringService::class)->renewalStatus($module, $progress);
        $waiver = $this->waiverForUser($user, $module);
        $acknowledgement = $this->acknowledgementStatus($user, $module);

        $isAssigned = $roleTargeting['matches'] && $complianceTargeting['matches'] && $prerequisites['is_unlocked'] && $schedule['is_open'];
        $isRequired = $isAssigned && $module->is_required && $waiver === null;
        $isOverdue = $isRequired && ($renewal['is_due'] ?? false);
        $isDueSoon = $isRequired && ! $isOverdue && ($renewal['is_due_soon'] ?? false);
        $isIncompleteRequired = $isRequired && (
            ($progress?->status ?? 'not_started') !== 'completed'
            || ($module->requires_acknowledgement && ! $acknowledgement['is_acknowledged'])
        );

        return [
            'is_assigned' => $isAssigned,
            'is_required' => $isRequired,
            'is_incomplete_required' => $isIncompleteRequired,
            'is_overdue' => $isOverdue,
            'is_due_soon' => $isDueSoon,
            'is_waived' => $waiver !== null,
            'urgency' => $waiver !== null ? 'waived' : ($isOverdue ? 'overdue' : ($isDueSoon ? 'due_soon' : ($isIncompleteRequired ? 'required' : 'recommended'))),
            'renewal' => $renewal,
            'role_targeting' => $roleTargeting,
            'compliance_targeting' => $complianceTargeting,
            'prerequisites' => $prerequisites,
            'schedule' => $schedule,
            'acknowledgement' => $acknowledgement,
            'waiver' => $waiver ? [
                'id' => $waiver->id,
                'reason' => $waiver->reason,
                'created_by' => $waiver->created_by,
                'created_at' => $waiver->created_at,
            ] : null,
        ];
    }

    public function isVisibleToUser(User $user, LearningModule $module): bool
    {
        $roleTargeting = $this->roleTargetingStatus($user, $module);
        $complianceTargeting = $this->complianceTargetingStatus($user, $module);
        $prerequisites = $this->prerequisiteStatus($user, $module);
        $schedule = $this->scheduleStatus($module);
        $waiver = $this->waiverForUser($user, $module);

        return $roleTargeting['matches']
            && $complianceTargeting['matches']
            && $prerequisites['is_unlocked']
            && $schedule['is_open']
            && $waiver === null;
    }

    public function scheduleStatus(LearningModule $module): array
    {
        $now = now();
        $availableFrom = $module->available_from;
        $availableUntil = $module->available_until;

        $startsInFuture = $availableFrom !== null && $availableFrom->isAfter($now);
        $ended = $availableUntil !== null && $availableUntil->isBefore($now);

        return [
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
            'starts_in_future' => $startsInFuture,
            'ended' => $ended,
            'is_open' => ! $startsInFuture && ! $ended,
        ];
    }

    public function roleTargetingStatus(User $user, LearningModule $module): array
    {
        $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
        $targetRoles = collect($module->target_roles ?? [])
            ->filter()
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->values()
            ->all();

        if ($targetRoles === []) {
            return [
                'is_targeted' => false,
                'matches' => true,
                'target_roles' => [],
            ];
        }

        return [
            'is_targeted' => true,
            'matches' => $userRole !== '' && in_array($userRole, $targetRoles, true),
            'target_roles' => $targetRoles,
        ];
    }

    public function complianceTargetingStatus(User $user, LearningModule $module): array
    {
        $complianceArea = strtolower(trim((string) ($module->compliance_area ?? '')));
        $userRole = strtolower(trim((string) ($user->preference?->role ?? '')));
        $inheritedAreas = $this->inheritedComplianceAreas($userRole);

        if (! $module->is_required || $complianceArea === '') {
            return [
                'is_targeted' => false,
                'matches' => true,
                'compliance_area' => $complianceArea ?: null,
                'inherited_areas' => $inheritedAreas,
            ];
        }

        return [
            'is_targeted' => true,
            'matches' => in_array($complianceArea, $inheritedAreas, true),
            'compliance_area' => $complianceArea,
            'inherited_areas' => $inheritedAreas,
        ];
    }

    public function inheritedComplianceAreas(string $userRole): array
    {
        $userRole = strtolower(trim($userRole));
        if ($userRole === '') {
            return [];
        }

        if (Schema::hasTable('compliance_role_rules')) {
            $databaseAreas = ComplianceRoleRule::query()
                ->where('role', $userRole)
                ->orderBy('compliance_area')
                ->pluck('compliance_area')
                ->map(fn ($area) => strtolower(trim((string) $area)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($databaseAreas !== []) {
                return $databaseAreas;
            }
        }

        return collect(config('learning_assignments.role_compliance_areas.' . $userRole, []))
            ->filter()
            ->map(fn ($area) => strtolower(trim((string) $area)))
            ->unique()
            ->values()
            ->all();
    }

    public function waiverForUser(User $user, LearningModule $module): ?AssignmentWaiver
    {
        if (! Schema::hasTable('assignment_waivers')) {
            return null;
        }

        return AssignmentWaiver::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->id)
            ->first();
    }

    public function prerequisiteStatus(User $user, LearningModule $module): array
    {
        if (! Schema::hasTable('learning_module_prerequisites')) {
            return [
                'has_prerequisites' => false,
                'is_unlocked' => true,
                'required_module_ids' => [],
                'completed_module_ids' => [],
                'missing_module_ids' => [],
                'missing_titles' => [],
            ];
        }

        $prerequisites = $module->relationLoaded('prerequisites')
            ? $module->prerequisites
            : $module->prerequisites()->get(['learning_modules.id', 'learning_modules.title']);

        if ($prerequisites->isEmpty()) {
            return [
                'has_prerequisites' => false,
                'is_unlocked' => true,
                'required_module_ids' => [],
                'completed_module_ids' => [],
                'missing_module_ids' => [],
                'missing_titles' => [],
            ];
        }

        $requiredIds = $prerequisites->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $completedIds = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('learning_module_id', $requiredIds)
            ->where('status', 'completed')
            ->pluck('learning_module_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $missing = $prerequisites
            ->reject(fn (LearningModule $prerequisite) => in_array((int) $prerequisite->id, $completedIds, true))
            ->values();

        return [
            'has_prerequisites' => true,
            'is_unlocked' => $missing->isEmpty(),
            'required_module_ids' => $requiredIds,
            'completed_module_ids' => $completedIds,
            'missing_module_ids' => $missing->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'missing_titles' => $missing->pluck('title')->values()->all(),
        ];
    }

    public function acknowledgementStatus(User $user, LearningModule $module): array
    {
        if (! $module->requires_acknowledgement || ! Schema::hasTable('module_acknowledgements')) {
            return [
                'is_required' => (bool) $module->requires_acknowledgement,
                'is_acknowledged' => false,
                'acknowledged_at' => null,
            ];
        }

        $acknowledgement = ModuleAcknowledgement::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->id)
            ->first();

        return [
            'is_required' => true,
            'is_acknowledged' => $acknowledgement !== null,
            'acknowledged_at' => $acknowledgement?->acknowledged_at,
        ];
    }
}
