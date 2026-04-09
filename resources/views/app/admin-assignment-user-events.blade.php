@extends('layouts.learninguiux')

@section('title', 'Learner Event Timeline - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .event-report-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .event-report-band {
            border-radius: 1.5rem;
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }

        .event-report-summary-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .event-filter-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(203, 213, 225, 0.95);
            padding: 0.55rem 1rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: all 150ms ease;
        }

        .event-filter-control {
            min-height: 3rem;
            border-radius: 1rem !important;
            border-color: rgba(203, 213, 225, 0.95) !important;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: none !important;
        }

        .event-stream-table thead th {
            border-bottom: 1px solid rgba(226, 232, 240, 0.95);
            color: rgb(37 99 235);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .event-stream-table tbody tr {
            transition: background-color 150ms ease;
        }

        .event-stream-table tbody tr:hover {
            background: rgba(248, 250, 252, 0.88);
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
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Reporting Evidence</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">{{ __('Learner Event Timeline') }}</h1>
                        <p class="mt-3 text-base text-slate-600">{{ $learner->name }} | {{ $learner->email }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('app.admin.assignments.user', ['user' => $learner->id]) }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">
                            Back to Learner
                        </a>
                        <a href="{{ route('app.admin.compliance.learners', ['source_type' => 'scorm']) }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">
                            Learner Matrix
                        </a>
                        <a href="{{ route('app.admin.assignments.user.events.export', array_filter([
                            'user' => $learner->id,
                            'event_type' => $filters['event_type'] ?? null,
                            'entity_type' => $filters['entity_type'] ?? null,
                            'from' => $filters['from'] ?? null,
                            'to' => $filters['to'] ?? null,
                        ])) }}" class="admin-feed-action inline-flex items-center bg-sky-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-white shadow-sm transition hover:bg-sky-700">
                            Export CSV
                        </a>
                    </div>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="event-report-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4">
                            <i class="bi bi-list-ul fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['rows'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Rows</div>
                        <div class="mt-2 text-sm text-slate-600">Events currently in this timeline</div>
                        <div class="mt-auto pt-3 small text-secondary">Scope</div>
                    </div>
                </div>
                <div class="event-report-card admin-feed-kpi p-5 transition hover:border-sky-300 hover:bg-sky-50/80">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#0369a1;background:linear-gradient(135deg, rgba(224, 242, 254, 0.96), rgba(219, 234, 254, 0.96));">
                            <i class="bi bi-box-arrow-up-right fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['scorm_launches'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">SCORM Launches</div>
                        <div class="mt-2 text-sm text-slate-600">Open-package events recorded</div>
                        <div class="mt-auto pt-3 small text-secondary">Launch</div>
                    </div>
                </div>
                <div class="event-report-card admin-feed-kpi p-5 transition hover:border-indigo-300 hover:bg-indigo-50/80">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#4338ca;background:linear-gradient(135deg, rgba(224, 231, 255, 0.96), rgba(238, 242, 255, 0.96));">
                            <i class="bi bi-arrow-repeat fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['scorm_runtime_commits'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Runtime Commits</div>
                        <div class="mt-2 text-sm text-slate-600">Progress and lesson-location writes</div>
                        <div class="mt-auto pt-3 small text-secondary">Runtime</div>
                    </div>
                </div>
                <div class="event-report-card admin-feed-kpi p-5 transition hover:border-violet-300 hover:bg-violet-50/80">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#7c3aed;background:linear-gradient(135deg, rgba(237, 233, 254, 0.96), rgba(243, 232, 255, 0.96));">
                            <i class="bi bi-check2-square fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['reinforcement_completed'] ?? 0 }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Reinforcement Completed</div>
                        <div class="mt-2 text-sm text-slate-600">Successful follow-up checks recorded</div>
                        <div class="mt-auto pt-3 small text-secondary">Follow-up</div>
                    </div>
                </div>
                <div class="event-report-card admin-feed-kpi p-5 transition hover:border-rose-300 hover:bg-rose-50/80">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#e11d48;background:linear-gradient(135deg, rgba(255, 228, 230, 0.98), rgba(254, 226, 226, 0.98));">
                            <i class="bi bi-x-octagon fs-3"></i>
                        </div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['reinforcement_failed'] ?? 0 }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Reinforcement Failed</div>
                        <div class="mt-2 text-sm text-slate-600">Retries and remediation evidence</div>
                        <div class="mt-auto pt-3 small text-secondary">Proof</div>
                    </div>
                </div>
            </div>

            @if ($latestCompletedScormProof || $latestScormRuntimeProof || $latestScormLaunchProof || $latestReinforcementProof || $latestReinforcementFailure)
                <div class="event-report-card overflow-hidden border-emerald-200 bg-emerald-50/90">
                    <div class="event-report-band border-b border-emerald-200 px-5 py-4">
                        <h3 class="text-lg font-semibold text-emerald-900">Latest Learning Proof</h3>
                        <p class="mt-1 text-sm text-emerald-700">Use this proof-first strip before dropping into the full raw event stream.</p>
                    </div>
                    <div class="grid gap-4 px-5 py-4 lg:grid-cols-3">
                        @if ($latestCompletedScormProof)
                            <div class="rounded-[1.4rem] border border-emerald-200 bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Completion Proof</div>
                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-800">
                                        {{ ucfirst(str_replace('_', ' ', (string) ($latestCompletedScormProof['status'] ?? 'completed'))) }}
                                    </span>
                                </div>
                                <div class="mt-3 text-sm font-semibold text-slate-900">{{ $latestCompletedScormProof['module_title'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $latestCompletedScormProof['when']?->format('Y-m-d H:i:s') ?? 'n/a' }}</div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Progress</div>
                                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ $latestCompletedScormProof['percent_complete'] !== null ? $latestCompletedScormProof['percent_complete'].'%' : 'n/a' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Score</div>
                                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ $latestCompletedScormProof['score_raw'] ?? 'n/a' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Session</div>
                                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ $latestCompletedScormProof['session_label'] }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Location</div>
                                        <div class="mt-1 text-xs font-semibold text-slate-900 break-all">{{ $latestCompletedScormProof['lesson_location'] ?? 'n/a' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($latestScormRuntimeProof)
                            <div class="rounded-[1.4rem] border border-sky-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Latest Runtime Commit</div>
                                <div class="mt-3 text-sm font-semibold text-slate-900">{{ $latestScormRuntimeProof['module_title'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $latestScormRuntimeProof['when']?->format('Y-m-d H:i:s') ?? 'n/a' }}</div>
                                <div class="mt-4 space-y-2 text-xs text-slate-600">
                                    <div><span class="font-semibold text-slate-900">Status:</span> {{ ucfirst(str_replace('_', ' ', (string) ($latestScormRuntimeProof['status'] ?? 'n/a'))) }}</div>
                                    <div><span class="font-semibold text-slate-900">Progress:</span> {{ $latestScormRuntimeProof['percent_complete'] !== null ? $latestScormRuntimeProof['percent_complete'].'%' : 'n/a' }}</div>
                                    <div><span class="font-semibold text-slate-900">Score:</span> {{ $latestScormRuntimeProof['score_raw'] ?? 'n/a' }}</div>
                                    <div><span class="font-semibold text-slate-900">Session:</span> {{ $latestScormRuntimeProof['session_label'] }}</div>
                                    <div class="break-all"><span class="font-semibold text-slate-900">Location:</span> {{ $latestScormRuntimeProof['lesson_location'] ?? 'n/a' }}</div>
                                </div>
                            </div>
                        @endif

                        @if ($latestScormLaunchProof)
                            <div class="rounded-[1.4rem] border border-indigo-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700">Latest Launch Event</div>
                                <div class="mt-3 text-sm font-semibold text-slate-900">{{ $latestScormLaunchProof['module_title'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $latestScormLaunchProof['when']?->format('Y-m-d H:i:s') ?? 'n/a' }}</div>
                                <div class="mt-4 space-y-2 text-xs text-slate-600">
                                    <div><span class="font-semibold text-slate-900">Asset:</span> {{ $latestScormLaunchProof['asset_id'] ?? 'n/a' }}</div>
                                    <div class="break-all"><span class="font-semibold text-slate-900">Launch path:</span> {{ $latestScormLaunchProof['launch_path'] ?? 'n/a' }}</div>
                                </div>
                                @if ($latestScormLaunchProof['module_id'])
                                    <div class="mt-4">
                                        <a href="{{ route('app.admin.modules.edit', ['module' => $latestScormLaunchProof['module_id']]) }}" class="rounded-full border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Module admin
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($latestReinforcementProof)
                            <div class="rounded-[1.4rem] border border-violet-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-700">Latest Reinforcement Proof</div>
                                <div class="mt-3 text-sm font-semibold text-slate-900">{{ $latestReinforcementProof['module_title'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $latestReinforcementProof['completed_at']?->format('Y-m-d H:i:s') ?? 'n/a' }}</div>
                                <div class="mt-4 space-y-2 text-xs text-slate-600">
                                    <div><span class="font-semibold text-slate-900">Touchpoint:</span> {{ $latestReinforcementProof['interval_days'] }}-day {{ $latestReinforcementProof['proof_type'] }}</div>
                                    <div><span class="font-semibold text-slate-900">Due:</span> {{ $latestReinforcementProof['due_on']?->format('Y-m-d') ?? 'n/a' }}</div>
                                    <div><span class="font-semibold text-slate-900">Proof:</span> {{ $latestReinforcementProof['proof_summary'] }}</div>
                                </div>
                            </div>
                        @endif

                        @if ($latestReinforcementFailure)
                            <div class="rounded-[1.4rem] border border-rose-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Latest Reinforcement Retry</div>
                                <div class="mt-3 text-sm font-semibold text-slate-900">{{ $latestReinforcementFailure['module_title'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $latestReinforcementFailure['updated_at']?->format('Y-m-d H:i:s') ?? 'n/a' }}</div>
                                <div class="mt-4 space-y-2 text-xs text-slate-600">
                                    <div><span class="font-semibold text-slate-900">Incorrect:</span> {{ $latestReinforcementFailure['incorrect_count'] }}</div>
                                    <div><span class="font-semibold text-slate-900">Remediation:</span> {{ $latestReinforcementFailure['remediation_count'] }} assigned</div>
                                    @if(($latestReinforcementFailure['remediation_titles'] ?? collect())->isNotEmpty())
                                        <div><span class="font-semibold text-slate-900">Modules:</span> {{ $latestReinforcementFailure['remediation_titles']->join(', ') }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="event-report-card overflow-hidden">
                <div class="event-report-band border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Timeline Filters</h3>
                    <p class="mt-1 text-sm text-slate-600">Narrow the evidence trail to launches, runtime commits, reinforcement follow-up, or a specific date window.</p>
                </div>
                <div class="space-y-5 p-5">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('app.admin.assignments.user.events', array_filter([
                        'user' => $learner->id,
                        'entity_type' => $filters['entity_type'] ?? null,
                        'from' => $filters['from'] ?? null,
                        'to' => $filters['to'] ?? null,
                    ])) }}" class="event-filter-pill {{ empty($filters['event_type']) ? 'border-slate-900 bg-slate-900 text-white shadow-sm' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                        All Events
                    </a>
                    <a href="{{ route('app.admin.assignments.user.events', array_filter([
                        'user' => $learner->id,
                        'event_type' => 'scorm_launched',
                        'entity_type' => 'learning_module',
                        'from' => $filters['from'] ?? null,
                        'to' => $filters['to'] ?? null,
                    ])) }}" class="event-filter-pill {{ ($filters['event_type'] ?? null) === 'scorm_launched' ? 'border-sky-700 bg-sky-700 text-white shadow-sm' : 'border-sky-300 bg-white text-sky-700 hover:bg-sky-50' }}">
                        SCORM Launches
                    </a>
                    <a href="{{ route('app.admin.assignments.user.events', array_filter([
                        'user' => $learner->id,
                        'event_type' => 'scorm_runtime_committed',
                        'entity_type' => 'learning_module',
                        'from' => $filters['from'] ?? null,
                        'to' => $filters['to'] ?? null,
                    ])) }}" class="event-filter-pill {{ ($filters['event_type'] ?? null) === 'scorm_runtime_committed' ? 'border-indigo-700 bg-indigo-700 text-white shadow-sm' : 'border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50' }}">
                        SCORM Runtime
                    </a>
                    <a href="{{ route('app.admin.assignments.user.events', array_filter([
                        'user' => $learner->id,
                        'event_type' => 'reinforcement_failed',
                        'from' => $filters['from'] ?? null,
                        'to' => $filters['to'] ?? null,
                    ])) }}" class="event-filter-pill {{ ($filters['event_type'] ?? null) === 'reinforcement_failed' ? 'border-rose-700 bg-rose-700 text-white shadow-sm' : 'border-rose-300 bg-white text-rose-700 hover:bg-rose-50' }}">
                        Reinforcement Failed
                    </a>
                    <a href="{{ route('app.admin.assignments.user.events', array_filter([
                        'user' => $learner->id,
                        'event_type' => 'reinforcement_completed',
                        'from' => $filters['from'] ?? null,
                        'to' => $filters['to'] ?? null,
                    ])) }}" class="event-filter-pill {{ ($filters['event_type'] ?? null) === 'reinforcement_completed' ? 'border-violet-700 bg-violet-700 text-white shadow-sm' : 'border-violet-300 bg-white text-violet-700 hover:bg-violet-50' }}">
                        Reinforcement Completed
                    </a>
                </div>
                <form method="GET" action="{{ route('app.admin.assignments.user.events', ['user' => $learner->id]) }}" class="grid gap-4 rounded-[1.35rem] border border-slate-200 bg-slate-50/80 p-4 md:grid-cols-5 md:items-end">
                    <div>
                        <label for="event_type" class="block text-sm font-medium text-slate-700">Event Type</label>
                        <select id="event_type" name="event_type" class="event-filter-control mt-1 block w-full text-sm">
                            <option value="">All types</option>
                            @foreach ($eventTypes as $eventType)
                                <option value="{{ $eventType }}" @selected(($filters['event_type'] ?? null) === $eventType)>{{ $eventType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="entity_type" class="block text-sm font-medium text-slate-700">Entity Type</label>
                        <select id="entity_type" name="entity_type" class="event-filter-control mt-1 block w-full text-sm">
                            <option value="">All entities</option>
                            @foreach (['learning_module', 'learning_path', 'user_preference'] as $entityType)
                                <option value="{{ $entityType }}" @selected(($filters['entity_type'] ?? null) === $entityType)>{{ $entityType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="from" class="block text-sm font-medium text-slate-700">From</label>
                        <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="event-filter-control mt-1 block w-full text-sm">
                    </div>
                    <div>
                        <label for="to" class="block text-sm font-medium text-slate-700">To</label>
                        <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="event-filter-control mt-1 block w-full text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="admin-feed-action inline-flex items-center bg-sky-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-white shadow-sm transition hover:bg-sky-700">Apply</button>
                        <a href="{{ route('app.admin.assignments.user.events', ['user' => $learner->id]) }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Reset</a>
                    </div>
                </form>
                </div>
            </div>

            <div class="event-report-card overflow-hidden">
                <div class="event-report-band border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Learner Event Stream</h3>
                    <p class="mt-1 text-sm text-slate-600">Use this page to show the exact launch and runtime evidence created by learner activity.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="event-stream-table min-w-full text-sm">
                        <thead class="bg-slate-50/90">
                            <tr>
                                <th class="px-5 py-3 text-left">When</th>
                                <th class="px-5 py-3 text-left">Event</th>
                                <th class="px-5 py-3 text-left">Entity</th>
                                <th class="px-5 py-3 text-left">Metadata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($events as $event)
                                <tr>
                                    <td class="px-5 py-4 text-slate-600">{{ $event->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <span class="font-medium text-slate-900">{{ $event->event_type }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        <div class="font-medium text-slate-900">{{ $event->entity_type }} #{{ $event->entity_id }}</div>
                                        @if ($event->entity_type === 'learning_module')
                                            @php($moduleTitle = $moduleTitles[(int) $event->entity_id] ?? null)
                                            @if ($moduleTitle)
                                                <div class="mt-1 text-xs text-slate-500">{{ $moduleTitle }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $eventMetadataSummary[$event->id] ?? json_encode($event->metadata ?? []) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-5 text-slate-500">No events found for this learner.</td>
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
