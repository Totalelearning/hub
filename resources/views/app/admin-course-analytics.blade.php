@extends('layouts.learninguiux')

@section('title', 'Course Analytics - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .analytics-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 32px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }
    .analytics-stat-card {
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(248, 250, 252, 0.95);
        padding: 1.25rem;
    }
    .analytics-section-title {
        font-size: 0.78rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #5f7699;
        font-weight: 700;
    }
    .analytics-progress-bar {
        height: 8px;
        border-radius: 4px;
        background: rgba(226, 232, 240, 0.6);
    }
    .analytics-progress-bar .bar {
        height: 100%;
        border-radius: 4px;
        transition: width 300ms ease;
    }
    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .filter-chip .btn-close {
        font-size: 0.5rem;
        padding: 0;
        filter: none;
        opacity: 0.6;
    }
    .filter-chip .btn-close:hover { opacity: 1; }
    [x-cloak] { display: none !important; }
    .section-toggle {
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .section-toggle .chevron {
        transition: transform 200ms ease;
        font-size: 0.85rem;
        color: #94a3b8;
    }
    .section-toggle .chevron.collapsed { transform: rotate(-90deg); }
    .section-collapse { transition: max-height 300ms ease, opacity 200ms ease; overflow: hidden; }
    .section-collapse.open { max-height: 5000px; opacity: 1; }
    .section-collapse.closed { max-height: 0; opacity: 0; }
    .sortable-th { cursor: pointer; user-select: none; white-space: nowrap; }
    .sortable-th:hover { color: #2563eb; }
    .sort-icon { font-size: 0.65rem; margin-left: 0.2rem; opacity: 0.35; }
    .sort-icon.active { opacity: 1; color: #2563eb; }
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
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Analytics</div>
                        <h1 class="fs-3 fw-semibold mb-2">Course Analytics</h1>
                        <p class="text-secondary mb-3">Completion rates, knowledge check performance, and gap trends across all published courses.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.admin.course-analytics.export') }}" class="btn btn-theme btn-sm"><i class="bi bi-download me-1"></i>Export CSV</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="card analytics-card mb-3" x-data x-ref="filterCard">
                <div class="card-body p-4">
                    @if ($adminScope)
                    <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                        <i class="bi bi-shield-check text-primary"></i>
                        <span class="small fw-semibold">{{ $adminScope['role_label'] }}</span>
                        <span class="text-secondary small">
                            @if (!empty($adminScope['teams']) && !empty($adminScope['locations']))
                                &middot; {{ implode(', ', $adminScope['teams']) }} &middot; {{ implode(', ', $adminScope['locations']) }}
                            @elseif (!empty($adminScope['teams']))
                                &middot; {{ implode(', ', $adminScope['teams']) }}
                            @elseif (!empty($adminScope['locations']))
                                &middot; {{ implode(', ', $adminScope['locations']) }}
                            @endif
                        </span>
                    </div>
                    @endif
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label small text-secondary mb-1">Employee</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Search by name..." x-model="$store.analyticsFilters.employee" @input.debounce.400ms="$store.analyticsFilters.apply()">
                        </div>
                        <div class="col-12 col-md-6 col-xl-2">
                            <label class="form-label small text-secondary mb-1">Location</label>
                            <select class="form-select form-select-sm" x-model="$store.analyticsFilters.location" @change="$store.analyticsFilters.apply()">
                                <option value="">All Locations</option>
                                @foreach ($filterLocations as $loc)
                                    <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl-2">
                            <label class="form-label small text-secondary mb-1">Role</label>
                            <select class="form-select form-select-sm" x-model="$store.analyticsFilters.role" @change="$store.analyticsFilters.apply()">
                                <option value="">All Roles</option>
                                @foreach ($filterRoles as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl-2">
                            <label class="form-label small text-secondary mb-1">Team</label>
                            <select class="form-select form-select-sm" x-model="$store.analyticsFilters.team" @change="$store.analyticsFilters.apply()">
                                <option value="">All Teams</option>
                                @foreach ($filterTeams as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-xl-3 d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm" @click="$store.analyticsFilters.reset()">Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Filter Chips --}}
            <div class="mb-3" x-data x-show="$store.analyticsFilters.location || $store.analyticsFilters.role || $store.analyticsFilters.team || $store.analyticsFilters.employee" x-cloak>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="small text-secondary fw-semibold me-1">Active filters:</span>
                    <template x-if="$store.analyticsFilters.employee">
                        <span class="filter-chip">
                            Employee: <span x-text="$store.analyticsFilters.employee"></span>
                            <button type="button" class="btn-close" @click="$store.analyticsFilters.employee = ''; $store.analyticsFilters.apply()"></button>
                        </span>
                    </template>
                    <template x-if="$store.analyticsFilters.location">
                        <span class="filter-chip">
                            Location: <span x-text="$store.analyticsFilters.location"></span>
                            <button type="button" class="btn-close" @click="$store.analyticsFilters.location = ''; $store.analyticsFilters.apply()"></button>
                        </span>
                    </template>
                    <template x-if="$store.analyticsFilters.role">
                        <span class="filter-chip">
                            Role: <span x-text="$store.analyticsFilters.role"></span>
                            <button type="button" class="btn-close" @click="$store.analyticsFilters.role = ''; $store.analyticsFilters.apply()"></button>
                        </span>
                    </template>
                    <template x-if="$store.analyticsFilters.team">
                        <span class="filter-chip">
                            Team: <span x-text="$store.analyticsFilters.team"></span>
                            <button type="button" class="btn-close" @click="$store.analyticsFilters.team = ''; $store.analyticsFilters.apply()"></button>
                        </span>
                    </template>
                </div>
            </div>

            {{-- Summary Cards + Donut --}}
            <div class="row g-3 mb-4" x-data="analyticsSummary('{{ route('app.admin.course-analytics.summary-json') }}', {{ json_encode($summary) }})" x-init="load()">
                <div class="col-xl-8">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="analytics-stat-card h-100">
                                <div class="small text-secondary">Published Courses</div>
                                <div class="fs-3 fw-bold text-primary mt-1" x-text="total_courses"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="analytics-stat-card h-100">
                                <div class="small text-secondary">Total Enrolments</div>
                                <div class="fs-3 fw-bold mt-1" x-text="total_assigned.toLocaleString()"></div>
                                <div class="small text-secondary"><span x-text="total_completed.toLocaleString()"></span> completed</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="analytics-stat-card h-100">
                                <div class="small text-secondary">Completion Rate</div>
                                <div class="fs-3 fw-bold mt-1" :class="overall_completion_rate >= 75 ? 'text-success' : (overall_completion_rate >= 50 ? 'text-warning' : 'text-danger')">
                                    <span x-text="overall_completion_rate + '%'"></span>
                                </div>
                                <div class="analytics-progress-bar mt-2">
                                    <div class="bar" :class="overall_completion_rate >= 75 ? 'bg-success' : (overall_completion_rate >= 50 ? 'bg-warning' : 'bg-danger')" :style="'width:' + overall_completion_rate + '%'"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="analytics-stat-card h-100">
                                <div class="small text-secondary">Avg Knowledge Check Score</div>
                                <div class="fs-3 fw-bold mt-1" :class="(overall_avg_score ?? 0) >= 75 ? 'text-success' : ((overall_avg_score ?? 0) >= 50 ? 'text-warning' : 'text-danger')">
                                    <span x-text="overall_avg_score !== null ? overall_avg_score + '%' : '—'"></span>
                                </div>
                                <div class="small text-secondary"><span x-text="total_reinforcement_attempts.toLocaleString()"></span> quizzes taken</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="analytics-stat-card h-100 d-flex flex-column align-items-center justify-content-center">
                        <div class="analytics-section-title mb-2">Completion Breakdown</div>
                        <div style="position:relative;max-width:180px;max-height:180px;">
                            <canvas id="completionDonutChart"></canvas>
                        </div>
                        <div class="d-flex gap-3 mt-2 small">
                            <span><span class="text-success">&bull;</span> Completed</span>
                            <span><span class="text-primary">&bull;</span> In Progress</span>
                            <span><span class="text-warning">&bull;</span> Not Started</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Location Comparison (trustee + site_admin only) --}}
                @if ($showLocationComparison)
                <div class="col-12" x-data="{ ...ajaxPager('{{ route('app.admin.course-analytics.location-comparison-json') }}', 'locations', { lazy: true, sortable: true }), open: false }" x-init="load()">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Location Comparison</div>
                                    <p class="small text-secondary mb-0 mt-1">How each school or site compares across enrolments, completions, and knowledge checks.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">

                            <template x-if="loading">
                                <div class="text-center py-3 text-secondary">
                                    <div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;
                                </div>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <div class="rounded-3 bg-light p-4 text-secondary text-center">
                                    No location data available yet.
                                </div>
                            </template>
                            <template x-if="!loading && items.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2 sortable-th" @click="toggleSort('location')">Location <span class="sort-icon" :class="{ active: sortBy === 'location' }"><i class="bi" :class="sortBy === 'location' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('learners')">Learners <span class="sort-icon" :class="{ active: sortBy === 'learners' }"><i class="bi" :class="sortBy === 'learners' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('enrolled')">Enrolled <span class="sort-icon" :class="{ active: sortBy === 'enrolled' }"><i class="bi" :class="sortBy === 'enrolled' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('completed')">Completed <span class="sort-icon" :class="{ active: sortBy === 'completed' }"><i class="bi" :class="sortBy === 'completed' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" style="min-width:160px" @click="toggleSort('completion_rate')">Completion % <span class="sort-icon" :class="{ active: sortBy === 'completion_rate' }"><i class="bi" :class="sortBy === 'completion_rate' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('quizzes')">Quizzes <span class="sort-icon" :class="{ active: sortBy === 'quizzes' }"><i class="bi" :class="sortBy === 'quizzes' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('pass_rate')">Pass Rate <span class="sort-icon" :class="{ active: sortBy === 'pass_rate' }"><i class="bi" :class="sortBy === 'pass_rate' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('avg_score')">Avg Score <span class="sort-icon" :class="{ active: sortBy === 'avg_score' }"><i class="bi" :class="sortBy === 'avg_score' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="loc in sorted()" :key="loc.location">
                                                <tr>
                                                    <td class="px-3 fw-medium" x-text="loc.location"></td>
                                                    <td class="px-3 text-center" x-text="loc.learners"></td>
                                                    <td class="px-3 text-center" x-text="loc.enrolled"></td>
                                                    <td class="px-3 text-center">
                                                        <span class="fw-semibold" x-text="loc.completed"></span>
                                                        <template x-if="loc.in_progress > 0">
                                                            <div class="small text-secondary" x-text="loc.in_progress + ' in progress'"></div>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="loc.enrolled > 0">
                                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                                <div class="analytics-progress-bar flex-grow-1" style="max-width:80px;">
                                                                    <div class="bar" :class="loc.completion_rate >= 75 ? 'bg-success' : (loc.completion_rate >= 50 ? 'bg-warning' : 'bg-danger')" :style="'width:' + loc.completion_rate + '%'"></div>
                                                                </div>
                                                                <span class="fw-medium" x-text="loc.completion_rate + '%'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="loc.enrolled === 0">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center" x-text="loc.quizzes || '—'"></td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="loc.pass_rate !== null">
                                                            <span class="badge rounded-pill px-2 py-1" :class="loc.pass_rate >= 75 ? 'bg-success-subtle text-success' : (loc.pass_rate >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger')" x-text="loc.pass_rate + '%'"></span>
                                                        </template>
                                                        <template x-if="loc.pass_rate === null">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="loc.avg_score !== null">
                                                            <span class="fw-medium" :class="loc.avg_score >= 75 ? 'text-success' : (loc.avg_score >= 50 ? 'text-warning' : 'text-danger')" x-text="loc.avg_score + '%'"></span>
                                                        </template>
                                                        <template x-if="loc.avg_score === null">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                            </div>{{-- /section-collapse --}}
                        </div>
                    </div>
                </div>
                {{-- Team Comparison (trustee + site_admin only) --}}
                <div class="col-12" x-data="{ ...ajaxPager('{{ route('app.admin.course-analytics.team-comparison-json') }}', 'teams', { lazy: true, sortable: true }), open: false }" x-init="load()">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Team Comparison</div>
                                    <p class="small text-secondary mb-0 mt-1">How each team compares across enrolments, completions, and knowledge checks.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">

                            <template x-if="loading">
                                <div class="text-center py-3 text-secondary">
                                    <div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;
                                </div>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <div class="rounded-3 bg-light p-4 text-secondary text-center">
                                    No team data available yet.
                                </div>
                            </template>
                            <template x-if="!loading && items.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2 sortable-th" @click="toggleSort('team')">Team <span class="sort-icon" :class="{ active: sortBy === 'team' }"><i class="bi" :class="sortBy === 'team' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('learners')">Learners <span class="sort-icon" :class="{ active: sortBy === 'learners' }"><i class="bi" :class="sortBy === 'learners' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('enrolled')">Enrolled <span class="sort-icon" :class="{ active: sortBy === 'enrolled' }"><i class="bi" :class="sortBy === 'enrolled' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('completed')">Completed <span class="sort-icon" :class="{ active: sortBy === 'completed' }"><i class="bi" :class="sortBy === 'completed' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" style="min-width:160px" @click="toggleSort('completion_rate')">Completion % <span class="sort-icon" :class="{ active: sortBy === 'completion_rate' }"><i class="bi" :class="sortBy === 'completion_rate' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('quizzes')">Quizzes <span class="sort-icon" :class="{ active: sortBy === 'quizzes' }"><i class="bi" :class="sortBy === 'quizzes' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('pass_rate')">Pass Rate <span class="sort-icon" :class="{ active: sortBy === 'pass_rate' }"><i class="bi" :class="sortBy === 'pass_rate' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('avg_score')">Avg Score <span class="sort-icon" :class="{ active: sortBy === 'avg_score' }"><i class="bi" :class="sortBy === 'avg_score' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="t in sorted()" :key="t.team">
                                                <tr>
                                                    <td class="px-3 fw-medium" x-text="t.team"></td>
                                                    <td class="px-3 text-center" x-text="t.learners"></td>
                                                    <td class="px-3 text-center" x-text="t.enrolled"></td>
                                                    <td class="px-3 text-center">
                                                        <span class="fw-semibold" x-text="t.completed"></span>
                                                        <template x-if="t.in_progress > 0">
                                                            <div class="small text-secondary" x-text="t.in_progress + ' in progress'"></div>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="t.enrolled > 0">
                                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                                <div class="analytics-progress-bar flex-grow-1" style="max-width:80px;">
                                                                    <div class="bar" :class="t.completion_rate >= 75 ? 'bg-success' : (t.completion_rate >= 50 ? 'bg-warning' : 'bg-danger')" :style="'width:' + t.completion_rate + '%'"></div>
                                                                </div>
                                                                <span class="fw-medium" x-text="t.completion_rate + '%'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="t.enrolled === 0">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center" x-text="t.quizzes || '—'"></td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="t.pass_rate !== null">
                                                            <span class="badge rounded-pill px-2 py-1" :class="t.pass_rate >= 75 ? 'bg-success-subtle text-success' : (t.pass_rate >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger')" x-text="t.pass_rate + '%'"></span>
                                                        </template>
                                                        <template x-if="t.pass_rate === null">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="t.avg_score !== null">
                                                            <span class="fw-medium" :class="t.avg_score >= 75 ? 'text-success' : (t.avg_score >= 50 ? 'text-warning' : 'text-danger')" x-text="t.avg_score + '%'"></span>
                                                        </template>
                                                        <template x-if="t.avg_score === null">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                            </div>{{-- /section-collapse --}}
                        </div>
                    </div>
                </div>
                {{-- Role Comparison (trustee + site_admin only) --}}
                <div class="col-12" x-data="{ ...ajaxPager('{{ route('app.admin.course-analytics.role-comparison-json') }}', 'roles', { lazy: true, sortable: true }), open: false }" x-init="load()">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Role Comparison</div>
                                    <p class="small text-secondary mb-0 mt-1">How each role compares across enrolments, completions, and knowledge checks.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">

                            <template x-if="loading">
                                <div class="text-center py-3 text-secondary">
                                    <div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;
                                </div>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <div class="rounded-3 bg-light p-4 text-secondary text-center">
                                    No role data available yet.
                                </div>
                            </template>
                            <template x-if="!loading && items.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2 sortable-th" @click="toggleSort('role')">Role <span class="sort-icon" :class="{ active: sortBy === 'role' }"><i class="bi" :class="sortBy === 'role' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('learners')">Learners <span class="sort-icon" :class="{ active: sortBy === 'learners' }"><i class="bi" :class="sortBy === 'learners' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('enrolled')">Enrolled <span class="sort-icon" :class="{ active: sortBy === 'enrolled' }"><i class="bi" :class="sortBy === 'enrolled' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('completed')">Completed <span class="sort-icon" :class="{ active: sortBy === 'completed' }"><i class="bi" :class="sortBy === 'completed' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" style="min-width:160px" @click="toggleSort('completion_rate')">Completion % <span class="sort-icon" :class="{ active: sortBy === 'completion_rate' }"><i class="bi" :class="sortBy === 'completion_rate' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('quizzes')">Quizzes <span class="sort-icon" :class="{ active: sortBy === 'quizzes' }"><i class="bi" :class="sortBy === 'quizzes' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('pass_rate')">Pass Rate <span class="sort-icon" :class="{ active: sortBy === 'pass_rate' }"><i class="bi" :class="sortBy === 'pass_rate' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                                <th class="px-3 py-2 text-center sortable-th" @click="toggleSort('avg_score')">Avg Score <span class="sort-icon" :class="{ active: sortBy === 'avg_score' }"><i class="bi" :class="sortBy === 'avg_score' && sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i></span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="r in sorted()" :key="r.role">
                                                <tr>
                                                    <td class="px-3 fw-medium" x-text="r.role"></td>
                                                    <td class="px-3 text-center" x-text="r.learners"></td>
                                                    <td class="px-3 text-center" x-text="r.enrolled"></td>
                                                    <td class="px-3 text-center">
                                                        <span class="fw-semibold" x-text="r.completed"></span>
                                                        <template x-if="r.in_progress > 0">
                                                            <div class="small text-secondary" x-text="r.in_progress + ' in progress'"></div>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="r.enrolled > 0">
                                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                                <div class="analytics-progress-bar flex-grow-1" style="max-width:80px;">
                                                                    <div class="bar" :class="r.completion_rate >= 75 ? 'bg-success' : (r.completion_rate >= 50 ? 'bg-warning' : 'bg-danger')" :style="'width:' + r.completion_rate + '%'"></div>
                                                                </div>
                                                                <span class="fw-medium" x-text="r.completion_rate + '%'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="r.enrolled === 0">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center" x-text="r.quizzes || '—'"></td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="r.pass_rate !== null">
                                                            <span class="badge rounded-pill px-2 py-1" :class="r.pass_rate >= 75 ? 'bg-success-subtle text-success' : (r.pass_rate >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger')" x-text="r.pass_rate + '%'"></span>
                                                        </template>
                                                        <template x-if="r.pass_rate === null">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="r.avg_score !== null">
                                                            <span class="fw-medium" :class="r.avg_score >= 75 ? 'text-success' : (r.avg_score >= 50 ? 'text-warning' : 'text-danger')" x-text="r.avg_score + '%'"></span>
                                                        </template>
                                                        <template x-if="r.avg_score === null">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                            </div>{{-- /section-collapse --}}
                        </div>
                    </div>
                </div>
                @endif

                {{-- Course Performance Table --}}
                <div class="col-12" x-data="{ ...ajaxPager('{{ route('app.admin.course-analytics.courses-json') }}', 'courses', { lazy: true }), open: false }" x-init="load()">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div class="analytics-section-title mb-0">Course Performance</div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2">Course</th>
                                            <th class="px-3 py-2 text-center">Enrolled</th>
                                            <th class="px-3 py-2 text-center">Completed</th>
                                            <th class="px-3 py-2 text-center">Completion %</th>
                                            <th class="px-3 py-2 text-center">Quizzes</th>
                                            <th class="px-3 py-2 text-center">Pass Rate</th>
                                            <th class="px-3 py-2 text-center">Avg Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="loading">
                                            <tr><td colspan="7" class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;</td></tr>
                                        </template>
                                        <template x-if="!loading && items.length === 0">
                                            <tr><td colspan="7" class="text-center py-4 text-secondary">No published courses yet.</td></tr>
                                        </template>
                                        <template x-for="c in items" :key="c.id">
                                            <tr>
                                                <td class="px-3">
                                                    <a :href="c.edit_url" class="fw-semibold text-decoration-none" x-text="c.title"></a>
                                                    <div class="small text-secondary" x-text="c.modules_count + ' modules'"></div>
                                                </td>
                                                <td class="px-3 text-center" x-text="c.assigned"></td>
                                                <td class="px-3 text-center">
                                                    <span class="fw-semibold" x-text="c.completed"></span>
                                                    <template x-if="c.in_progress > 0">
                                                        <div class="small text-secondary" x-text="c.in_progress + ' in progress'"></div>
                                                    </template>
                                                </td>
                                                <td class="px-3 text-center">
                                                    <template x-if="c.assigned > 0">
                                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                                            <div class="analytics-progress-bar flex-grow-1" style="max-width:80px;">
                                                                <div class="bar" :class="c.completion_rate >= 75 ? 'bg-success' : (c.completion_rate >= 50 ? 'bg-warning' : 'bg-danger')" :style="'width:' + c.completion_rate + '%'"></div>
                                                            </div>
                                                            <span class="fw-medium" x-text="c.completion_rate + '%'"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="c.assigned === 0">
                                                        <span class="text-secondary">&mdash;</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 text-center">
                                                    <template x-if="c.total_attempts > 0">
                                                        <div>
                                                            <span class="fw-semibold" x-text="c.total_attempts"></span>
                                                            <div class="small text-secondary"><span x-text="c.passed"></span> passed &middot; <span x-text="c.failed"></span> failed</div>
                                                        </div>
                                                    </template>
                                                    <template x-if="c.total_attempts === 0">
                                                        <span class="text-secondary">&mdash;</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 text-center">
                                                    <template x-if="c.pass_rate !== null">
                                                        <span class="badge rounded-pill px-2 py-1" :class="c.pass_rate >= 75 ? 'bg-success-subtle text-success' : (c.pass_rate >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger')" x-text="c.pass_rate + '%'"></span>
                                                    </template>
                                                    <template x-if="c.pass_rate === null">
                                                        <span class="text-secondary">&mdash;</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 text-center">
                                                    <template x-if="c.avg_score !== null">
                                                        <span class="fw-medium" x-text="c.avg_score + '%'"></span>
                                                    </template>
                                                    <template x-if="c.avg_score === null">
                                                        <span class="text-secondary">&mdash;</span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <template x-if="lastPage > 1">
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="small text-secondary">
                                        Showing <span x-text="from"></span>&ndash;<span x-text="to"></span> of <span x-text="total"></span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page <= 1" @click="goTo(page - 1)"><i class="bi bi-chevron-left"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page >= lastPage" @click="goTo(page + 1)"><i class="bi bi-chevron-right"></i></button>
                                    </div>
                                </div>
                            </template>
                            </div>{{-- /section-collapse --}}
                        </div>
                    </div>
                </div>

                {{-- Knowledge Gap Hotspots (full width table) --}}
                <div class="col-12" x-data="{ ...ajaxPager('{{ route('app.admin.course-analytics.hotspots-json') }}', 'hotspots', { lazy: true }), open: false }" x-init="load()">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Knowledge Gap Hotspots</div>
                                    <p class="small text-secondary mb-0 mt-1">Modules with the most incorrect answers across all knowledge checks.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">

                            <template x-if="loading">
                                <div class="text-center py-3 text-secondary">
                                    <div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;
                                </div>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <div class="rounded-3 bg-light p-4 text-secondary text-center">
                                    No knowledge check data yet. Gap hotspots will appear once learners take quizzes.
                                </div>
                            </template>
                            <template x-if="!loading && items.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2">Module</th>
                                                <th class="px-3 py-2">Course</th>
                                                <th class="px-3 py-2 text-center" style="width:130px">Incorrect</th>
                                                <th class="px-3 py-2" style="min-width:180px">Severity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(gap, i) in items" :key="i">
                                                <tr>
                                                    <td class="px-3">
                                                        <div class="fw-medium" x-text="gap.module_title"></div>
                                                    </td>
                                                    <td class="px-3">
                                                        <span class="small text-secondary" x-text="gap.course_title || '—'"></span>
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" x-text="gap.incorrect_count"></span>
                                                    </td>
                                                    <td class="px-3">
                                                        <div class="analytics-progress-bar">
                                                            <div class="bar bg-danger" :style="'width:' + (items.length > 0 && items[0].incorrect_count > 0 ? Math.round(gap.incorrect_count / items[0].incorrect_count * 100) : 0) + '%'"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template x-if="lastPage > 1">
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="small text-secondary">
                                        Showing <span x-text="from"></span>&ndash;<span x-text="to"></span> of <span x-text="total"></span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page <= 1" @click="goTo(page - 1)"><i class="bi bi-chevron-left"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page >= lastPage" @click="goTo(page + 1)"><i class="bi bi-chevron-right"></i></button>
                                    </div>
                                </div>
                            </template>
                            </div>{{-- /section-collapse --}}
                        </div>
                    </div>
                </div>

                {{-- Learners Needing Attention (full width table) --}}
                <div class="col-12" x-data="{ ...ajaxPager('{{ route('app.admin.course-analytics.gaps') }}', 'gaps', { lazy: true }), open: false }" x-init="load()">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Learners Needing Attention</div>
                                    <p class="small text-secondary mb-0 mt-1">Learners who failed a knowledge check and haven't yet re-completed the course.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">

                            <template x-if="loading">
                                <div class="text-center py-3 text-secondary">
                                    <div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;
                                </div>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <div class="rounded-3 bg-light p-4 text-secondary text-center">
                                    No learners with knowledge gaps right now.
                                </div>
                            </template>
                            <template x-if="!loading && items.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2">Learner</th>
                                                <th class="px-3 py-2">Team</th>
                                                <th class="px-3 py-2">Course</th>
                                                <th class="px-3 py-2 text-center">Score</th>
                                                <th class="px-3 py-2 text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(learner, i) in items" :key="i">
                                                <tr>
                                                    <td class="px-3 fw-medium" x-text="learner.name"></td>
                                                    <td class="px-3">
                                                        <span class="small text-secondary" x-text="learner.team || '—'"></span>
                                                    </td>
                                                    <td class="px-3" x-text="learner.course_title"></td>
                                                    <td class="px-3 text-center">
                                                        <template x-if="learner.latest_score !== null">
                                                            <span class="badge rounded-pill px-2 py-1" :class="learner.latest_score >= 75 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger'" x-text="learner.latest_score + '%'"></span>
                                                        </template>
                                                        <template x-if="learner.latest_score === null">
                                                            <span class="text-secondary">&mdash;</span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 text-end">
                                                        <template x-if="learner.attempt_url">
                                                            <a :href="learner.attempt_url" class="btn btn-sm btn-outline-danger me-1">View Gaps</a>
                                                        </template>
                                                        <a :href="learner.course_url" class="btn btn-sm btn-outline-secondary">Course</a>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template x-if="lastPage > 1">
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="small text-secondary">
                                        Showing <span x-text="from"></span>&ndash;<span x-text="to"></span> of <span x-text="total"></span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page <= 1" @click="goTo(page - 1)"><i class="bi bi-chevron-left"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page >= lastPage" @click="goTo(page + 1)"><i class="bi bi-chevron-right"></i></button>
                                    </div>
                                </div>
                            </template>
                            </div>{{-- /section-collapse --}}
                        </div>
                    </div>
                </div>

                {{-- Recent Knowledge Check Results --}}
                <div class="col-12" x-data="{ ...ajaxPager('{{ route('app.admin.course-analytics.attempts-json') }}', 'attempts', { lazy: true }), open: false }" x-init="load()">
                    <div class="card analytics-card mb-4">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div class="analytics-section-title mb-0">Recent Knowledge Check Results</div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2">Learner</th>
                                            <th class="px-3 py-2">Team</th>
                                            <th class="px-3 py-2">Course</th>
                                            <th class="px-3 py-2 text-center">Score</th>
                                            <th class="px-3 py-2 text-center">Result</th>
                                            <th class="px-3 py-2">Completed</th>
                                            <th class="px-3 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="loading">
                                            <tr><td colspan="7" class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;</td></tr>
                                        </template>
                                        <template x-if="!loading && items.length === 0">
                                            <tr><td colspan="7" class="text-center py-4 text-secondary">No knowledge check results yet.</td></tr>
                                        </template>
                                        <template x-for="a in items" :key="a.id">
                                            <tr>
                                                <td class="px-3">
                                                    <div class="fw-medium" x-text="a.learner_name"></div>
                                                    <div class="small text-secondary" x-text="a.learner_email"></div>
                                                </td>
                                                <td class="px-3">
                                                    <span class="small text-secondary" x-text="a.team || '—'"></span>
                                                </td>
                                                <td class="px-3" x-text="a.course_title"></td>
                                                <td class="px-3 text-center">
                                                    <span class="fw-bold" :class="a.score_percent >= 75 ? 'text-success' : (a.score_percent >= 50 ? 'text-warning' : 'text-danger')" x-text="a.score_percent + '%'"></span>
                                                </td>
                                                <td class="px-3 text-center">
                                                    <template x-if="a.status === 'completed'">
                                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Passed</span>
                                                    </template>
                                                    <template x-if="a.status !== 'completed'">
                                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1">Gaps Found</span>
                                                    </template>
                                                </td>
                                                <td class="px-3">
                                                    <span class="small text-secondary" x-text="a.completed_at"></span>
                                                </td>
                                                <td class="px-3 text-end">
                                                    <a :href="a.detail_url" class="btn btn-sm btn-outline-secondary">Details</a>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <template x-if="lastPage > 1">
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="small text-secondary">
                                        Showing <span x-text="from"></span>&ndash;<span x-text="to"></span> of <span x-text="total"></span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page <= 1" @click="goTo(page - 1)"><i class="bi bi-chevron-left"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary" :disabled="page >= lastPage" @click="goTo(page + 1)"><i class="bi bi-chevron-right"></i></button>
                                    </div>
                                </div>
                            </template>
                            </div>{{-- /section-collapse --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/learninguiux/js/component/component-chartjs.js') }}"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('analyticsFilters', {
        location: '',
        role: '',
        team: '',
        employee: '',
        _pagers: [],

        register(pager) {
            this._pagers.push(pager);
        },

        queryString() {
            const params = new URLSearchParams();
            if (this.location) params.set('location', this.location);
            if (this.role) params.set('role', this.role);
            if (this.team) params.set('team', this.team);
            if (this.employee) params.set('employee', this.employee);
            return params.toString();
        },

        apply() {
            this._pagers.forEach(p => p.fetchPage(1));
        },

        reset() {
            this.location = '';
            this.role = '';
            this.team = '';
            this.employee = '';
            this.apply();
        }
    });
});

function analyticsSummary(url, initial) {
    return {
        total_courses: initial.total_courses ?? 0,
        total_assigned: initial.total_assigned ?? 0,
        total_completed: initial.total_completed ?? 0,
        total_in_progress: initial.total_in_progress ?? 0,
        total_not_started: initial.total_not_started ?? 0,
        overall_completion_rate: initial.overall_completion_rate ?? 0,
        total_reinforcement_attempts: initial.total_reinforcement_attempts ?? 0,
        total_passed: initial.total_passed ?? 0,
        total_failed: initial.total_failed ?? 0,
        overall_avg_score: initial.overall_avg_score ?? null,
        loading: false,
        _chart: null,

        load() {
            this.$store.analyticsFilters.register(this);
            this.$nextTick(() => this.updateChart());
        },

        async fetchPage(_p) {
            this.loading = true;
            try {
                const filterQs = this.$store.analyticsFilters.queryString();
                const sep = url.includes('?') ? '&' : '?';
                const filterPart = filterQs ? `${sep}${filterQs}` : '';
                const res = await fetch(`${url}${filterPart}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                Object.assign(this, json);
                this.updateChart();
            } catch (e) {
                console.error('Failed to load summary:', e);
            } finally {
                this.loading = false;
            }
        },

        updateChart() {
            const canvas = document.getElementById('completionDonutChart');
            if (!canvas || typeof Chart === 'undefined') return;
            if (this._chart) this._chart.destroy();
            this._chart = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Not Started'],
                    datasets: [{
                        data: [this.total_completed, this.total_in_progress, this.total_not_started],
                        backgroundColor: ['#198754', '#0d6efd', '#ffc107'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '62%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? Math.round(ctx.raw / total * 100) : 0;
                                    return `${ctx.label}: ${ctx.raw.toLocaleString()} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    };
}

function ajaxPager(url, name, opts) {
    const lazy = opts?.lazy ?? false;
    const sortable = opts?.sortable ?? false;
    return {
        items: [],
        page: 1,
        lastPage: 1,
        from: 0,
        to: 0,
        total: 0,
        loading: false,
        _url: url,
        _name: name,
        _loaded: false,
        _lazy: lazy,
        _dirty: false,
        _sortable: sortable,
        sortBy: '',
        sortDir: 'desc',

        sorted() {
            if (!this._sortable || !this.sortBy) return this.items;
            return [...this.items].sort((a, b) => {
                let va = a[this.sortBy], vb = b[this.sortBy];
                if (va === null || va === undefined) va = -Infinity;
                if (vb === null || vb === undefined) vb = -Infinity;
                if (typeof va === 'string') return this.sortDir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
                return this.sortDir === 'asc' ? va - vb : vb - va;
            });
        },

        toggleSort(col) {
            if (this.sortBy === col) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = col;
                this.sortDir = 'desc';
            }
        },

        load() {
            this.$store.analyticsFilters.register(this);
            if (!this._lazy) {
                this.fetchPage(1);
            } else {
                this.$watch('open', (val) => {
                    if (val && (!this._loaded || this._dirty)) {
                        this._dirty = false;
                        this.fetchPage(1);
                    }
                });
            }
        },

        goTo(p) {
            if (p < 1 || p > this.lastPage || this.loading) return;
            this.fetchPage(p);
        },

        async fetchPage(p) {
            if (this._lazy && !this.open && this._loaded) {
                this._dirty = true;
                return;
            }
            this.loading = true;
            try {
                const filterQs = this.$store.analyticsFilters.queryString();
                const sep = this._url.includes('?') ? '&' : '?';
                const filterPart = filterQs ? `&${filterQs}` : '';
                const res = await fetch(`${this._url}${sep}page=${p}${filterPart}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                this.items = json.data;
                this.page = json.current_page;
                this.lastPage = json.last_page;
                this.from = json.from ?? 0;
                this.to = json.to ?? 0;
                this.total = json.total;
                this._loaded = true;
            } catch (e) {
                console.error(`Failed to load ${this._name}:`, e);
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
