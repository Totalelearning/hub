<?php

namespace App\Services;

use App\Models\LearningModule;
use App\Models\LearningModuleRevision;
use Illuminate\Support\Facades\Schema;

class LearningModuleRevisionService
{
    public function record(LearningModule $module, string $changeType): ?LearningModuleRevision
    {
        if (! Schema::hasTable('learning_module_revisions')) {
            return null;
        }

        $nextRevision = (int) LearningModuleRevision::query()
            ->where('learning_module_id', $module->id)
            ->max('revision_number') + 1;

        return LearningModuleRevision::query()->create([
            'learning_module_id' => $module->id,
            'user_id' => auth()->id(),
            'revision_number' => $nextRevision,
            'change_type' => $changeType,
            'status' => (string) $module->status,
            'snapshot' => $this->snapshot($module),
        ]);
    }

    private function snapshot(LearningModule $module): array
    {
        $module->loadMissing('prerequisites:id,title');

        return [
            'title' => $module->title,
            'description' => $module->description,
            'topic' => $module->topic,
            'difficulty' => $module->difficulty,
            'status' => $module->status,
            'owner_user_id' => $module->owner_user_id,
            'review_status' => $module->review_status,
            'approved_by' => $module->approved_by,
            'approved_at' => optional($module->approved_at)?->toDateTimeString(),
            'compliance_area' => $module->compliance_area,
            'refresh_interval_days' => $module->refresh_interval_days,
            'source_type' => $module->source_type,
            'source_uri' => $module->source_uri,
            'content_text' => $module->content_text,
            'target_roles' => $module->target_roles ?? [],
            'is_required' => (bool) $module->is_required,
            'requires_acknowledgement' => (bool) $module->requires_acknowledgement,
            'prerequisites' => $module->prerequisites->map(fn (LearningModule $prerequisite) => [
                'id' => $prerequisite->id,
                'title' => $prerequisite->title,
            ])->values()->all(),
        ];
    }
}
