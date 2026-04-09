<?php

namespace App\Console\Commands;

use App\Services\CourseReinforcementDispatchService;
use Illuminate\Console\Command;

class CourseReinforcementSendCommand extends Command
{
    protected $signature = 'course-reinforcement:send {--limit=50 : Max emails to send per run} {--dry-run : Show eligible users without sending}';

    protected $description = 'Send reinforcement knowledge check emails to learners who completed courses after the configured delay';

    public function handle(CourseReinforcementDispatchService $service): int
    {
        $limit = (int) $this->option('limit');

        if ($this->option('dry-run')) {
            $this->info('Dry run mode — no emails will be sent.');
            // For dry run, just show the count
            $count = \Illuminate\Support\Facades\DB::table('course_user')
                ->join('courses', 'courses.id', '=', 'course_user.course_id')
                ->where('course_user.status', 'completed')
                ->whereNotNull('course_user.completed_at')
                ->whereNull('course_user.reinforcement_sent_at')
                ->whereNotNull('courses.reinforcement_delay_days')
                ->where('courses.status', 'published')
                ->whereRaw('course_user.completed_at + (courses.reinforcement_delay_days || \' days\')::interval <= now()')
                ->count();

            $this->info("Found {$count} eligible reinforcement(s) to send.");

            return self::SUCCESS;
        }

        $sent = $service->dispatchDueReinforcements($limit);

        $this->info("Sent {$sent} reinforcement email(s).");

        return self::SUCCESS;
    }
}
