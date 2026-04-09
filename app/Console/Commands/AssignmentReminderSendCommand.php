<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class AssignmentReminderSendCommand extends Command
{
    protected $signature = 'assignments:send-reminders {--limit=50 : Max pending reminders to mark as sent in this run}';

    protected $description = 'Dispatch pending assignment reminders and mark them sent.';

    public function handle(ReminderService $reminders): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $pending = $reminders->pendingQueue()->take($limit);

        foreach ($pending as $reminder) {
            $reminders->markSent($reminder);
            $this->line(sprintf(
                'Sent %s reminder to %s for %s.',
                $reminder->reminder_type,
                $reminder->user?->email ?? 'unknown-user',
                $reminder->module?->title ?? 'unknown-module',
            ));
        }

        $this->info(sprintf('Marked %d reminders as sent.', $pending->count()));

        return self::SUCCESS;
    }
}
