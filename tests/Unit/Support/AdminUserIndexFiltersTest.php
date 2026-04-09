<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Support\AdminUserIndexFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_normalizes_and_compacts_filters(): void
    {
        $normalized = AdminUserIndexFilters::normalize([
            'q' => '  admin@example.com  ',
            'role' => 'admin',
            'sort' => 'email',
            'sort_dir' => 'asc',
            'limit' => 50,
        ]);

        $this->assertSame('admin@example.com', $normalized['q']);
        $this->assertSame('admin', $normalized['role']);
        $this->assertSame('email', $normalized['sort']);
        $this->assertSame('asc', $normalized['sort_dir']);
        $this->assertSame(50, $normalized['limit']);

        $this->assertSame([
            'q' => 'admin@example.com',
            'role' => 'admin',
            'sort' => 'email',
            'sort_dir' => 'asc',
            'limit' => 50,
        ], AdminUserIndexFilters::compact($normalized));
    }

    public function test_it_applies_attention_filters_and_sorting(): void
    {
        User::factory()->create([
            'name' => 'Dormant User',
            'email_verified_at' => null,
            'last_login_at' => null,
            'suspended_at' => null,
            'is_admin' => false,
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'suspended_at' => null,
        ]);

        $filters = AdminUserIndexFilters::normalize([
            'attention_status' => 'needs_attention',
            'sort' => 'name',
            'sort_dir' => 'asc',
        ]);

        $results = AdminUserIndexFilters::applySorting(
            AdminUserIndexFilters::filteredQuery($filters),
            $filters
        )->pluck('name')->all();

        $this->assertContains('Dormant User', $results);
        $this->assertNotContains('Admin User', $results);
    }
}
