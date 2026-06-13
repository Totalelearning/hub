<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\Location;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCourseAnalyticsComparisonTest extends TestCase
{
    use RefreshDatabase;

    private function createSiteAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createManager(): User
    {
        return User::factory()->create([
            'system_role' => 'manager',
            'is_admin' => true,
        ]);
    }

    private function seedComparisonData(): array
    {
        $location = Location::firstOrCreate(['slug' => 'oakwood-test'], ['name' => 'Oakwood Primary', 'sort_order' => 1, 'is_active' => true]);
        $location2 = Location::firstOrCreate(['slug' => 'riverside-test'], ['name' => 'Riverside Secondary', 'sort_order' => 2, 'is_active' => true]);

        $team = Team::firstOrCreate(['slug' => 'teaching-test'], ['name' => 'Teaching Staff Test', 'sort_order' => 100]);
        $team2 = Team::firstOrCreate(['slug' => 'slt-test'], ['name' => 'SLT Test', 'sort_order' => 101]);

        $role = Role::firstOrCreate(['slug' => 'classroom-teacher-test'], ['name' => 'Classroom Teacher Test', 'sort_order' => 100]);
        $role2 = Role::firstOrCreate(['slug' => 'head-test'], ['name' => 'Headteacher Test', 'sort_order' => 101]);

        $course = Course::create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'status' => 'published',
            'topic' => 'general',
        ]);

        // Learner at Oakwood, Teaching Staff, Classroom Teacher
        $learner1 = User::factory()->create(['system_role' => 'learner']);
        UserPreference::create([
            'user_id' => $learner1->id,
            'location_id' => $location->id,
            'team' => 'Teaching Staff Test',
            'role' => 'Classroom Teacher Test',
        ]);
        $course->assignedUsers()->attach($learner1->id, ['status' => 'completed', 'completed_at' => now()]);

        // Learner at Riverside, SLT, Headteacher
        $learner2 = User::factory()->create(['system_role' => 'learner']);
        UserPreference::create([
            'user_id' => $learner2->id,
            'location_id' => $location2->id,
            'team' => 'SLT Test',
            'role' => 'Headteacher Test',
        ]);
        $course->assignedUsers()->attach($learner2->id, ['status' => 'in_progress']);

        // Reinforcement attempt for learner1
        CourseReinforcementAttempt::create([
            'user_id' => $learner1->id,
            'course_id' => $course->id,
            'token' => Str::uuid()->toString(),
            'status' => 'completed',
            'score_percent' => 80,
            'completed_at' => now(),
            'metadata' => [],
        ]);

        return compact('location', 'location2', 'team', 'team2', 'role', 'role2', 'course', 'learner1', 'learner2');
    }

    // ── Location Comparison ──

    public function test_location_comparison_requires_unrestricted_view(): void
    {
        Location::create(['slug' => 'test', 'name' => 'Test', 'sort_order' => 1, 'is_active' => true]);
        $manager = $this->createManager();

        $this->actingAs($manager)
            ->getJson(route('app.admin.course-analytics.location-comparison-json'))
            ->assertForbidden();
    }

    public function test_location_comparison_returns_data_for_site_admin(): void
    {
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.location-comparison-json'))
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);

        $locations = collect($response->json('data'));
        $oakwood = $locations->first(fn ($l) => str_contains($l['location'], 'Oakwood'));
        $this->assertNotNull($oakwood);
        $this->assertEquals(1, $oakwood['learners']);
        $this->assertEquals(1, $oakwood['enrolled']);
        $this->assertEquals(1, $oakwood['completed']);
        $this->assertEquals(100, $oakwood['completion_rate']);
    }

    public function test_location_comparison_hides_zero_enrolment_locations(): void
    {
        Location::create(['slug' => 'empty', 'name' => 'Empty School', 'sort_order' => 1, 'is_active' => true]);
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.location-comparison-json'))
            ->assertOk();

        $names = collect($response->json('data'))->pluck('location');
        $this->assertFalse($names->contains('Empty School'));
    }

    public function test_location_comparison_filters_by_role(): void
    {
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.location-comparison-json', ['role' => 'Classroom Teacher Test']))
            ->assertOk();

        $locations = collect($response->json('data'));
        // Only Oakwood has a Classroom Teacher enrolment
        $this->assertCount(1, $locations);
        $this->assertEquals('Oakwood Primary', $locations[0]['location']);
    }

    // ── Team Comparison ──

    public function test_team_comparison_requires_unrestricted_view(): void
    {
        Team::create(['slug' => 'test', 'name' => 'Test', 'sort_order' => 1]);
        $manager = $this->createManager();

        $this->actingAs($manager)
            ->getJson(route('app.admin.course-analytics.team-comparison-json'))
            ->assertForbidden();
    }

    public function test_team_comparison_returns_data_for_site_admin(): void
    {
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.team-comparison-json'))
            ->assertOk();

        $teams = collect($response->json('data'));
        $teaching = $teams->firstWhere('team', 'Teaching Staff Test');
        $this->assertNotNull($teaching);
        $this->assertEquals(1, $teaching['enrolled']);
        $this->assertEquals(1, $teaching['completed']);
    }

    public function test_team_comparison_hides_zero_enrolment_teams(): void
    {
        Team::create(['slug' => 'empty', 'name' => 'Empty Team', 'sort_order' => 99]);
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.team-comparison-json'))
            ->assertOk();

        $names = collect($response->json('data'))->pluck('team');
        $this->assertFalse($names->contains('Empty Team'));
    }

    public function test_team_comparison_filters_by_location(): void
    {
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.team-comparison-json', ['location' => 'Oakwood Primary']))

            ->assertOk();

        $teams = collect($response->json('data'));
        // Only Teaching Staff is at Oakwood
        $this->assertCount(1, $teams);
        $this->assertEquals('Teaching Staff Test', $teams[0]['team']);
    }

    // ── Role Comparison ──

    public function test_role_comparison_requires_unrestricted_view(): void
    {
        Role::create(['slug' => 'test', 'name' => 'Test Role', 'sort_order' => 1]);
        $manager = $this->createManager();

        $this->actingAs($manager)
            ->getJson(route('app.admin.course-analytics.role-comparison-json'))
            ->assertForbidden();
    }

    public function test_role_comparison_returns_data_for_site_admin(): void
    {
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.role-comparison-json'))
            ->assertOk();

        $roles = collect($response->json('data'));
        $teacher = $roles->firstWhere('role', 'Classroom Teacher Test');
        $this->assertNotNull($teacher);
        $this->assertEquals(1, $teacher['enrolled']);
        $this->assertEquals(80, $teacher['avg_score']);
    }

    public function test_role_comparison_hides_zero_enrolment_roles(): void
    {
        Role::create(['slug' => 'empty', 'name' => 'Empty Role', 'sort_order' => 99]);
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.role-comparison-json'))
            ->assertOk();

        $names = collect($response->json('data'))->pluck('role');
        $this->assertFalse($names->contains('Empty Role'));
    }

    public function test_role_comparison_filters_by_team(): void
    {
        $data = $this->seedComparisonData();
        $admin = $this->createSiteAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('app.admin.course-analytics.role-comparison-json', ['team' => 'SLT Test']))
            ->assertOk();

        $roles = collect($response->json('data'));
        // Only Headteacher Test is in SLT Test
        $this->assertCount(1, $roles);
        $this->assertEquals('Headteacher Test', $roles[0]['role']);
    }

    // ── Trustee access ──

    public function test_comparison_endpoints_accessible_by_trustee(): void
    {
        $data = $this->seedComparisonData();
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->getJson(route('app.admin.course-analytics.location-comparison-json'))
            ->assertOk();

        $this->actingAs($trustee)
            ->getJson(route('app.admin.course-analytics.team-comparison-json'))
            ->assertOk();

        $this->actingAs($trustee)
            ->getJson(route('app.admin.course-analytics.role-comparison-json'))
            ->assertOk();
    }
}
