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

class AppCourseControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createCourseWithModules(int $moduleCount = 3): Course
    {
        $course = Course::create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'status' => 'published',
            'topic' => 'compliance',
            'estimated_minutes' => 15,
        ]);

        for ($i = 1; $i <= $moduleCount; $i++) {
            $module = LearningModule::create([
                'title' => "Module $i",
                'status' => 'published',
                'topic' => 'compliance',
            ]);
            $course->modules()->attach($module->id, ['sort_order' => $i]);
        }

        return $course;
    }

    private function createLearner(): User
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'role' => 'Classroom Teacher',
            'team' => 'Teaching Staff',
        ]);

        return $user;
    }

    public function test_course_show_returns_200_for_enrolled_user(): void
    {
        $user = $this->createLearner();
        $course = $this->createCourseWithModules(2);

        DB::table('course_user')->insert([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'status' => 'assigned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('app.courses.show', $course));

        $response->assertOk();
        $response->assertViewHas('course');
        $response->assertViewHas('totalModules', 2);
    }

    public function test_course_show_displays_correct_progress_for_completed_course(): void
    {
        $user = $this->createLearner();
        $course = $this->createCourseWithModules(3);
        $moduleIds = $course->modules->pluck('id');

        DB::table('course_user')->insert([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'completed_at' => now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($moduleIds as $moduleId) {
            ModuleProgress::create([
                'user_id' => $user->id,
                'learning_module_id' => $moduleId,
                'status' => 'completed',
                'percent_complete' => 100,
                'started_at' => now()->subDays(3),
                'completed_at' => now()->subDay(),
            ]);
        }

        $response = $this->actingAs($user)->get(route('app.courses.show', $course));

        $response->assertOk();
        $response->assertViewHas('completedModules', 3);
        $response->assertViewHas('overallPercent', 100);
        $response->assertViewHas('courseStatus', 'completed');
    }

    public function test_course_show_displays_partial_progress_for_in_progress_course(): void
    {
        $user = $this->createLearner();
        $course = $this->createCourseWithModules(2);
        $moduleIds = $course->modules->pluck('id');

        DB::table('course_user')->insert([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Complete first module, second still not started
        ModuleProgress::create([
            'user_id' => $user->id,
            'learning_module_id' => $moduleIds[0],
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('app.courses.show', $course));

        $response->assertOk();
        $response->assertViewHas('completedModules', 1);
        $response->assertViewHas('inProgressModules', 0);
        $response->assertViewHas('overallPercent', 50); // avg(100, 0) = 50
        $response->assertViewHas('courseStatus', 'in_progress');
    }

    public function test_course_show_identifies_next_module(): void
    {
        $user = $this->createLearner();
        $course = $this->createCourseWithModules(3);
        $moduleIds = $course->modules->pluck('id');

        DB::table('course_user')->insert([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Complete first module, second in progress
        ModuleProgress::create([
            'user_id' => $user->id,
            'learning_module_id' => $moduleIds[0],
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(3),
            'completed_at' => now()->subDays(2),
        ]);
        ModuleProgress::create([
            'user_id' => $user->id,
            'learning_module_id' => $moduleIds[1],
            'status' => 'in_progress',
            'percent_complete' => 60,
            'started_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('app.courses.show', $course));

        $response->assertOk();
        $nextModule = $response->viewData('nextModule');
        $this->assertNotNull($nextModule);
        // Next module should be the in-progress one (module 2)
        $this->assertSame('in_progress', $nextModule['status']);
    }

    public function test_course_show_returns_404_for_draft_course(): void
    {
        $user = $this->createLearner();
        $course = Course::create([
            'title' => 'Draft Course',
            'slug' => 'draft-course',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('app.courses.show', $course));

        $response->assertNotFound();
    }

    public function test_course_show_returns_not_assigned_status_for_unenrolled_user(): void
    {
        $user = $this->createLearner();
        $course = $this->createCourseWithModules(1);

        $response = $this->actingAs($user)->get(route('app.courses.show', $course));

        $response->assertOk();
        $response->assertViewHas('courseStatus', 'not_assigned');
    }

    public function test_course_show_redirects_unauthenticated_user(): void
    {
        $course = Course::create([
            'title' => 'Test',
            'slug' => 'test',
            'status' => 'published',
        ]);

        $response = $this->get(route('app.courses.show', $course));

        $response->assertRedirect(route('login'));
    }
}
