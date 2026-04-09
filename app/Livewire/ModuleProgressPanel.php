<?php

namespace App\Livewire;

use App\Models\LearningModule;
use App\Models\AssignmentAuditEvent;
use App\Models\LearningEvent;
use App\Models\ModuleAcknowledgement;
use App\Models\ModuleProgress;
use App\Services\ProgressService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ModuleProgressPanel extends Component
{
    public LearningModule $module;
    public int $percentComplete = 0;
    public string $status = 'not_started';
    public ?string $lastActivityAt = null;
    public ?string $startedAt = null;
    public ?string $completedAt = null;
    public array $lastPosition = [];
    public bool $requiresAcknowledgement = false;
    public bool $isAcknowledged = false;
    public ?string $acknowledgedAt = null;

    public function mount(LearningModule $module): void
    {
        $this->module = $module;
        $this->loadProgress();
    }

    public function incrementForTesting(): void
    {
        $this->persist(min(100, $this->percentComplete + 10), [
            'source' => 'livewire-test-button',
            'percent' => min(100, $this->percentComplete + 10),
        ]);
    }

    public function markCompleted(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $progress = $this->service()->getProgress($user, $this->module);

        if ($progress) {
            Gate::authorize('update', $progress);
        } else {
            Gate::authorize('create', ModuleProgress::class);
        }

        $saved = $this->service()->markCompleted($user, $this->module);
        $this->syncFromModel($saved);
        $this->recordEvent('module_completed', [
            'percent_complete' => 100,
            'status' => 'completed',
        ]);
    }

    public function render()
    {
        return view('livewire.module-progress-panel');
    }

    public function acknowledge(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if (! $this->module->requires_acknowledgement || ! Schema::hasTable('module_acknowledgements')) {
            return;
        }

        ModuleAcknowledgement::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'learning_module_id' => $this->module->id,
            ],
            [
                'acknowledged_at' => now(),
            ],
        );

        if (Schema::hasTable('assignment_audit_events')) {
            AssignmentAuditEvent::query()->create([
                'actor_user_id' => $user->id,
                'target_user_id' => $user->id,
                'learning_module_id' => $this->module->id,
                'entity_type' => 'module_acknowledgement',
                'entity_id' => null,
                'action' => 'acknowledgement_recorded',
                'meta' => [
                    'module_title' => $this->module->title,
                    'learner_email' => $user->email,
                ],
            ]);
        }

        $this->loadAcknowledgement();
        $this->recordEvent('module_acknowledged', [
            'acknowledged' => true,
        ]);
    }

    private function loadProgress(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $progress = $this->service()->getProgress($user, $this->module);

        if ($progress) {
            Gate::authorize('view', $progress);
            $this->syncFromModel($progress);
        }

        $this->loadAcknowledgement();
    }

    private function persist(int $percent, array $lastPosition): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $progress = $this->service()->getProgress($user, $this->module);

        if ($progress) {
            Gate::authorize('update', $progress);
        } else {
            Gate::authorize('create', ModuleProgress::class);
        }

        $saved = $this->service()->updateProgress($user, $this->module, $percent, $lastPosition);
        $this->syncFromModel($saved);
        $this->recordEvent('module_progress_updated', [
            'percent_complete' => (int) $saved->percent_complete,
            'status' => (string) $saved->status,
            'last_position' => $saved->last_position ?? [],
        ]);
    }

    private function syncFromModel(ModuleProgress $progress): void
    {
        $this->percentComplete = (int) $progress->percent_complete;
        $this->status = (string) $progress->status;
        $this->lastActivityAt = optional($progress->last_activity_at)?->toDateTimeString();
        $this->startedAt = optional($progress->started_at)?->toDateTimeString();
        $this->completedAt = optional($progress->completed_at)?->toDateTimeString();
        $this->lastPosition = $progress->last_position ?? [];
    }

    private function loadAcknowledgement(): void
    {
        $this->requiresAcknowledgement = (bool) $this->module->requires_acknowledgement;

        if (! $this->requiresAcknowledgement || ! Schema::hasTable('module_acknowledgements')) {
            $this->isAcknowledged = false;
            $this->acknowledgedAt = null;

            return;
        }

        $user = auth()->user();
        abort_unless($user, 403);

        $acknowledgement = ModuleAcknowledgement::query()
            ->where('user_id', $user->id)
            ->where('learning_module_id', $this->module->id)
            ->first();

        $this->isAcknowledged = $acknowledgement !== null;
        $this->acknowledgedAt = $acknowledgement?->acknowledged_at?->toDateTimeString();
    }

    private function recordEvent(string $eventType, array $metadata = []): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'entity_type' => 'learning_module',
            'entity_id' => $this->module->id,
            'metadata' => $metadata,
        ]);
    }

    private function service(): ProgressService
    {
        return app(ProgressService::class);
    }
}
