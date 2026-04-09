<?php

namespace App\Console\Commands;

use App\Models\AiProviderUsage;
use App\Models\AssignmentAuditEvent;
use App\Models\AssignmentReminder;
use App\Models\LearningEvent;
use App\Models\MentorRetrievalTrace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class OpsPruneCommand extends Command
{
    protected $signature = 'ops:prune {--dry-run : Show rows that would be pruned without deleting}';

    protected $description = 'Prune old operations/audit rows based on retention config.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now = now();
        $aiOpsCutoff = $now->copy()->subDays((int) config('ops.ai_ops_retention_days', 30));
        $eventsCutoff = $now->copy()->subDays((int) config('ops.learning_events_retention_days', 90));
        $tracesCutoff = $now->copy()->subDays((int) config('ops.mentor_traces_retention_days', 90));
        $assignmentAuditCutoff = $now->copy()->subDays((int) config('ops.assignment_audit_retention_days', 180));
        $assignmentReminderCutoff = $now->copy()->subDays((int) config('ops.assignment_reminders_retention_days', 180));

        $aiOpsQuery = AiProviderUsage::query()
            ->where('created_at', '<', $aiOpsCutoff);
        $deletedAiOps = $dryRun ? $aiOpsQuery->count() : $aiOpsQuery->delete();

        $eventsQuery = LearningEvent::query()
            ->where('created_at', '<', $eventsCutoff);
        $deletedEvents = $dryRun ? $eventsQuery->count() : $eventsQuery->delete();

        $tracesQuery = MentorRetrievalTrace::query()
            ->where('created_at', '<', $tracesCutoff);
        $deletedTraces = $dryRun ? $tracesQuery->count() : $tracesQuery->delete();

        $deletedAssignmentAuditEvents = Schema::hasTable('assignment_audit_events')
            ? ($dryRun
                ? AssignmentAuditEvent::query()
                    ->where('created_at', '<', $assignmentAuditCutoff)
                    ->count()
                : AssignmentAuditEvent::query()
                    ->where('created_at', '<', $assignmentAuditCutoff)
                    ->delete())
            : 0;

        $deletedAssignmentReminders = Schema::hasTable('assignment_reminders')
            ? ($dryRun
                ? AssignmentReminder::query()
                    ->where('created_at', '<', $assignmentReminderCutoff)
                    ->count()
                : AssignmentReminder::query()
                    ->where('created_at', '<', $assignmentReminderCutoff)
                    ->delete())
            : 0;

        if ($dryRun) {
            $this->info('Dry run mode enabled. No rows were deleted.');
            $this->info("Would prune ai_provider_usages: {$deletedAiOps}");
            $this->info("Would prune learning_events: {$deletedEvents}");
            $this->info("Would prune mentor_retrieval_traces: {$deletedTraces}");
            $this->info("Would prune assignment_audit_events: {$deletedAssignmentAuditEvents}");
            $this->info("Would prune assignment_reminders: {$deletedAssignmentReminders}");

            return self::SUCCESS;
        }

        $this->info("Pruned ai_provider_usages: {$deletedAiOps}");
        $this->info("Pruned learning_events: {$deletedEvents}");
        $this->info("Pruned mentor_retrieval_traces: {$deletedTraces}");
        $this->info("Pruned assignment_audit_events: {$deletedAssignmentAuditEvents}");
        $this->info("Pruned assignment_reminders: {$deletedAssignmentReminders}");

        return self::SUCCESS;
    }
}
