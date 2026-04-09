<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseReinforcementNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly CourseReinforcementAttempt $attempt,
        private readonly Course $course,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'attempt_id' => $this->attempt->id,
            'attempt_token' => $this->attempt->token,
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'message' => "Knowledge check available: {$this->course->title}. Complete it to reinforce your learning.",
            'action_url' => route('course-reinforcement.show', ['token' => $this->attempt->token]),
            'type' => 'course_reinforcement',
        ];
    }
}
