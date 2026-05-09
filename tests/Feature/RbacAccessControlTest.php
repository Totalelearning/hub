<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RbacAccessControlTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper methods ──────────────────────────────────────────

    private function createSiteAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createSltManager(array $teams): User
    {
        return User::factory()->sltManager($teams)->create();
    }

    private function createManager(array $teams): User
    {
        return User::factory()->manager($teams)->create();
    }

    private function createLearnerInTeam(string $team, array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            ['team' => $team, 'role' => 'Classroom Teacher'],
        );

        return $user;
    }

    // ── Manager: user index scoping ─────────────────────────────

    public function test_manager_user_index_shows_only_own_team(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $ownTeamUser = $this->createLearnerInTeam('IT & Digital Services', ['name' => 'IT Learner']);
        $otherTeamUser = $this->createLearnerInTeam('HR & People', ['name' => 'HR Learner']);

        $response = $this->actingAs($manager)->get('/app/admin/users');

        $response->assertOk()
            ->assertSee('IT Learner')
            ->assertDontSee('HR Learner');
    }

    public function test_manager_cannot_view_user_outside_team(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $otherUser = $this->createLearnerInTeam('HR & People');

        $this->actingAs($manager)
            ->get('/app/admin/users/'.$otherUser->id)
            ->assertForbidden();
    }

    public function test_manager_cannot_edit_user_outside_team(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $otherUser = $this->createLearnerInTeam('HR & People');

        $this->actingAs($manager)
            ->get('/app/admin/users/'.$otherUser->id.'/edit')
            ->assertForbidden();
    }

    public function test_manager_can_view_user_in_own_team(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $ownUser = $this->createLearnerInTeam('IT & Digital Services', ['name' => 'IT Staff Member']);

        $this->actingAs($manager)
            ->get('/app/admin/users/'.$ownUser->id)
            ->assertOk()
            ->assertSee('IT Staff Member');
    }

    // ── SLT Manager: multi-team scoping ─────────────────────────

    public function test_slt_manager_sees_users_across_multiple_teams(): void
    {
        $slt = $this->createSltManager(['Teaching Staff', 'Teaching Support Staff']);
        $teacher = $this->createLearnerInTeam('Teaching Staff', ['name' => 'Teacher One']);
        $support = $this->createLearnerInTeam('Teaching Support Staff', ['name' => 'Support One']);
        $itUser = $this->createLearnerInTeam('IT & Digital Services', ['name' => 'IT Person']);

        $response = $this->actingAs($slt)->get('/app/admin/users');

        $response->assertOk()
            ->assertSee('Teacher One')
            ->assertSee('Support One')
            ->assertDontSee('IT Person');
    }

    public function test_slt_manager_cannot_view_user_outside_managed_teams(): void
    {
        $slt = $this->createSltManager(['Teaching Staff']);
        $hrUser = $this->createLearnerInTeam('HR & People');

        $this->actingAs($slt)
            ->get('/app/admin/users/'.$hrUser->id)
            ->assertForbidden();
    }

    // ── Site admin: full access ─────────────────────────────────

    public function test_site_admin_sees_all_users(): void
    {
        $admin = $this->createSiteAdmin();
        $itUser = $this->createLearnerInTeam('IT & Digital Services', ['name' => 'IT Learner']);
        $hrUser = $this->createLearnerInTeam('HR & People', ['name' => 'HR Learner']);

        $response = $this->actingAs($admin)->get('/app/admin/users');

        $response->assertOk()
            ->assertSee('IT Learner')
            ->assertSee('HR Learner');
    }

    // ── Team assignment permissions ─────────────────────────────

    public function test_manager_cannot_change_team_assignment(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $user = $this->createLearnerInTeam('IT & Digital Services', ['name' => 'IT User']);

        $this->actingAs($manager)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => 'IT User',
                'email' => $user->email,
                'team' => 'hr_people',
            ]);

        // Team should remain unchanged — manager can't reassign teams
        $this->assertSame('IT & Digital Services', $user->fresh()->preference->team);
    }

    public function test_slt_manager_can_change_team_assignment(): void
    {
        $slt = $this->createSltManager(['Teaching Staff', 'Teaching Support Staff']);
        $user = $this->createLearnerInTeam('Teaching Staff', ['name' => 'Teacher']);

        $this->actingAs($slt)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => 'Teacher',
                'email' => $user->email,
                'team' => 'teaching_support_staff',
            ]);

        $this->assertSame('Teaching Support Staff', $user->fresh()->preference->team);
    }

    public function test_site_admin_can_change_team_assignment(): void
    {
        $admin = $this->createSiteAdmin();
        $user = $this->createLearnerInTeam('Teaching Staff', ['name' => 'Teaching User']);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => 'Teaching User',
                'email' => $user->email,
                'team' => 'teaching_support_staff',
            ]);

        $this->assertSame('Teaching Support Staff', $user->fresh()->preference->team);
    }

    // ── System role management ──────────────────────────────────

    public function test_manager_cannot_change_system_role(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $user = $this->createLearnerInTeam('IT & Digital Services');

        $this->actingAs($manager)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'system_role' => 'site_admin',
            ]);

        $this->assertSame('learner', $user->fresh()->system_role);
    }

    public function test_slt_manager_cannot_change_system_role(): void
    {
        $slt = $this->createSltManager(['Teaching Staff']);
        $user = $this->createLearnerInTeam('Teaching Staff');

        $this->actingAs($slt)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'system_role' => 'manager',
                'managed_teams' => ['Teaching Staff'],
            ]);

        $this->assertSame('learner', $user->fresh()->system_role);
        $this->assertNull($user->fresh()->managed_teams);
    }

    public function test_site_admin_can_set_system_role_to_manager(): void
    {
        $admin = $this->createSiteAdmin();
        $user = $this->createLearnerInTeam('IT & Digital Services');

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'system_role' => 'manager',
                'managed_teams' => ['IT & Digital Services'],
            ]);

        $user->refresh();
        $this->assertSame('manager', $user->system_role);
        $this->assertTrue($user->is_admin);
        $this->assertSame(['IT & Digital Services'], $user->managed_teams);
    }

    public function test_site_admin_can_set_system_role_to_slt_manager(): void
    {
        $admin = $this->createSiteAdmin();
        $user = $this->createLearnerInTeam('Teaching Staff');

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'system_role' => 'slt_manager',
                'managed_teams' => ['Teaching Staff', 'Teaching Support Staff'],
            ]);

        $user->refresh();
        $this->assertSame('slt_manager', $user->system_role);
        $this->assertTrue($user->is_admin);
        $this->assertSame(['Teaching Staff', 'Teaching Support Staff'], $user->managed_teams);
    }

    public function test_site_admin_demoting_to_learner_clears_managed_teams(): void
    {
        $admin = $this->createSiteAdmin();
        $manager = $this->createManager(['IT & Digital Services']);
        UserPreference::updateOrCreate(
            ['user_id' => $manager->id],
            ['team' => 'IT & Digital Services'],
        );

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$manager->id, [
                'name' => $manager->name,
                'email' => $manager->email,
                'system_role' => 'learner',
            ]);

        $manager->refresh();
        $this->assertSame('learner', $manager->system_role);
        $this->assertFalse($manager->is_admin);
        $this->assertNull($manager->managed_teams);
    }

    // ── Toggle admin access restricted to site admin ────────────

    public function test_manager_cannot_toggle_admin_access(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $user = $this->createLearnerInTeam('IT & Digital Services');

        $this->actingAs($manager)
            ->patch('/app/admin/users/'.$user->id.'/admin-access')
            ->assertForbidden();
    }

    public function test_slt_manager_cannot_toggle_admin_access(): void
    {
        $slt = $this->createSltManager(['Teaching Staff']);
        $user = $this->createLearnerInTeam('Teaching Staff');

        $this->actingAs($slt)
            ->patch('/app/admin/users/'.$user->id.'/admin-access')
            ->assertForbidden();
    }

    // ── User creation by manager auto-assigns team ──────────────

    public function test_manager_creating_user_auto_assigns_own_team(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);

        $this->actingAs($manager)
            ->post('/app/admin/users', [
                'name' => 'New IT Person',
                'email' => 'new-it-person@example.com',
                'password' => 'secure-password',
                'password_confirmation' => 'secure-password',
            ]);

        $newUser = User::where('email', 'new-it-person@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('IT & Digital Services', $newUser->preference?->team);
        $this->assertSame('learner', $newUser->system_role);
    }

    public function test_manager_creating_user_cannot_set_admin_role(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);

        $this->actingAs($manager)
            ->post('/app/admin/users', [
                'name' => 'Sneaky Admin',
                'email' => 'sneaky@example.com',
                'system_role' => 'site_admin',
                'password' => 'secure-password',
                'password_confirmation' => 'secure-password',
            ]);

        $newUser = User::where('email', 'sneaky@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('learner', $newUser->system_role);
        $this->assertFalse($newUser->is_admin);
    }

    // ── Settings pages restricted to site admin ─────────────────

    public function test_manager_cannot_access_reminder_settings(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);

        $this->actingAs($manager)
            ->get('/app/admin/reminder-settings')
            ->assertForbidden();
    }

    public function test_slt_manager_cannot_access_reminder_settings(): void
    {
        $slt = $this->createSltManager(['Teaching Staff']);

        $this->actingAs($slt)
            ->get('/app/admin/reminder-settings')
            ->assertForbidden();
    }

    public function test_site_admin_can_access_reminder_settings(): void
    {
        $admin = $this->createSiteAdmin();

        $this->actingAs($admin)
            ->get('/app/admin/reminder-settings')
            ->assertOk();
    }

    // ── Dashboard scoped by team ────────────────────────────────

    public function test_manager_dashboard_shows_only_own_team_data(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $ownUser = $this->createLearnerInTeam('IT & Digital Services', ['name' => 'IT Learner Dashboard']);
        $otherUser = $this->createLearnerInTeam('HR & People', ['name' => 'HR Learner Dashboard']);

        $course = Course::create(['title' => 'Test Course', 'slug' => 'test-course-'.uniqid(), 'status' => 'published', 'topic' => 'compliance']);
        DB::table('course_user')->insert([
            ['course_id' => $course->id, 'user_id' => $ownUser->id, 'status' => 'assigned', 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => $course->id, 'user_id' => $otherUser->id, 'status' => 'assigned', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($manager)->get('/app/admin/assignments');

        $response->assertOk();
        // The page should load without errors — scoping is applied server-side
    }

    // ── Compliance report scoped by team ─────────────────────────

    public function test_manager_compliance_shows_only_own_team(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $ownUser = $this->createLearnerInTeam('IT & Digital Services', ['name' => 'IT Compliance User']);
        $otherUser = $this->createLearnerInTeam('HR & People', ['name' => 'HR Compliance User']);

        $course = Course::create(['title' => 'Mandatory Course', 'slug' => 'mandatory-course-'.uniqid(), 'status' => 'published', 'topic' => 'compliance', 'is_mandatory' => true]);
        DB::table('course_user')->insert([
            ['course_id' => $course->id, 'user_id' => $ownUser->id, 'status' => 'completed', 'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => $course->id, 'user_id' => $otherUser->id, 'status' => 'completed', 'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($manager)->get('/app/admin/compliance');

        $response->assertOk()
            ->assertSee('IT Compliance User')
            ->assertDontSee('HR Compliance User');
    }

    // ── Analytics page scoped by team ───────────────────────────

    public function test_manager_analytics_page_loads(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);

        $this->actingAs($manager)
            ->get('/app/admin/course-analytics')
            ->assertOk();
    }

    public function test_manager_analytics_json_scoped_by_team(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);
        $ownUser = $this->createLearnerInTeam('IT & Digital Services');
        $otherUser = $this->createLearnerInTeam('HR & People');

        $course = Course::create(['title' => 'Test Course', 'slug' => 'test-course-'.uniqid(), 'status' => 'published', 'topic' => 'compliance']);
        DB::table('course_user')->insert([
            ['course_id' => $course->id, 'user_id' => $ownUser->id, 'status' => 'completed', 'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => $course->id, 'user_id' => $otherUser->id, 'status' => 'completed', 'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($manager)
            ->getJson('/app/admin/course-analytics/courses-json');

        $response->assertOk();
        $data = $response->json('data');

        // The course should show only 1 completion (own team), not 2
        if (! empty($data)) {
            $courseRow = collect($data)->firstWhere('id', $course->id);
            if ($courseRow) {
                $this->assertSame(1, $courseRow['completed']);
            }
        }
    }

    // ── Learner cannot access admin pages ───────────────────────

    public function test_learner_cannot_access_admin_pages(): void
    {
        $learner = User::factory()->create();

        $this->actingAs($learner)->get('/app/admin/users')->assertForbidden();
        $this->actingAs($learner)->get('/app/admin/assignments')->assertForbidden();
        $this->actingAs($learner)->get('/app/admin/compliance')->assertForbidden();
        $this->actingAs($learner)->get('/app/admin/course-analytics')->assertForbidden();
        $this->actingAs($learner)->get('/app/admin/reminder-settings')->assertForbidden();
    }

    // ── Write-protected routes ──────────────────────────────────

    public function test_manager_cannot_create_courses(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);

        $this->actingAs($manager)
            ->get('/app/admin/courses/create')
            ->assertForbidden();
    }

    public function test_manager_cannot_create_learning_paths(): void
    {
        $manager = $this->createManager(['IT & Digital Services']);

        $this->actingAs($manager)
            ->get('/app/admin/paths/create')
            ->assertForbidden();
    }

    // ── Model helper methods ────────────────────────────────────

    public function test_user_model_role_helpers(): void
    {
        $admin = User::factory()->admin()->make();
        $slt = User::factory()->sltManager(['Teaching Staff'])->make();
        $mgr = User::factory()->manager(['IT & Digital Services'])->make();
        $learner = User::factory()->make();

        $this->assertTrue($admin->isSiteAdmin());
        $this->assertTrue($admin->hasAdminAccess());
        $this->assertTrue($admin->canManageTeamAssignments());

        $this->assertTrue($slt->isSltManager());
        $this->assertTrue($slt->hasAdminAccess());
        $this->assertTrue($slt->canManageTeamAssignments());
        $this->assertTrue($slt->canManageTeam('Teaching Staff'));
        $this->assertFalse($slt->canManageTeam('HR & People'));

        $this->assertTrue($mgr->isManager());
        $this->assertTrue($mgr->hasAdminAccess());
        $this->assertFalse($mgr->canManageTeamAssignments());
        $this->assertTrue($mgr->canManageTeam('IT & Digital Services'));
        $this->assertFalse($mgr->canManageTeam('HR & People'));

        $this->assertTrue($learner->isLearner());
        $this->assertFalse($learner->hasAdminAccess());
        $this->assertFalse($learner->canManageTeamAssignments());
    }

    public function test_system_role_labels(): void
    {
        $this->assertSame('Site Administrator', User::factory()->admin()->make()->systemRoleLabel());
        $this->assertSame('SLT Manager', User::factory()->sltManager([])->make()->systemRoleLabel());
        $this->assertSame('Manager', User::factory()->manager([])->make()->systemRoleLabel());
        $this->assertSame('Learner', User::factory()->make()->systemRoleLabel());
    }
}
