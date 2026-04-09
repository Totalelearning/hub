<?php

namespace App\Support;

class AdminUserBulkPresets
{
    public static function definitions(): array
    {
        return [
            'Verification Follow-up' => [
                'attention_status' => 'needs_attention',
                'verification_status' => 'unverified',
                'bulk_action' => 'resend_verification',
            ],
            'Mark Verified Queue' => [
                'verification_status' => 'unverified',
                'bulk_action' => 'mark_verified',
            ],
            'Suspend Inactive 30+' => [
                'attention_status' => 'needs_attention',
                'inactivity_status' => 'inactive_30',
                'bulk_action' => 'suspend',
            ],
            'Restore Suspended' => [
                'account_status' => 'suspended',
                'bulk_action' => 'restore',
            ],
        ];
    }

    public static function descriptions(): array
    {
        return [
            'Verification Follow-up' => 'Targets unverified users already in Needs Attention for verification reminders.',
            'Mark Verified Queue' => 'Targets unverified users so admins can resolve verification directly from the queue.',
            'Suspend Inactive 30+' => 'Targets Needs Attention users inactive for 30+ days for access suspension.',
            'Restore Suspended' => 'Targets currently suspended accounts for access restoration.',
        ];
    }

    public static function auditActions(): array
    {
        return [
            'Verification Follow-up' => 'user_verification_link_sent',
            'Mark Verified Queue' => 'user_verification_marked',
            'Suspend Inactive 30+' => 'user_suspended',
            'Restore Suspended' => 'user_restored',
        ];
    }

    public static function styles(?string $preset): array
    {
        if ($preset === 'Verification Follow-up' || $preset === 'Mark Verified Queue') {
            return [
                'container' => 'border-indigo-200 bg-indigo-50 text-indigo-800',
                'badge' => 'border-indigo-200 bg-white text-indigo-700',
            ];
        }

        if ($preset === 'Suspend Inactive 30+') {
            return [
                'container' => 'border-amber-200 bg-amber-50 text-amber-900',
                'badge' => 'border-amber-200 bg-white text-amber-800',
            ];
        }

        if ($preset === 'Restore Suspended') {
            return [
                'container' => 'border-sky-200 bg-sky-50 text-sky-900',
                'badge' => 'border-sky-200 bg-white text-sky-800',
            ];
        }

        return [
            'container' => 'border-green-200 bg-green-50 text-green-800',
            'badge' => 'border-green-200 bg-white text-green-700',
        ];
    }
}
