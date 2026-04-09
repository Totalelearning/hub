<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseReinforcementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CourseReinforcementAttempt $attempt,
        public Course $course,
        public User $learner,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Knowledge Check: ' . $this->course->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.course-reinforcement',
            with: [
                'courseTitle' => $this->course->title,
                'learnerName' => $this->learner->name,
                'actionUrl' => route('course-reinforcement.show', ['token' => $this->attempt->token]),
                'delayDays' => $this->course->reinforcement_delay_days,
            ],
        );
    }
}
