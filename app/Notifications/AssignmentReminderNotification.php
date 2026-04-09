<?php

namespace App\Notifications;

use App\Models\AssignmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly AssignmentReminder $reminder,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'reminder_id' => $this->reminder->id,
            'reminder_type' => $this->reminder->reminder_type,
            'due_on' => $this->reminder->due_on?->toDateString(),
            'module_id' => $this->reminder->learning_module_id,
            'module_title' => $this->reminder->module?->title,
            'compliance_area' => $this->reminder->module?->compliance_area,
            'message' => $this->message(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Learning Reminder')
            ->line($this->message());
    }

    private function message(): string
    {
        $type = str_replace('_', ' ', $this->reminder->reminder_type);
        $title = $this->reminder->module?->title ?? 'your assigned module';

        if ($this->reminder->reminder_type === 'inactive_nudge') {
            return "Reminder: return to {$title} to keep your progress moving.";
        }

        if ($this->reminder->reminder_type === 'not_started_nudge') {
            return "Reminder: start {$title} to avoid falling behind.";
        }

        return "Reminder: {$title} is {$type}.";
    }
}
