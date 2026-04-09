@extends('layouts.learninguiux')

@section('title', 'Assignment Audit - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .assignment-audit-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .assignment-audit-band {
            border-radius: 1.5rem;
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }
    </style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            <div class="mb-4 admin-feed-hero">
                <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between lg:p-5">
                    <div class="admin-feed-hero-copy">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Audit Evidence</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">{{ __('Assignment Audit') }}</h1>
                        <p class="mt-3 text-base text-slate-600">Filtered audit trail for rule changes, waivers, reminders, scoring, ranking, SCORM resets, and user operations.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('app.admin.assignments.audit.export', ['action' => $currentAction, 'q' => $search ?: null, 'from' => $dateRange['from']?->format('Y-m-d'), 'to' => $dateRange['to']?->format('Y-m-d'), 'actor' => $scope['actor'], 'target' => $scope['target'], 'module' => $scope['module']]) }}" class="admin-feed-action inline-flex items-center bg-sky-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-white shadow-sm transition hover:bg-sky-700">
                            Export CSV
                        </a>
                        <a href="{{ route('app.admin.assignments') }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            <div class="admin-workflow-grid">
                <div class="assignment-audit-card p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.26em] text-cyan-700">Operational Shortcuts</div>
                            <h2 class="mt-2 text-xl font-semibold text-slate-900">Use the audit trail as an action hub</h2>
                            <p class="mt-2 max-w-2xl text-sm text-slate-600">Jump straight into reminders, rule changes, waivers, or exports instead of scanning the whole event set first.</p>
                        </div>
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-cyan-700">Audit workflow</span>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <a href="{{ route('app.admin.assignments.audit', ['action' => 'reminder_marked_sent']) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Reminder Activity</div>
                            <p class="mt-2 text-sm text-slate-600">Open the audit view focused on individual reminder sends.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.audit', ['action' => 'assignment_rule_saved']) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Rule Changes</div>
                            <p class="mt-2 text-sm text-slate-600">Inspect when assignment targeting rules were added or adjusted.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.audit', ['action' => 'assignment_waiver_saved']) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Waiver Changes</div>
                            <p class="mt-2 text-sm text-slate-600">Jump to the waiver trail for compliance exceptions and reversals.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.audit.export', ['action' => $currentAction, 'q' => $search ?: null, 'from' => $dateRange['from']?->format('Y-m-d'), 'to' => $dateRange['to']?->format('Y-m-d'), 'actor' => $scope['actor'], 'target' => $scope['target'], 'module' => $scope['module']]) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Export Audit Proof</div>
                            <p class="mt-2 text-sm text-slate-600">Download the current filtered audit evidence for external review.</p>
                        </a>
                    </div>
                </div>

                <div class="assignment-audit-card p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Workflow Focus</div>
                    <div class="mt-3 space-y-3">
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">1. Narrow the event set</div>
                            <p class="mt-1 text-sm text-slate-600">Use action, dates, and scope to isolate the operational change you’re investigating.</p>
                        </div>
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">2. Read the summary counts</div>
                            <p class="mt-1 text-sm text-slate-600">Use the metric cards to confirm the scale of rules, waivers, reminders, or admin actions.</p>
                        </div>
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">3. Export when the trail is final</div>
                            <p class="mt-1 text-sm text-slate-600">Take the filtered audit slice out once the page already shows the exact evidence you need.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="assignment-audit-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4">
                            <i class="bi bi-activity fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['total'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Events</div>
                        <div class="mt-2 text-sm text-slate-600">Audit rows in the current scope</div>
                        <div class="mt-auto pt-3 small text-secondary">Volume</div>
                    </div>
                </div>
                <div class="assignment-audit-card admin-feed-kpi p-5 transition hover:border-cyan-300 hover:bg-cyan-50/70">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#0369a1;background:linear-gradient(135deg, rgba(224, 242, 254, 0.96), rgba(232, 246, 255, 0.96));">
                            <i class="bi bi-sliders fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['rule_saved'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Rules Added</div>
                        <div class="mt-2 text-sm text-slate-600">Assignment logic updates recorded</div>
                        <div class="mt-auto pt-3 small text-secondary">Rules</div>
                    </div>
                </div>
                <div class="assignment-audit-card admin-feed-kpi p-5 transition hover:border-amber-300 hover:bg-amber-50/70">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#b45309;background:linear-gradient(135deg, rgba(254, 243, 199, 0.98), rgba(255, 237, 213, 0.98));">
                            <i class="bi bi-slash-circle fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['rule_removed'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Rules Removed</div>
                        <div class="mt-2 text-sm text-slate-600">Historical targeting logic retired</div>
                        <div class="mt-auto pt-3 small text-secondary">Rules</div>
                    </div>
                </div>
                <div class="assignment-audit-card admin-feed-kpi p-5 transition hover:border-violet-300 hover:bg-violet-50/70">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#7c3aed;background:linear-gradient(135deg, rgba(237, 233, 254, 0.96), rgba(243, 232, 255, 0.96));">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['waiver_saved'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Waivers Added</div>
                        <div class="mt-2 text-sm text-slate-600">Compliance exceptions recorded</div>
                        <div class="mt-auto pt-3 small text-secondary">Waivers</div>
                    </div>
                </div>
                <div class="assignment-audit-card admin-feed-kpi p-5 transition hover:border-indigo-300 hover:bg-indigo-50/70">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#4338ca;background:linear-gradient(135deg, rgba(224, 231, 255, 0.96), rgba(238, 242, 255, 0.96));">
                            <i class="bi bi-bell fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['reminder_marked_sent'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Reminders Sent</div>
                        <span class="sr-only">Reminder Batches</span>
                        <div class="mt-2 text-sm text-slate-600">Outbound reminder events in scope</div>
                        <div class="mt-auto pt-3 small text-secondary">Reminders</div>
                    </div>
                </div>
            </div>

            <div class="assignment-audit-card overflow-hidden">
                <div class="assignment-audit-band border-b border-gray-200 px-5 py-4">
                    <div class="flex flex-col gap-4">
                        <form method="GET" action="{{ route('app.admin.assignments.audit') }}" class="grid gap-3 lg:grid-cols-[1fr_160px_160px_auto]">
                            <input type="hidden" name="action" value="{{ $currentAction }}">
                            <input type="hidden" name="actor" value="{{ $scope['actor'] }}">
                            <input type="hidden" name="target" value="{{ $scope['target'] }}">
                            <input type="hidden" name="module" value="{{ $scope['module'] }}">
                            <input
                                type="text"
                                name="q"
                                value="{{ $search }}"
                                class="w-full rounded border-gray-300 text-sm"
                                placeholder="Search actor, learner, module, role, area, or reason"
                            >
                            <input
                                type="date"
                                name="from"
                                value="{{ $dateRange['from']?->format('Y-m-d') }}"
                                class="w-full rounded border-gray-300 text-sm"
                            >
                            <input
                                type="date"
                                name="to"
                                value="{{ $dateRange['to']?->format('Y-m-d') }}"
                                class="w-full rounded border-gray-300 text-sm"
                            >
                            <button type="submit" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Search
                            </button>
                        </form>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($datePresets as $preset)
                                <a
                                    href="{{ route('app.admin.assignments.audit', ['action' => $currentAction, 'q' => $search ?: null, 'from' => $preset['from'], 'to' => $preset['to'], 'actor' => $scope['actor'], 'target' => $scope['target'], 'module' => $scope['module']]) }}"
                                    class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                                >
                                    {{ $preset['label'] }}
                                </a>
                            @endforeach
                            <a
                                href="{{ route('app.admin.assignments.audit', ['action' => $currentAction, 'q' => $search ?: null, 'actor' => $scope['actor'], 'target' => $scope['target'], 'module' => $scope['module']]) }}"
                                class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                            >
                                Clear Dates
                            </a>
                        </div>
                        @if ($scope['actor'] || $scope['target'] || $scope['module'])
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($scope['actor'])
                                    <span class="rounded bg-slate-100 px-3 py-2 text-sm text-slate-700">Actor #{{ $scope['actor'] }}</span>
                                @endif
                                @if ($scope['target'])
                                    <span class="rounded bg-slate-100 px-3 py-2 text-sm text-slate-700">Learner #{{ $scope['target'] }}</span>
                                @endif
                                @if ($scope['module'])
                                    <span class="rounded bg-slate-100 px-3 py-2 text-sm text-slate-700">Module #{{ $scope['module'] }}</span>
                                @endif
                                <a
                                    href="{{ route('app.admin.assignments.audit', ['action' => $currentAction, 'q' => $search ?: null, 'from' => $dateRange['from']?->format('Y-m-d'), 'to' => $dateRange['to']?->format('Y-m-d')]) }}"
                                    class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                                >
                                    Clear Scope
                                </a>
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center gap-2">
                            @php
                                $actions = [
                                    'all' => 'All',
                                    'rule_saved' => 'Rule Saved',
                                    'rule_removed' => 'Rule Removed',
                                    'waiver_saved' => 'Waiver Saved',
                                    'waiver_removed' => 'Waiver Removed',
                                    'acknowledgement_recorded' => 'Acknowledged',
                                    'reminder_marked_sent' => 'Reminder Sent',
                                    'reminder_batch_run' => 'Reminder Batch',
                                    'feed_scoring_settings_updated' => 'Scoring Updated',
                                    'feed_scoring_settings_reset' => 'Scoring Reset',
                                    'feed_scoring_preset_applied' => 'Scoring Preset Applied',
                                    'ranking_settings_updated' => 'Ranking Settings Updated',
                                    'ranking_settings_reset' => 'Ranking Settings Reset',
                                    'ranking_provider_tested' => 'Ranking Provider Tested',
                                    'ranking_probe_history_exported' => 'Probe CSV Exported',
                                    'ranking_severity_transitions_exported' => 'Severity CSV Exported',
                                    'ranking_incident_bundle_exported' => 'Incident Bundle Exported',
                                    'scorm_demo_reset' => 'SCORM Demo Reset',
                                    'ranking_severity_changed' => 'Ranking Severity Changed',
                                    'reminder_settings_updated' => 'Reminder Settings Updated',
                                    'reminder_settings_reset' => 'Reminder Settings Reset',
                                    'user_created' => 'User Created',
                                    'user_updated' => 'User Updated',
                                    'user_password_reset' => 'User Password Reset',
                                    'user_password_reset_link_sent' => 'User Password Reset Link Sent',
                                    'user_suspended' => 'User Suspended',
                                    'user_restored' => 'User Restored',
                                    'user_verification_link_sent' => 'User Verification Link Sent',
                                    'user_verification_marked' => 'User Verification Marked',
                                    'user_verification_cleared' => 'User Verification Cleared',
                                ];
                            @endphp
                            @foreach ($actions as $value => $label)
                                <a
                                    href="{{ route('app.admin.assignments.audit', ['action' => $value, 'q' => $search ?: null, 'from' => $dateRange['from']?->format('Y-m-d'), 'to' => $dateRange['to']?->format('Y-m-d'), 'actor' => $scope['actor'], 'target' => $scope['target'], 'module' => $scope['module']]) }}"
                                    class="rounded border px-3 py-2 text-sm font-medium {{ $currentAction === $value ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}"
                                >
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">When</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Actor</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Action</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Target</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Module</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($auditEvents as $event)
                                <tr>
                                    <td class="px-5 py-3 text-gray-600">{{ $event->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($event->actor)
                                            <a href="{{ route('app.admin.assignments.audit', ['action' => $currentAction, 'q' => $search ?: null, 'from' => $dateRange['from']?->format('Y-m-d'), 'to' => $dateRange['to']?->format('Y-m-d'), 'actor' => $event->actor->id, 'target' => $scope['target'], 'module' => $scope['module']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $event->actor->name }}
                                            </a>
                                        @else
                                            system
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                            {{ str_replace('_', ' ', $event->action) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($event->targetUser)
                                            <a href="{{ route('app.admin.assignments.audit', ['action' => $currentAction, 'q' => $search ?: null, 'from' => $dateRange['from']?->format('Y-m-d'), 'to' => $dateRange['to']?->format('Y-m-d'), 'actor' => $scope['actor'], 'target' => $event->target_user_id, 'module' => $scope['module']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $event->targetUser->email }}
                                            </a>
                                        @elseif (($event->meta['role'] ?? null) && ($event->meta['compliance_area'] ?? null))
                                            {{ $event->meta['role'] }} / {{ $event->meta['compliance_area'] }}
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($event->module)
                                            <a href="{{ route('app.admin.assignments.audit', ['action' => $currentAction, 'q' => $search ?: null, 'from' => $dateRange['from']?->format('Y-m-d'), 'to' => $dateRange['to']?->format('Y-m-d'), 'actor' => $scope['actor'], 'target' => $scope['target'], 'module' => $event->module->id]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $event->module->title }}
                                            </a>
                                        @else
                                            {{ $event->meta['module_title'] ?? 'n/a' }}
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if (($event->meta['reason'] ?? null) !== null)
                                            {{ $event->meta['reason'] }}
                                        @elseif ($event->action === 'reminder_batch_run')
                                            synced {{ $event->meta['synced_total'] ?? 0 }}, sent {{ $event->meta['sent_total'] ?? 0 }}, remaining total {{ $event->meta['remaining_pending'] ?? 0 }}
                                            @if (isset($event->meta['remaining_pending_filtered']))
                                                ; remaining filtered {{ $event->meta['remaining_pending_filtered'] }}
                                            @endif
                                            @if (! empty($event->meta['mode']))
                                                ; mode {{ $event->meta['mode'] }}
                                            @endif
                                            @php($types = collect($event->meta['types'] ?? [])->filter()->implode('|'))
                                            @if ($types !== '')
                                                ; types {{ $types }}
                                            @endif
                                        @elseif (($event->meta['reminder_type'] ?? null) !== null)
                                            {{ $event->meta['reminder_type'] }}
                                        @elseif (($event->meta['preset_label'] ?? null) !== null)
                                            preset {{ $event->meta['preset_label'] }}
                                            @if (!empty($event->meta['changed_keys']) && is_array($event->meta['changed_keys']))
                                                ; changed {{ implode(', ', $event->meta['changed_keys']) }}
                                            @endif
                                        @elseif (($event->meta['provider'] ?? null) !== null && array_key_exists('success', $event->meta))
                                            provider {{ $event->meta['provider'] }}; {{ $event->meta['success'] ? 'success' : 'failure' }}
                                            @if (!empty($event->meta['message']))
                                                ; {{ $event->meta['message'] }}
                                            @endif
                                        @elseif ($event->action === 'ranking_incident_bundle_exported')
                                            bundle {{ $event->meta['bundle_id'] ?? 'n/a' }}
                                            ; provider {{ $event->meta['provider'] ?? 'all' }}
                                            ; trigger {{ $event->meta['trigger'] ?? 'all' }}
                                            ; probes {{ $event->meta['probe_count'] ?? 0 }}
                                            ; transitions {{ $event->meta['severity_transition_count'] ?? 0 }}
                                        @elseif ($event->action === 'scorm_demo_reset')
                                            {{ $event->meta['message'] ?? 'SCORM demo data reset completed.' }}
                                            @if (!empty($event->meta['status']))
                                                ; status {{ $event->meta['status'] }}
                                            @endif
                                        @elseif ($event->action === 'ranking_probe_history_exported')
                                            provider {{ $event->meta['provider'] ?? 'all' }}
                                            ; probes {{ $event->meta['probe_count'] ?? 0 }}
                                            @if (!empty($event->meta['export_from']))
                                                ; from {{ $event->meta['export_from'] }}
                                            @endif
                                            @if (!empty($event->meta['export_to']))
                                                ; to {{ $event->meta['export_to'] }}
                                            @endif
                                        @elseif ($event->action === 'ranking_severity_transitions_exported')
                                            trigger {{ $event->meta['trigger'] ?? 'all' }}
                                            ; transitions {{ $event->meta['severity_transition_count'] ?? 0 }}
                                            @if (!empty($event->meta['export_from']))
                                                ; from {{ $event->meta['export_from'] }}
                                            @endif
                                            @if (!empty($event->meta['export_to']))
                                                ; to {{ $event->meta['export_to'] }}
                                            @endif
                                        @elseif (!empty($event->meta['changed_keys']) && is_array($event->meta['changed_keys']))
                                            changed {{ implode(', ', $event->meta['changed_keys']) }}
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-4 text-gray-500">No audit events match the current filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
        </div>
    </main>
</div>
@endsection
