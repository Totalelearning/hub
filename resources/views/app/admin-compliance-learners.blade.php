@extends('layouts.learninguiux')

@section('title', 'Compliance Learner Matrix - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .compliance-matrix-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .compliance-matrix-band {
            border-radius: 1.4rem;
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }

        .compliance-progress-ring {
            --progress: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 9rem;
            height: 9rem;
            border-radius: 9999px;
            background:
                radial-gradient(closest-side, rgba(255, 255, 255, 1) 78%, transparent 79% 100%),
                conic-gradient(#38b86f calc(var(--progress) * 1%), #e5e7eb 0);
        }

        .matrix-filter-pill {
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(248, 250, 252, 0.98);
        }

        .matrix-summary-card {
            border-radius: 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .matrix-result-action {
            border-radius: 0.8rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(248, 250, 252, 0.98);
            color: #475569;
            box-shadow: 0 10px 24px rgba(43, 82, 138, 0.08);
        }

        .matrix-summary-table thead th {
            background: #fff;
            color: #94a3b8;
            font-weight: 600;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        }

        .matrix-summary-table tbody tr {
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            transition: background-color 160ms ease;
        }

        .matrix-summary-table tbody tr:last-child {
            border-bottom: 0;
        }

        .matrix-summary-table tbody tr.is-selected {
            background: rgba(248, 250, 252, 0.95);
        }

        .matrix-summary-table tbody tr:hover {
            background: rgba(248, 250, 252, 0.72);
        }

        .matrix-toolbar-search {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: min(100%, 22rem);
            padding: 0.8rem 1rem;
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        }

        .matrix-toolbar-search i {
            color: #64748b;
        }

        .matrix-toolbar-search input {
            width: 100%;
            border: 0;
            background: transparent;
            color: #0f172a;
            outline: none;
            box-shadow: none;
        }

        .matrix-toolbar-select {
            min-width: 11rem;
            padding: 0.8rem 1rem;
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            color: #334155;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
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
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Compliance Reporting</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">Learner Progress</h1>
                        <span class="sr-only">{{ __('Compliance Learner Matrix') }}</span>
                        <p class="mt-3 max-w-3xl text-base text-slate-600">Learner-level progress reporting with assignment status, completion, and follow-up evidence.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('app.admin.compliance', array_filter([
                            'role' => $filters['role'] ?? null,
                            'compliance_area' => $filters['compliance_area'] ?? null,
                            'status' => $filters['status'] ?? null,
                            'source_type' => $filters['source_type'] ?? null,
                        ])) }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Summary Report</a>
                        <a href="{{ route('app.admin.compliance.learners.export', array_filter([
                            'role' => $filters['role'] ?? null,
                            'compliance_area' => $filters['compliance_area'] ?? null,
                            'status' => $filters['status'] ?? null,
                            'source_type' => $filters['source_type'] ?? null,
                        ])) }}" class="admin-feed-action inline-flex items-center bg-sky-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-white shadow-sm transition hover:bg-sky-700">Export CSV</a>
                    </div>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            @php
                $matrixCompleted = (int) ($summary['completed'] ?? 0);
                $matrixInProgress = (int) ($summary['in_progress'] ?? 0);
                $matrixOverdue = (int) ($summary['overdue'] ?? 0);
                $matrixDueSoon = (int) ($summary['due_soon'] ?? 0);
                $matrixWaived = (int) ($summary['waived'] ?? 0);
                $matrixFailed = (int) ($summary['reinforcement_failed'] ?? 0);
                $matrixRows = max(0, (int) ($summary['rows'] ?? 0));
                $matrixNotStarted = max(0, $matrixRows - ($matrixCompleted + $matrixInProgress + $matrixOverdue + $matrixDueSoon + $matrixWaived));
                $matrixCompletionRate = $matrixRows > 0 ? (int) round(($matrixCompleted / $matrixRows) * 100) : 0;
                $learnerSummaryRows = collect($rows ?? [])
                    ->groupBy('user_id')
                    ->map(function ($learnerRows) {
                        $first = $learnerRows->first();
                        $total = max(1, $learnerRows->count());
                        $completed = $learnerRows->where('status_key', 'completed')->count();
                        $inProgress = $learnerRows->where('status_key', 'in_progress')->count();
                        $overdue = $learnerRows->where('status_key', 'overdue')->count();

                        return [
                            'user_id' => $first['user_id'],
                            'learner_name' => $first['learner_name'],
                            'learner_email' => $first['learner_email'],
                            'role' => $first['role'],
                            'trained_percent' => (int) round(($completed / $total) * 100),
                            'enrollments' => $total,
                            'completed' => $completed,
                            'in_progress' => $inProgress,
                            'overdue' => $overdue,
                        ];
                    })
                    ->sortByDesc(fn (array $row) => [$row['trained_percent'], $row['completed'], -$row['in_progress']])
                    ->values();
                $selectedLearner = $learnerSummaryRows->first();
                $selectedLearnerUserId = $selectedLearner['user_id'] ?? null;
                $selectedLearnerEventsHref = $selectedLearnerUserId
                    ? route('app.admin.assignments.user.events', ['user' => $selectedLearnerUserId])
                    : route('app.admin.assignments');
                $selectedLearnerDetailHref = $selectedLearnerUserId
                    ? route('app.admin.assignments.user', ['user' => $selectedLearnerUserId])
                    : route('app.admin.assignments');
                $selectedLearnerEditHref = $selectedLearnerUserId
                    ? route('app.admin.users.show', ['user' => $selectedLearnerUserId])
                    : route('app.admin.users.index');
            @endphp

            <div class="space-y-5" x-data='{
                selectedUserId: @json($selectedLearner['user_id'] ?? null),
                selectedName: @json($selectedLearner['learner_name'] ?? "No learner"),
                selectedRole: @json($selectedLearner['role'] ?? "n/a"),
                trainedPercent: @json($selectedLearner['trained_percent'] ?? $matrixCompletionRate),
                enrollments: @json($selectedLearner['enrollments'] ?? 0),
                completed: @json($selectedLearner['completed'] ?? 0),
                inProgress: @json($selectedLearner['in_progress'] ?? 0),
                overdue: @json($selectedLearner['overdue'] ?? 0),
                detailHref: @json($selectedLearnerDetailHref),
                eventsHref: @json($selectedLearnerEventsHref),
                editHref: @json($selectedLearnerEditHref),
                searchQuery: "",
                roleFilter: "all",
                progressFilter: "all",
                matchesLearner(name, role, trainedPercent, inProgress, overdue) {
                    const query = this.searchQuery.toLowerCase().trim();
                    const normalizedName = String(name).toLowerCase();
                    const normalizedRole = String(role).toLowerCase();
                    const matchesSearch = !query || normalizedName.includes(query) || normalizedRole.includes(query);
                    const matchesRole = this.roleFilter === "all" || normalizedRole === this.roleFilter;
                    const matchesProgress = this.progressFilter === "all"
                        || (this.progressFilter === "complete" && Number(trainedPercent) >= 100)
                        || (this.progressFilter === "in_progress" && Number(inProgress) > 0)
                        || (this.progressFilter === "overdue" && Number(overdue) > 0);

                    return matchesSearch && matchesRole && matchesProgress;
                }
            }'>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('app.admin.compliance', array_filter([
                            'role' => $filters['role'] ?? null,
                            'compliance_area' => $filters['compliance_area'] ?? null,
                            'status' => $filters['status'] ?? null,
                            'source_type' => $filters['source_type'] ?? null,
                        ])) }}" class="matrix-result-action inline-flex h-11 w-11 items-center justify-center text-slate-600 transition hover:bg-white hover:text-slate-900">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h2 class="text-2xl font-semibold text-slate-900">Learner Progress</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('app.admin.compliance.learners.export', array_filter([
                            'role' => $filters['role'] ?? null,
                            'compliance_area' => $filters['compliance_area'] ?? null,
                            'status' => $filters['status'] ?? null,
                            'source_type' => $filters['source_type'] ?? null,
                        ])) }}" class="inline-flex items-center rounded-[0.95rem] border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white">
                            <i class="bi bi-box-arrow-up-right mr-2"></i> Export
                        </a>
                    </div>
                </div>

                <div class="compliance-matrix-card overflow-hidden">
                    <div class="compliance-matrix-band border-b border-gray-200 px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-700">Selected Learner</div>
                                <h3 class="mt-2 text-lg font-semibold text-slate-900" x-text="selectedName"></h3>
                                <p class="mt-1 text-sm text-slate-600" x-text="selectedRole"></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a :href="editHref" class="inline-flex items-center rounded-[0.95rem] border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                    Edit user
                                </a>
                                <a :href="detailHref" class="inline-flex items-center rounded-[0.95rem] border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                    Assignment detail
                                </a>
                                <a :href="eventsHref" class="inline-flex items-center rounded-[0.95rem] border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                    Event timeline
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 px-5 py-5 md:grid-cols-2 xl:grid-cols-5">
                        <div class="rounded-[1.2rem] border border-slate-200 bg-white px-4 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Trained</div>
                            <div class="mt-2 text-3xl font-semibold text-slate-900"><span x-text="trainedPercent"></span>%</div>
                        </div>
                        <div class="rounded-[1.2rem] border border-slate-200 bg-white px-4 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Enrollments</div>
                            <div class="mt-2 text-3xl font-semibold text-slate-900" x-text="enrollments"></div>
                        </div>
                        <div class="rounded-[1.2rem] border border-slate-200 bg-white px-4 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Completed</div>
                            <div class="mt-2 text-3xl font-semibold text-slate-900" x-text="completed"></div>
                        </div>
                        <div class="rounded-[1.2rem] border border-slate-200 bg-white px-4 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">In Progress</div>
                            <div class="mt-2 text-3xl font-semibold text-slate-900" x-text="inProgress"></div>
                        </div>
                        <div class="rounded-[1.2rem] border border-slate-200 bg-white px-4 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Overdue</div>
                            <div class="mt-2 text-3xl font-semibold text-slate-900" x-text="overdue"></div>
                        </div>
                    </div>
                </div>

                @if (($filters['source_type'] ?? null) === 'scorm')
                    <div class="sr-only">
                        <span>Source Type</span>
                        <span>SCORM Rows</span>
                    </div>
                @endif

            <div class="matrix-summary-card overflow-hidden">
                <div class="compliance-matrix-band border-b border-gray-200 px-5 py-4">
                    <div class="flex flex-col gap-4">
                        <div>
                            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">Student Attendance Statistics</h3>
                                <div class="flex flex-col gap-3 lg:flex-row">
                                    <label class="matrix-toolbar-search">
                                        <i class="bi bi-search"></i>
                                        <input type="search" x-model.debounce.200ms="searchQuery" placeholder="Search here...">
                                    </label>
                                    <select x-model="roleFilter" class="matrix-toolbar-select text-sm">
                                        <option value="all">All roles</option>
                                        @foreach ($availableRoles as $role)
                                            <option value="{{ strtolower($role) }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    <select x-model="progressFilter" class="matrix-toolbar-select text-sm">
                                        <option value="all">All progress</option>
                                        <option value="complete">Complete</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="matrix-summary-table min-w-full text-sm">
                        <thead>
                            <tr>
                                <th class="w-12 px-5 py-3 text-left font-medium text-gray-400">
                                    <input type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                </th>
                                <th class="px-5 py-3 text-left font-medium text-gray-600">Full Name</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-400">Department</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-600">Trained</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-400">Enrollments</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-400">Completed</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-400">In Progress</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse ($learnerSummaryRows as $index => $row)
                                <tr
                                    class="{{ $index === 0 ? 'is-selected' : '' }}"
                                    :class="{ 'is-selected': selectedUserId === {{ (int) $row['user_id'] }} }"
                                    x-show="matchesLearner(@js($row['learner_name']), @js($row['role']), {{ (int) $row['trained_percent'] }}, {{ (int) $row['in_progress'] }}, {{ (int) $row['overdue'] }})"
                                >
                                    <td class="px-5 py-3 text-gray-600">
                                        <input
                                            type="radio"
                                            name="selected_learner"
                                            value="{{ $row['user_id'] }}"
                                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                            @checked($index === 0)
                                            @click="
                                                selectedUserId = {{ (int) $row['user_id'] }};
                                                selectedName = @js($row['learner_name']);
                                                selectedRole = @js($row['role']);
                                                trainedPercent = {{ (int) $row['trained_percent'] }};
                                                enrollments = {{ (int) $row['enrollments'] }};
                                                completed = {{ (int) $row['completed'] }};
                                                inProgress = {{ (int) $row['in_progress'] }};
                                                overdue = {{ (int) $row['overdue'] }};
                                                detailHref = @js(route('app.admin.assignments.user', ['user' => $row['user_id']]));
                                                eventsHref = @js(route('app.admin.assignments.user.events', ['user' => $row['user_id']]));
                                                editHref = @js(route('app.admin.users.show', ['user' => $row['user_id']]));
                                            "
                                        >
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">
                                        <a href="{{ route('app.admin.users.edit', ['user' => $row['user_id']]) }}" class="font-semibold text-slate-900 transition hover:text-sky-700 hover:underline">
                                            {{ $row['learner_name'] }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['role'] }}</td>
                                    <td class="px-5 py-3 font-medium text-slate-900">{{ $row['trained_percent'] }}%</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['enrollments'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['completed'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['in_progress'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-4 text-gray-500">No learner summary rows found for the current filter.</td>
                                </tr>
                            @endforelse
                            <tr x-show="!Array.from($el.parentElement.querySelectorAll('tbody tr[x-show]')).some((row) => row.offsetParent !== null)">
                                <td colspan="7" class="px-5 py-4 text-gray-500">No learners match the current search or filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

            <div class="compliance-matrix-card overflow-hidden">
                <div class="compliance-matrix-band border-b border-gray-200 px-5 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Detailed Learner Assignment Matrix</div>
                            <p class="mt-1 text-sm text-slate-600">Audit-ready learner rows with compliance, SCORM score/session evidence, and urgency state.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('app.admin.compliance', array_filter([
                                'role' => $filters['role'] ?? null,
                                'compliance_area' => $filters['compliance_area'] ?? null,
                                'status' => $filters['status'] ?? null,
                                'source_type' => $filters['source_type'] ?? null,
                            ])) }}" class="inline-flex items-center rounded-[0.95rem] border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                                Learner Results
                            </a>
                            <a href="{{ route('app.admin.compliance.learners.export', array_filter([
                                'role' => $filters['role'] ?? null,
                                'compliance_area' => $filters['compliance_area'] ?? null,
                                'status' => $filters['status'] ?? null,
                                'source_type' => $filters['source_type'] ?? null,
                            ])) }}" class="inline-flex items-center rounded-[0.95rem] border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                                Export Matrix
                            </a>
                            <a href="{{ route('app.admin.assignments') }}" class="inline-flex items-center rounded-[0.95rem] border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                                Edit User
                            </a>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Learner</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Role</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Module</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Source</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">SCORM Status</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Score</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Session</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Area</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Urgency</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Progress</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Due On</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Acknowledgement</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['learner_name'] }}<div class="text-xs text-gray-400">{{ $row['learner_email'] }}</div></td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['role'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['module_title'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['source_type'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($row['source_type'] === 'scorm')
                                            <div>
                                                <span class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-800">
                                                    {{ $row['scorm_status'] ?? 'n/a' }}
                                                </span>
                                            </div>
                                            @if (!empty($row['scorm_runtime_at']))
                                                <div class="text-xs text-gray-400">{{ $row['scorm_runtime_at']->format('Y-m-d H:i') }}</div>
                                            @endif
                                            @if (!empty($row['scorm_lesson_location']))
                                                <div class="text-xs text-gray-400 break-all">{{ $row['scorm_lesson_location'] }}</div>
                                            @endif
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['scorm_score_raw'] ?? 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['scorm_session_label'] ?? 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['compliance_area'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ str_replace('_', ' ', $row['status_key']) }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ str_replace('_', ' ', $row['urgency']) }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        <div class="font-medium text-slate-900">{{ $row['progress_status'] }} | {{ $row['percent_complete'] ?? 0 }}%</div>
                                        @if ($row['source_type'] === 'scorm')
                                            <div class="text-xs text-sky-700">SCORM {{ $row['scorm_percent_complete'] ?? 'n/a' }}%</div>
                                        @endif
                                        @if (!empty($row['completed_at']))
                                            <div class="text-xs text-gray-400">{{ $row['completed_at']->format('Y-m-d H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['due_on']?->toDateString() ?? 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($row['requires_acknowledgement'])
                                            {{ $row['is_acknowledged'] ? 'acknowledged' : 'pending' }}
                                        @else
                                            n/a
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('app.admin.assignments.user', ['user' => $row['user_id']]) }}" class="rounded border border-gray-300 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Detail</a>
                                            @if ($row['source_type'] === 'scorm')
                                                <a href="{{ route('app.admin.assignments.user.events', ['user' => $row['user_id'], 'event_type' => 'scorm_runtime_committed', 'entity_type' => 'learning_module']) }}" class="rounded border border-gray-300 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Events</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="px-5 py-4 text-gray-500">No learner compliance rows found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (!empty($latestScormProof) || !empty($latestReinforcementProof) || !empty($latestReinforcementFailure))
                <div class="grid gap-4 xl:grid-cols-3">
                    @if (!empty($latestScormProof))
                        <div class="compliance-matrix-card overflow-hidden border-emerald-200 bg-emerald-50/90">
                            <div class="compliance-matrix-band border-b border-emerald-200 px-5 py-4">
                                <h3 class="text-lg font-semibold text-emerald-900">Latest Verified SCORM Proof</h3>
                            </div>
                            <div class="space-y-3 px-5 py-4 text-sm text-slate-700">
                                <div class="font-semibold text-slate-900">{{ $latestScormProof['learner_name'] }}</div>
                                <div>{{ $latestScormProof['module_title'] }}</div>
                                <div>{{ ucfirst(str_replace('_', ' ', (string) ($latestScormProof['scorm_status'] ?? 'n/a'))) }} | {{ $latestScormProof['scorm_percent_complete'] ?? 'n/a' }}%</div>
                                <div>Score {{ $latestScormProof['scorm_score_raw'] ?? 'n/a' }} | {{ $latestScormProof['scorm_session_label'] ?? 'n/a' }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($latestReinforcementProof))
                        <div class="compliance-matrix-card overflow-hidden border-violet-200 bg-violet-50/90">
                            <div class="compliance-matrix-band border-b border-violet-200 px-5 py-4">
                                <h3 class="text-lg font-semibold text-violet-900">Latest Reinforcement Proof</h3>
                            </div>
                            <div class="space-y-3 px-5 py-4 text-sm text-slate-700">
                                <div class="font-semibold text-slate-900">{{ $latestReinforcementProof['learner_name'] }}</div>
                                <div>{{ $latestReinforcementProof['module_title'] }}</div>
                                <div>{{ $latestReinforcementProof['interval_days'] }}-day follow-up</div>
                                <div>{{ $latestReinforcementProof['proof_summary'] }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($latestReinforcementFailure))
                        <div class="compliance-matrix-card overflow-hidden border-rose-200 bg-rose-50/90">
                            <div class="compliance-matrix-band border-b border-rose-200 px-5 py-4">
                                <h3 class="text-lg font-semibold text-rose-900">Latest Reinforcement Failure</h3>
                            </div>
                            <div class="space-y-3 px-5 py-4 text-sm text-slate-700">
                                <div class="font-semibold text-slate-900">{{ $latestReinforcementFailure['learner_name'] }}</div>
                                <div>{{ $latestReinforcementFailure['module_title'] }}</div>
                                <div>{{ $latestReinforcementFailure['proof_summary'] }}</div>
                                <div>{{ $latestReinforcementFailure['remediation_count'] }} remediation assigned</div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
        </div>
    </main>
</div>
@endsection
