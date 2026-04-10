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

class AdminControllerAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createLearner(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    private function seedDemoData(): void
    {
        // Create a published course with modules and an enrolled user
        $course = Course::create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'status' => 'published',
            'topic' => 'compliance',
        ]);

        $module = LearningModule::create([
            'title' => 'Test Module',
            'status' => 'published',
            'topic' => 'compliance',
        ]);

        $course->modules()->attach($module->id, ['sort_order' => 1]);

        $learner = $this->createLearner();
        UserPreference::create([
            'user_id' => $learner->id,
            'role' => 'Classroom Teacher',
            'team' => 'Teaching Staff',
        ]);

        DB::table('course_user')->insert([
            'course_id' => $course->id,
            'user_id' => $learner->id,
            'status' => 'completed',
            'completed_at' => now()->subDays(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ModuleProgress::create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(7),
            'completed_at' => now()->subDays(5),
        ]);
    }

    // ── Assignment Dashboard ────────────────────────────────────────────

    public function test_admin_assignments_page_loads_for_admin(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.assignments'));

        $response->assertOk();
    }

    public function test_admin_assignments_page_returns_403_for_non_admin(): void
    {
        $response = $this->actingAs($this->createLearner())
            ->get(route('app.admin.assignments'));

        $response->assertForbidden();
    }

    public function test_admin_assignments_page_redirects_guest_to_login(): void
    {
        $response = $this->get(route('app.admin.assignments'));

        $response->assertRedirect(route('login'));
    }

    // ── Compliance Report ───────────────────────────────────────────────

    public function test_admin_compliance_page_loads_for_admin(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.compliance'));

        $response->assertOk();
    }

    public function test_admin_compliance_page_returns_403_for_non_admin(): void
    {
        $response = $this->actingAs($this->createLearner())
            ->get(route('app.admin.compliance'));

        $response->assertForbidden();
    }

    // ── Course Analytics ────────────────────────────────────────────────

    public function test_admin_course_analytics_page_loads_for_admin(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.course-analytics'));

        $response->assertOk();
    }

    public function test_admin_course_analytics_returns_403_for_non_admin(): void
    {
        $response = $this->actingAs($this->createLearner())
            ->get(route('app.admin.course-analytics'));

        $response->assertForbidden();
    }

    // ── Users ───────────────────────────────────────────────────────────

    public function test_admin_users_page_loads_for_admin(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.users.index'));

        $response->assertOk();
    }

    public function test_admin_users_returns_403_for_non_admin(): void
    {
        $response = $this->actingAs($this->createLearner())
            ->get(route('app.admin.users.index'));

        $response->assertForbidden();
    }

    // ── Courses ─────────────────────────────────────────────────────────

    public function test_admin_courses_page_loads_for_admin(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.courses.index'));

        $response->assertOk();
    }

    public function test_admin_courses_returns_403_for_non_admin(): void
    {
        $response = $this->actingAs($this->createLearner())
            ->get(route('app.admin.courses.index'));

        $response->assertForbidden();
    }

    // ── SCORM Overview ──────────────────────────────────────────────────

    public function test_admin_scorm_page_loads_for_admin(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.scorm.index'));

        $response->assertOk();
    }

    // ── Modules ─────────────────────────────────────────────────────────

    public function test_admin_modules_page_loads_for_admin(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.modules.index'));

        $response->assertOk();
    }

    // ── Learning Paths ──────────────────────────────────────────────────

    public function test_admin_paths_page_loads_for_admin(): void
    {
        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.paths.index'));

        $response->assertOk();
    }

    // ── CSV Exports ─────────────────────────────────────────────────────

    public function test_admin_assignments_export_returns_csv(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.assignments.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_course_analytics_export_returns_csv(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.course-analytics.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_compliance_export_returns_csv(): void
    {
        $this->seedDemoData();

        $response = $this->actingAs($this->createAdmin())
            ->get(route('app.admin.compliance.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
