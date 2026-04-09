<?php

namespace App\Services;

use App\Models\AssignmentAuditEvent;
use Illuminate\Support\Facades\Schema;

class RankingSeverityAuditService
{
    public function recordIfChanged(array $beforeSnapshot, array $afterSnapshot, string $trigger, array $meta = []): void
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return;
        }

        $before = $beforeSnapshot['severity'] ?? null;
        $after = $afterSnapshot['severity'] ?? null;

        $beforeLevel = $before['level'] ?? null;
        $afterLevel = $after['level'] ?? null;

        if ($beforeLevel === $afterLevel) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => null,
            'learning_module_id' => null,
            'entity_type' => 'ranking_health',
            'entity_id' => null,
            'action' => 'ranking_severity_changed',
            'meta' => array_merge([
                'trigger' => $trigger,
                'before_level' => $beforeLevel,
                'before_label' => $before['label'] ?? null,
                'before_reason' => $before['reason'] ?? null,
                'after_level' => $afterLevel,
                'after_label' => $after['label'] ?? null,
                'after_reason' => $after['reason'] ?? null,
            ], $meta),
        ]);
    }
}
