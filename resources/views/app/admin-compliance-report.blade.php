@extends('layouts.learninguiux')

@section('title', 'Compliance Report - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .compliance-mini-ring {
            --progress: 0;
            --ring-color: #38b86f;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 7rem;
            height: 7rem;
            border-radius: 9999px;
            background: conic-gradient(var(--ring-color) calc(var(--progress) * 1%), #e5e7eb 0);
        }

        .compliance-mini-ring__inner {
            display: flex;
            height: 5.2rem;
            width: 5.2rem;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: #fff;
            text-align: center;
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
                    <div class="col-12 col-lg-8 admin-feed-hero-copy mb-3 mb-lg-0">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Compliance Reports</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ __('Compliance Report') }}</h1>
                        <p class="text-secondary mb-0">Course completion rates, knowledge check results, and learning path progress across all published courses.</p>
                    </div>
                    <div class="col-12 col-lg-4 d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('app.admin.compliance.learners', array_filter([
                            'role' => $filters['role'] ?? null,
                            'team' => $filters['team'] ?? null,
                            'location' => $filters['location'] ?? null,
                            'status' => $filters['status'] ?? null,
                        ])) }}" class="btn btn-outline-theme">Learner Matrix</a>
                        <a href="{{ route('app.admin.compliance.export', array_filter([
                            'role' => $filters['role'] ?? null,
                            'team' => $filters['team'] ?? null,
                            'location' => $filters['location'] ?? null,
                            'status' => $filters['status'] ?? null,
                        ])) }}" class="btn btn-theme">Export CSV</a>
                    </div>
                </div>
            </div>

            @php
                $activeFilterMap = collect([
                    'Location' => $filters['location'] ?? null,
                    'Role' => $filters['role'] ?? null,
                    'Team' => $filters['team'] ?? null,
                    'Status' => $filters['status'] ?? null,
                ])->filter(fn ($value) => filled($value));

                $hasActiveFilters = $activeFilterMap->isNotEmpty();
                $totalEnrollments = $summary['total_enrollments'] ?? 0;
                $completionRate = $totalEnrollments > 0
                    ? (int) round((($summary['completed'] ?? 0) / max(1, $totalEnrollments)) * 100)
                    : 0;

                $teamCount = ($byTeam ?? collect())->count();
                $fullyCompleteTeamCount = collect($byTeam ?? [])->filter(fn ($row) => ($row['total_enrollments'] ?? 0) > 0 && ($row['completed'] ?? 0) >= ($row['total_enrollments'] ?? 0))->count();
                $teamWithNotStartedCount = collect($byTeam ?? [])->filter(fn ($row) => ($row['not_started'] ?? 0) > 0)->count();
                $teamCompletionPercent = $teamCount > 0
                    ? (int) round(collect($byTeam ?? [])->avg(function ($row) {
                        $total = max(1, (int) ($row['total_enrollments'] ?? 0));
                        return ((int) ($row['completed'] ?? 0) / $total) * 100;
                    }) ?? 0)
                    : 0;

                $employeeCount = ($employeeOverview ?? collect())->count();
                $fullyCompleteEmployeeCount = collect($employeeOverview ?? [])->filter(fn ($row) => ($row['enrollments'] ?? 0) > 0 && ($row['completed'] ?? 0) >= ($row['enrollments'] ?? 0))->count();
                $employeeNotCompleteCount = max(0, $employeeCount - $fullyCompleteEmployeeCount);
                $employeeCompletionPercent = $employeeCount > 0
                    ? (int) round(collect($employeeOverview ?? [])->avg(fn ($row) => (float) ($row['trained_percent'] ?? 0)) ?? 0)
                    : 0;

                $roleCount = ($byRole ?? collect())->count();
                $fullyCompleteRoleCount = collect($byRole ?? [])->filter(fn ($row) => ($row['total_enrollments'] ?? 0) > 0 && ($row['completed'] ?? 0) >= ($row['total_enrollments'] ?? 0))->count();
                $roleNotCompleteCount = max(0, $roleCount - $fullyCompleteRoleCount);
                $roleCompletionPercent = $roleCount > 0
                    ? (int) round(collect($byRole ?? [])->avg(function ($row) {
                        $total = max(1, (int) ($row['total_enrollments'] ?? 0));
                        return ((int) ($row['completed'] ?? 0) / $total) * 100;
                    }) ?? 0)
                    : 0;
            @endphp

            {{-- Filter Card --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <a href="{{ route('app.admin.assignments') }}" class="btn btn-sm btn-outline-secondary rounded-circle"><i class="bi bi-arrow-left"></i></a>
                        </div>
                        <div class="col">
                            <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">Course Completions</div>
                            <h5 class="fw-semibold mb-0">Compliance Report</h5>
                        </div>
                        <div class="col-auto d-flex flex-wrap gap-2">
                            <a href="{{ route('app.admin.compliance.export', array_filter([
                                'role' => $filters['role'] ?? null,
                                'team' => $filters['team'] ?? null,
                                'location' => $filters['location'] ?? null,
                                'status' => $filters['status'] ?? null,
                            ])) }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i> Export</a>
                            <a href="{{ route('app.admin.compliance.learners', array_filter([
                                'role' => $filters['role'] ?? null,
                                'team' => $filters['team'] ?? null,
                                'location' => $filters['location'] ?? null,
                                'status' => $filters['status'] ?? null,
                            ])) }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-people me-1"></i> Learner Results</a>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('app.admin.compliance') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-xl">
                                <label for="role" class="form-label small fw-semibold text-secondary">Role</label>
                                <select id="role" name="role" class="form-select form-select-sm">
                                    <option value="">All roles</option>
                                    @foreach ($availableRoles as $role)
                                        <option value="{{ $role }}" @selected(($filters['role'] ?? null) === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl">
                                <label for="location" class="form-label small fw-semibold text-secondary">Location</label>
                                <select id="location" name="location" class="form-select form-select-sm">
                                    @foreach (($availableLocations ?? ['all' => 'All']) as $locValue => $locLabel)
                                        <option value="{{ $locValue === 'all' ? '' : $locValue }}" @selected(($filters['location'] ?? null) === ($locValue === 'all' ? null : $locValue))>{{ $locLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl">
                                <label for="team" class="form-label small fw-semibold text-secondary">Team</label>
                                <select id="team" name="team" class="form-select form-select-sm">
                                    <option value="">All teams</option>
                                    @foreach (($availableTeams ?? collect()) as $team)
                                        <option value="{{ $team }}" @selected(($filters['team'] ?? null) === $team)>{{ $team }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl">
                                <label for="status" class="form-label small fw-semibold text-secondary">Status</label>
                                <select id="status" name="status" class="form-select form-select-sm">
                                    <option value="">All statuses</option>
                                    @foreach ($availableStatuses as $status)
                                        <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ str_replace('_', ' ', $status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto d-flex gap-2">
                                <button type="submit" class="btn btn-theme btn-sm">Apply</button>
                                <a href="{{ route('app.admin.compliance') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3 KPI cards with mini rings --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3">
                                <i class="bi bi-diagram-3 fs-4"></i>
                            </div>
                            <div class="fs-4 fw-semibold">Teams</div>
                            <p class="small text-secondary mb-3">Course completions by team</p>
                            <div class="compliance-mini-ring mx-auto mb-3" style="--progress: {{ $teamCompletionPercent }}; --ring-color: #38b86f;">
                                <div class="compliance-mini-ring__inner">
                                    <div class="fs-3 fw-semibold">{{ $teamCompletionPercent }}%</div>
                                    <div class="small text-secondary" style="font-size:10px;letter-spacing:.2em;text-transform:uppercase">Complete</div>
                                </div>
                            </div>
                            <div class="mt-auto text-start">
                                <div class="admin-feed-kpi-stat d-flex justify-content-between align-items-center mb-2 px-3 py-2">
                                    <span class="small text-secondary">Teams in report</span>
                                    <span class="fw-semibold">{{ $teamCount }}</span>
                                </div>
                                <div class="admin-feed-kpi-stat d-flex justify-content-between align-items-center px-3 py-2">
                                    <span class="small text-secondary">Teams with not started</span>
                                    <span class="fw-semibold {{ $teamWithNotStartedCount > 0 ? 'text-warning' : '' }}">{{ $teamWithNotStartedCount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3" style="color:#0f766e;background:linear-gradient(135deg,rgba(213,250,229,.96),rgba(220,252,231,.96));">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                            <div class="fs-4 fw-semibold">Employees</div>
                            <p class="small text-secondary mb-3">Course completions by learner</p>
                            <div class="compliance-mini-ring mx-auto mb-3" style="--progress: {{ $employeeCompletionPercent }}; --ring-color: #38b86f;">
                                <div class="compliance-mini-ring__inner">
                                    <div class="fs-3 fw-semibold">{{ $employeeCompletionPercent }}%</div>
                                    <div class="small text-secondary" style="font-size:10px;letter-spacing:.2em;text-transform:uppercase">Complete</div>
                                </div>
                            </div>
                            <div class="mt-auto text-start">
                                <div class="admin-feed-kpi-stat d-flex justify-content-between align-items-center mb-2 px-3 py-2">
                                    <span class="small text-secondary">All courses complete</span>
                                    <span class="fw-semibold text-success">{{ $fullyCompleteEmployeeCount }}</span>
                                </div>
                                <div class="admin-feed-kpi-stat d-flex justify-content-between align-items-center px-3 py-2">
                                    <span class="small text-secondary">Not all complete</span>
                                    <span class="fw-semibold {{ $employeeNotCompleteCount > 0 ? 'text-warning' : '' }}">{{ $employeeNotCompleteCount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3" style="color:#7c3aed;background:linear-gradient(135deg,rgba(237,233,254,.96),rgba(243,232,255,.96));">
                                <i class="bi bi-person-badge fs-4"></i>
                            </div>
                            <div class="fs-4 fw-semibold">Roles</div>
                            <p class="small text-secondary mb-3">Course completions by role</p>
                            <div class="compliance-mini-ring mx-auto mb-3" style="--progress: {{ $roleCompletionPercent }}; --ring-color: #38b86f;">
                                <div class="compliance-mini-ring__inner">
                                    <div class="fs-3 fw-semibold">{{ $roleCompletionPercent }}%</div>
                                    <div class="small text-secondary" style="font-size:10px;letter-spacing:.2em;text-transform:uppercase">Complete</div>
                                </div>
                            </div>
                            <div class="mt-auto text-start">
                                <div class="admin-feed-kpi-stat d-flex justify-content-between align-items-center mb-2 px-3 py-2">
                                    <span class="small text-secondary">Roles in report</span>
                                    <span class="fw-semibold">{{ $roleCount }}</span>
                                </div>
                                <div class="admin-feed-kpi-stat d-flex justify-content-between align-items-center px-3 py-2">
                                    <span class="small text-secondary">Roles not fully complete</span>
                                    <span class="fw-semibold {{ $roleNotCompleteCount > 0 ? 'text-warning' : '' }}">{{ $roleNotCompleteCount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Directory Report --}}
            <div class="card adminuiux-card shadow-sm mb-4" x-data="{ reportLens: 'team' }">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">Directory Report</div>
                                <h6 class="fw-semibold mb-0 mt-1">Course completion by team, role, or employee</h6>
                                <p class="small text-secondary mb-0">Switch the lens depending on the audience you want to compare.</p>
                            </div>
                            <div class="col-auto d-flex flex-wrap gap-2">
                                <button type="button" @click="reportLens = 'team'" :class="reportLens === 'team' ? 'btn btn-theme btn-sm' : 'btn btn-outline-secondary btn-sm'">
                                    Team <span class="badge bg-white text-dark ms-1">{{ ($byTeam ?? collect())->count() }}</span>
                                </button>
                                <button type="button" @click="reportLens = 'role'" :class="reportLens === 'role' ? 'btn btn-theme btn-sm' : 'btn btn-outline-secondary btn-sm'">
                                    Role <span class="badge bg-white text-dark ms-1">{{ ($byRole ?? collect())->count() }}</span>
                                </button>
                                <button type="button" @click="reportLens = 'employee'" :class="reportLens === 'employee' ? 'btn btn-theme btn-sm' : 'btn btn-outline-secondary btn-sm'">
                                    Employee <span class="badge bg-white text-dark ms-1">{{ ($employeeOverview ?? collect())->count() }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-2 border-bottom bg-light small text-secondary" x-cloak x-show="reportLens === 'team'">Compare course completion across teams.</div>
                    <div class="px-4 py-2 border-bottom bg-light small text-secondary" x-cloak x-show="reportLens === 'role'">Compare course completion across learner roles.</div>
                    <div class="px-4 py-2 border-bottom bg-light small text-secondary" x-cloak x-show="reportLens === 'employee'">Review individual learner course completions.</div>

                    <div class="table-responsive" x-cloak x-show="reportLens === 'team'">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Team</th>
                                    <th class="px-4 py-3">Learners</th>
                                    <th class="px-4 py-3">Enrollments</th>
                                    <th class="px-4 py-3">Completed</th>
                                    <th class="px-4 py-3">In Progress</th>
                                    <th class="px-4 py-3">Not Started</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($byTeam ?? collect()) as $row)
                                    <tr>
                                        <td class="px-4 fw-semibold">{{ $row['team'] }}</td>
                                        <td class="px-4">{{ $row['learners'] }}</td>
                                        <td class="px-4">{{ $row['total_enrollments'] }}</td>
                                        <td class="px-4"><span class="badge bg-success-subtle text-success">{{ $row['completed'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-primary-subtle text-primary">{{ $row['in_progress'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-secondary-subtle text-secondary">{{ $row['not_started'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-4 text-secondary">No team rows available for this scope.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive" x-cloak x-show="reportLens === 'role'">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3">Enrollments</th>
                                    <th class="px-4 py-3">Completed</th>
                                    <th class="px-4 py-3">In Progress</th>
                                    <th class="px-4 py-3">Not Started</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($byRole as $row)
                                    <tr>
                                        <td class="px-4 fw-semibold">{{ $row['role'] }}</td>
                                        <td class="px-4">{{ $row['total_enrollments'] }}</td>
                                        <td class="px-4"><span class="badge bg-success-subtle text-success">{{ $row['completed'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-primary-subtle text-primary">{{ $row['in_progress'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-secondary-subtle text-secondary">{{ $row['not_started'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-4 text-secondary">No role rows available for this scope.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive" x-cloak x-show="reportLens === 'employee'">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Employee</th>
                                    <th class="px-4 py-3">Team</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3">Progress</th>
                                    <th class="px-4 py-3">Completed</th>
                                    <th class="px-4 py-3">In Progress</th>
                                    <th class="px-4 py-3">Not Started</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($employeeOverview ?? collect()) as $row)
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-semibold">{{ $row['learner_name'] }}</div>
                                            <div class="small text-secondary">{{ $row['learner_email'] }}</div>
                                        </td>
                                        <td class="px-4">{{ $row['team'] }}</td>
                                        <td class="px-4">{{ $row['role'] }}</td>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
                                                    <div class="progress-bar {{ $row['trained_percent'] >= 100 ? 'bg-success' : ($row['trained_percent'] >= 50 ? 'bg-primary' : 'bg-warning') }}" style="width:{{ min(100, $row['trained_percent']) }}%"></div>
                                                </div>
                                                <span class="small fw-semibold">{{ $row['trained_percent'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4"><span class="badge bg-success-subtle text-success">{{ $row['completed'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-primary-subtle text-primary">{{ $row['in_progress'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-secondary-subtle text-secondary">{{ $row['not_started'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-4 py-4 text-secondary">No employee rows available for this scope.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Report Scope + Donut Chart --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-6">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">Current Report Scope</div>
                                    <h6 class="fw-semibold mt-1">What this report is showing</h6>
                                    <p class="small text-secondary mb-0">Snapshot of course enrolment and completion.</p>
                                </div>
                                <span class="badge {{ $hasActiveFilters ? 'bg-primary-subtle text-primary' : 'bg-light text-secondary' }}">
                                    {{ $hasActiveFilters ? 'Filtered' : 'All data' }}
                                </span>
                            </div>
                            <div class="row g-3">
                                @foreach ([
                                    ['label' => 'Course Enrollments', 'value' => $summary['total_enrollments'] ?? 0],
                                    ['label' => 'Employees in report', 'value' => ($employeeOverview ?? collect())->count()],
                                    ['label' => 'Teams in report', 'value' => ($byTeam ?? collect())->count()],
                                    ['label' => 'Roles in report', 'value' => ($byRole ?? collect())->count()],
                                ] as $item)
                                    <div class="col-6">
                                        <div class="admin-feed-kpi-stat px-3 py-2">
                                            <div class="small text-secondary" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.15em;">{{ $item['label'] }}</div>
                                            <div class="fs-4 fw-semibold mt-1">{{ $item['value'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($activeFilterMap->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    @foreach ($activeFilterMap as $label => $value)
                                        <span class="badge bg-primary-subtle text-primary">{{ $label }}: {{ $value }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    @include('app.partials.admin-report-donut', [
                        'eyebrow' => 'Course Completion',
                        'title' => 'Enrolment status breakdown',
                        'subtitle' => 'A visual overview of course completion status across all enrollments.',
                        'items' => [
                            ['label' => 'Completed', 'value' => $summary['completed'] ?? 0, 'color' => '#10b981', 'meta' => 'Courses completed'],
                            ['label' => 'In progress', 'value' => $summary['in_progress'] ?? 0, 'color' => '#3b82f6', 'meta' => 'Active learners'],
                            ['label' => 'Not started', 'value' => $summary['not_started'] ?? 0, 'color' => '#94a3b8', 'meta' => 'Assigned but not begun'],
                        ],
                        'centerValue' => $completionRate.'%',
                        'centerLabel' => 'Complete',
                        'badge' => ($summary['total_enrollments'] ?? 0).' enrollments',
                        'cardClass' => 'card adminuiux-card shadow-sm h-100 p-4',
                    ])
                </div>
            </div>

            {{-- Course Breakdown --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">By Course</div>
                        <h6 class="fw-semibold mt-1 mb-0">Course Completion Breakdown</h6>
                        <p class="small text-secondary mb-0">Enrolment and completion status for each published course.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Course</th>
                                    <th class="px-4 py-3">Enrollments</th>
                                    <th class="px-4 py-3">Completed</th>
                                    <th class="px-4 py-3">In Progress</th>
                                    <th class="px-4 py-3">Not Started</th>
                                    <th class="px-4 py-3">Completion %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($byCourse ?? collect()) as $row)
                                    @php
                                        $courseCompPct = ($row['total_enrollments'] ?? 0) > 0
                                            ? (int) round(($row['completed'] / $row['total_enrollments']) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td class="px-4 fw-semibold">{{ $row['course_title'] }}</td>
                                        <td class="px-4">{{ $row['total_enrollments'] }}</td>
                                        <td class="px-4"><span class="badge bg-success-subtle text-success">{{ $row['completed'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-primary-subtle text-primary">{{ $row['in_progress'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-secondary-subtle text-secondary">{{ $row['not_started'] }}</span></td>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
                                                    <div class="progress-bar {{ $courseCompPct >= 75 ? 'bg-success' : ($courseCompPct >= 50 ? 'bg-primary' : 'bg-warning') }}" style="width:{{ $courseCompPct }}%"></div>
                                                </div>
                                                <span class="small fw-semibold">{{ $courseCompPct }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-4 text-secondary">No course enrolment data available.@if ($hasActiveFilters) Try broadening the filters. @endif</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Knowledge Check Results --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">Knowledge Checks</div>
                        <h6 class="fw-semibold mt-1 mb-0">Recent Knowledge Check Results</h6>
                        <p class="small text-secondary mb-0">Post-completion knowledge checks showing pass/fail outcomes.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Completed</th>
                                    <th class="px-4 py-3">Learner</th>
                                    <th class="px-4 py-3">Course</th>
                                    <th class="px-4 py-3">Score</th>
                                    <th class="px-4 py-3">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($recentReinforcementResults ?? collect()) as $attempt)
                                    <tr>
                                        <td class="px-4">{{ $attempt->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td class="px-4">
                                            {{ $attempt->user?->name ?? 'Unknown' }}
                                            <div class="small text-secondary">{{ $attempt->user?->email ?? '' }}</div>
                                        </td>
                                        <td class="px-4">{{ $attempt->course?->title ?? 'Unknown' }}</td>
                                        <td class="px-4">
                                            <span class="fw-bold {{ ($attempt->score_percent ?? 0) >= 75 ? 'text-success' : (($attempt->score_percent ?? 0) >= 50 ? 'text-warning' : 'text-danger') }}">
                                                {{ $attempt->score_percent ?? '—' }}%
                                            </span>
                                        </td>
                                        <td class="px-4">
                                            @if ($attempt->status === 'completed')
                                                <span class="badge bg-success-subtle text-success">Passed</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Gaps Found</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-secondary">
                                            No knowledge check results found.
                                            @if ($hasActiveFilters) The current filters may exclude learners with completed checks. @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Learning Paths + Path Coverage --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-6">
                    <div class="card adminuiux-card shadow-sm h-100" id="learning-track-progress-section">
                        <div class="card-body p-0">
                            <div class="px-4 py-3 border-bottom">
                                <h6 class="fw-semibold mb-0">Learning Paths</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3">Path</th>
                                            <th class="px-4 py-3">Roles</th>
                                            <th class="px-4 py-3">Users</th>
                                            <th class="px-4 py-3">Steps</th>
                                            <th class="px-4 py-3">Complete</th>
                                            <th class="px-4 py-3">Overdue</th>
                                            <th class="px-4 py-3">Avg</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pathRows as $row)
                                            <tr>
                                                <td class="px-4"><a href="{{ route('app.admin.paths.show', ['path' => $row['id']]) }}" class="text-primary fw-medium text-decoration-none">{{ $row['title'] }}</a></td>
                                                <td class="px-4 small">{{ $row['target_roles']->join(', ') ?: 'all' }}</td>
                                                <td class="px-4">{{ $row['eligible_users'] }}</td>
                                                <td class="px-4">{{ $row['total_steps'] }}</td>
                                                <td class="px-4">{{ $row['fully_completed_users'] }}</td>
                                                <td class="px-4">@if($row['overdue_users'] > 0)<span class="badge bg-danger-subtle text-danger">{{ $row['overdue_users'] }}</span>@else 0 @endif</td>
                                                <td class="px-4">{{ $row['average_completion'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="px-4 py-4 text-secondary">No published learning paths available.@if ($hasActiveFilters) Try clearing filters. @endif</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="px-4 py-3 border-bottom">
                                <h6 class="fw-semibold mb-0">Path Coverage by Role</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3">Role</th>
                                            <th class="px-4 py-3">Paths</th>
                                            <th class="px-4 py-3">Users</th>
                                            <th class="px-4 py-3">Complete</th>
                                            <th class="px-4 py-3">Overdue</th>
                                            <th class="px-4 py-3">Avg</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pathCoverageByRole as $row)
                                            <tr>
                                                <td class="px-4 fw-medium">{{ $row['role'] }}</td>
                                                <td class="px-4">{{ $row['paths'] }}</td>
                                                <td class="px-4">{{ $row['eligible_users'] }}</td>
                                                <td class="px-4">{{ $row['fully_completed_paths'] }}</td>
                                                <td class="px-4">@if($row['overdue_paths'] > 0)<span class="badge bg-danger-subtle text-danger">{{ $row['overdue_paths'] }}</span>@else 0 @endif</td>
                                                <td class="px-4">{{ $row['average_completion'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="px-4 py-4 text-secondary">No path coverage rows available.@if ($hasActiveFilters) Clear filters to see all roles. @endif</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
