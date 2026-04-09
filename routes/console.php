<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ops:prune')->dailyAt('03:10');
Schedule::command('assignments:run-reminders --limit=100')->dailyAt('07:00');
Schedule::command('course-reinforcement:send --limit=50')->dailyAt('08:00');
