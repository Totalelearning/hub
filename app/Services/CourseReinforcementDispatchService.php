<?php

namespace App\Services;

use App\Mail\CourseReinforcementMail;
use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\User;
use App\Notifications\CourseReinforcementNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CourseReinforcementDispatchService
{
    /**
     * Find users who completed a course and are due for reinforcement,
     * then send them the knowledge check email.
     */
    public function dispatchDueReinforcements(int $limit = 50): int
    {
        $eligibleRows = DB::table('course_user')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->where('course_user.status', 'completed')
            ->whereNotNull('course_user.completed_at')
            ->whereNull('course_user.reinforcement_sent_at')
            ->whereNotNull('courses.reinforcement_delay_days')
            ->where('courses.status', 'published')
            ->whereRaw('course_user.completed_at + (courses.reinforcement_delay_days || \' days\')::interval <= now()')
            ->select('course_user.course_id', 'course_user.user_id')
            ->limit($limit)
            ->get();

        $sent = 0;

        foreach ($eligibleRows as $row) {
            $course = Course::with('modules')->find($row->course_id);
            $user = User::find($row->user_id);

            if (! $course || ! $user) {
                continue;
            }

            // Check the course actually has approved question sets
            $questionSets = $course->approvedQuestionSets();
            if ($questionSets->isEmpty()) {
                continue;
            }

            // Create the reinforcement attempt
            $attempt = CourseReinforcementAttempt::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'token' => Str::random(64),
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Send the email
            Mail::to($user->email)->send(
                new CourseReinforcementMail($attempt, $course, $user)
            );

            // Send in-app notification (shows on bell icon)
            $user->notify(new CourseReinforcementNotification($attempt, $course));

            // Mark as sent on the pivot
            DB::table('course_user')
                ->where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->update([
                    'reinforcement_sent_at' => now(),
                    'reinforcement_status' => 'sent',
                ]);

            $sent++;
        }

        return $sent;
    }
}
