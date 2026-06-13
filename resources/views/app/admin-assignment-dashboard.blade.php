@extends('layouts.learninguiux')

@section('title', 'Admin Assignments - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .assignment-report-action {
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(248, 250, 252, 0.98);
        }

        .assignment-report-summary-card {
            border-radius: 1.9rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 20px 56px rgba(43, 82, 138, 0.1);
        }

        .assignment-dashboard-legacy-preview {
            display: none;
        }

.assignment-feed-summary {
            overflow: hidden;
            border-radius: 28px;
            border: 0;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .assignment-feed-summary::after {
            display: none;
        }

        .assignment-feed-kpi {
            overflow: hidden;
            border-radius: 28px;
            border: 0;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
            min-height: 10.5rem;
        }

        .assignment-feed-kpi::after {
            display: none;
        }

        .assignment-feed-kpi-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4rem;
            height: 4rem;
            border-radius: 1.25rem;
            color: #2454db;
            background: linear-gradient(135deg, rgba(213, 226, 255, 0.96), rgba(227, 236, 255, 0.96));
            box-shadow: none;
        }
    </style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            {{-- Hero --}}
            <div class="mb-4 admin-feed-hero">
                <div class="row align-items-center g-0 p-4 p-lg-5">
                    <div class="col-12 col-lg-8 admin-feed-hero-copy">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">{{ auth()->user()->systemRoleLabel() }} Dashboard</div>
                        <h1 class="fs-3 fw-semibold mb-2">Welcome, {{ auth()->user()->name }}</h1>
                        <p class="text-secondary mb-0">Learner progress, assignment status, compliance tracking, and reinforcement results at a glance.</p>
                    </div>
                </div>
            </div>

            {{-- Analytics snapshot --}}
            @php
                $totalAssigned = $summary['analytics_total_assigned'] ?? 0;
                $totalCompleted = $summary['analytics_total_completed'] ?? 0;
                $totalInProgress = $summary['analytics_total_in_progress'] ?? 0;
                $totalNotStarted = $summary['analytics_total_not_started'] ?? 0;
                $completionRate = $summary['analytics_completion_rate'] ?? 0;
                $learnersCount = $summary['analytics_total_learners'] ?? 0;
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card assignment-feed-kpi p-4 h-100">
                        <div class="small text-secondary">Total Enrolments</div>
                        <div class="fs-3 fw-bold mt-1">{{ number_format($totalAssigned) }}</div>
                        <div class="small text-secondary mt-1">{{ $learnersCount }} learners</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card assignment-feed-kpi p-4 h-100">
                        <div class="small text-secondary">Completion Rate</div>
                        <div class="fs-3 fw-bold mt-1 {{ $completionRate >= 75 ? 'text-success' : ($completionRate >= 50 ? 'text-warning' : 'text-danger') }}">{{ $completionRate }}%</div>
                        <div class="mt-2" style="height:8px;border-radius:4px;background:rgba(226,232,240,0.6);">
                            <div style="height:100%;border-radius:4px;width:{{ $completionRate }}%;" class="{{ $completionRate >= 75 ? 'bg-success' : ($completionRate >= 50 ? 'bg-warning' : 'bg-danger') }}"></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card assignment-feed-kpi p-4 h-100">
                        <div class="small text-secondary">Completed</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ number_format($totalCompleted) }}</div>
                        <div class="small text-secondary mt-1">{{ $totalInProgress }} in progress</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card assignment-feed-kpi p-4 h-100">
                        <div class="small text-secondary">Not Started</div>
                        <div class="fs-3 fw-bold mt-1 {{ $totalNotStarted > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($totalNotStarted) }}</div>
                        <div class="small text-secondary mt-1">{{ $summary['overdue_assignments_count'] ?? 0 }} overdue</div>
                    </div>
                </div>
            </div>

            <div class="assignment-dashboard-legacy-preview mb-6">
                <div class="assignment-report-summary-card p-5 backdrop-blur">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.26em] text-cyan-700">Report Library</div>
                            <h2 class="mt-2 text-xl font-semibold text-slate-900">Move through assignment reporting faster</h2>
                            <p class="mt-2 max-w-2xl text-sm text-slate-600">Start with the overview report, then drill into learner evidence, reminder activity, and audit-ready proof.</p>
                        </div>
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-cyan-700">Workflow first</span>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <a href="{{ route('app.admin.assignments.reminders.run') }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Reminder Activity Report</div>
                            <p class="mt-2 text-sm text-slate-600">Send the current reminder queue and keep due items moving.</p>
                        </a>
                        <a href="{{ route('app.admin.compliance', ['status' => 'overdue']) }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Learner Progress Report</div>
                            <p class="mt-2 text-sm text-slate-600">Open the compliance report already filtered to the biggest risk group.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.audit') }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Audit Trail Report</div>
                            <p class="mt-2 text-sm text-slate-600">Inspect assignment rules, waivers, reminder activity, and admin actions.</p>
                        </a>
                        <a href="{{ route('app.admin.assignments.export') }}" class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            <div class="text-sm font-semibold text-slate-900">Assignment Results Export</div>
                            <p class="mt-2 text-sm text-slate-600">Download the current assignment and SCORM proof summary as CSV.</p>
                        </a>
                    </div>
                </div>

                <div class="assignment-report-summary-card p-5 backdrop-blur">
                    <div class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">How To Read</div>
                    <div class="mt-3 space-y-3">
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">1. Start with the overview</div>
                            <p class="mt-1 text-sm text-slate-600">Start with overdue learners, pending reminders, and reinforcement failures.</p>
                        </div>
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">2. Validate the evidence</div>
                            <p class="mt-1 text-sm text-slate-600">Use SCORM proof, learner detail, and compliance drilldowns to confirm the state.</p>
                        </div>
                        <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-sm font-semibold text-slate-900">3. Export or intervene</div>
                            <p class="mt-1 text-sm text-slate-600">Run reminders, export proof, or move into the audit trail for follow-up.</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $assignmentStatusChart = [
                    ['label' => 'Completed', 'value' => $summary['course_completion_completed_count'] ?? 0, 'color' => '#10b981', 'meta' => 'Learners who finished assigned learning'],
                    ['label' => 'In progress', 'value' => $summary['course_completion_in_progress_count'] ?? 0, 'color' => '#3b82f6', 'meta' => 'Started but not yet completed'],
                    ['label' => 'Not started', 'value' => $summary['course_completion_not_started_count'] ?? 0, 'color' => '#f59e0b', 'meta' => 'Assigned with no learner progress yet'],
                ];
                $assignmentUrgencyChart = [
                    ['label' => 'Overdue', 'value' => $summary['overdue_assignments_count'] ?? 0, 'color' => '#ef4444', 'meta' => 'Require immediate intervention'],
                    ['label' => 'Due soon', 'value' => $summary['due_soon_assignments_count'] ?? 0, 'color' => '#f59e0b', 'meta' => 'Approaching the due window'],
                    ['label' => 'Pending reminders', 'value' => $summary['pending_reminders_count'] ?? 0, 'color' => '#8b5cf6', 'meta' => 'Queued nudges not yet sent'],
                ];
                $assignmentReinforcementChart = [
                    ['label' => 'Completed', 'value' => $summary['reinforcement_completed_count'] ?? 0, 'color' => '#10b981', 'meta' => 'Proof recorded after completion'],
                    ['label' => 'Due', 'value' => $summary['reinforcement_due_count'] ?? 0, 'color' => '#6366f1', 'meta' => 'Follow-up checks due now'],
                    ['label' => 'Failed', 'value' => $summary['reinforcement_failed_count'] ?? 0, 'color' => '#ef4444', 'meta' => 'Learners needing retry or remediation'],
                ];
                $assignmentRoleBars = collect($overdueByRole ?? [])->take(5)->map(function ($row) {
                    return [
                        'label' => ucwords(str_replace('-', ' ', $row['role'] ?? 'unassigned')),
                        'value' => (int) ($row['overdue_count'] ?? 0),
                        'color' => '#0ea5e9',
                        'meta' => ($row['users_count'] ?? 0).' learners tracked',
                    ];
                })->all();
            @endphp

            {{-- Charts row --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    @include('app.partials.admin-report-donut', [
                        'eyebrow' => 'Learner Progress',
                        'title' => 'Assignment status breakdown',
                        'subtitle' => 'See how assigned learning is split between completed, active, and untouched work.',
                        'items' => $assignmentStatusChart,
                        'centerValue' => $summary['analytics_completion_rate'] ?? 0,
                        'centerLabel' => 'Completion %',
                        'badge' => ($summary['analytics_total_assigned'] ?? 0).' assigned',
                        'cardClass' => 'assignment-report-summary-card assignment-feed-summary p-5 h-100',
                    ])
                </div>
                @if (auth()->user()->hasUnrestrictedView() && !empty($summary['location_completion_rows']))
                {{-- Trustee: Location completion ranking --}}
                <div class="col-12 col-lg-6">
                    <div class="assignment-report-summary-card assignment-feed-summary p-5 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">Location Performance</div>
                                <h6 class="fw-semibold mt-1 mb-0">Completion by school</h6>
                                <p class="small text-secondary mb-0">Lowest completion rates shown first.</p>
                            </div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary">{{ count($summary['location_completion_rows']) }} locations</span>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            @foreach ($summary['location_completion_rows'] as $locRow)
                                <div>
                                    <div class="d-flex justify-content-between align-items-baseline mb-1">
                                        <span class="small fw-semibold">{{ $locRow['location'] }}</span>
                                        <span class="small fw-bold {{ $locRow['completion_rate'] >= 90 ? 'text-success' : ($locRow['completion_rate'] >= 75 ? 'text-primary' : ($locRow['completion_rate'] >= 50 ? 'text-warning' : 'text-danger')) }}">{{ $locRow['completion_rate'] }}%</span>
                                    </div>
                                    <div class="progress" style="height:8px;border-radius:4px;">
                                        <div class="progress-bar {{ $locRow['completion_rate'] >= 90 ? 'bg-success' : ($locRow['completion_rate'] >= 75 ? 'bg-primary' : ($locRow['completion_rate'] >= 50 ? 'bg-warning' : 'bg-danger')) }}" style="width:{{ $locRow['completion_rate'] }}%;border-radius:4px;"></div>
                                    </div>
                                    <div class="d-flex gap-3 mt-1">
                                        <span class="text-secondary" style="font-size:.68rem;">{{ $locRow['completed'] }} of {{ $locRow['enrolled'] }} completed</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                {{-- SLT / Manager: Priority Queue only --}}
                <div class="col-12 col-lg-6">
                    @include('app.partials.admin-report-bars', [
                        'eyebrow' => 'Priority Queue',
                        'title' => 'Urgency report',
                        'subtitle' => 'Use this view to decide where to intervene first.',
                        'items' => $assignmentUrgencyChart,
                        'badge' => 'Action first',
                        'cardClass' => 'assignment-report-summary-card assignment-feed-summary p-5 h-100',
                    ])
                </div>
                @endif
            </div>

            <div class="sr-only">Learning Events</div>

            <div class="assignment-dashboard-legacy-preview bg-gradient-to-b from-slate-100 via-slate-50 to-white py-2">
        <div class="w-full" data-ranking-health-page="dashboard" data-ranking-health-endpoint="{{ url('/api/admin/ai/ranking-health?limit=5') }}">
            <div class="space-y-6 rounded-[2rem] border border-white/70 bg-white/70 p-4 shadow-[0_24px_70px_-40px_rgba(15,23,42,0.45)] backdrop-blur sm:p-6">
            <style>
                .admin-preview-tile {
                    display: block;
                    padding: 1.5rem;
                    border-radius: 1.75rem;
                    border: 1px solid rgba(203, 213, 225, 0.72);
                    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 251, 255, 0.96) 100%);
                    box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.28);
                    text-decoration: none;
                    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
                }

                .admin-preview-tile:hover {
                    transform: translateY(-2px);
                    border-color: rgba(96, 165, 250, 0.55);
                    box-shadow: 0 22px 55px -34px rgba(37, 99, 235, 0.3);
                }

                .admin-preview-feature {
                    border: 1px solid rgba(203, 213, 225, 0.7);
                    border-radius: 1.75rem;
                    overflow: hidden;
                    background: rgba(255, 255, 255, 0.96);
                    box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.28);
                }

                .admin-preview-feature-band {
                    min-height: 7rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 1.25rem 1.5rem;
                }

                .admin-preview-ring {
                    --progress: 0;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 5rem;
                    height: 5rem;
                    border-radius: 9999px;
                    background:
                        radial-gradient(closest-side, rgba(255, 255, 255, 1) 72%, transparent 73% 100%),
                        conic-gradient(#1565c0 calc(var(--progress) * 1%), #dbeafe 0);
                }
            </style>

            @if (session('status'))
                <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $operationalPreviewRate = max(0, min(100, (int) $summary['course_completion_rate']));
                $completionPreviewRate = max(0, min(100, (int) $summary['course_completion_average_percent']));
                $scormPreviewRate = max(0, min(100, (int) $summary['scorm_average_score']));
            @endphp

            <div class="grid gap-4 xl:grid-cols-3">
                <a href="#operational-snapshot-section" class="admin-preview-feature">
                    <div class="admin-preview-feature-band bg-gradient-to-br from-sky-100 via-cyan-50 to-white">
                        <div class="admin-preview-ring shrink-0" style="--progress: {{ $operationalPreviewRate }};">
                            <span class="text-lg font-semibold text-sky-700">{{ $operationalPreviewRate }}%</span>
                        </div>
                        <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700 shadow-sm">Operations</div>
                    </div>
                    <div class="space-y-2 p-5">
                        <div class="min-w-0">
                            <h3 class="font-display text-2xl font-semibold text-slate-800">Operational Snapshot</h3>
                            <p class="mt-1 text-base text-slate-600">Assignments, reminders, and compliance</p>
                        </div>
                        <div class="pt-4 space-y-1 text-base text-slate-500">
                        <p>You have {{ $summary['pending_reminders_count'] }} pending reminder{{ $summary['pending_reminders_count'] === 1 ? '' : 's' }} across {{ $summary['reminder_batches_24h_count'] }} recent batch{{ $summary['reminder_batches_24h_count'] === 1 ? '' : 'es' }}</p>
                        <p>You are tracking {{ $summary['required_modules_count'] }} required module{{ $summary['required_modules_count'] === 1 ? '' : 's' }} across {{ $summary['roles_count'] }} active role{{ $summary['roles_count'] === 1 ? '' : 's' }}</p>
                        </div>
                    </div>
                </a>

                <a href="#admin-assignments-section" class="admin-preview-feature">
                    <div class="admin-preview-feature-band bg-gradient-to-br from-indigo-100 via-slate-50 to-white">
                        <div class="admin-preview-ring shrink-0" style="--progress: {{ $completionPreviewRate }};">
                            <span class="text-lg font-semibold text-sky-700">{{ $completionPreviewRate }}%</span>
                        </div>
                        <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700 shadow-sm">Assignments</div>
                    </div>
                    <div class="space-y-2 p-5">
                        <div class="min-w-0">
                            <h3 class="font-display text-2xl font-semibold text-slate-800">Admin Assignments</h3>
                            <p class="mt-1 text-base text-slate-600">Assignment activity and follow-up</p>
                        </div>
                        <div class="pt-4 space-y-1 text-base text-slate-500">
                        <p>You have completed {{ $summary['course_completion_completed_count'] }}/{{ $summary['course_completion_total_assignments'] }} tracked assignment{{ $summary['course_completion_total_assignments'] === 1 ? '' : 's' }}</p>
                        <p>You have {{ $summary['audit_events_count'] }} audit event{{ $summary['audit_events_count'] === 1 ? '' : 's' }} and {{ $summary['course_completion_in_progress_count'] }} learner{{ $summary['course_completion_in_progress_count'] === 1 ? '' : 's' }} currently in progress</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('app.admin.scorm.index') }}" class="admin-preview-feature">
                    <div class="admin-preview-feature-band bg-gradient-to-br from-cyan-100 via-sky-50 to-white">
                        <div class="admin-preview-ring shrink-0" style="--progress: {{ $scormPreviewRate }};">
                            <span class="text-lg font-semibold text-sky-700">{{ $scormPreviewRate }}%</span>
                        </div>
                        <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700 shadow-sm">SCORM</div>
                    </div>
                    <div class="space-y-2 p-5">
                        <div class="min-w-0">
                            <h3 class="font-display text-2xl font-semibold text-slate-800">SCORM Overview</h3>
                            <p class="mt-1 text-base text-slate-600">Prototype performance and activity</p>
                        </div>
                        <div class="pt-4 space-y-1 text-base text-slate-500">
                        <p>You have completed {{ $summary['scorm_completed_count'] }}/{{ max(1, $summary['scorm_required_assignments_count']) }} SCORM assignment{{ $summary['scorm_required_assignments_count'] === 1 ? '' : 's' }}</p>
                        <p>You have logged {{ $summary['scorm_total_session_label'] }} with {{ $summary['scorm_in_progress_count'] }} active in-progress learner{{ $summary['scorm_in_progress_count'] === 1 ? '' : 's' }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <div id="operational-snapshot-section" class="grid gap-4 xl:grid-cols-[1.6fr_1fr]">
                <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-gradient-to-br from-white via-sky-50 to-slate-100 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                    <div class="px-6 py-6">
                        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                            <div class="max-w-2xl">
                                <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-sky-800">
                                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                    Operational Snapshot
                                </div>
                                <h3 class="mt-4 text-4xl font-semibold leading-tight text-slate-950">Assignment, reminder, and compliance health in one admin view.</h3>
                                <p class="mt-3 max-w-xl text-base text-slate-600">Start here for the executive readout, then jump into operations, intelligence, or governance without scanning a long report page.</p>
                            </div>
                            <div class="grid w-full gap-3 sm:grid-cols-2 xl:max-w-md">
                                <div class="rounded-2xl border border-sky-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Required Modules</div>
                                    <div class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['required_modules_count'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $summary['compliance_areas_count'] }} compliance area{{ $summary['compliance_areas_count'] === 1 ? '' : 's' }}</div>
                                </div>
                                <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Pending Reminders</div>
                                    <div class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['pending_reminders_count'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $summary['reminder_batches_24h_count'] }} batch{{ $summary['reminder_batches_24h_count'] === 1 ? '' : 'es' }} in 24h</div>
                                </div>
                                <div class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Rules Covered</div>
                                    <div class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['rules_count'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $summary['roles_count'] }} active role{{ $summary['roles_count'] === 1 ? '' : 's' }}</div>
                                </div>
                                <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">AI Severity</div>
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ ($summary['ranking_severity']['level'] ?? 'healthy') === 'critical' ? 'bg-red-100 text-red-800' : (($summary['ranking_severity']['level'] ?? 'healthy') === 'degraded' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                            {{ $summary['ranking_severity']['label'] ?? 'Healthy' }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-xs text-slate-500">{{ $summary['ranking_severity']['reason'] ?? 'Provider is ready and recent probe health is good.' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-3 md:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Waivers</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['waivers_count'] }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Acknowledgements</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['acknowledgements_count'] }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Inactive Nudges</div>
                                <div class="mt-2 text-2xl font-semibold text-amber-700">{{ $summary['inactive_nudge_count'] }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Audit Events</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['audit_events_count'] }}</div>
                            </div>
                            <div id="reinforcement-proof-section" class="rounded-2xl border border-indigo-200 bg-white px-4 py-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700">Reinforcement Proof</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['reinforcement_completed_count'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $summary['reinforcement_due_count'] }} due follow-up{{ $summary['reinforcement_due_count'] === 1 ? '' : 's' }}</div>
                            </div>
                            <div class="rounded-2xl border border-rose-200 bg-white px-4 py-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Reinforcement Failed</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['reinforcement_failed_count'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $summary['reinforcement_remediation_assigned_count'] }} remediation assignment{{ $summary['reinforcement_remediation_assigned_count'] === 1 ? '' : 's' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-gradient-to-br from-white via-cyan-50 to-sky-100 shadow-[0_20px_60px_-38px_rgba(14,116,144,0.45)]">
                    <div class="px-6 py-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="max-w-2xl">
                                <div class="inline-flex items-center gap-2 rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-cyan-800">
                                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                    SCORM Demo
                                </div>
                                <h3 class="mt-4 text-3xl font-semibold leading-tight text-slate-950">Client walkthrough course data</h3>
                                <p class="mt-3 text-sm text-slate-600">Keep the prototype visible on the landing page with a cleaner operational readout for assignments, progress, score, and time-on-course.</p>
                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-cyan-800 ring-1 ring-cyan-100">SCORM Demo Course</span>
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">{{ $summary['scorm_required_modules_count'] }} module{{ $summary['scorm_required_modules_count'] === 1 ? '' : 's' }}</span>
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">{{ $summary['scorm_completed_count'] }} completed</span>
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">{{ $summary['scorm_in_progress_count'] }} in progress</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-start gap-3 lg:items-end">
                                <a href="{{ route('app.admin.compliance', ['source_type' => 'scorm']) }}" class="inline-flex items-center rounded-full border border-cyan-200 bg-white px-4 py-2 text-sm font-semibold text-cyan-800 shadow-sm transition hover:bg-cyan-50">
                                    View Report
                                </a>
                                <div class="rounded-2xl border border-white/80 bg-white/90 px-4 py-3 text-right shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Logged Time</div>
                                    <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $summary['scorm_total_session_label'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">avg score {{ $summary['scorm_average_score'] }} | logged time {{ $summary['scorm_total_session_label'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border border-cyan-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Assignments</div>
                                <div class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['scorm_required_assignments_count'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $summary['scorm_required_modules_count'] }} module{{ $summary['scorm_required_modules_count'] === 1 ? '' : 's' }} in this walkthrough</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Average Score</div>
                                <div class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['scorm_average_score'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">Stable benchmark for the demo path</div>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Completed</div>
                                <div class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['scorm_completed_count'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">Learners who finished the SCORM flow</div>
                                <div class="mt-2 text-xs text-emerald-700">{{ $summary['scorm_completed_last_24h_count'] }} completion{{ $summary['scorm_completed_last_24h_count'] === 1 ? '' : 's' }} in the last 24h</div>
                            </div>
                            <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">In Progress</div>
                                <div class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['scorm_in_progress_count'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">Active sessions still moving through content</div>
                            </div>
                        </div>
                        @if(($recentScormCompletions ?? collect())->isNotEmpty())
                            <div class="mt-6 overflow-hidden rounded-2xl border border-cyan-200 bg-white/90 shadow-sm">
                                <div class="border-b border-cyan-100 bg-cyan-50/80 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Recent completions</div>
                                            <div class="mt-1 text-sm text-slate-600">Latest learners who completed the SCORM walkthrough.</div>
                                        </div>
                                        @if ($summary['scorm_latest_completion_at'])
                                            <div class="text-xs text-slate-500">latest {{ $summary['scorm_latest_completion_at']->format('Y-m-d H:i') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    @foreach ($recentScormCompletions as $completion)
                                        <div class="grid gap-3 px-4 py-3 md:grid-cols-[1.1fr_1.1fr_0.7fr_0.9fr_1fr_1.1fr] md:items-center">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">{{ $completion['learner_name'] }}</div>
                                                @if (!empty($completion['learner_email']))
                                                    <div class="text-xs text-slate-400">{{ $completion['learner_email'] }}</div>
                                                @endif
                                                <div class="text-xs text-slate-500">{{ $completion['completed_at']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                                            </div>
                                            <div class="text-sm text-slate-700">{{ $completion['module_title'] }}</div>
                                            <div class="text-sm font-semibold text-slate-900">{{ $completion['percent_complete'] }}%</div>
                                            <div class="text-sm text-slate-700">
                                                <div>{{ ucfirst(str_replace('_', ' ', (string) ($completion['scorm_status'] ?? 'completed'))) }}</div>
                                                <div class="text-xs text-slate-500">score {{ $completion['score_raw'] ?? 'n/a' }}</div>
                                            </div>
                                            <div class="text-sm text-slate-700">
                                                <div>{{ $completion['session_label'] }}</div>
                                                @if (!empty($completion['lesson_location']))
                                                    <div class="text-xs text-slate-500 break-all">{{ $completion['lesson_location'] }}</div>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('app.admin.assignments.user', ['user' => $completion['user_id']]) }}" class="rounded-full border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Learner</a>
                                                <a href="{{ route('app.admin.modules.edit', ['module' => $completion['module_id']]) }}" class="rounded-full border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Module</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(($recentReinforcementProof ?? collect())->isNotEmpty())
                            <div class="mt-6 overflow-hidden rounded-2xl border border-indigo-200 bg-white/90 shadow-sm">
                                <div class="border-b border-indigo-100 bg-indigo-50/80 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-700">Recent reinforcement proof</div>
                                            <div class="mt-1 text-sm text-slate-600">Latest ongoing reinforcement touchpoints completed after course completion.</div>
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $summary['reinforcement_completed_count'] }} recorded</div>
                                    </div>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    @foreach ($recentReinforcementProof as $proof)
                                        <div class="grid gap-3 px-4 py-3 md:grid-cols-[1.1fr_1.1fr_0.7fr_1.6fr] md:items-center">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">{{ $proof['learner_name'] }}</div>
                                                @if (!empty($proof['learner_email']))
                                                    <div class="text-xs text-slate-400">{{ $proof['learner_email'] }}</div>
                                                @endif
                                                <div class="text-xs text-slate-500">{{ $proof['completed_at']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                                            </div>
                                            <div class="text-sm text-slate-700">{{ $proof['module_title'] }}</div>
                                            <div class="text-sm font-semibold text-indigo-700">{{ $proof['interval_days'] }}-day</div>
                                            <div class="text-sm text-slate-700">{{ $proof['proof_summary'] ?: 'Reinforcement proof recorded.' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(($recentReinforcementFailures ?? collect())->isNotEmpty())
                            <div class="mt-6 overflow-hidden rounded-2xl border border-rose-200 bg-white/90 shadow-sm">
                                <div class="border-b border-rose-100 bg-rose-50/80 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Recent reinforcement failures</div>
                                            <div class="mt-1 text-sm text-slate-600">Latest failed checks and the extra learning assigned afterwards.</div>
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $summary['reinforcement_failed_count'] }} recorded</div>
                                    </div>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    @foreach ($recentReinforcementFailures as $failure)
                                        <div class="grid gap-3 px-4 py-3 md:grid-cols-[1.1fr_1.1fr_0.9fr_1.4fr] md:items-center">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">{{ $failure['learner_name'] }}</div>
                                                @if (!empty($failure['learner_email']))
                                                    <div class="text-xs text-slate-400">{{ $failure['learner_email'] }}</div>
                                                @endif
                                                <div class="text-xs text-slate-500">{{ $failure['updated_at']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                                            </div>
                                            <div class="text-sm text-slate-700">{{ $failure['module_title'] }}</div>
                                            <div class="text-sm text-slate-700">{{ $failure['proof_summary'] }}</div>
                                            <div class="text-sm text-slate-700">
                                                <div>{{ $failure['remediation_count'] }} assigned</div>
                                                @if(($failure['remediation_titles'] ?? collect())->isNotEmpty())
                                                    <div class="text-xs text-slate-500">{{ $failure['remediation_titles']->join(', ') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="overflow-hidden rounded-[1.75rem] border border-orange-200 bg-white px-5 py-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-orange-700">Not Started Nudges</div>
                            <div class="mt-3 text-3xl font-semibold text-slate-950">{{ $summary['not_started_nudge_count'] }}</div>
                            <div class="mt-1 text-sm text-slate-500">Learners needing a first-touch prompt.</div>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm ring-1 ring-orange-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 2a4 4 0 0 0-4 4v1.382c0 .537-.214 1.053-.595 1.434L4.31 9.91A1.5 1.5 0 0 0 5.37 12.5h9.26a1.5 1.5 0 0 0 1.06-2.56l-1.095-1.094A2.03 2.03 0 0 1 14 7.382V6a4 4 0 0 0-4-4Z" />
                                <path d="M8 14a2 2 0 1 0 4 0H8Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 border-t border-orange-100 pt-4">
                        <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">Reminder funnel</span>
                    </div>
                </div>
                <div class="overflow-hidden rounded-[1.75rem] border border-sky-200 bg-white px-5 py-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-700">Reminder Batches (24h)</div>
                            <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['reminder_batches_24h_count'] }}</div>
                            <div class="mt-1 text-sm text-slate-500">Recent reminder automation activity.</div>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 shadow-sm ring-1 ring-sky-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v3.69c0 .266.141.512.37.647l2.25 1.31a.75.75 0 0 0 .76-1.294l-1.88-1.095V6.75Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 border-t border-sky-100 pt-4">
                        <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">Automation pulse</span>
                    </div>
                </div>
                <div class="overflow-hidden rounded-[1.75rem] border border-emerald-200 bg-white px-5 py-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Roles Covered</div>
                            <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['roles_count'] }}</div>
                            <div class="mt-1 text-sm text-slate-500">Active learning coverage by role.</div>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-sm ring-1 ring-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M3 5.75A2.75 2.75 0 0 1 5.75 3h8.5A2.75 2.75 0 0 1 17 5.75v8.5A2.75 2.75 0 0 1 14.25 17h-8.5A2.75 2.75 0 0 1 3 14.25v-8.5Zm4.5 1.5a.75.75 0 0 0 0 1.5h5a.75.75 0 0 0 0-1.5h-5Zm0 4a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 border-t border-emerald-100 pt-4">
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Coverage map</span>
                    </div>
                </div>
                <div class="overflow-hidden rounded-[1.75rem] border border-sky-200 bg-white px-5 py-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-700">Compliance Areas</div>
                            <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['compliance_areas_count'] }}</div>
                            <div class="mt-1 text-sm text-slate-500">Distinct policy domains in play.</div>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 shadow-sm ring-1 ring-sky-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.75 2.5a.75.75 0 0 0-1.5 0v1.02a6.5 6.5 0 1 0 6.23 6.23h1.02a.75.75 0 0 0 0-1.5h-1.72a.75.75 0 0 0-.75.75 5 5 0 1 1-5.03-5V5a.75.75 0 0 0 1.5 0V2.5Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 border-t border-sky-100 pt-4">
                        <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">Policy scope</span>
                    </div>
                </div>
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white px-5 py-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-600">Rules</div>
                            <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['rules_count'] }}</div>
                            <div class="mt-1 text-sm text-slate-500">Assignment rules currently configured.</div>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-50 text-slate-700 shadow-sm ring-1 ring-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.25 3A2.25 2.25 0 0 0 2 5.25v9.5A2.25 2.25 0 0 0 4.25 17h11.5A2.25 2.25 0 0 0 18 14.75v-9.5A2.25 2.25 0 0 0 15.75 3H4.25Zm2.22 3.47a.75.75 0 0 1 1.06 0L10 8.94l2.47-2.47a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Rule inventory</span>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-[0_24px_55px_-35px_rgba(15,23,42,0.35)] backdrop-blur">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-sky-50 px-6 py-6">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Jump to Workspace</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">Dashboard tiles for the next admin action</h3>
                            <p class="mt-1 text-sm text-slate-500">Use these as the quick navigation layer so the page feels closer to the preview dashboard model.</p>
                        </div>
                        <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                            Tile-first navigation
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 bg-gradient-to-b from-slate-50/80 to-white p-4 md:grid-cols-2 xl:grid-cols-3">
                    <a href="{{ route('app.admin.compliance') }}" class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/95 px-6 py-6 shadow-sm transition hover:-translate-y-1 hover:border-sky-200 hover:bg-sky-50/80 hover:shadow-xl">
                        <div class="absolute -right-2 -top-2 h-28 w-28 rounded-full bg-sky-100/80 blur-2xl transition group-hover:bg-sky-200/90"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Compliance</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Open Compliance Report</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['required_modules_count'] }} required module{{ $summary['required_modules_count'] === 1 ? '' : 's' }} across {{ $summary['compliance_areas_count'] }} area{{ $summary['compliance_areas_count'] === 1 ? '' : 's' }}.</div>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">{{ $summary['compliance_areas_count'] }} areas</span>
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 shadow-sm ring-1 ring-white/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.75 2.5a.75.75 0 0 0-1.5 0v1.02a6.5 6.5 0 1 0 6.23 6.23h1.02a.75.75 0 0 0 0-1.5h-1.72a.75.75 0 0 0-.75.75 5 5 0 1 1-5.03-5V5a.75.75 0 0 0 1.5 0V2.5Z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-sky-100 pt-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Coverage</span>
                            <span class="text-sm font-medium text-sky-700">View report</span>
                        </div>
                    </a>
                    <a href="{{ route('app.admin.compliance.learners') }}" class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/95 px-6 py-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:bg-emerald-50/80 hover:shadow-xl">
                        <div class="absolute -right-2 -top-2 h-28 w-28 rounded-full bg-emerald-100/80 blur-2xl transition group-hover:bg-emerald-200/90"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Learners</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Open Learner Matrix</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['course_completion_learners_count'] }} learner{{ $summary['course_completion_learners_count'] === 1 ? '' : 's' }} with assigned courses and tracked completion.</div>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $summary['course_completion_rate'] }}% done</span>
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 shadow-sm ring-1 ring-white/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.53-9.22a.75.75 0 0 0-1.06-1.06L9 11.19 7.53 9.72a.75.75 0 1 0-1.06 1.06l2 2a.75.75 0 0 0 1.06 0l4-4Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-emerald-100 pt-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Completion</span>
                            <span class="text-sm font-medium text-emerald-700">View learners</span>
                        </div>
                    </a>
                    <a href="{{ route('app.admin.users.index') }}" class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/95 px-6 py-6 shadow-sm transition hover:-translate-y-1 hover:border-slate-300 hover:bg-slate-50 hover:shadow-xl">
                        <div class="absolute -right-2 -top-2 h-28 w-28 rounded-full bg-slate-200/80 blur-2xl transition group-hover:bg-sky-200/70"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-700">Users</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Open User Management</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['roles_count'] }} role segment{{ $summary['roles_count'] === 1 ? '' : 's' }} with active compliance coverage and account operations.</div>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $summary['roles_count'] }} roles</span>
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 shadow-sm ring-1 ring-white/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 3a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM5.5 8a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5ZM14.5 8a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5ZM2 14.25A2.25 2.25 0 0 1 4.25 12h11.5A2.25 2.25 0 0 1 18 14.25V15a.75.75 0 0 1-.75.75H2.75A.75.75 0 0 1 2 15v-.75Z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-slate-200 pt-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Accounts</span>
                            <span class="text-sm font-medium text-slate-700">Manage users</span>
                        </div>
                    </a>
                    <a href="{{ route('app.admin.modules.index') }}" class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/95 px-6 py-6 shadow-sm transition hover:-translate-y-1 hover:border-amber-200 hover:bg-amber-50/80 hover:shadow-xl">
                        <div class="absolute -right-2 -top-2 h-28 w-28 rounded-full bg-amber-100/80 blur-2xl transition group-hover:bg-amber-200/90"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Modules</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Open Module Management</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['required_modules_count'] }} required module{{ $summary['required_modules_count'] === 1 ? '' : 's' }} and SCORM-ready admin flows from one workspace.</div>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ $summary['scorm_required_modules_count'] }} SCORM</span>
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-sm ring-1 ring-white/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M3 3.75A.75.75 0 0 1 3.75 3h12.5a.75.75 0 0 1 0 1.5H4.5v11h11.75a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 16.25V3.75Z" />
                                        <path d="M8 6.75A.75.75 0 0 1 8.75 6h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 8 6.75Zm0 3.5a.75.75 0 0 1 .75-.75h6.5a.75.75 0 0 1 0 1.5h-6.5a.75.75 0 0 1-.75-.75Zm0 3.5a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 0 1.5h-3.5a.75.75 0 0 1-.75-.75Z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-amber-100 pt-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Content</span>
                            <span class="text-sm font-medium text-amber-700">Open modules</span>
                        </div>
                    </a>
                    <a href="{{ route('app.admin.scorm.index') }}" class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/95 px-6 py-6 shadow-sm transition hover:-translate-y-1 hover:border-cyan-200 hover:bg-cyan-50/80 hover:shadow-xl">
                        <div class="absolute -right-2 -top-2 h-28 w-28 rounded-full bg-cyan-100/80 blur-2xl transition group-hover:bg-cyan-200/90"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">SCORM</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Open SCORM Overview</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['scorm_completed_count'] }} completed, {{ $summary['scorm_in_progress_count'] }} in progress, average score {{ $summary['scorm_average_score'] }}.</div>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">{{ $summary['scorm_total_session_label'] }}</span>
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700 shadow-sm ring-1 ring-white/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.75 2.5a.75.75 0 0 0-1.5 0v1.02a6.5 6.5 0 1 0 6.23 6.23h1.02a.75.75 0 0 0 0-1.5h-1.72a.75.75 0 0 0-.75.75 5 5 0 1 1-5.03-5V5a.75.75 0 0 0 1.5 0V2.5Z" />
                                        <path d="M12.5 2.75a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0V4.56l-3.97 3.97a.75.75 0 1 1-1.06-1.06l3.97-3.97h-1.19a.75.75 0 0 1-.75-.75Z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-cyan-100 pt-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Prototype</span>
                            <span class="text-sm font-medium text-cyan-700">Open SCORM hub</span>
                        </div>
                    </a>
                    <a href="{{ route('app.admin.assignments.audit') }}" class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/95 px-6 py-6 shadow-sm transition hover:-translate-y-1 hover:border-rose-200 hover:bg-rose-50/80 hover:shadow-xl">
                        <div class="absolute -right-2 -top-2 h-28 w-28 rounded-full bg-rose-100/80 blur-2xl transition group-hover:bg-rose-200/90"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Audit</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Open Assignment Audit</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['audit_events_count'] }} audit event{{ $summary['audit_events_count'] === 1 ? '' : 's' }} captured across reminders, assignments, ranking, and SCORM resets.</div>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">{{ $summary['audit_events_count'] }} events</span>
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700 shadow-sm ring-1 ring-white/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v3.69c0 .266.141.512.37.647l2.25 1.31a.75.75 0 1 0 .76-1.294l-1.88-1.095V6.75Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-rose-100 pt-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Evidence</span>
                            <span class="text-sm font-medium text-rose-700">Open audit</span>
                        </div>
                    </a>
                </div>
            </div>

            @php
                $dashboardCompletionPreviewCount = 6;
                $dashboardReminderPreviewCount = 6;
                $dashboardFocusPreviewCount = 6;
                $dashboardWaiverPreviewCount = 6;
                $dashboardAcknowledgementPreviewCount = 6;
                $dashboardAuditPreviewCount = 6;
                $dashboardRulesPreviewCount = 6;
                $dashboardOverduePreviewCount = 6;
                $dashboardRequiredAreasPreviewCount = 6;
            @endphp

            <?php if ($currentFocus === 'all'): ?>
                <div id="course-completion-section" class="grid gap-4 xl:grid-cols-[2fr_1fr]">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-6 text-white shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-200">Admin Dashboard</div>
                                <h3 class="mt-2 text-xl font-semibold text-white">Course Completion Statistics</h3>
                                <p class="mt-1 text-sm text-slate-300">Required-course completion across assigned learners, rendered with the same chart-driven pattern used on the preview dashboard.</p>
                            </div>
                            <a href="{{ route('app.admin.compliance.learners') }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur hover:bg-white/20">
                                Open Learner Matrix
                            </a>
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <div class="text-xs font-medium uppercase tracking-[0.2em] text-slate-300">Assigned Courses</div>
                                <div class="mt-2 text-3xl font-semibold text-white">{{ $summary['course_completion_total_assignments'] }}</div>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <div class="text-xs font-medium uppercase tracking-[0.2em] text-slate-300">Completion Rate</div>
                                <div class="mt-2 text-3xl font-semibold text-emerald-300">{{ $summary['course_completion_rate'] }}%</div>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <div class="text-xs font-medium uppercase tracking-[0.2em] text-slate-300">Average Progress</div>
                                <div class="mt-2 text-3xl font-semibold text-white">{{ $summary['course_completion_average_percent'] }}%</div>
                            </div>
                        </div>
                        <div class="mt-6 rounded-3xl border border-white/10 bg-slate-950/30 p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium text-white">Completion Trend Snapshot</div>
                                    <div class="text-xs text-slate-300">Completed, in progress, and not started required assignments.</div>
                                </div>
                                <div class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">
                                    Live Admin Data
                                </div>
                            </div>
                            <div class="h-72">
                                <canvas id="adminAssignmentCompletionChart" aria-label="Admin assignment completion chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Completion Mix</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $summary['course_completion_learners_count'] }} learner{{ $summary['course_completion_learners_count'] === 1 ? '' : 's' }} with assigned courses</div>
                            </div>
                            <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                                Required only
                            </div>
                        </div>
                        <div class="mt-6">
                            <div class="mx-auto h-64 w-64 max-w-full">
                                <canvas id="adminAssignmentCompletionDoughnut" aria-label="Admin assignment completion doughnut chart"></canvas>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-3 text-sm text-slate-700">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[#6faa00]"></span> Completed</span>
                                <span>{{ $summary['course_completion_completed_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[#ffc107]"></span> In Progress</span>
                                <span>{{ $summary['course_completion_in_progress_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[#becede]"></span> Not Started</span>
                                <span>{{ $summary['course_completion_not_started_count'] }}</span>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-center">
                                <div class="text-3xl font-semibold text-slate-900">{{ $summary['course_completion_rate'] }}%</div>
                                <div class="text-xs uppercase tracking-[0.2em] text-slate-500">completed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Completion Leaders</div>
                                <h3 class="mt-2 text-lg font-semibold text-gray-900">Learner Completion Breakdown</h3>
                                <p class="mt-1 text-sm text-gray-500">Per-learner required-course completion counts for admin follow-up.</p>
                                <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($courseCompletionUserRows->count(), $dashboardCompletionPreviewCount) }} of {{ $courseCompletionUserRows->count() }} learner row{{ $courseCompletionUserRows->count() === 1 ? '' : 's' }} in the dashboard preview.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                    Preview rows
                                </span>
                                <a href="{{ route('app.admin.compliance.learners') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    View Full Report
                                </a>
                            </div>
                        </div>
                    </div>
                    @if ($courseCompletionUserRows->isNotEmpty())
                        <div class="grid gap-4 border-b border-gray-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                            @foreach ($courseCompletionUserRows->take(3) as $row)
                                @php
                                    $leaderboardClasses = $row['completion_rate'] >= 80
                                        ? 'border-emerald-200 bg-emerald-50'
                                        : ($row['completion_rate'] >= 40 ? 'border-amber-200 bg-amber-50' : 'border-rose-200 bg-rose-50');
                                @endphp
                                <div class="rounded-2xl border p-4 shadow-sm {{ $leaderboardClasses }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Top Learner</div>
                                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $row['name'] }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $row['email'] }}</div>
                                        </div>
                                        <div class="rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ $row['completion_rate'] }}%
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                                        <div class="rounded-xl bg-white/80 px-2 py-2">
                                            <div class="font-semibold text-slate-900">{{ $row['assigned_count'] }}</div>
                                            <div class="mt-1 text-slate-500">assigned</div>
                                        </div>
                                        <div class="rounded-xl bg-white/80 px-2 py-2">
                                            <div class="font-semibold text-emerald-700">{{ $row['completed_count'] }}</div>
                                            <div class="mt-1 text-slate-500">completed</div>
                                        </div>
                                        <div class="rounded-xl bg-white/80 px-2 py-2">
                                            <div class="font-semibold text-amber-700">{{ $row['in_progress_count'] }}</div>
                                            <div class="mt-1 text-slate-500">in progress</div>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                                        <span>{{ $row['role'] ?: 'unassigned' }}</span>
                                        <span>{{ $row['last_activity_at']?->diffForHumans() ?? 'no activity' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="overflow-x-auto px-3 py-3">
                        <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Learner</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Assigned</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Completed</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">In Progress</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Not Started</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Completion</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courseCompletionUserRows->take($dashboardCompletionPreviewCount) as $row)
                                    <tr class="transition hover:-translate-y-0.5">
                                        <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 shadow-sm">
                                            <div class="font-medium text-gray-900">{{ $row['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $row['email'] }}</div>
                                        </td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-700 shadow-sm">{{ $row['role'] ?: 'unassigned' }}</td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-700 shadow-sm">{{ $row['assigned_count'] }}</td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-green-700 shadow-sm">{{ $row['completed_count'] }}</td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-amber-700 shadow-sm">{{ $row['in_progress_count'] }}</td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-700 shadow-sm">{{ $row['not_started_count'] }}</td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 shadow-sm">
                                            @php
                                                $completionRateClasses = $row['completion_rate'] >= 80
                                                    ? 'bg-emerald-100 text-emerald-800'
                                                    : ($row['completion_rate'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800');
                                            @endphp
                                            <div class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $completionRateClasses }}">{{ $row['completion_rate'] }}% completion</div>
                                            <div class="mt-2 text-xs text-gray-500">avg progress {{ $row['average_percent'] }}%</div>
                                        </td>
                                        <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-500 shadow-sm">
                                            <div>{{ $row['last_activity_at']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                                            @if ($row['last_activity_at'])
                                                <div class="mt-1 text-xs text-slate-400">{{ $row['last_activity_at']->diffForHumans() }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-5 py-6 text-center text-sm text-gray-500">No required-course assignments are currently active for tracked learners.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-sky-50 via-white to-emerald-50 px-5 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Admin Workspace</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">Dashboard Sections</h3>
                            <p class="mt-1 text-sm text-slate-600">Keep one reporting lane open at a time instead of scanning the full admin page top to bottom.</p>
                        </div>
                        <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                            One active workspace at a time
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 bg-gradient-to-b from-slate-50/80 to-white p-4 lg:grid-cols-3" data-admin-dashboard-tabs>
                    <button type="button" data-admin-dashboard-tab="operations" class="group relative overflow-hidden rounded-[1.75rem] border border-transparent bg-white px-6 py-6 text-left shadow-sm transition">
                        <div class="absolute right-0 top-0 h-24 w-24 rounded-full bg-sky-100/70 blur-2xl"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Operations</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Run the daily queue</div>
                                <div class="mt-1 text-sm text-slate-500">Reminders, focused assignments, and execution pressure.</div>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 shadow-sm ring-1 ring-white/80">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v3.69c0 .266.141.512.37.647l2.25 1.31a.75.75 0 1 0 .76-1.294l-1.88-1.095V6.75Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-sky-100 pt-4 text-sm">
                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">{{ $summary['pending_reminders_count'] }} pending</span>
                            <span class="font-medium text-slate-700">Execution lane</span>
                        </div>
                    </button>
                    <button type="button" data-admin-dashboard-tab="intelligence" class="group relative overflow-hidden rounded-[1.75rem] border border-transparent bg-white px-6 py-6 text-left shadow-sm transition">
                        <div class="absolute right-0 top-0 h-24 w-24 rounded-full bg-emerald-100/70 blur-2xl"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">AI Intelligence</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Track ranking health</div>
                                <div class="mt-1 text-sm text-slate-500">Provider status, probe history, exports, and tuning drift.</div>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 shadow-sm ring-1 ring-white/80">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M3 3.75A.75.75 0 0 1 3.75 3h12.5a.75.75 0 0 1 0 1.5H4.5v11h2.75a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 16.25V3.75Z" />
                                    <path d="M8 13.25a.75.75 0 0 1-.75-.75V10a.75.75 0 0 1 1.5 0v2.5a.75.75 0 0 1-.75.75Zm4 0a.75.75 0 0 1-.75-.75V7a.75.75 0 0 1 1.5 0v5.5a.75.75 0 0 1-.75.75Zm4 0a.75.75 0 0 1-.75-.75V5a.75.75 0 0 1 1.5 0v7.5a.75.75 0 0 1-.75.75Z" />
                                </svg>
                            </span>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-emerald-100 pt-4 text-sm">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $summary['ranking_provider'] }}</span>
                            <span class="font-medium text-slate-700">Provider health</span>
                        </div>
                    </button>
                    <button type="button" data-admin-dashboard-tab="governance" class="group relative overflow-hidden rounded-[1.75rem] border border-transparent bg-white px-6 py-6 text-left shadow-sm transition">
                        <div class="absolute right-0 top-0 h-24 w-24 rounded-full bg-rose-100/70 blur-2xl"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Governance</div>
                                <div class="mt-2 text-lg font-semibold text-slate-900">Review policy evidence</div>
                                <div class="mt-1 text-sm text-slate-500">Waivers, acknowledgements, audit trails, and coverage by role.</div>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700 shadow-sm ring-1 ring-white/80">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v3.69c0 .266.141.512.37.647l2.25 1.31a.75.75 0 1 0 .76-1.294l-1.88-1.095V6.75Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="relative mt-6 flex items-center justify-between border-t border-rose-100 pt-4 text-sm">
                            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">{{ $summary['waivers_count'] }} waivers</span>
                            <span class="font-medium text-slate-700">Evidence lane</span>
                        </div>
                    </button>
                </div>
            </div>

            <div data-admin-dashboard-panel="intelligence" class="space-y-6 hidden">
            <div class="overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-sky-50 shadow-sm">
                <div class="grid gap-4 px-6 py-5 lg:grid-cols-[1.5fr_1fr] lg:items-center">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-700">AI Intelligence</div>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900">Ranking health, probe quality, and tuning history.</h3>
                        <p class="mt-2 text-sm text-slate-600">Use this panel when the priority is recommendation reliability and settings governance rather than learner operations.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-emerald-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Provider</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $summary['ranking_provider'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-emerald-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Probe Trend</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $summary['ranking_probe_success_count'] }} ok / {{ $summary['ranking_probe_failure_count'] }} fail</div>
                        </div>
                        <div class="rounded-2xl border border-emerald-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Severity</div>
                            <div class="mt-2">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ ($summary['ranking_severity']['level'] ?? 'healthy') === 'critical' ? 'bg-red-100 text-red-800' : (($summary['ranking_severity']['level'] ?? 'healthy') === 'degraded' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $summary['ranking_severity']['label'] ?? 'Healthy' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid gap-4 xl:grid-cols-[1.35fr_1fr_1fr]">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-emerald-50 to-white px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Probe Health Snapshot
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Success and failure split for recent ranking probes.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-2 text-right">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Recent probes</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $summary['ranking_probe_success_count'] + $summary['ranking_probe_failure_count'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="h-72 p-5">
                        <canvas id="adminAiProbeChart" aria-label="Admin AI probe chart"></canvas>
                    </div>
                </div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-emerald-50 via-white to-teal-100 shadow-sm">
                    <div class="px-5 py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Provider Snapshot
                                </div>
                                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['ranking_provider'] }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $summary['ranking_provider_ready'] ? 'Provider ready for ranking' : 'Provider needs attention' }}</div>
                            </div>
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/90 text-emerald-700 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M3 3.75A.75.75 0 0 1 3.75 3h12.5a.75.75 0 0 1 0 1.5H4.5v11h11.75a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 16.25V3.75Z" />
                                    <path d="M8 13.25a.75.75 0 0 1-.75-.75V10a.75.75 0 0 1 1.5 0v2.5a.75.75 0 0 1-.75.75Zm4 0a.75.75 0 0 1-.75-.75V7a.75.75 0 0 1 1.5 0v5.5a.75.75 0 0 1-.75.75Zm4 0a.75.75 0 0 1-.75-.75V5a.75.75 0 0 1 1.5 0v7.5a.75.75 0 0 1-.75.75Z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Severity</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['ranking_severity']['label'] ?? 'Healthy' }}</div>
                            </div>
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Success Gap</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['ranking_success_gap']['label'] ?? 'n/a' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-sky-50 via-white to-slate-100 shadow-sm">
                    <div class="px-5 py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">
                                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                    Export & Incident Snapshot
                                </div>
                                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $summary['ranking_overrides_count'] }}</div>
                                <div class="mt-1 text-sm text-slate-500">active ranking override{{ $summary['ranking_overrides_count'] === 1 ? '' : 's' }}</div>
                            </div>
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/90 text-sky-700 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4.25 3A2.25 2.25 0 0 0 2 5.25v9.5A2.25 2.25 0 0 0 4.25 17h11.5A2.25 2.25 0 0 0 18 14.75v-9.5A2.25 2.25 0 0 0 15.75 3H4.25Zm2.22 3.47a.75.75 0 0 1 1.06 0L10 8.94l2.47-2.47a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Last Probe</div>
                                <div class="mt-2 text-sm font-semibold text-slate-900">{{ $summary['ranking_last_probe_at']?->format('Y-m-d H:i') ?? 'No probe yet' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $summary['ranking_last_probe_success'] ? 'success' : 'failure' }}</div>
                            </div>
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Last Export</div>
                                <div class="mt-2 text-sm font-semibold text-slate-900">{{ data_get($summary, 'ranking_last_export.label', 'No export yet') }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ data_get($summary, 'ranking_last_export.created_at')?->format('Y-m-d H:i') ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">Inactive Nudge Window</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900">{{ $summary['inactive_nudge_after_days'] }} days</div>
                    <div class="mt-1 text-xs text-gray-500">Cooldown: {{ $summary['inactive_nudge_cooldown_days'] }} days</div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">Not Started Nudge Window</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900">{{ $summary['not_started_nudge_after_days'] }} days</div>
                    <div class="mt-1 text-xs text-gray-500">Cooldown: {{ $summary['not_started_nudge_cooldown_days'] }} days</div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">Top Feed Weights</div>
                    <div class="mt-2 text-sm text-gray-700">
                        required={{ $summary['score_required_module_weight'] }},
                        topic={{ $summary['score_topic_match_weight'] }},
                        goal_max={{ $summary['score_goal_affinity_max'] }}
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">Settings Overrides</div>
                    <div class="mt-2 text-sm text-gray-700">
                        scoring={{ $summary['scoring_overrides_count'] }},
                        reminder={{ $summary['reminder_overrides_count'] }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        @if (count($scoringOverrideKeys) > 0)
                            scoring keys: {{ implode(', ', $scoringOverrideKeys) }}
                        @else
                            scoring keys: none
                        @endif
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        @if (count($reminderOverrideKeys) > 0)
                            reminder keys: {{ implode(', ', $reminderOverrideKeys) }}
                        @else
                            reminder keys: none
                        @endif
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">Last Tuning Change</div>
                    @if (!empty($summary['last_tuning_at']))
                        <div class="mt-2 text-sm text-gray-700">
                            {{ $summary['last_tuning_at']->format('Y-m-d H:i') }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            {{ str_replace('_', ' ', $summary['last_tuning_action']) }} by {{ $summary['last_tuning_actor'] ?? 'system' }}
                        </div>
                    @else
                        <div class="mt-2 text-sm text-gray-500">No tuning changes yet.</div>
                    @endif
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">AI Ranking Health</div>
                    <div class="mt-2 flex items-center gap-2">
                        <span
                            data-health-dashboard-severity-badge
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ ($summary['ranking_severity']['level'] ?? 'healthy') === 'critical' ? 'bg-red-100 text-red-800' : (($summary['ranking_severity']['level'] ?? 'healthy') === 'degraded' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}"
                        >
                            {{ $summary['ranking_severity']['label'] ?? 'Healthy' }}
                        </span>
                        <span data-health-dashboard-badge class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $summary['ranking_provider_ready'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $summary['ranking_provider_ready'] ? 'ready' : 'needs attention' }}
                        </span>
                        <span data-health-dashboard-provider class="text-sm font-semibold text-gray-900">{{ $summary['ranking_provider'] }}</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500" data-health-provider-filter-label-wrapper>Viewing {{ $rankingProbeProviderOptions[$selectedRankingProvider] ?? 'All providers' }}</div>
                    <div data-health-dashboard-summary class="mt-2 text-xs text-gray-500">
                        enabled={{ $summary['ranking_enabled'] ? 'yes' : 'no' }},
                        overrides={{ $summary['ranking_overrides_count'] }},
                        probes ok={{ $summary['ranking_probe_success_count'] }},
                        fail={{ $summary['ranking_probe_failure_count'] }}
                    </div>
                    <div data-health-dashboard-severity-reason class="mt-1 text-xs text-gray-500">{{ $summary['ranking_severity']['reason'] ?? 'Provider is ready and recent probe health is good.' }}</div>
                    @if (!empty($summary['ranking_last_probe_at']))
                        <div data-health-dashboard-last-probe class="mt-2 text-xs text-gray-500">
                            last probe {{ $summary['ranking_last_probe_at']->format('Y-m-d H:i') }}:
                            {{ $summary['ranking_last_probe_success'] ? 'success' : 'failure' }}
                        </div>
                        @if (!empty($summary['ranking_last_probe_message']))
                            <div data-health-dashboard-last-message class="mt-1 text-xs text-gray-500">
                                {{ $summary['ranking_last_probe_message'] }}
                            </div>
                        @endif
                    @else
                        <div data-health-dashboard-last-probe class="mt-2 text-xs text-gray-500">No ranking probe recorded yet.</div>
                        <div data-health-dashboard-last-message class="mt-1 text-xs text-gray-500"></div>
                    @endif
                    @if (!empty($summary['ranking_last_successful_probe_at']))
                        <div data-health-dashboard-last-successful-probe class="mt-2 text-xs text-gray-500">
                            last success {{ $summary['ranking_last_successful_probe_at']->format('Y-m-d H:i') }} via {{ $summary['ranking_last_successful_probe_provider'] }}{{ $summary['ranking_last_successful_probe_latency_ms'] !== null ? ' ('.$summary['ranking_last_successful_probe_latency_ms'].' ms)' : '' }}
                        </div>
                        <div data-health-dashboard-success-gap class="mt-1 text-xs text-gray-500">
                            healthy {{ $summary['ranking_success_gap']['label'] ?? 'n/a' }} ago
                        </div>
                    @else
                        <div data-health-dashboard-last-successful-probe class="mt-2 text-xs text-gray-500">No successful ranking probe recorded yet.</div>
                        <div data-health-dashboard-success-gap class="mt-1 text-xs text-gray-500"></div>
                    @endif
                    @if (!empty($summary['ranking_last_export']))
                        <div class="mt-2 rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                            <div>
                                Last export: {{ $summary['ranking_last_export']['label'] }} at {{ $summary['ranking_last_export']['created_at']?->format('Y-m-d H:i') }}
                                @if (!empty($summary['ranking_last_export']['bundle_id']))
                                    ; {{ $summary['ranking_last_export']['bundle_id'] }}
                                @endif
                                @if (!empty($summary['ranking_last_export']['provider']))
                                    ; provider {{ $summary['ranking_last_export']['provider'] }}
                                @endif
                                @if (!empty($summary['ranking_last_export']['trigger']))
                                    ; trigger {{ $summary['ranking_last_export']['trigger'] }}
                                @endif
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <a href="{{ route('app.admin.assignments.audit', ['action' => $summary['ranking_last_export']['action']]) }}" class="rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-white">
                                    Open Audit
                                </a>
                                @if (!empty($summary['ranking_last_export']['bundle_id']))
                                    <button type="button" data-ranking-health-copy-bundle-id="{{ $summary['ranking_last_export']['bundle_id'] }}" class="rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-white">
                                        Copy bundle ID
                                    </button>
                                @endif
                                <span data-ranking-health-copy-bundle-status class="text-xs text-slate-500" aria-live="polite"></span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $rankingHealthCopy['health_heading'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $rankingHealthCopy['health_body'] }}</p>
                            @include('app.partials.ranking-health-summary', [
                                'isFiltered' => $selectedRankingProvider !== 'all' || $selectedRankingSeverityTrigger !== 'all',
                                'filterCount' => (($selectedRankingProvider !== 'all') ? 1 : 0) + (($selectedRankingSeverityTrigger !== 'all') ? 1 : 0),
                                'providerLabel' => $rankingProbeProviderOptions[$selectedRankingProvider] ?? 'All providers',
                                'triggerLabel' => $rankingSeverityTriggerOptions[$selectedRankingSeverityTrigger] ?? 'All triggers',
                                'apiUrl' => url('/api/admin/ai/ranking-health?limit=5'),
                                'auditUrl' => route('app.admin.assignments.audit', array_filter([
                                    'action' => 'ranking_severity_changed',
                                    'q' => $selectedRankingSeverityTrigger !== 'all' ? $selectedRankingSeverityTrigger : null,
                                ])),
                                'providerMismatchMessage' => $rankingProviderMismatchMessage,
                                'latencyDataAttribute' => 'data-health-dashboard-latency-summary',
                                'latencySummary' => $recentRankingProbeLatencySummary,
                            ])
                        </div>
                        <div class="flex flex-wrap items-center gap-2 rounded-[1.5rem] border border-slate-200 bg-slate-50/90 p-3">
                            <label class="flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                                <span class="uppercase tracking-[0.16em]">Provider</span>
                                <select data-ranking-health-provider-filter class="rounded-full border-slate-300 bg-slate-50 py-1 pl-2 pr-8 text-xs">
                                    @foreach ($rankingProbeProviderOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $selectedRankingProvider === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                                <span class="uppercase tracking-[0.16em]">From</span>
                                <input type="date" name="ranking_export_from" value="{{ $selectedRankingExportFrom }}" data-ranking-health-export-from class="rounded-full border-slate-300 bg-slate-50 py-1 px-2 text-xs">
                            </label>
                            <label class="flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                                <span class="uppercase tracking-[0.16em]">To</span>
                                <input type="date" name="ranking_export_to" value="{{ $selectedRankingExportTo }}" data-ranking-health-export-to class="rounded-full border-slate-300 bg-slate-50 py-1 px-2 text-xs">
                            </label>
                            <a
                                href="{{ route('app.admin.ranking.export.probes', array_filter([
                                    'ranking_provider' => $selectedRankingProvider !== 'all' ? $selectedRankingProvider : null,
                                    'ranking_export_from' => $selectedRankingExportFrom,
                                    'ranking_export_to' => $selectedRankingExportTo,
                                ])) }}"
                                data-ranking-health-export-probes
                                data-export-base-url="{{ route('app.admin.ranking.export.probes') }}"
                                class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                            >
                                Export CSV
                            </a>
                            <div data-health-refreshed-at class="rounded-full bg-white px-3 py-2 text-xs font-medium text-slate-500 shadow-sm">Last updated on page load</div>
                            <button type="button" data-ranking-health-refresh class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                Refresh now
                            </button>
                            <button type="button" data-ranking-health-copy-url class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                Copy API URL
                            </button>
                            <a href="{{ route('app.admin.ranking.export.incident-bundle') }}" data-ranking-health-export-json data-export-base-url="{{ route('app.admin.ranking.export.incident-bundle') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                Export JSON
                            </a>
                            <button type="button" data-ranking-health-clear-filters class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                Clear filters
                            </button>
                            <a href="{{ url('/api/admin/ai/ranking-health?limit=5') }}" data-ranking-health-open-url class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                Open API
                            </a>
                            <span data-ranking-health-copy-status class="text-xs text-slate-500" aria-live="polite"></span>
                            <a href="{{ route('app.admin.ranking.export.incident-bundle', array_filter([
                                'ranking_provider' => $selectedRankingProvider !== 'all' ? $selectedRankingProvider : null,
                                'ranking_severity_trigger' => $selectedRankingSeverityTrigger !== 'all' ? $selectedRankingSeverityTrigger : null,
                                'ranking_export_from' => $selectedRankingExportFrom,
                                'ranking_export_to' => $selectedRankingExportTo,
                            ])) }}" data-ranking-health-export-json data-export-base-url="{{ route('app.admin.ranking.export.incident-bundle') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                Export JSON
                            </a>
                            <a href="{{ route('app.admin.ranking.edit') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                Open Ranking Settings
                            </a>
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 border-b border-gray-200 px-5 py-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Provider</div>
                        <div class="mt-2 text-sm font-semibold text-slate-900">{{ $summary['ranking_provider'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">AI Layer</div>
                        <div class="mt-2 text-sm font-semibold text-slate-900">{{ $summary['ranking_enabled'] ? 'Enabled' : 'Disabled' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Probe Trend</div>
                        <div data-health-dashboard-trend class="mt-2 text-sm font-semibold text-slate-900">ok {{ $summary['ranking_probe_success_count'] }} / fail {{ $summary['ranking_probe_failure_count'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Provider Status</div>
                        <div class="mt-2">
                            <span data-health-dashboard-status-badge class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $summary['ranking_provider_ready'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $summary['ranking_provider_ready'] ? 'Ready' : 'Needs attention' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">When</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Provider</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Latency</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Message</th>
                            </tr>
                        </thead>
                        <tbody data-health-dashboard-history-body>
                            @forelse ($recentRankingProbes as $probe)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        @if (!empty($probe['created_at']))
                                            @php $probeCreatedAt = \Illuminate\Support\Carbon::parse($probe['created_at']); @endphp
                                            <div>{{ $probeCreatedAt->format('Y-m-d H:i') }}</div>
                                            <div class="mt-1 text-xs text-slate-400">{{ $probeCreatedAt->diffForHumans() }}</div>
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-900 shadow-sm">{{ $probe['provider'] }}</td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 shadow-sm">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $probe['success'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $probe['success'] ? 'success' : 'failure' }}
                                        </span>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $probe['latency_ms'] !== null ? $probe['latency_ms'].' ms' : 'n/a' }}</td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $probe['message'] ?? 'n/a' }}</td>
                                </tr>
                            @empty
                                @include('app.partials.ranking-health-empty-row', [
                                    'tdAttributes' => 'data-health-dashboard-history-empty',
                                    'colspan' => 5,
                                    'message' => $recentRankingProbeEmptyMessage,
                                ])
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-4">
                    <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">{{ $rankingHealthCopy['failure_summary_heading'] }}</div>
                    <div class="mt-3 grid gap-3 md:grid-cols-2" data-health-failure-summary>
                        @forelse ($recentRankingFailures as $failure)
                            @include('app.partials.ranking-health-failure-summary-item', ['failure' => $failure])
                        @empty
                            @include('app.partials.ranking-health-empty-state', ['message' => 'No recent failures in this probe window.'])
                        @endforelse
                    </div>
                </div>
                <div class="border-t border-gray-200 px-5 py-4">
                    <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Recent Live Ranking Failures</div>
                    <div class="mt-1 text-sm text-slate-500">Latest runtime `feed_ranking` failures for the selected provider filter.</div>
                    <div class="mt-3 grid gap-3 md:grid-cols-2" data-health-live-failures>
                        @forelse ($recentRankingLiveFailures as $failure)
                            <div class="rounded border border-rose-200 bg-rose-50 px-3 py-3 text-sm text-rose-900">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-semibold">{{ $failure['provider'] }}</div>
                                    <div class="text-xs text-rose-700">{{ !empty($failure['created_at']) ? \Illuminate\Support\Carbon::parse($failure['created_at'])->format('Y-m-d H:i:s') : 'n/a' }}</div>
                                </div>
                                <div class="mt-1 text-xs text-rose-800">request {{ $failure['request_id'] ?: 'n/a' }}; latency {{ $failure['latency_ms'] !== null ? $failure['latency_ms'].' ms' : 'n/a' }}</div>
                                <div class="mt-1 text-xs text-rose-700">{{ $failure['message'] ?? 'Unknown runtime failure.' }}</div>
                                <div class="mt-2">
                                    <a href="{{ route('app.admin.ai-usages', ['provider' => $failure['provider'] ?? null, 'capability' => 'feed_ranking', 'success' => 0, 'request_id' => $failure['request_id'] ?? null, 'limit' => 10]) }}" class="rounded border border-rose-300 px-2 py-1 text-xs font-semibold text-rose-800 hover:bg-white">
                                        Open Ops
                                    </a>
                                </div>
                            </div>
                        @empty
                            @include('app.partials.ranking-health-empty-state', ['message' => 'No recent live ranking failures in this window.'])
                        @endforelse
                    </div>
                </div>
                <div class="border-t border-gray-200 px-5 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">{{ $rankingHealthCopy['severity_transitions_heading'] }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $rankingHealthCopy['severity_transitions_body'] }}</div>
                            <div class="mt-1 text-xs text-slate-500" data-health-severity-trigger-filter-label-wrapper>Showing {{ $rankingSeverityTriggerOptions[$selectedRankingSeverityTrigger] ?? 'All triggers' }}.</div>
                            <div class="mt-3 flex flex-wrap gap-2" data-health-severity-trigger-summary>
                                <button type="button" data-ranking-health-trigger-chip data-trigger="all" class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition {{ $selectedRankingSeverityTrigger === 'all' ? 'border-sky-300 bg-sky-50 text-sky-700' : 'border-gray-200 bg-white text-gray-700 hover:border-sky-200 hover:bg-sky-50/50' }}">
                                    All triggers {{ $severityTriggerSummary->sum('count') }}
                                </button>
                                @foreach ($severityTriggerSummary as $row)
                                    <button type="button" data-ranking-health-trigger-chip data-trigger="{{ $row['trigger'] }}" class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition {{ $selectedRankingSeverityTrigger !== 'all' && $selectedRankingSeverityTrigger === $row['trigger'] ? 'border-sky-300 bg-sky-50 text-sky-700' : 'border-gray-200 bg-white text-gray-700 hover:border-sky-200 hover:bg-sky-50/50' }}">
                                        {{ $rankingSeverityTriggerOptions[$row['trigger']] ?? $row['trigger'] }} {{ $row['count'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                                <span>Trigger</span>
                                <select data-ranking-health-severity-trigger-filter class="rounded border-gray-300 py-1 pl-2 pr-8 text-xs">
                                    @foreach ($rankingSeverityTriggerOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $selectedRankingSeverityTrigger === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                                <span>From</span>
                                <input type="date" name="ranking_export_from" value="{{ $selectedRankingExportFrom }}" data-ranking-health-export-from class="rounded border-gray-300 py-1 px-2 text-xs">
                            </label>
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                                <span>To</span>
                                <input type="date" name="ranking_export_to" value="{{ $selectedRankingExportTo }}" data-ranking-health-export-to class="rounded border-gray-300 py-1 px-2 text-xs">
                            </label>
                            <a
                                href="{{ route('app.admin.ranking.export.severity-transitions', array_filter([
                                    'ranking_severity_trigger' => $selectedRankingSeverityTrigger !== 'all' ? $selectedRankingSeverityTrigger : null,
                                    'ranking_export_from' => $selectedRankingExportFrom,
                                    'ranking_export_to' => $selectedRankingExportTo,
                                ])) }}"
                                data-ranking-health-export-severity
                                data-export-base-url="{{ route('app.admin.ranking.export.severity-transitions') }}"
                                class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Export CSV
                            </a>
                            <a href="{{ route('app.admin.assignments.audit', array_filter([
                                'action' => 'ranking_severity_changed',
                                'q' => $selectedRankingSeverityTrigger !== 'all' ? $selectedRankingSeverityTrigger : null,
                            ])) }}" data-ranking-health-open-audit data-audit-base-url="{{ route('app.admin.assignments.audit', ['action' => 'ranking_severity_changed']) }}" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Open Audit
                            </a>
                        </div>
                    </div>
                    <div class="mt-3 space-y-3" data-health-severity-transitions>
                        @forelse ($recentSeverityTransitions as $event)
                            @include('app.partials.ranking-health-severity-transition', [
                                'transition' => $event,
                                'class' => 'rounded border border-gray-200 bg-gray-50/60 px-4 py-3',
                            ])
                        @empty
                            @include('app.partials.ranking-health-empty-state', ['message' => $recentSeverityTransitionsEmptyMessage])
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Tuning Changes</h3>
                    <p class="mt-1 text-sm text-gray-500">Latest scoring and reminder settings changes recorded in audit logs.</p>
                </div>
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">When</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Actor</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Action</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentTuningEvents as $event)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <div>{{ $event->created_at?->format('Y-m-d H:i') }}</div>
                                        @if ($event->created_at)
                                            <div class="mt-1 text-xs text-slate-400">{{ $event->created_at->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $event->actor?->name ?? 'system' }}</td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ str_replace('_', ' ', $event->action) }}</span>
                                    </td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        @if (!empty($event->meta['changed_keys']) && is_array($event->meta['changed_keys']))
                                            changed {{ implode(', ', $event->meta['changed_keys']) }}
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-4 text-gray-500">No tuning changes recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

            <div id="admin-assignments-section" data-admin-dashboard-panel="operations" class="space-y-5">
            <div class="overflow-hidden rounded-3xl border border-sky-200 bg-gradient-to-r from-sky-50 via-white to-cyan-50 shadow-sm">
                <div class="grid gap-4 px-6 py-5 lg:grid-cols-[1.5fr_1fr] lg:items-center">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Operations</div>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900">Reminder queues, active assignment focus, and role workload.</h3>
                        <p class="mt-2 text-sm text-slate-600">Use this panel for real admin follow-up work: who needs nudges, what is overdue, and where active queues need intervention.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-sky-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Pending</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['pending_reminders_count'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-sky-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Inactive</div>
                            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ $summary['inactive_nudge_count'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-sky-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Not Started</div>
                            <div class="mt-2 text-2xl font-semibold text-orange-600">{{ $summary['not_started_nudge_count'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid gap-4 xl:grid-cols-[1.35fr_1fr_1fr]">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-sky-50 to-white px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">
                                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                    Operations Pressure Mix
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Visible urgency split across focused assignments on the dashboard.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-2 text-right">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Visible rows</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $focusRows->count() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="h-72 p-5">
                        <canvas id="adminOperationsUrgencyChart" aria-label="Admin operations urgency chart"></canvas>
                    </div>
                </div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-amber-50 via-white to-orange-100 shadow-sm">
                    <div class="px-5 py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    Reminder Queue Health
                                </div>
                                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $pendingReminders->count() }}</div>
                                <div class="mt-1 text-sm text-slate-500">queued reminder{{ $pendingReminders->count() === 1 ? '' : 's' }} currently visible on this dashboard slice.</div>
                            </div>
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/90 text-amber-700 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 2a4 4 0 0 0-4 4v1.382c0 .537-.214 1.053-.595 1.434L4.31 9.91A1.5 1.5 0 0 0 5.37 12.5h9.26a1.5 1.5 0 0 0 1.06-2.56l-1.095-1.094A2.03 2.03 0 0 1 14 7.382V6a4 4 0 0 0-4-4Z" />
                                    <path d="M8 14a2 2 0 1 0 4 0H8Z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Overdue</div>
                                <div class="mt-2 text-2xl font-semibold text-rose-700">{{ $pendingReminders->where('reminder_type', 'overdue')->count() }}</div>
                            </div>
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Due Soon</div>
                                <div class="mt-2 text-2xl font-semibold text-amber-700">{{ $pendingReminders->where('reminder_type', 'due_soon')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-sky-50 via-white to-indigo-100 shadow-sm">
                    <div class="px-5 py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">
                                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                    Focused Assignment Pressure
                                </div>
                                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $focusRows->count() }}</div>
                                <div class="mt-1 text-sm text-slate-500">assignment{{ $focusRows->count() === 1 ? '' : 's' }} in the current dashboard focus set.</div>
                            </div>
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/90 text-sky-700 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M3 3.75A.75.75 0 0 1 3.75 3h12.5a.75.75 0 0 1 0 1.5H4.5v11h11.75a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 16.25V3.75Z" />
                                    <path d="M8 6.75A.75.75 0 0 1 8.75 6h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 8 6.75Zm0 3.5a.75.75 0 0 1 .75-.75h6.5a.75.75 0 0 1 0 1.5h-6.5a.75.75 0 0 1-.75-.75Zm0 3.5a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 0 1.5h-3.5a.75.75 0 0 1-.75-.75Z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Overdue</div>
                                <div class="mt-2 text-2xl font-semibold text-rose-700">{{ $focusRows->where('urgency', 'overdue')->count() }}</div>
                            </div>
                            <div class="rounded-2xl bg-white/80 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Due Soon / Inactive</div>
                                <div class="mt-2 text-2xl font-semibold text-amber-700">{{ $focusRows->whereIn('urgency', ['due_soon', 'inactive', 'inactive_nudge'])->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-amber-50 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Reminder Queue
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Run reminder operations without leaving the dashboard.</h3>
                            <p class="mt-1 text-sm text-slate-500">Generate and manage due soon, overdue, inactive, and not started learner reminders from one control strip.</p>
                            <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($pendingReminders->count(), $dashboardReminderPreviewCount) }} of {{ $pendingReminders->count() }} queued reminder{{ $pendingReminders->count() === 1 ? '' : 's' }} on the dashboard.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">
                                Preview rows
                            </span>
                            <form method="POST" action="{{ route('app.admin.assignments.reminders.sync') }}">
                                @csrf
                                <button type="submit" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                    Sync Reminder Queue
                                </button>
                            </form>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('app.admin.assignments.reminders.run') }}" class="mt-5 rounded-[1.5rem] border border-slate-200 bg-white/90 p-4 shadow-sm">
                        @csrf
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4 xl:flex-1">
                                <div>
                                    <label class="mb-1 block text-xs font-medium uppercase tracking-[0.16em] text-slate-600">Mode</label>
                                    <select name="mode" class="w-full rounded-2xl border-slate-300 bg-slate-50 text-sm">
                                        <option value="sync_and_send">sync_and_send</option>
                                        <option value="sync_only">sync_only</option>
                                        <option value="send_only">send_only</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium uppercase tracking-[0.16em] text-slate-600">Limit</label>
                                    <input type="number" name="limit" value="100" min="1" max="1000" class="w-full rounded-2xl border-slate-300 bg-slate-50 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium uppercase tracking-[0.16em] text-slate-600">Types</label>
                                    <input type="text" name="types" value="" placeholder="overdue,due_soon,inactive_nudge,not_started_nudge" class="w-full rounded-2xl border-slate-300 bg-slate-50 text-sm">
                                </div>
                                <label for="dry_run" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    <input id="dry_run" type="checkbox" name="dry_run" value="1" class="rounded border-gray-300 text-sky-700">
                                    <span>
                                        <span class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Execution</span>
                                        <span class="mt-1 block font-medium text-slate-800">Dry run only</span>
                                    </span>
                                </label>
                            </div>
                            <div class="flex items-center justify-between gap-3 xl:justify-end">
                                <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">
                                    Queue control
                                </div>
                                <button type="submit" class="rounded-full bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-600">
                                    Run Reminders
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @if ($pendingReminders->isNotEmpty())
                    <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 lg:grid-cols-[1.1fr_0.9fr]">
                        <div>
                            <div class="grid gap-4 md:grid-cols-3">
                                @foreach ($pendingReminders->groupBy('reminder_type')->take(3) as $type => $rows)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ str_replace('_', ' ', $type) }}</div>
                                        <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $rows->count() }}</div>
                                        <div class="mt-2 text-xs text-slate-500">
                                            latest due {{ optional($rows->sortByDesc('due_on')->first()?->due_on)?->diffForHumans() ?? 'n/a' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-3">
                                @foreach ($pendingReminders->take(3) as $reminder)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Reminder Card</div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">{{ $reminder->user?->name ?? 'Unknown learner' }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ $reminder->module?->title ?? 'Unknown module' }}</div>
                                            </div>
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ str_replace('_', ' ', $reminder->reminder_type) }}</span>
                                        </div>
                                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                                            <span>{{ strtolower((string) $reminder->user?->preference?->role) ?: 'unassigned' }}</span>
                                            <span>{{ $reminder->due_on?->diffForHumans() ?? 'n/a' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium text-slate-900">Reminder Mix</div>
                                    <div class="text-xs text-slate-500">Queue split by reminder type.</div>
                                </div>
                                <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                                    Queue Live
                                </div>
                            </div>
                            <div class="mt-4 h-64">
                                <canvas id="adminReminderMixChart" aria-label="Admin reminder mix chart"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Learner</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Module</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Due On</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingReminders->take($dashboardReminderPreviewCount) as $reminder)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <a href="{{ route('app.admin.assignments.user', ['user' => $reminder->user_id]) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $reminder->user?->name ?? 'Unknown learner' }}
                                        </a>
                                        <div class="text-xs text-gray-400">{{ $reminder->user?->email }}</div>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ strtolower((string) $reminder->user?->preference?->role) ?: 'unassigned' }}</td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $reminder->module?->title ?? 'Unknown module' }}</td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ str_replace('_', ' ', $reminder->reminder_type) }}</span>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <div>{{ $reminder->due_on?->toDateString() }}</div>
                                        @if ($reminder->due_on)
                                            <div class="mt-1 text-xs text-slate-400">{{ $reminder->due_on->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $reminder->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $reminder->status }}</span>
                                    </td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <form method="POST" action="{{ route('app.admin.assignments.reminders.sent', ['reminder' => $reminder->id]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded border border-gray-300 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                                Mark Sent
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-4 text-gray-500">No pending reminders in the queue.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Operations Rulebook</div>
                                <h3 class="mt-2 text-lg font-semibold text-gray-900">Rules by Role</h3>
                                <p class="mt-1 text-sm text-gray-500">Database-backed compliance inheritance currently used for assignment.</p>
                                <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($rulesByRole->count(), $dashboardRulesPreviewCount) }} of {{ $rulesByRole->count() }} role rule row{{ $rulesByRole->count() === 1 ? '' : 's' }}.</p>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                                Preview rows
                            </span>
                        </div>
                    </div>
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
                        <form method="POST" action="{{ route('app.admin.assignments.rules.store') }}" class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                            @csrf
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                                <input type="text" name="role" class="w-full rounded border-gray-300 text-sm" placeholder="e.g. Classroom Teacher" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Compliance Area</label>
                                <input type="text" name="compliance_area" class="w-full rounded border-gray-300 text-sm" placeholder="e.g. data-privacy" required>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="rounded bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                    Add Rule
                                </button>
                            </div>
                        </form>
                    </div>
                    @if ($rulesByRole->isNotEmpty())
                        <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                            @foreach ($rulesByRole->take(3) as $role => $rules)
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Rule Cluster</div>
                                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $role }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $rules->count() }} compliance area{{ $rules->count() === 1 ? '' : 's' }}</div>
                                        </div>
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">
                                            {{ $rules->pluck('compliance_area')->unique()->count() }} areas
                                        </span>
                                    </div>
                                    <div class="mt-4 rounded-xl bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                        {{ $rules->pluck('compliance_area')->take(3)->join(', ') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="overflow-x-auto px-3 py-3">
                        <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Compliance Areas</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rulesByRole->take($dashboardRulesPreviewCount) as $role => $rules)
                                    <tr class="transition hover:-translate-y-0.5">
                                        <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 font-medium text-gray-900 shadow-sm">
                                            <a href="{{ route('app.admin.assignments.role', ['role' => $role]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $role }}
                                            </a>
                                        </td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $rules->pluck('compliance_area')->join(', ') }}</td>
                                        <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($rules as $rule)
                                                    <form method="POST" action="{{ route('app.admin.assignments.rules.destroy', ['rule' => $rule->id]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded border border-red-200 px-2 py-1 text-xs text-red-700 hover:bg-red-50">
                                                            Remove {{ $rule->compliance_area }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-4 text-gray-500">No assignment rules found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Operations Load</div>
                                <h3 class="mt-2 text-lg font-semibold text-gray-900">Overdue by Role</h3>
                                <p class="mt-1 text-sm text-gray-500">Current overdue required-learning load across users with saved roles.</p>
                                <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($overdueByRole->count(), $dashboardOverduePreviewCount) }} of {{ $overdueByRole->count() }} overdue role row{{ $overdueByRole->count() === 1 ? '' : 's' }}.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                                    Preview rows
                                </span>
                                <a href="{{ route('app.admin.assignments', ['focus' => 'overdue']) }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Open Overdue Queue
                                </a>
                            </div>
                        </div>
                    </div>
                    @if ($overdueByRole->isNotEmpty())
                        <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                            @foreach ($overdueByRole->take(3) as $row)
                                <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Overdue Pressure</div>
                                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $row['role'] }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $row['user_count'] }} user{{ $row['user_count'] === 1 ? '' : 's' }}</div>
                                        </div>
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                            {{ $row['overdue_count'] }} overdue
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="overflow-x-auto px-3 py-3">
                        <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Users</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overdueByRole->take($dashboardOverduePreviewCount) as $row)
                                    <tr class="transition hover:-translate-y-0.5">
                                        <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 font-medium text-gray-900 shadow-sm">
                                            <a href="{{ route('app.admin.assignments.role', ['role' => $row['role']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $row['role'] }}
                                            </a>
                                        </td>
                                        <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['user_count'] }}</td>
                                        <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['overdue_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-4 text-gray-500">No role-based learners found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                @php
                    $focusOptions = [
                        'all' => 'All',
                        'overdue' => 'Overdue',
                        'due_soon' => 'Due Soon',
                        'inactive' => 'Inactive',
                        'waived' => 'Waived',
                    ];
                @endphp
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-sky-50 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">
                                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                Focused Assignments
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Switch operational queues in place.</h3>
                            <p class="mt-1 text-sm text-slate-500">Filter overdue, due soon, inactive, and waived populations without leaving the dashboard.</p>
                            <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($focusRows->count(), $dashboardFocusPreviewCount) }} of {{ $focusRows->count() }} assignment row{{ $focusRows->count() === 1 ? '' : 's' }} for the current focus.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">
                                Preview rows
                            </span>
                            <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">
                                Current focus: {{ $focusOptions[$currentFocus] ?? 'All' }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col gap-3 rounded-[1.5rem] border border-slate-200 bg-white/90 p-4 shadow-sm xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($focusOptions as $value => $label)
                                <a
                                    href="{{ route('app.admin.assignments', ['focus' => $value]) }}"
                                    class="rounded-full border px-3 py-2 text-sm font-medium transition {{ $currentFocus === $value ? 'border-sky-600 bg-sky-50 text-sky-700 shadow-sm' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50' }}"
                                >
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('app.admin.assignments.export', ['focus' => $currentFocus]) }}"
                                class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                @if ($focusRows->isNotEmpty())
                    <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                        @foreach ($focusRows->take(3) as $row)
                            @php
                                $focusCardClasses = $row['urgency'] === 'overdue'
                                    ? 'border-rose-200 bg-rose-50'
                                    : ($row['urgency'] === 'due_soon'
                                        ? 'border-amber-200 bg-amber-50'
                                        : ($row['urgency'] === 'inactive'
                                            ? 'border-orange-200 bg-orange-50'
                                            : ($row['urgency'] === 'waived'
                                                ? 'border-slate-200 bg-slate-100'
                                                : 'border-sky-200 bg-sky-50')));
                            @endphp
                            <div class="rounded-2xl border p-4 shadow-sm {{ $focusCardClasses }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Urgent Assignment</div>
                                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $row['module_title'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $row['compliance_area'] }}</div>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['urgency'] === 'overdue' ? 'bg-rose-100 text-rose-800' : ($row['urgency'] === 'due_soon' ? 'bg-amber-100 text-amber-800' : ($row['urgency'] === 'inactive_nudge' ? 'bg-orange-100 text-orange-800' : ($row['urgency'] === 'waived' ? 'bg-slate-200 text-slate-700' : 'bg-sky-100 text-sky-800'))) }}">
                                        {{ str_replace('_', ' ', $row['urgency']) }}
                                    </span>
                                </div>
                                <div class="mt-4 rounded-xl bg-white/80 px-3 py-3 text-sm">
                                    <div class="font-medium text-slate-900">{{ $row['learner_name'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $row['learner_email'] }}</div>
                                    <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                        <span>{{ $row['role'] }}</span>
                                        <span>{{ $row['renewal_due_at'] ? $row['renewal_due_at']->diffForHumans() : 'no due date' }}</span>
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <div class="text-xs text-slate-500">
                                        @if ($row['waiver_reason'])
                                            {{ $row['waiver_reason'] }}
                                        @else
                                            Progress: {{ str_replace('_', ' ', $row['progress_status']) }}
                                        @endif
                                    </div>
                                    <a href="{{ route('app.admin.assignments.user', ['user' => $row['learner_id']]) }}" class="rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Open Learner
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Learner</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Module</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Area</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Urgency</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Due</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($focusRows->take($dashboardFocusPreviewCount) as $row)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 font-medium text-gray-900 shadow-sm">
                                        <a href="{{ route('app.admin.assignments.role', ['role' => $row['role']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $row['role'] }}
                                        </a>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <a href="{{ route('app.admin.assignments.user', ['user' => $row['learner_id']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $row['learner_name'] }}
                                        </a>
                                        <div class="text-xs text-gray-400">{{ $row['learner_email'] }}</div>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <div class="font-medium text-slate-900">{{ $row['module_title'] }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $row['progress_status'] === 'completed' ? 'completion captured' : str_replace('_', ' ', $row['progress_status']) }}</div>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <a href="{{ route('app.admin.assignments.compliance-area', ['area' => $row['compliance_area']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $row['compliance_area'] }}
                                        </a>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 shadow-sm">
                                        <span class="rounded px-2 py-1 text-xs font-semibold {{ $row['urgency'] === 'overdue' ? 'bg-red-100 text-red-700' : ($row['urgency'] === 'due_soon' ? 'bg-amber-100 text-amber-700' : ($row['urgency'] === 'inactive_nudge' ? 'bg-orange-100 text-orange-700' : ($row['urgency'] === 'waived' ? 'bg-slate-200 text-slate-700' : 'bg-blue-100 text-blue-700'))) }}">
                                            {{ str_replace('_', ' ', $row['urgency']) }}
                                        </span>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <div>{{ $row['renewal_due_at'] ? $row['renewal_due_at']->toDateString() : 'n/a' }}</div>
                                        @if ($row['renewal_due_at'])
                                            <div class="mt-1 text-xs text-slate-400">{{ $row['renewal_due_at']->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        @if ($row['waiver_reason'])
                                            {{ $row['waiver_reason'] }}
                                        @else
                                            Progress: {{ $row['progress_status'] }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-4 text-gray-500">No assignments match the current focus.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

            <div data-admin-dashboard-panel="governance" class="space-y-5">
            <div class="overflow-hidden rounded-3xl border border-rose-200 bg-gradient-to-r from-rose-50 via-white to-slate-50 shadow-sm">
                <div class="grid gap-4 px-6 py-5 lg:grid-cols-[1.5fr_1fr] lg:items-center">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-700">Governance</div>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900">Rules, exceptions, acknowledgements, and audit evidence.</h3>
                        <p class="mt-2 text-sm text-slate-600">Use this panel when the discussion is policy coverage, exemptions, acknowledgements, and proof of admin activity.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-rose-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Rules</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['rules_count'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-rose-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Waivers</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['waivers_count'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-rose-200 bg-white/90 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Acknowledgements</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['acknowledgements_count'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid gap-4 xl:grid-cols-[1.4fr_1fr_1fr]">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-rose-50 to-white px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                    Governance Snapshot
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Top policy signals surfaced as dashboard cards before the detailed tables.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-2 text-right">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Audit rows</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $recentAuditEvents->count() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 p-5 md:grid-cols-3">
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Top Waiver Role</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $waiverByRole->first()['role'] ?? 'n/a' }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $waiverByRole->first()['waiver_count'] ?? 0 }} waiver{{ (($waiverByRole->first()['waiver_count'] ?? 0) === 1) ? '' : 's' }}</div>
                        </div>
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Top Acknowledgement Role</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $acknowledgementsByRole->first()['role'] ?? 'n/a' }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $acknowledgementsByRole->first()['acknowledgement_count'] ?? 0 }} acknowledgement{{ (($acknowledgementsByRole->first()['acknowledgement_count'] ?? 0) === 1) ? '' : 's' }}</div>
                        </div>
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Recent Audit Action</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ str_replace('_', ' ', $recentAuditEvents->first()->action ?? 'n/a') }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $recentAuditEvents->first()?->created_at?->diffForHumans() ?? 'No recent event' }}</div>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-rose-50 to-white px-5 py-4">
                        <div class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            Waiver vs Acknowledgement Mix
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Top roles by waiver and acknowledgement volume.</p>
                    </div>
                    <div class="h-72 p-5">
                        <canvas id="adminGovernanceMixChart" aria-label="Admin governance mix chart"></canvas>
                    </div>
                </div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-rose-50 to-white px-5 py-4">
                        <div class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            Audit Action Mix
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Recent governance activity types from the current audit preview.</p>
                    </div>
                    <div class="h-72 p-5">
                        <canvas id="adminGovernanceAuditChart" aria-label="Admin governance audit chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Governance Role View</div>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900">Waivers by Role</h3>
                            <p class="mt-1 text-sm text-gray-500">Operational exceptions currently applied by learner role.</p>
                            <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($waiverByRole->count(), $dashboardWaiverPreviewCount) }} of {{ $waiverByRole->count() }} waiver summary row{{ $waiverByRole->count() === 1 ? '' : 's' }}.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                                Preview rows
                            </span>
                            <a href="{{ route('app.admin.assignments.audit', ['action' => 'waiver_created']) }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Open Waiver Audit
                            </a>
                        </div>
                    </div>
                </div>
                @if ($waiverByRole->isNotEmpty())
                    <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                        @foreach ($waiverByRole->take(3) as $row)
                            <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Waiver Leader</div>
                                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $row['role'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $row['waiver_count'] }} waiver{{ $row['waiver_count'] === 1 ? '' : 's' }}</div>
                                    </div>
                                    <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                        {{ count($row['users']) }} user{{ count($row['users']) === 1 ? '' : 's' }}
                                    </span>
                                </div>
                                <div class="mt-4 rounded-xl bg-slate-50 px-3 py-3 text-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Users</div>
                                    <div class="mt-2 text-slate-700">{{ $row['users']->take(3)->join(', ') ?: 'n/a' }}</div>
                                </div>
                                <div class="mt-3 text-xs text-slate-500">
                                    {{ $row['modules']->take(2)->join(', ') ?: 'No modules' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Waivers</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Users</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Modules</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($waiverByRole->take($dashboardWaiverPreviewCount) as $row)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 font-medium text-gray-900 shadow-sm">
                                        <a href="{{ route('app.admin.assignments.role', ['role' => $row['role']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $row['role'] }}
                                        </a>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['waiver_count'] }}</td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['users']->join(', ') ?: 'n/a' }}</td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['modules']->join(', ') ?: 'n/a' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-4 text-gray-500">No waivers recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Governance Role View</div>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900">Acknowledgements by Role</h3>
                            <p class="mt-1 text-sm text-gray-500">Recorded learner acknowledgements grouped by role.</p>
                            <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($acknowledgementsByRole->count(), $dashboardAcknowledgementPreviewCount) }} of {{ $acknowledgementsByRole->count() }} acknowledgement summary row{{ $acknowledgementsByRole->count() === 1 ? '' : 's' }}.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                                Preview rows
                            </span>
                            <a href="{{ route('app.admin.assignments.audit', ['action' => 'assignment_acknowledged']) }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Open Acknowledgement Audit
                            </a>
                        </div>
                    </div>
                </div>
                @if ($acknowledgementsByRole->isNotEmpty())
                    <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                        @foreach ($acknowledgementsByRole->take(3) as $row)
                            <div class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Acknowledgement Leader</div>
                                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $row['role'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $row['acknowledgement_count'] }} acknowledgement{{ $row['acknowledgement_count'] === 1 ? '' : 's' }}</div>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                        {{ count($row['users']) }} user{{ count($row['users']) === 1 ? '' : 's' }}
                                    </span>
                                </div>
                                <div class="mt-4 rounded-xl bg-slate-50 px-3 py-3 text-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Users</div>
                                    <div class="mt-2 text-slate-700">{{ $row['users']->take(3)->join(', ') ?: 'n/a' }}</div>
                                </div>
                                <div class="mt-3 text-xs text-slate-500">
                                    {{ $row['modules']->take(2)->join(', ') ?: 'No modules' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Acknowledgements</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Users</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Modules</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($acknowledgementsByRole->take($dashboardAcknowledgementPreviewCount) as $row)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 font-medium text-gray-900 shadow-sm">
                                        <a href="{{ route('app.admin.assignments.role', ['role' => $row['role']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $row['role'] }}
                                        </a>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['acknowledgement_count'] }}</td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['users']->join(', ') ?: 'n/a' }}</td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['modules']->join(', ') ?: 'n/a' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-4 text-gray-500">No acknowledgements recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Recent Assignment Activity</h3>
                            <p class="mt-1 text-sm text-gray-500">Audit trail for admin rule and waiver changes.</p>
                            <p class="mt-1 text-xs text-slate-500">Showing the latest {{ min($recentAuditEvents->count(), $dashboardAuditPreviewCount) }} of {{ $recentAuditEvents->count() }} recent audit event{{ $recentAuditEvents->count() === 1 ? '' : 's' }}.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">
                                Preview rows
                            </span>
                            <a href="{{ route('app.admin.assignments.audit') }}" class="rounded-full border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                View Full Audit
                            </a>
                        </div>
                    </div>
                </div>
                @if ($recentAuditEvents->isNotEmpty())
                    <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                        @foreach ($recentAuditEvents->take(3) as $event)
                            <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Recent Governance Event</div>
                                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ str_replace('_', ' ', $event->action) }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $event->created_at?->diffForHumans() ?? 'n/a' }}</div>
                                    </div>
                                    <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                        {{ $event->actor ? 'admin' : 'system' }}
                                    </span>
                                </div>
                                <div class="mt-4 rounded-xl bg-slate-50 px-3 py-3 text-sm">
                                    <div class="font-medium text-slate-900">
                                        @if ($event->actor)
                                            {{ $event->actor->name }}
                                        @else
                                            system
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        @if ($event->targetUser)
                                            {{ $event->targetUser->name }}
                                        @elseif (($event->meta['role'] ?? null) && ($event->meta['compliance_area'] ?? null))
                                            {{ $event->meta['role'] }} -> {{ $event->meta['compliance_area'] }}
                                        @else
                                            No direct user target
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 text-xs text-slate-500">
                                    @if ($event->module)
                                        {{ $event->module->title }}
                                    @elseif ($event->meta['module_title'] ?? null)
                                        {{ $event->meta['module_title'] }}
                                    @elseif (($event->meta['role'] ?? null) && ($event->meta['compliance_area'] ?? null))
                                        {{ $event->meta['role'] }} / {{ $event->meta['compliance_area'] }}
                                    @elseif ($event->action === 'reminder_batch_run')
                                        synced {{ $event->meta['synced_total'] ?? 0 }}, sent {{ $event->meta['sent_total'] ?? 0 }}
                                    @elseif (($event->meta['reminder_type'] ?? null) !== null)
                                        {{ $event->meta['reminder_type'] }}
                                    @else
                                        {{ $event->meta['reason'] ?? 'No additional detail' }}
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('app.admin.assignments.audit', ['action' => $event->action]) }}" class="inline-flex rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Open Matching Audit
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">When</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Actor</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Action</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Target</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAuditEvents->take($dashboardAuditPreviewCount) as $event)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        <div>{{ $event->created_at?->format('Y-m-d H:i') }}</div>
                                        @if ($event->created_at)
                                            <div class="mt-1 text-xs text-slate-400">{{ $event->created_at->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        @if ($event->actor)
                                            <a href="{{ route('app.admin.assignments.audit', ['actor' => $event->actor->id]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $event->actor->name }}
                                            </a>
                                        @else
                                            system
                                        @endif
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 shadow-sm">
                                        <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                            {{ str_replace('_', ' ', $event->action) }}
                                        </span>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        @if ($event->targetUser)
                                            <a href="{{ route('app.admin.assignments.audit', ['target' => $event->target_user_id]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $event->targetUser->name }}
                                            </a>
                                        @elseif (($event->meta['role'] ?? null) && ($event->meta['compliance_area'] ?? null))
                                            {{ $event->meta['role'] }} -> {{ $event->meta['compliance_area'] }}
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">
                                        @if ($event->module)
                                            <a href="{{ route('app.admin.assignments.audit', ['module' => $event->module->id]) }}" class="text-indigo-600 hover:text-indigo-500">
                                                {{ $event->module->title }}
                                            </a>
                                        @elseif ($event->meta['module_title'] ?? null)
                                            {{ $event->meta['module_title'] }}
                                        @elseif (($event->meta['role'] ?? null) && ($event->meta['compliance_area'] ?? null))
                                            {{ $event->meta['role'] }} / {{ $event->meta['compliance_area'] }}
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
                                        @else
                                            {{ $event->meta['reason'] ?? 'n/a' }}
                                        @endif
                                        @if ($event->meta['reason'] ?? null)
                                            <div class="text-xs text-gray-400">{{ $event->meta['reason'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-4 text-gray-500">No assignment audit events recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Coverage Map</div>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900">Required Modules by Compliance Area</h3>
                            <p class="mt-1 text-sm text-gray-500">Use this to see where required learning is concentrated.</p>
                            <p class="mt-1 text-xs text-slate-500">Showing the first {{ min($requiredModulesByComplianceArea->count(), $dashboardRequiredAreasPreviewCount) }} of {{ $requiredModulesByComplianceArea->count() }} compliance area{{ $requiredModulesByComplianceArea->count() === 1 ? '' : 's' }}.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                                Preview rows
                            </span>
                            <a href="{{ route('app.admin.compliance') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Open Compliance Report
                            </a>
                        </div>
                    </div>
                </div>
                @if ($requiredModulesByComplianceArea->isNotEmpty())
                    <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-5 md:grid-cols-3">
                        @foreach ($requiredModulesByComplianceArea->take(3) as $row)
                            <div class="rounded-2xl border border-sky-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Coverage Area</div>
                                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $row['compliance_area'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $row['module_count'] }} module{{ $row['module_count'] === 1 ? '' : 's' }}</div>
                                    </div>
                                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">
                                        {{ $row['module_count'] }} required
                                    </span>
                                </div>
                                <div class="mt-4 rounded-xl bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                    {{ $row['modules']->take(2)->join(', ') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="overflow-x-auto px-3 py-3">
                    <table class="min-w-full border-separate border-spacing-y-3 text-sm">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Compliance Area</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Modules</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Titles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requiredModulesByComplianceArea->take($dashboardRequiredAreasPreviewCount) as $row)
                                <tr class="transition hover:-translate-y-0.5">
                                    <td class="rounded-l-2xl border border-r-0 border-slate-200 bg-white px-4 py-4 font-medium text-gray-900 shadow-sm">
                                        <a href="{{ route('app.admin.assignments.compliance-area', ['area' => $row['compliance_area']]) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $row['compliance_area'] }}
                                        </a>
                                    </td>
                                    <td class="border border-r-0 border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['module_count'] }}</td>
                                    <td class="rounded-r-2xl border border-l-0 border-slate-200 bg-white px-4 py-4 text-gray-600 shadow-sm">{{ $row['modules']->join(', ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-4 text-gray-500">No required modules found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>
    <?php if ($currentFocus === 'all'): ?>
        <script src="{{ asset('vendor/learninguiux/js/component/component-chartjs.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const completionLabels = ['Completed', 'In Progress', 'Not Started'];
                const completionData = [
                    {{ (int) $summary['course_completion_completed_count'] }},
                    {{ (int) $summary['course_completion_in_progress_count'] }},
                    {{ (int) $summary['course_completion_not_started_count'] }},
                ];
                const completionColors = ['#6faa00', '#ffc107', '#becede'];

                const barCanvas = document.getElementById('adminAssignmentCompletionChart');
                if (barCanvas && typeof Chart !== 'undefined') {
                    new Chart(barCanvas, {
                        type: 'bar',
                        data: {
                            labels: completionLabels,
                            datasets: [{
                                label: 'Assignments',
                                data: completionData,
                                backgroundColor: completionColors,
                                borderRadius: 10,
                                maxBarThickness: 68,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                    ticks: {
                                        color: '#e2e8f0',
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#cbd5e1',
                                        precision: 0,
                                    },
                                    grid: {
                                        color: 'rgba(226, 232, 240, 0.15)',
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                            },
                        },
                    });
                }

                const doughnutCanvas = document.getElementById('adminAssignmentCompletionDoughnut');
                if (doughnutCanvas && typeof Chart !== 'undefined') {
                    new Chart(doughnutCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: completionLabels,
                            datasets: [{
                                data: completionData,
                                backgroundColor: completionColors,
                                hoverBackgroundColor: completionColors,
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    display: false,
                                },
                            },
                        },
                    });
                }

                const reminderMixCanvas = document.getElementById('adminReminderMixChart');
                if (reminderMixCanvas && typeof Chart !== 'undefined') {
                    new Chart(reminderMixCanvas, {
                        type: 'bar',
                        data: {
                            labels: [
                                'Overdue',
                                'Due Soon',
                                'Inactive',
                                'Not Started',
                            ],
                            datasets: [{
                                label: 'Reminders',
                                data: [
                                    {{ (int) $pendingReminders->where('reminder_type', 'overdue')->count() }},
                                    {{ (int) $pendingReminders->where('reminder_type', 'due_soon')->count() }},
                                    {{ (int) $pendingReminders->where('reminder_type', 'inactive_nudge')->count() }},
                                    {{ (int) $pendingReminders->where('reminder_type', 'not_started_nudge')->count() }},
                                ],
                                backgroundColor: ['#ef4444', '#f59e0b', '#f97316', '#6366f1'],
                                borderRadius: 10,
                                maxBarThickness: 54,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                    ticks: {
                                        color: '#64748b',
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#64748b',
                                        precision: 0,
                                    },
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)',
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                            },
                        },
                    });
                }

                const governanceMixCanvas = document.getElementById('adminGovernanceMixChart');
                if (governanceMixCanvas && typeof Chart !== 'undefined') {
                    new Chart(governanceMixCanvas, {
                        type: 'bar',
                        data: {
                            labels: [
                                @foreach ($waiverByRole->take(4) as $row)
                                    '{{ $row['role'] }}',
                                @endforeach
                            ],
                            datasets: [
                                {
                                    label: 'Waivers',
                                    data: [
                                        @foreach ($waiverByRole->take(4) as $row)
                                            {{ (int) $row['waiver_count'] }},
                                        @endforeach
                                    ],
                                    backgroundColor: '#8b5cf6',
                                    borderRadius: 10,
                                    maxBarThickness: 32,
                                },
                                {
                                    label: 'Acknowledgements',
                                    data: [
                                        @foreach ($waiverByRole->take(4) as $row)
                                            {{ (int) (($acknowledgementsByRole->firstWhere('role', $row['role'])['acknowledgement_count'] ?? 0)) }},
                                        @endforeach
                                    ],
                                    backgroundColor: '#10b981',
                                    borderRadius: 10,
                                    maxBarThickness: 32,
                                }
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#475569',
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#64748b' },
                                    border: { display: false },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: '#64748b', precision: 0 },
                                    grid: { color: 'rgba(148, 163, 184, 0.2)' },
                                    border: { display: false },
                                },
                            },
                        },
                    });
                }

                const governanceAuditCanvas = document.getElementById('adminGovernanceAuditChart');
                if (governanceAuditCanvas && typeof Chart !== 'undefined') {
                    new Chart(governanceAuditCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: [
                                @foreach ($recentAuditEvents->take(4)->groupBy('action') as $action => $events)
                                    '{{ str_replace('_', ' ', $action) }}',
                                @endforeach
                            ],
                            datasets: [{
                                data: [
                                    @foreach ($recentAuditEvents->take(4)->groupBy('action') as $events)
                                        {{ $events->count() }},
                                    @endforeach
                                ],
                                backgroundColor: ['#f43f5e', '#8b5cf6', '#0ea5e9', '#f59e0b'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#475569',
                                    },
                                },
                            },
                        },
                    });
                }

                const operationsUrgencyCanvas = document.getElementById('adminOperationsUrgencyChart');
                if (operationsUrgencyCanvas && typeof Chart !== 'undefined') {
                    new Chart(operationsUrgencyCanvas, {
                        type: 'bar',
                        data: {
                            labels: ['Overdue', 'Due Soon', 'Inactive', 'Waived'],
                            datasets: [{
                                label: 'Assignments',
                                data: [
                                    {{ $focusRows->where('urgency', 'overdue')->count() }},
                                    {{ $focusRows->where('urgency', 'due_soon')->count() }},
                                    {{ $focusRows->whereIn('urgency', ['inactive', 'inactive_nudge'])->count() }},
                                    {{ $focusRows->where('urgency', 'waived')->count() }},
                                ],
                                backgroundColor: ['#ef4444', '#f59e0b', '#f97316', '#94a3b8'],
                                borderRadius: 10,
                                maxBarThickness: 48,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#64748b' },
                                    border: { display: false },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: '#64748b', precision: 0 },
                                    grid: { color: 'rgba(148, 163, 184, 0.2)' },
                                    border: { display: false },
                                },
                            },
                        },
                    });
                }

                const aiProbeCanvas = document.getElementById('adminAiProbeChart');
                if (aiProbeCanvas && typeof Chart !== 'undefined') {
                    new Chart(aiProbeCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Success', 'Failure'],
                            datasets: [{
                                data: [
                                    {{ (int) $summary['ranking_probe_success_count'] }},
                                    {{ (int) $summary['ranking_probe_failure_count'] }},
                                ],
                                backgroundColor: ['#10b981', '#f59e0b'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#475569',
                                    },
                                },
                            },
                        },
                    });
                }
            });
        </script>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dashboardTabs = document.querySelectorAll('[data-admin-dashboard-tab]');
            const dashboardPanels = document.querySelectorAll('[data-admin-dashboard-panel]');

            if (!dashboardTabs.length || !dashboardPanels.length) {
                return;
            }

            const activeTabClasses = ['ring-2', 'ring-sky-300', 'border-sky-300', 'bg-gradient-to-br', 'from-sky-100', 'via-white', 'to-cyan-50', 'text-slate-900', 'shadow-xl', '-translate-y-1', 'scale-[1.01]'];
            const inactiveTabClasses = ['bg-white', 'text-slate-700', 'border-transparent', 'scale-100'];

            function setDashboardTab(panelKey) {
                dashboardPanels.forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.getAttribute('data-admin-dashboard-panel') !== panelKey);
                });

                dashboardTabs.forEach(function (tab) {
                    const isActive = tab.getAttribute('data-admin-dashboard-tab') === panelKey;
                    activeTabClasses.forEach(function (className) {
                        tab.classList.toggle(className, isActive);
                    });
                    inactiveTabClasses.forEach(function (className) {
                        tab.classList.toggle(className, !isActive);
                    });
                });
            }

            dashboardTabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    setDashboardTab(tab.getAttribute('data-admin-dashboard-tab'));
                });
            });

            setDashboardTab('operations');
        });
    </script>
        </div>
    </main>
</div>
@endsection
