<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningModule;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    // ── Registration ────────────────────────────────────────────

    public function test_parent_registration_page_loads(): void
    {
        $this->get(route('parent.register'))->assertOk();
    }

    public function test_parent_can_register(): void
    {
        $response = $this->post(route('parent.register'), [
            'name' => 'Jane Parent',
            'email' => 'jane.parent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('app.parent.dashboard'));

        $user = User::where('email', 'jane.parent@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('parent', $user->system_role);
        $this->assertTrue($user->isParent());

        // UserPreference created with role 'Parent'
        $this->assertNotNull($user->preference);
        $this->assertSame('Parent', $user->preference->role);
    }

    public function test_parent_registration_requires_valid_data(): void
    {
        $this->post(route('parent.register'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    // ── Authentication & redirects ──────────────────────────────

    public function test_parent_login_redirects_to_parent_dashboard(): void
    {
        $parent = User::factory()->parent()->create([
            'email' => 'parent@example.com',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'parent@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('app.parent.dashboard'));
    }

    public function test_dashboard_redirect_sends_parent_to_parent_portal(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)
            ->get('/dashboard')
            ->assertRedirect(route('app.parent.dashboard'));
    }

    // ── Parent dashboard ────────────────────────────────────────

    public function test_parent_dashboard_loads_for_parent_user(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)
            ->get(route('app.parent.dashboard'))
            ->assertOk()
            ->assertSee('Parent Portal')
            ->assertSee('Your Courses');
    }

    public function test_parent_dashboard_blocked_for_learner(): void
    {
        $learner = User::factory()->create();

        $this->actingAs($learner)
            ->get(route('app.parent.dashboard'))
            ->assertForbidden();
    }

    public function test_parent_dashboard_blocked_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('app.parent.dashboard'))
            ->assertForbidden();
    }

    public function test_parent_dashboard_shows_assigned_courses(): void
    {
        $parent = User::factory()->parent()->create();
        UserPreference::create([
            'user_id' => $parent->id,
            'role' => 'Parent',
        ]);

        $courseA = Course::create([
            'title' => 'Safeguarding for Parents',
            'slug' => 'safeguarding-parents',
            'status' => 'published',
            'topic' => 'compliance',
        ]);
        $courseB = Course::create([
            'title' => 'Online Safety Guide',
            'slug' => 'online-safety',
            'status' => 'published',
            'topic' => 'compliance',
        ]);

        $modA = LearningModule::create(['title' => 'Mod A', 'status' => 'published']);
        $modB = LearningModule::create(['title' => 'Mod B', 'status' => 'published']);
        $courseA->modules()->attach([$modA->id => ['sort_order' => 1]]);
        $courseB->modules()->attach([$modB->id => ['sort_order' => 1]]);

        DB::table('course_user')->insert([
            ['course_id' => $courseA->id, 'user_id' => $parent->id, 'status' => 'assigned', 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => $courseB->id, 'user_id' => $parent->id, 'status' => 'in_progress', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($parent)->get(route('app.parent.dashboard'));

        $response->assertOk();
        $response->assertViewHas('courses', function ($courses) {
            return $courses->count() === 2;
        });
        $response->assertViewHas('summary', function (array $s) {
            return $s['total'] === 2
                && $s['outstanding'] === 2
                && $s['completed'] === 0;
        });
    }

    public function test_parent_dashboard_shows_empty_state(): void
    {
        $parent = User::factory()->parent()->create();

        $response = $this->actingAs($parent)->get(route('app.parent.dashboard'));

        $response->assertOk();
        $response->assertSee('No courses assigned yet');
        $response->assertViewHas('summary', function (array $s) {
            return $s['total'] === 0 && $s['completed'] === 0;
        });
    }

    // ── Role helpers ────────────────────────────────────────────

    public function test_parent_role_helpers(): void
    {
        $parent = User::factory()->parent()->create();

        $this->assertTrue($parent->isParent());
        $this->assertFalse($parent->isLearner());
        $this->assertFalse($parent->hasAdminAccess());
        $this->assertSame('Parent', $parent->systemRoleLabel());
    }
}
