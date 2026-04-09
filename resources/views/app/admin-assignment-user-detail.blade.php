@extends('layouts.learninguiux')

@section('title', 'Learner Assignment Detail - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
    @push('styles')
        <style>
            .learner-detail-card {
                border-radius: 1.25rem;
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
            }

            .learner-detail-band {
                border-radius: 1.5rem;
                background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
            }

            .learner-detail-action {
                border-radius: 0.8rem;
                border: 1px solid rgba(226, 232, 240, 0.95);
                background: rgba(248, 250, 252, 0.98);
            }
        </style>
    @endpush
            <div class="mb-4 admin-feed-hero">
                <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between lg:p-5">
                    <div class="admin-feed-hero-copy">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Learner Evidence</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">{{ __('Learner Assignment Detail') }}</h1>
                        <p class="mt-3 max-w-3xl text-base text-slate-600">{{ $learner->name }} | {{ $learner->email }} | role: {{ strtolower((string) $learner->preference?->role) ?: 'unassigned' }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('app.admin.assignments.user.events', ['user' => $learner->id]) }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">
                            Learning Events
                        </a>
                        <a href="{{ route('app.admin.assignments.user.export', ['user' => $learner->id]) }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">
                            Export CSV
                        </a>
                        <a href="{{ route('app.admin.assignments.role', ['role' => strtolower((string) $learner->preference?->role)]) }}" class="admin-feed-action inline-flex items-center bg-sky-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-white shadow-sm transition hover:bg-sky-700">
                            Back to Role Detail
                        </a>
                    </div>
                </div>
            </div>

    <div class="py-2">
        <div class="w-full space-y-6">
            @if (session('status'))
                <div class="learner-detail-card border-green-200 bg-green-50/90 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="admin-workflow-grid">
                <div class="learner-detail-card p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.26em] text-cyan-700">Quick Actions</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">Move from learner evidence into action</h3>
                            <p class="mt-2 max-w-2xl text-sm text-slate-600">Use this page to confirm learner proof, open the runtime trail, export evidence, or move back into role-level follow-up.</p>
                        </div>
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-cyan-700">Learner drilldown</span>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <a href="{{ route('app.admin.assignments.user.events', ['user' => $learner->id, 'event_type' => 'scorm_runtime_committed', 'entity_type' => 'learning_module']) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Open Runtime Events</div>
                            <p class="mt-2 text-sm text-slate-600">Jump straight to SCORM runtime commits and lesson-location proof.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.user.events', ['user' => $learner->id, 'event_type' => 'reinforcement_failed']) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Review Reinforcement Retry</div>
                            <p class="mt-2 text-sm text-slate-600">Focus on failed follow-up checks and remediation assigned to this learner.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.user.export', ['user' => $learner->id]) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Export Learner Proof</div>
                            <p class="mt-2 text-sm text-slate-600">Download the learner’s current assignment, SCORM, and reinforcement evidence.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.role', ['role' => strtolower((string) $learner->preference?->role)]) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Back to Role Detail</div>
                            <p class="mt-2 text-sm text-slate-600">Return to the wider learner group to compare this evidence in context.</p>
                        </a>
                    </div>
                </div>

                <div class="learner-detail-card p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Workflow Focus</div>
                    <div class="mt-3 space-y-3">
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">1. Validate the latest proof</div>
                            <p class="mt-1 text-sm text-slate-600">Read the SCORM and reinforcement strips before scanning the full assignment table.</p>
                        </div>
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">2. Check what is still at risk</div>
                            <p class="mt-1 text-sm text-slate-600">Use overdue, due soon, acknowledgement, and remediation counts to spot the next intervention.</p>
                        </div>
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">3. Move into the event trail</div>
                            <p class="mt-1 text-sm text-slate-600">Open runtime or reinforcement events when you need the full step-by-step record.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="learner-detail-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4">
                            <i class="bi bi-journal-check fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['required_total'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Required Modules</div>
                        <div class="mt-2 text-sm text-slate-600">Assigned items in this learner queue</div>
                        <div class="mt-auto pt-3 small text-secondary">Coverage</div>
                    </div>
                </div>
                <div class="learner-detail-card admin-feed-kpi p-5 transition hover:border-rose-300 hover:bg-rose-50/80">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#dc2626;background:linear-gradient(135deg, rgba(254, 226, 226, 0.98), rgba(255, 237, 213, 0.98));">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['overdue_total'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Overdue</div>
                        <div class="mt-2 text-sm text-slate-600">Items needing immediate follow-up</div>
                        <div class="mt-auto pt-3 small text-secondary">Risk</div>
                    </div>
                </div>
                <div class="learner-detail-card admin-feed-kpi p-5 transition hover:border-amber-300 hover:bg-amber-50/80">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#b45309;background:linear-gradient(135deg, rgba(254, 243, 199, 0.98), rgba(255, 237, 213, 0.98));">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['due_soon_total'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Due Soon</div>
                        <div class="mt-2 text-sm text-slate-600">Upcoming due-window pressure</div>
                        <div class="mt-auto pt-3 small text-secondary">Timing</div>
                    </div>
                </div>
                <div class="learner-detail-card admin-feed-kpi p-5 transition hover:border-emerald-300 hover:bg-emerald-50/80">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#0f766e;background:linear-gradient(135deg, rgba(213, 250, 229, 0.96), rgba(220, 252, 231, 0.96));">
                            <i class="bi bi-check2-circle fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['completed_total'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Completed</div>
                        <div class="mt-2 text-sm text-slate-600">Learner proof already recorded</div>
                        <div class="mt-auto pt-3 small text-secondary">Progress</div>
                    </div>
                </div>
                <div class="learner-detail-card p-5">
                    <div class="text-sm text-gray-500">Waived</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $summary['waived_total'] }}</div>
                </div>
                <div class="learner-detail-card p-5">
                    <div class="text-sm text-gray-500">Acknowledged</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $summary['acknowledged_total'] }}</div>
                </div>
                <div class="learner-detail-card p-5">
                    <div class="text-sm text-gray-500">Pending Acknowledgement</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $summary['pending_acknowledgement_total'] }}</div>
                </div>
                <div class="learner-detail-card border-sky-200 bg-sky-50/90 p-5">
                    <div class="text-sm text-sky-700">SCORM Completed</div>
                    <div class="mt-2 text-3xl font-semibold text-sky-900">{{ $summary['scorm_completed_total'] }}</div>
                </div>
                <div class="learner-detail-card border-violet-200 bg-violet-50/90 p-5">
                    <div class="text-sm text-violet-700">Reinforcement Completed</div>
                    <div class="mt-2 text-3xl font-semibold text-violet-900">{{ $summary['reinforcement_completed_total'] ?? 0 }}</div>
                </div>
                <div class="learner-detail-card border-rose-200 bg-rose-50/90 p-5">
                    <div class="text-sm text-rose-700">Reinforcement Failed</div>
                    <div class="mt-2 text-3xl font-semibold text-rose-900">{{ $summary['reinforcement_failed_total'] ?? 0 }}</div>
                </div>
                <div class="learner-detail-card border-amber-200 bg-amber-50/90 p-5">
                    <div class="text-sm text-amber-700">Remediation Assigned</div>
                    <div class="mt-2 text-3xl font-semibold text-amber-900">{{ $summary['reinforcement_remediation_assigned_total'] ?? 0 }}</div>
                </div>
            </div>

            @if (($recentScormAttempts ?? collect())->isNotEmpty())
                @php($latestLearnerProof = $recentScormAttempts->first())
                <div class="learner-detail-card overflow-hidden border-emerald-200 bg-emerald-50/90">
                    <div class="learner-detail-band border-b border-emerald-200 px-5 py-4">
                        <h3 class="text-lg font-semibold text-emerald-900">Latest SCORM Proof For This Learner</h3>
                        <p class="mt-1 text-sm text-emerald-700">Use this strip when you need the fastest learner-level proof of completion evidence.</p>
                    </div>
                    <div class="grid gap-4 px-5 py-4 md:grid-cols-[1.2fr_1fr_1fr_1fr]">
                        <div class="rounded-[1.4rem] border border-emerald-200 bg-white p-4 shadow-sm">
                            <div class="text-sm font-semibold text-emerald-900">{{ $latestLearnerProof['module_title'] }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $latestLearnerProof['when']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                        </div>
                        <div class="rounded-[1.4rem] border border-emerald-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Status</div>
                            <div class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', (string) ($latestLearnerProof['status'] ?? 'n/a'))) }}</div>
                        </div>
                        <div class="rounded-[1.4rem] border border-emerald-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Progress</div>
                            <div class="mt-2 text-sm font-semibold text-slate-900">{{ $latestLearnerProof['percent_complete'] !== null ? $latestLearnerProof['percent_complete'].'%' : 'n/a' }}</div>
                            <div class="mt-1 text-xs text-slate-500">score {{ $latestLearnerProof['score_raw'] ?? 'n/a' }} | {{ $latestLearnerProof['session_label'] }}</div>
                        </div>
                        <div class="rounded-[1.4rem] border border-emerald-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Lesson Location</div>
                            <div class="mt-2 text-xs font-semibold text-slate-900 break-all">{{ $latestLearnerProof['lesson_location'] ?? 'n/a' }}</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('app.admin.assignments.user.events', ['user' => $learner->id, 'event_type' => 'scorm_runtime_committed', 'entity_type' => 'learning_module']) }}" class="rounded-full border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">Runtime events</a>
                                <a href="{{ route('app.admin.modules.edit', ['module' => $latestLearnerProof['module_id']]) }}" class="rounded-full border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Module admin</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($latestReinforcementProof || $latestReinforcementFailure)
                <div class="grid gap-4 lg:grid-cols-2">
                    @if ($latestReinforcementProof)
                        <div class="learner-detail-card overflow-hidden border-violet-200 bg-violet-50/90">
                            <div class="learner-detail-band border-b border-violet-200 px-5 py-4">
                                <h3 class="text-lg font-semibold text-violet-900">Latest Reinforcement Proof</h3>
                                <p class="mt-1 text-sm text-violet-700">Latest successful follow-up evidence recorded after course completion.</p>
                            </div>
                            <div class="grid gap-4 px-5 py-4 md:grid-cols-[1.2fr_1fr_1fr]">
                                <div class="rounded-[1.4rem] border border-violet-200 bg-white p-4 shadow-sm">
                                    <div class="text-sm font-semibold text-slate-900">{{ $latestReinforcementProof['module_title'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $latestReinforcementProof['completed_at']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                                </div>
                                <div class="rounded-[1.4rem] border border-violet-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-700">Touchpoint</div>
                                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $latestReinforcementProof['interval_days'] }}-day {{ $latestReinforcementProof['proof_type'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">due {{ $latestReinforcementProof['due_on']?->format('Y-m-d') ?? 'n/a' }}</div>
                                </div>
                                <div class="rounded-[1.4rem] border border-violet-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-700">Proof summary</div>
                                    <div class="mt-2 text-xs text-slate-700">{{ $latestReinforcementProof['proof_summary'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($latestReinforcementFailure)
                        <div class="learner-detail-card overflow-hidden border-rose-200 bg-rose-50/90">
                            <div class="learner-detail-band border-b border-rose-200 px-5 py-4">
                                <h3 class="text-lg font-semibold text-rose-900">Latest Reinforcement Retry</h3>
                                <p class="mt-1 text-sm text-rose-700">Most recent failed reinforcement check and the follow-up learning assigned afterwards.</p>
                            </div>
                            <div class="grid gap-4 px-5 py-4 md:grid-cols-[1.2fr_1fr_1fr]">
                                <div class="rounded-[1.4rem] border border-rose-200 bg-white p-4 shadow-sm">
                                    <div class="text-sm font-semibold text-slate-900">{{ $latestReinforcementFailure['module_title'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $latestReinforcementFailure['updated_at']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                                </div>
                                <div class="rounded-[1.4rem] border border-rose-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Incorrect answers</div>
                                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $latestReinforcementFailure['incorrect_count'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $latestReinforcementFailure['remediation_count'] }} remediation module{{ $latestReinforcementFailure['remediation_count'] === 1 ? '' : 's' }} assigned</div>
                                </div>
                                <div class="rounded-[1.4rem] border border-rose-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Remediation</div>
                                    <div class="mt-2 text-xs text-slate-700">
                                        @if(($latestReinforcementFailure['remediation_titles'] ?? collect())->isNotEmpty())
                                            {{ $latestReinforcementFailure['remediation_titles']->join(', ') }}
                                        @else
                                            No remediation module titles recorded.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="learner-detail-card overflow-hidden">
                <div class="learner-detail-band border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Assigned Required Queue</h3>
                    <p class="mt-1 text-sm text-gray-500">Required modules currently assigned to this learner, including renewal and targeting context.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Module</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Area</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Urgency</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Due</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Progress</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Acknowledgement</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Assignment Reason</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($moduleRows as $row)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $row['title'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['compliance_area'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['urgency'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['renewal_due_at']?->format('M d, Y') ?? 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        <div class="font-medium text-gray-900">
                                            {{ $row['progress_status'] }}
                                            <span class="text-gray-500">| {{ $row['percent_complete'] }}%</span>
                                        </div>
                                        @if ($row['completed_at'])
                                            <span class="block text-xs text-gray-400">completed {{ $row['completed_at']->format('M d, Y') }}</span>
                                        @endif
                                        @if ($row['source_type'] === 'scorm' && $row['latest_scorm_runtime_at'])
                                            <span class="mt-1 block text-xs text-sky-700">
                                                runtime {{ $row['latest_scorm_runtime_at']->format('M d, Y H:i') }}
                                                @if ($row['latest_scorm_status'])
                                                    | {{ $row['latest_scorm_status'] }}
                                                @endif
                                            </span>
                                            <span class="block text-xs text-gray-400">
                                                score={{ $row['latest_scorm_score_raw'] ?? 'n/a' }} | session={{ $row['latest_scorm_session_label'] }}
                                            </span>
                                            @if ($row['latest_scorm_lesson_location'])
                                                <span class="block text-xs text-gray-400 break-all">location={{ $row['latest_scorm_lesson_location'] }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($row['requires_acknowledgement'])
                                            {{ ($row['acknowledgement']['is_acknowledged'] ?? false) ? 'acknowledged' : 'pending' }}
                                            @if ($row['acknowledged_at'])
                                                <span class="block text-xs text-gray-400">{{ $row['acknowledged_at']->format('M d, Y H:i') }}</span>
                                            @endif
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if (!empty($row['role_targeting']['target_roles']))
                                            role={{ implode(', ', $row['role_targeting']['target_roles']) }}
                                        @else
                                            role=all
                                        @endif
                                        @if (!empty($row['compliance_targeting']['compliance_area']))
                                            <span class="block text-xs text-gray-400">compliance={{ $row['compliance_targeting']['compliance_area'] }}</span>
                                        @endif
                                        @if ($row['waiver'])
                                            <span class="block text-xs text-red-500">waiver={{ $row['waiver']['reason'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($row['waiver'])
                                            <form method="POST" action="{{ route('app.admin.assignments.waivers.destroy', ['user' => $learner->id, 'module' => $row['module_id']]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded border border-gray-300 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                                    Restore
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('app.admin.assignments.waivers.store', ['user' => $learner->id, 'module' => $row['module_id']]) }}" class="space-y-2">
                                                @csrf
                                                <input type="text" name="reason" class="w-full rounded border-gray-300 text-xs" placeholder="Waiver reason">
                                                <button type="submit" class="rounded border border-red-200 px-3 py-2 text-xs text-red-700 hover:bg-red-50">
                                                    Waive
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-4 text-gray-500">No required modules are currently assigned to this learner.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="learner-detail-card overflow-hidden">
                <div class="learner-detail-band border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Assignment Activity</h3>
                    <p class="mt-1 text-sm text-gray-500">Recent admin and learner actions affecting this learner's assignment state.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">When</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Actor</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Action</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Module</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($recentAuditEvents as $event)
                                <tr>
                                    <td class="px-5 py-3 text-gray-600">{{ $event->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($event->actor)
                                            <a href="{{ route('app.admin.assignments.audit', ['actor' => $event->actor->id, 'target' => $learner->id]) }}" class="text-indigo-600 hover:text-indigo-500">
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
                                        @if ($event->module)
                                            <a href="{{ route('app.admin.assignments.audit', ['target' => $learner->id, 'module' => $event->module->id]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $event->module->title }}
                                            </a>
                                        @else
                                            {{ $event->meta['module_title'] ?? 'n/a' }}
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($event->meta['reason'] ?? null)
                                            {{ $event->meta['reason'] }}
                                        @elseif (($event->meta['role'] ?? null) && ($event->meta['compliance_area'] ?? null))
                                            {{ $event->meta['role'] }} / {{ $event->meta['compliance_area'] }}
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-4 text-gray-500">No assignment activity has been recorded for this learner.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="learner-detail-card overflow-hidden">
                <div class="learner-detail-band border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent SCORM Attempts</h3>
                    <p class="mt-1 text-sm text-gray-500">Latest SCORM runtime commits recorded for this learner.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">When</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Module</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Score</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Session</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Progress</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Location</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($recentScormAttempts as $attempt)
                                <tr>
                                    <td class="px-5 py-3 text-gray-600">{{ $attempt['when']?->format('Y-m-d H:i') }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $attempt['module_title'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        <span class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-800">
                                            {{ $attempt['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ $attempt['score_raw'] ?? 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $attempt['session_label'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $attempt['percent_complete'] !== null ? $attempt['percent_complete'].'%' : 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600 break-all">{{ $attempt['lesson_location'] ?? 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('app.admin.assignments.user.events', ['user' => $learner->id, 'event_type' => 'scorm_runtime_committed', 'entity_type' => 'learning_module']) }}" class="rounded-full border border-sky-300 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-800 hover:bg-sky-100">
                                                Runtime events
                                            </a>
                                            <a href="{{ route('app.admin.modules.edit', ['module' => $attempt['module_id']]) }}" class="rounded-full border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                Module admin
                                            </a>
                                        </div>
                                        <div class="mt-2 text-xs text-slate-500">{{ $attempt['metadata_summary'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-4 text-gray-500">No SCORM attempts recorded for this learner.</td>
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
