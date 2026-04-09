<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class AssignmentReminderSyncCommand extends Command
{
    protected $signature = 'assignments:sync-reminders';

    protected $description = 'Build or refresh the pending assignment reminder queue.';

    public function handle(ReminderService $reminders): int
    {
        $synced = $reminders->syncPending();

        $this->info(sprintf('Synced %d reminder records.', $synced->count()));

        return self::SUCCESS;
    }
}
