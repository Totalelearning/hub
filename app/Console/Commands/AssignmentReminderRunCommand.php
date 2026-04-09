<?php

namespace App\Console\Commands;

use App\Models\AssignmentAuditEvent;
use App\Services\ReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssignmentReminderRunCommand extends Command
{
    protected $signature = 'assignments:run-reminders
        {--limit=100 : Max pending reminders to mark as sent in this run}
        {--types= : Comma-separated reminder types to send (overdue,due_soon,inactive_nudge)}
        {--sync-only : Only sync pending reminders and skip sending}
        {--send-only : Only send existing pending reminders and skip sync}
        {--dry-run : Preview sync/send counts without writing changes}';

    protected $description = 'Sync pending assignment reminders, then mark a bounded batch as sent.';

    public function handle(ReminderService $reminders): int
    {
        $limit = max(1, (int) $this->option('limit'));
        ['valid' => $types, 'invalid' => $invalidTypes] = $this->parseTypes((string) ($this->option('types') ?? ''));
        $syncOnly = (bool) $this->option('sync-only');
        $sendOnly = (bool) $this->option('send-only');
        $dryRun = (bool) $this->option('dry-run');

        if ($invalidTypes !== []) {
            $this->error('Invalid reminder types: '.implode(', ', $invalidTypes).'. Allowed: overdue, due_soon, inactive_nudge, not_started_nudge.');

            return self::INVALID;
        }

        if ($syncOnly && $sendOnly) {
            $this->error('Options --sync-only and --send-only cannot be combined.');

            return self::INVALID;
        }

        if ($dryRun) {
            return $this->handleDryRun($reminders, $limit, $types, $syncOnly, $sendOnly);
        }

        $synced = $sendOnly ? collect() : $reminders->syncPending();
        $pending = $syncOnly
            ? collect()
            : $this->pendingQueueForTypes($reminders, $types)->take($limit);

        foreach ($pending as $reminder) {
            $reminders->markSent($reminder);
            $this->line(sprintf(
                'Sent %s reminder to %s for %s.',
                $reminder->reminder_type,
                $reminder->user?->email ?? 'unknown-user',
                $reminder->module?->title ?? 'unknown-module',
            ));
        }

        $syncedByType = $synced
            ->groupBy('reminder_type')
            ->map(fn ($rows) => $rows->count())
            ->sortKeys();
        $sentByType = $pending
            ->groupBy('reminder_type')
            ->map(fn ($rows) => $rows->count())
            ->sortKeys();
        $remainingPendingTotal = $reminders->pendingQueue()->count();
        $remainingPendingFiltered = $this->pendingQueueForTypes($reminders, $types)->count();

        $this->info(sprintf(
            'Run complete. Synced %d reminder records; marked %d reminders as sent.',
            $synced->count(),
            $pending->count(),
        ));
        $this->line('Synced by type: '.$this->formatTypeCounts($syncedByType->toArray()));
        $this->line('Sent by type: '.$this->formatTypeCounts($sentByType->toArray()));
        $this->line('Mode: '.$this->modeLabel($syncOnly, $sendOnly));
        $this->line('Send filter: '.($types === [] ? 'all' : implode(', ', $types)));
        $this->line(sprintf(
            'Remaining pending reminders: total=%d, filtered=%d',
            $remainingPendingTotal,
            $remainingPendingFiltered,
        ));
        $this->recordBatchAuditEvent(
            limit: $limit,
            syncedTotal: $synced->count(),
            sentTotal: $pending->count(),
            remainingPending: $remainingPendingTotal,
            remainingPendingFiltered: $remainingPendingFiltered,
            syncedByType: $syncedByType->toArray(),
            sentByType: $sentByType->toArray(),
            mode: $this->modeLabel($syncOnly, $sendOnly),
            types: $types === [] ? ['all'] : $types,
        );

        return self::SUCCESS;
    }

    private function handleDryRun(
        ReminderService $reminders,
        int $limit,
        array $types,
        bool $syncOnly,
        bool $sendOnly,
    ): int
    {
        DB::beginTransaction();

        try {
            $synced = $sendOnly ? collect() : $reminders->syncPending();
            $pending = $syncOnly
                ? collect()
                : $this->pendingQueueForTypes($reminders, $types)->take($limit);

            DB::rollBack();

            $this->info('Dry run mode enabled. No reminder records were changed.');
            $this->info(sprintf(
                'Would sync %d reminder records and mark %d reminders as sent.',
                $synced->count(),
                $pending->count(),
            ));
            $this->line('Would sync by type: '.$this->formatTypeCounts(
                $synced
                    ->groupBy('reminder_type')
                    ->map(fn ($rows) => $rows->count())
                    ->sortKeys()
                    ->toArray()
            ));
            $this->line('Would send by type: '.$this->formatTypeCounts(
                $pending
                    ->groupBy('reminder_type')
                    ->map(fn ($rows) => $rows->count())
                    ->sortKeys()
                    ->toArray()
            ));
            $this->line('Would mode: '.$this->modeLabel($syncOnly, $sendOnly));
            $this->line('Would send filter: '.($types === [] ? 'all' : implode(', ', $types)));
            $this->line(sprintf(
                'Would remaining pending reminders: total=%d, filtered=%d',
                $reminders->pendingQueue()->count(),
                $this->pendingQueueForTypes($reminders, $types)->count(),
            ));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function pendingQueueForTypes(ReminderService $reminders, array $types)
    {
        $pending = $reminders->pendingQueue();

        if ($types === []) {
            return $pending;
        }

        return $pending->whereIn('reminder_type', $types)->values();
    }

    private function parseTypes(string $raw): array
    {
        $allowed = ['overdue', 'due_soon', 'inactive_nudge', 'not_started_nudge'];
        $parsed = collect(explode(',', $raw))
            ->map(fn ($value) => strtolower(trim($value)))
            ->filter()
            ->unique()
            ->values();

        return [
            'valid' => $parsed
                ->filter(fn ($value) => in_array($value, $allowed, true))
                ->values()
                ->all(),
            'invalid' => $parsed
                ->reject(fn ($value) => in_array($value, $allowed, true))
                ->values()
                ->all(),
        ];
    }

    private function formatTypeCounts(array $counts): string
    {
        if ($counts === []) {
            return 'none';
        }

        return collect($counts)
            ->map(fn ($count, $type) => sprintf('%s=%d', $type, $count))
            ->implode(', ');
    }

    private function recordBatchAuditEvent(
        int $limit,
        int $syncedTotal,
        int $sentTotal,
        int $remainingPending,
        int $remainingPendingFiltered,
        array $syncedByType,
        array $sentByType,
        string $mode,
        array $types,
    ): void {
        if (! Schema::hasTable('assignment_audit_events')) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => null,
            'learning_module_id' => null,
            'entity_type' => 'assignment_reminder_batch',
            'entity_id' => null,
            'action' => 'reminder_batch_run',
            'meta' => [
                'limit' => $limit,
                'synced_total' => $syncedTotal,
                'sent_total' => $sentTotal,
                'remaining_pending' => $remainingPending,
                'remaining_pending_filtered' => $remainingPendingFiltered,
                'synced_by_type' => $syncedByType,
                'sent_by_type' => $sentByType,
                'mode' => $mode,
                'types' => $types,
            ],
        ]);
    }

    private function modeLabel(bool $syncOnly, bool $sendOnly): string
    {
        return match (true) {
            $syncOnly => 'sync_only',
            $sendOnly => 'send_only',
            default => 'sync_and_send',
        };
    }
}
