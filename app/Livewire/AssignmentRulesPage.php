<?php

namespace App\Livewire;

use App\Models\ComplianceRoleRule;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AssignmentRulesPage extends Component
{
    public array $rules = [];
    public ?string $lastSavedAt = null;

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        Gate::authorize('admin-access');

        $this->loadRules();
    }

    public function addRule(): void
    {
        $this->rules[] = [
            'role' => '',
            'compliance_area' => '',
        ];
    }

    public function removeRule(int $index): void
    {
        unset($this->rules[$index]);
        $this->rules = array_values($this->rules);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'rules' => ['array'],
            'rules.*.role' => ['required', 'string', 'max:100'],
            'rules.*.compliance_area' => ['required', 'string', 'max:100'],
        ]);

        $normalized = collect($validated['rules'])
            ->map(fn (array $rule) => [
                'role' => strtolower(trim($rule['role'])),
                'compliance_area' => strtolower(trim($rule['compliance_area'])),
            ])
            ->filter(fn (array $rule) => $rule['role'] !== '' && $rule['compliance_area'] !== '')
            ->unique(fn (array $rule) => $rule['role'].'|'.$rule['compliance_area'])
            ->values();

        ComplianceRoleRule::query()->delete();

        foreach ($normalized as $rule) {
            ComplianceRoleRule::query()->create($rule);
        }

        $this->rules = $normalized->all();
        $this->lastSavedAt = now()->toDateTimeString();
        session()->flash('status', 'Assignment rules saved.');
    }

    public function render()
    {
        return view('livewire.assignment-rules-page');
    }

    private function loadRules(): void
    {
        $this->rules = ComplianceRoleRule::query()
            ->orderBy('role')
            ->orderBy('compliance_area')
            ->get(['role', 'compliance_area'])
            ->map(fn (ComplianceRoleRule $rule) => [
                'role' => $rule->role,
                'compliance_area' => $rule->compliance_area,
            ])
            ->all();

        if ($this->rules === []) {
            $this->addRule();
        }

        $this->lastSavedAt = optional(ComplianceRoleRule::query()->latest('updated_at')->first()?->updated_at)->toDateTimeString();
    }
}
