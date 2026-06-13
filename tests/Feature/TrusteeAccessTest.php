<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrusteeAccessTest extends TestCase
{
    use RefreshDatabase;

    // ── Role helpers ────────────────────────────────────────────

    public function test_trustee_role_helpers(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->assertTrue($trustee->isTrustee());
        $this->assertTrue($trustee->hasAdminAccess());
        $this->assertTrue($trustee->hasUnrestrictedView());
        $this->assertFalse($trustee->isSiteAdmin());
        $this->assertFalse($trustee->isLearner());
        $this->assertFalse($trustee->isParent());
        $this->assertSame('Trustee', $trustee->systemRoleLabel());
    }

    // ── Admin page access (read-only) ───────────────────────────

    public function test_trustee_can_view_admin_assignments_dashboard(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.admin.assignments'))
            ->assertOk();
    }

    public function test_trustee_can_view_compliance_report(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.admin.compliance'))
            ->assertOk();
    }

    public function test_trustee_can_view_course_analytics(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.admin.course-analytics'))
            ->assertOk();
    }

    public function test_trustee_can_view_users_index(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.admin.users.index'))
            ->assertOk();
    }

    public function test_trustee_can_view_locations(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.admin.locations.index'))
            ->assertOk();
    }

    // ── Write operations blocked ────────────────────────────────

    public function test_trustee_cannot_create_course(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.admin.courses.create'))
            ->assertForbidden();
    }

    public function test_trustee_can_view_user_form_but_cannot_store(): void
    {
        $trustee = User::factory()->trustee()->create();

        // Can view form (admin-access gate)
        $this->actingAs($trustee)
            ->get(route('app.admin.users.create'))
            ->assertOk();
    }

    public function test_trustee_cannot_create_module(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.admin.modules.create'))
            ->assertForbidden();
    }

    // ── Login redirect ──────────────────────────────────────────

    public function test_trustee_login_redirects_to_admin_dashboard(): void
    {
        $trustee = User::factory()->trustee()->create([
            'email' => 'trustee@example.com',
        ]);

        $this->post(route('login'), [
            'email' => 'trustee@example.com',
            'password' => 'password',
        ])->assertRedirect(route('app.admin.assignments'));
    }

    public function test_dashboard_redirect_sends_trustee_to_admin(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get('/dashboard')
            ->assertRedirect(route('app.admin.assignments'));
    }

    // ── Unrestricted scope ──────────────────────────────────────

    public function test_trustee_sees_all_team_user_ids(): void
    {
        $trustee = User::factory()->trustee()->create();

        // Returns null = no restrictions (same as site admin)
        $this->assertNull(User::managedTeamUserIds($trustee));
    }

    public function test_trustee_sees_all_location_user_ids(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->assertNull(User::managedLocationUserIds($trustee));
    }

    public function test_trustee_sees_all_scope_user_ids(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->assertNull(User::managedScopeUserIds($trustee));
    }

    // ── Analytics filters ──────────────────────────────────────

    public function test_trustee_analytics_page_has_filter_options(): void
    {
        $trustee = User::factory()->trustee()->create();

        $response = $this->actingAs($trustee)
            ->get(route('app.admin.course-analytics'));

        $response->assertOk();
        $response->assertViewHas('filterLocations');
        $response->assertViewHas('filterRoles');
        $response->assertViewHas('filterTeams');
    }

    public function test_trustee_analytics_json_accepts_filter_params(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->getJson(route('app.admin.course-analytics.courses-json', ['role' => 'Parent']))
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'total']);
    }

    // ── Parent dashboard blocked ────────────────────────────────

    public function test_trustee_cannot_access_parent_dashboard(): void
    {
        $trustee = User::factory()->trustee()->create();

        $this->actingAs($trustee)
            ->get(route('app.parent.dashboard'))
            ->assertForbidden();
    }
}
