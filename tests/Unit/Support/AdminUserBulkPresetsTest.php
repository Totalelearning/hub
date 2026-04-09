<?php

namespace Tests\Unit\Support;

use App\Support\AdminUserBulkPresets;
use Tests\TestCase;

class AdminUserBulkPresetsTest extends TestCase
{
    public function test_it_returns_bulk_preset_metadata(): void
    {
        $definitions = AdminUserBulkPresets::definitions();
        $descriptions = AdminUserBulkPresets::descriptions();
        $auditActions = AdminUserBulkPresets::auditActions();

        $this->assertArrayHasKey('Verification Follow-up', $definitions);
        $this->assertSame('resend_verification', $definitions['Verification Follow-up']['bulk_action']);
        $this->assertSame(
            'Targets unverified users already in Needs Attention for verification reminders.',
            $descriptions['Verification Follow-up']
        );
        $this->assertSame('user_suspended', $auditActions['Suspend Inactive 30+']);
    }

    public function test_it_returns_bulk_preset_styles(): void
    {
        $this->assertSame(
            [
                'container' => 'border-amber-200 bg-amber-50 text-amber-900',
                'badge' => 'border-amber-200 bg-white text-amber-800',
            ],
            AdminUserBulkPresets::styles('Suspend Inactive 30+')
        );

        $this->assertSame(
            [
                'container' => 'border-green-200 bg-green-50 text-green-800',
                'badge' => 'border-green-200 bg-white text-green-700',
            ],
            AdminUserBulkPresets::styles(null)
        );
    }
}
