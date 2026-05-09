<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\CourseReinforcementResponse;
use App\Models\LearningModule;
use App\Models\ReinforcementQuestion;
use App\Models\ReinforcementQuestionSet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCourseAnalyticsAttemptTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createLearner(string $name = 'Jane Doe'): User
    {
        return User::factory()->create([
            'name' => $name,
            'is_admin' => false,
        ]);
    }

    /**
     * Build a completed attempt with questions and responses.
     *
     * @return array{attempt: CourseReinforcementAttempt, course: Course, learner: User, questions: \Illuminate\Support\Collection}
     */
    private function seedAttemptWithResponses(string $status = 'completed', float $score = 80.00): array
    {
        $course = Course::create([
            'title' => 'Safeguarding Essentials',
            'slug' => 'safeguarding-essentials',
            'status' => 'published',
            'topic' => 'compliance',
        ]);

        $learner = $this->createLearner('Jane Doe');

        $module = LearningModule::create([
            'title' => 'Intro to Safeguarding',
            'status' => 'published',
            'topic' => 'compliance',
        ]);

        $questionSet = ReinforcementQuestionSet::create([
            'learning_module_id' => $module->id,
            'status' => 'published',
            'generation_mode' => 'ai_draft',
            'title' => 'Safeguarding Quiz',
        ]);

        $q1 = ReinforcementQuestion::create([
            'reinforcement_question_set_id' => $questionSet->id,
            'position' => 1,
            'question_text' => 'What is the primary goal of safeguarding?',
            'question_type' => 'multiple_choice',
            'options' => ['A' => 'Protecting children', 'B' => 'Filing paperwork', 'C' => 'Scheduling meetings', 'D' => 'Writing reports'],
            'correct_answer' => 'A',
            'explanation' => 'Safeguarding is fundamentally about protecting children and vulnerable adults.',
            'status' => 'published',
        ]);

        $q2 = ReinforcementQuestion::create([
            'reinforcement_question_set_id' => $questionSet->id,
            'position' => 2,
            'question_text' => 'Who should you report concerns to?',
            'question_type' => 'multiple_choice',
            'options' => ['A' => 'A friend', 'B' => 'Designated safeguarding lead', 'C' => 'No one', 'D' => 'Social media'],
            'correct_answer' => 'B',
            'explanation' => 'Always report to the designated safeguarding lead.',
            'status' => 'published',
        ]);

        $attempt = CourseReinforcementAttempt::create([
            'course_id' => $course->id,
            'user_id' => $learner->id,
            'token' => Str::uuid()->toString(),
            'status' => $status,
            'score_percent' => $score,
            'metadata' => ['total_questions' => 2, 'correct_count' => 2],
            'sent_at' => now()->subHours(2),
            'started_at' => now()->subHour(),
            'completed_at' => in_array($status, ['completed', 'gaps_found']) ? now() : null,
        ]);

        CourseReinforcementResponse::create([
            'course_reinforcement_attempt_id' => $attempt->id,
            'reinforcement_question_id' => $q1->id,
            'user_id' => $learner->id,
            'selected_answer' => 'A',
            'is_correct' => true,
            'answered_at' => now(),
        ]);

        CourseReinforcementResponse::create([
            'course_reinforcement_attempt_id' => $attempt->id,
            'reinforcement_question_id' => $q2->id,
            'user_id' => $learner->id,
            'selected_answer' => 'B',
            'is_correct' => true,
            'answered_at' => now(),
        ]);

        return [
            'attempt' => $attempt,
            'course' => $course,
            'learner' => $learner,
            'questions' => collect([$q1, $q2]),
        ];
    }

    // ── Admin can view a completed attempt ─────────────────────────────

    public function test_admin_can_view_completed_attempt(): void
    {
        $data = $this->seedAttemptWithResponses('completed', 80.00);

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.course-analytics.attempt', $data['attempt']));

        $response->assertOk();
        $response->assertSeeText('Safeguarding Essentials');
        $response->assertSeeText('Jane Doe');
        $response->assertSeeText('80.0');
    }

    // ── Admin can view a gaps_found attempt ─────────────────────────────

    public function test_admin_can_view_gaps_found_attempt(): void
    {
        $data = $this->seedAttemptWithResponses('gaps_found', 40.00);

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.course-analytics.attempt', $data['attempt']));

        $response->assertOk();
        $response->assertSeeText('Safeguarding Essentials');
    }

    // ── Non-admin gets 403 ──────────────────────────────────────────────

    public function test_non_admin_gets_403(): void
    {
        $data = $this->seedAttemptWithResponses('completed');
        $learner = $this->createLearner('Non Admin User');

        $response = $this->actingAs($learner)
            ->get(route('app.admin.course-analytics.attempt', $data['attempt']));

        $response->assertForbidden();
    }

    // ── Guest gets redirected to login ──────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $data = $this->seedAttemptWithResponses('completed');

        $response = $this->get(route('app.admin.course-analytics.attempt', $data['attempt']));

        $response->assertRedirect(route('login'));
    }

    // ── Sent attempt returns 404 ────────────────────────────────────────

    public function test_sent_attempt_returns_404(): void
    {
        $data = $this->seedAttemptWithResponses('sent', 0);

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.course-analytics.attempt', $data['attempt']));

        $response->assertNotFound();
    }

    // ── Pending attempt returns 404 ─────────────────────────────────────

    public function test_pending_attempt_returns_404(): void
    {
        $course = Course::create([
            'title' => 'Pending Course',
            'slug' => 'pending-course',
            'status' => 'published',
            'topic' => 'compliance',
        ]);

        $learner = $this->createLearner('Pending Learner');

        $attempt = CourseReinforcementAttempt::create([
            'course_id' => $course->id,
            'user_id' => $learner->id,
            'token' => Str::uuid()->toString(),
            'status' => 'pending',
            'score_percent' => null,
            'metadata' => null,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.course-analytics.attempt', $attempt));

        $response->assertNotFound();
    }

    // ── Question breakdown renders ──────────────────────────────────────

    public function test_question_breakdown_renders_question_text_and_options(): void
    {
        $data = $this->seedAttemptWithResponses('completed', 100.00);

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.course-analytics.attempt', $data['attempt']));

        $response->assertOk();

        // Verify question text appears
        $response->assertSeeText('What is the primary goal of safeguarding?');
        $response->assertSeeText('Who should you report concerns to?');

        // Verify option text appears
        $response->assertSeeText('Protecting children');
        $response->assertSeeText('Filing paperwork');
        $response->assertSeeText('Designated safeguarding lead');
    }
}
