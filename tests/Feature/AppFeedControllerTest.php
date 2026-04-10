<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppFeedControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedLearnerWithCourses(): array
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'role' => 'Classroom Teacher',
            'team' => 'Teaching Staff',
            'topics' => ['compliance'],
            'goal' => 'Complete mandatory training and stay up to date',
            'difficulty' => 'beginner',
        ]);

        $courseA = Course::create([
            'title' => 'Completed Course',
            'slug' => 'completed-course',
            'status' => 'published',
            'topic' => 'compliance',
        ]);
        $courseB = Course::create([
            'title' => 'In Progress Course',
            'slug' => 'in-progress-course',
            'status' => 'published',
            'topic' => 'productivity',
        ]);
        $courseC = Course::create([
            'title' => 'Assigned Course',
            'slug' => 'assigned-course',
            'status' => 'published',
            'topic' => 'leadership',
        ]);

        // Create modules for each course
        $modA1 = LearningModule::create(['title' => 'Mod A1', 'status' => 'published', 'topic' => 'compliance']);
        $modA2 = LearningModule::create(['title' => 'Mod A2', 'status' => 'published', 'topic' => 'compliance']);
        $modB1 = LearningModule::create(['title' => 'Mod B1', 'status' => 'published', 'topic' => 'productivity']);
        $modC1 = LearningModule::create(['title' => 'Mod C1', 'status' => 'published', 'topic' => 'leadership']);

        $courseA->modules()->attach([$modA1->id => ['sort_order' => 1], $modA2->id => ['sort_order' => 2]]);
        $courseB->modules()->attach([$modB1->id => ['sort_order' => 1]]);
        $courseC->modules()->attach([$modC1->id => ['sort_order' => 1]]);

        // Enrol user: courseA completed, courseB in_progress, courseC assigned
        DB::table('course_user')->insert([
            ['course_id' => $courseA->id, 'user_id' => $user->id, 'status' => 'completed', 'completed_at' => now()->subDays(3), 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => $courseB->id, 'user_id' => $user->id, 'status' => 'in_progress', 'completed_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => $courseC->id, 'user_id' => $user->id, 'status' => 'assigned', 'completed_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Module progress for completed course
        ModuleProgress::create(['user_id' => $user->id, 'learning_module_id' => $modA1->id, 'status' => 'completed', 'percent_complete' => 100, 'started_at' => now()->subDays(5), 'completed_at' => now()->subDays(3)]);
        ModuleProgress::create(['user_id' => $user->id, 'learning_module_id' => $modA2->id, 'status' => 'completed', 'percent_complete' => 100, 'started_at' => now()->subDays(4), 'completed_at' => now()->subDays(3)]);

        // Module progress for in-progress course
        ModuleProgress::create(['user_id' => $user->id, 'learning_module_id' => $modB1->id, 'status' => 'in_progress', 'percent_complete' => 40, 'started_at' => now()->subDays(2)]);

        return [
            'user' => $user,
            'courses' => [$courseA, $courseB, $courseC],
            'modules' => [$modA1, $modA2, $modB1, $modC1],
        ];
    }

    public function test_feed_returns_200_for_authenticated_user(): void
    {
        $data = $this->seedLearnerWithCourses();

        $response = $this->actingAs($data['user'])->get(route('app.feed'));

        $response->assertOk();
    }

    public function test_feed_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get(route('app.feed'));

        $response->assertRedirect(route('login'));
    }

    public function test_feed_kpis_reflect_course_level_enrolments(): void
    {
        $data = $this->seedLearnerWithCourses();

        $response = $this->actingAs($data['user'])->get(route('app.feed'));

        $response->assertOk();
        $response->assertViewHas('dashboardSummary', function (array $summary) {
            // 3 total enrolments, 1 completed, 1 in_progress
            return $summary['courses_total'] === 3
                && $summary['completed_total'] === 1
                && $summary['in_progress_total'] === 1
                && $summary['completion_rate_percent'] === 33; // 1/3 = 33%
        });
    }

    public function test_feed_passes_assigned_courses_to_view(): void
    {
        $data = $this->seedLearnerWithCourses();

        $response = $this->actingAs($data['user'])->get(route('app.feed'));

        $response->assertOk();
        $response->assertViewHas('assignedCourses');
        $assignedCourses = $response->viewData('assignedCourses');
        $this->assertCount(3, $assignedCourses);
    }

    public function test_feed_shows_zero_kpis_for_user_with_no_enrolments(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'role' => 'Classroom Teacher',
            'team' => 'Teaching Staff',
        ]);

        $response = $this->actingAs($user)->get(route('app.feed'));

        $response->assertOk();
        $response->assertViewHas('dashboardSummary', function (array $summary) {
            return $summary['courses_total'] === 0
                && $summary['completed_total'] === 0
                && $summary['in_progress_total'] === 0
                && $summary['completion_rate_percent'] === 0;
        });
    }

    public function test_required_catalogue_page_loads(): void
    {
        $data = $this->seedLearnerWithCourses();

        $response = $this->actingAs($data['user'])->get(route('app.feed.required'));

        $response->assertOk();
        $response->assertViewHas('catalogueTitle', 'Required');
    }

    public function test_recommended_catalogue_page_loads(): void
    {
        $data = $this->seedLearnerWithCourses();

        $response = $this->actingAs($data['user'])->get(route('app.feed.recommended'));

        $response->assertOk();
        $response->assertViewHas('catalogueTitle', 'Recommended');
    }

    public function test_saved_catalogue_page_loads(): void
    {
        $data = $this->seedLearnerWithCourses();

        $response = $this->actingAs($data['user'])->get(route('app.feed.saved'));

        $response->assertOk();
        $response->assertViewHas('catalogueTitle', 'Saved');
    }
}
