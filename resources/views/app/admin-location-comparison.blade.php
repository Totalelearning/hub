@extends('layouts.learninguiux')

@section('title', 'Location Comparison - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content"
             x-data="locationComparison({{ Js::from($statsJson) }})"
             x-cloak>

            {{-- Hero --}}
            <div class="mb-4 admin-feed-hero">
                <div class="row align-items-center g-0 p-4 p-lg-5">
                    <div class="col-12 col-lg-8 admin-feed-hero-copy mb-3 mb-lg-0">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Multi-Academy Trust</div>
                        <h1 class="fs-3 fw-semibold mb-2">Location Comparison</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-2">
                                <li class="breadcrumb-item"><a href="{{ $dashboardUrl }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Location Comparison</li>
                            </ol>
                        </nav>
                        <p class="text-secondary mb-0">Compare completion rates, compliance, and engagement across all school locations in the trust.</p>
                    </div>
                    <div class="col-12 col-lg-4 d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ $exportUrl }}" class="btn btn-outline-theme btn-sm">Export CSV</a>
                        <a href="{{ $manageUrl }}" class="btn btn-outline-theme btn-sm">Manage Locations</a>
                    </div>
                </div>
            </div>

            {{-- Trust-wide KPI cards (reactive) --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3" style="color:#0f766e;background:linear-gradient(135deg, rgba(213, 250, 229, 0.96), rgba(220, 252, 231, 0.96));">
                                <i class="bi bi-geo-alt fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat" x-text="filtered.length"></div>
                            <div class="fw-semibold mt-1">Locations</div>
                            <div class="small text-secondary mt-auto pt-2">
                                <span x-show="isFiltered()" x-text="'of ' + allStats.length + ' schools'" class="text-primary"></span>
                                <span x-show="!isFiltered()">Schools in the trust</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3">
                                <i class="bi bi-people fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat" x-text="kpi.users"></div>
                            <div class="fw-semibold mt-1">Total Staff</div>
                            <div class="small text-secondary mt-auto pt-2">Across filtered locations</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3" style="color:#0f766e;background:linear-gradient(135deg, rgba(213, 250, 229, 0.96), rgba(220, 252, 231, 0.96));">
                                <i class="bi bi-check-circle fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat" x-text="kpi.completionRate + '%'"></div>
                            <div class="fw-semibold mt-1">Completion Rate</div>
                            <div class="small text-secondary mt-auto pt-2">Filtered average</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"
                                 :style="kpi.overdue > 0
                                    ? 'color:#b91c1c;background:linear-gradient(135deg, rgba(254, 226, 226, 0.98), rgba(255, 237, 213, 0.98))'
                                    : 'color:#0f766e;background:linear-gradient(135deg, rgba(213, 250, 229, 0.96), rgba(220, 252, 231, 0.96))'">
                                <i class="bi bi-exclamation-triangle fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat" x-text="kpi.overdue"></div>
                            <div class="fw-semibold mt-1">Overdue Staff</div>
                            <div class="small text-secondary mt-auto pt-2">Across filtered locations</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Card (compliance-style, no page refresh) --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <a href="{{ $dashboardUrl }}" class="btn btn-sm btn-outline-secondary rounded-circle"><i class="bi bi-arrow-left"></i></a>
                        </div>
                        <div class="col">
                            <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">School Comparison</div>
                            <h5 class="fw-semibold mb-0">Location Comparison</h5>
                        </div>
                        <div class="col-auto d-flex flex-wrap gap-2">
                            <a href="{{ $exportUrl }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i> Export</a>
                            <a href="{{ $manageUrl }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-gear me-1"></i> Manage Locations</a>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-xl">
                            <label class="form-label small fw-semibold text-secondary">Search</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Search schools or staff..." x-model="search">
                        </div>
                        <div class="col-12 col-md-6 col-xl">
                            <label class="form-label small fw-semibold text-secondary">School</label>
                            <select class="form-select form-select-sm" x-model="schoolFilter">
                                <option value="">All schools</option>
                                <template x-for="s in allStats" :key="s.slug">
                                    <option :value="s.slug" x-text="s.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl">
                            <label class="form-label small fw-semibold text-secondary">Role</label>
                            <select class="form-select form-select-sm" x-model="roleFilter">
                                <option value="">All roles</option>
                                <template x-for="r in availableRoles" :key="r">
                                    <option :value="r" x-text="r"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl">
                            <label class="form-label small fw-semibold text-secondary">Team</label>
                            <select class="form-select form-select-sm" x-model="teamFilter">
                                <option value="">All teams</option>
                                <template x-for="t in availableTeams" :key="t">
                                    <option :value="t" x-text="t"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl">
                            <label class="form-label small fw-semibold text-secondary">Completion</label>
                            <select class="form-select form-select-sm" x-model="completionFilter">
                                <option value="">All rates</option>
                                <option value="high">High (80%+)</option>
                                <option value="medium">Medium (50-79%)</option>
                                <option value="low">Low (&lt;50%)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl">
                            <label class="form-label small fw-semibold text-secondary">Overdue</label>
                            <select class="form-select form-select-sm" x-model="overdueFilter">
                                <option value="">All</option>
                                <option value="overdue">Has overdue</option>
                                <option value="clean">No overdue</option>
                            </select>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="resetFilters()">Reset</button>
                        </div>
                    </div>
                    {{-- Active filter pills --}}
                    <div class="mt-2" x-show="isFiltered()">
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            <span class="small text-secondary me-1">Active:</span>
                            <template x-if="search">
                                <span class="badge bg-primary-subtle text-primary" x-text="'Search: ' + search"></span>
                            </template>
                            <template x-if="schoolFilter">
                                <span class="badge bg-primary-subtle text-primary" x-text="'School: ' + schoolLabel()"></span>
                            </template>
                            <template x-if="roleFilter">
                                <span class="badge bg-primary-subtle text-primary" x-text="'Role: ' + roleFilter"></span>
                            </template>
                            <template x-if="teamFilter">
                                <span class="badge bg-primary-subtle text-primary" x-text="'Team: ' + teamFilter"></span>
                            </template>
                            <template x-if="completionFilter">
                                <span class="badge bg-primary-subtle text-primary" x-text="'Completion: ' + completionLabel()"></span>
                            </template>
                            <template x-if="overdueFilter">
                                <span class="badge bg-primary-subtle text-primary" x-text="'Overdue: ' + overdueLabel()"></span>
                            </template>
                            <span class="small text-secondary ms-1" x-text="filtered.length + ' of ' + allStats.length + ' locations'"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Comparison Table --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header px-4 py-3 border-bottom">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                        <div>
                            <div class="text-uppercase fw-semibold text-primary" style="font-size:.68rem;letter-spacing:.18em;">Comparison Table</div>
                            <h3 class="fs-5 fw-semibold mt-1 mb-0">All Locations</h3>
                            <p class="small text-secondary mb-0 mt-1">
                                <span x-text="filtered.length + ' location' + (filtered.length !== 1 ? 's' : '') + ' shown'"></span>
                                <span x-show="isFiltered()" class="text-primary" x-text="' (filtered from ' + allStats.length + ')'"></span>
                                &mdash; click column headers to sort
                            </p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th style="cursor:pointer;" @click="toggleSort('name')">
                                    Location<span x-text="sortIcon('name')"></span>
                                </th>
                                <th class="text-center" style="cursor:pointer;" @click="toggleSort('user_count')">
                                    Staff<span x-text="sortIcon('user_count')"></span>
                                </th>
                                <th class="text-center">Active</th>
                                <th class="text-center">Verified</th>
                                <th class="text-center" style="cursor:pointer;" @click="toggleSort('completion_rate')">
                                    Completion<span x-text="sortIcon('completion_rate')"></span>
                                </th>
                                <th class="text-center" style="cursor:pointer;" @click="toggleSort('overdue_count')">
                                    Overdue<span x-text="sortIcon('overdue_count')"></span>
                                </th>
                                <th class="text-center" style="cursor:pointer;" @click="toggleSort('avg_xp')">
                                    Avg XP<span x-text="sortIcon('avg_xp')"></span>
                                </th>
                                <th class="text-center">Streaks</th>
                                <th class="text-center">Inactive</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="s in filtered" :key="s.slug">
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:2rem;height:2rem;background:linear-gradient(135deg, rgba(219, 234, 254, 0.98), rgba(199, 210, 254, 0.98));">
                                                <i class="bi bi-building text-primary" style="font-size:.8rem;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold" x-text="s.name"></div>
                                                <div class="text-secondary" style="font-size:.7rem;" x-text="s.course_completed + '/' + s.course_assigned + ' courses completed'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-semibold" x-text="s.user_count"></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success" x-text="s.active_count"></span>
                                        <template x-if="s.suspended_count > 0">
                                            <span class="badge bg-danger-subtle text-danger" x-text="s.suspended_count + ' susp.'"></span>
                                        </template>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success" x-text="s.verified_count"></span>
                                        <template x-if="s.unverified_count > 0">
                                            <span class="badge bg-warning-subtle text-warning" x-text="s.unverified_count + ' unv.'"></span>
                                        </template>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <div class="progress" style="width:4rem;height:.4rem;">
                                                <div class="progress-bar"
                                                     :class="s.completion_rate >= 80 ? 'bg-success' : (s.completion_rate >= 50 ? 'bg-warning' : 'bg-danger')"
                                                     :style="'width:' + s.completion_rate + '%'"></div>
                                            </div>
                                            <span class="badge"
                                                  :class="s.completion_rate >= 80 ? 'bg-success-subtle text-success' : (s.completion_rate >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger')"
                                                  x-text="s.completion_rate + '%'"></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <template x-if="s.overdue_count > 0">
                                            <span class="badge bg-danger-subtle text-danger" x-text="s.overdue_count"></span>
                                        </template>
                                        <template x-if="s.overdue_count === 0">
                                            <span class="text-success">0</span>
                                        </template>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-medium" x-text="s.avg_xp.toLocaleString()"></span>
                                    </td>
                                    <td class="text-center">
                                        <template x-if="s.active_streaks > 0">
                                            <span class="badge bg-warning-subtle text-warning" x-text="s.active_streaks"></span>
                                        </template>
                                        <template x-if="s.active_streaks === 0">
                                            <span class="text-secondary">0</span>
                                        </template>
                                    </td>
                                    <td class="text-center">
                                        <template x-if="s.never_logged_in > 0 || s.inactive_30 > 0">
                                            <span class="badge bg-warning-subtle text-warning"
                                                  :title="'Never logged in: ' + s.never_logged_in + ', Inactive 30+: ' + s.inactive_30"
                                                  x-text="s.never_logged_in + s.inactive_30"></span>
                                        </template>
                                        <template x-if="s.never_logged_in === 0 && s.inactive_30 === 0">
                                            <span class="text-success">0</span>
                                        </template>
                                    </td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <a class="btn btn-sm btn-link btn-square no-caret text-primary" data-bs-toggle="dropdown"><i class="bi bi-three-dots fs-5"></i></a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" :href="s.users_url">View Staff</a></li>
                                                <li><a class="dropdown-item" :href="s.compliance_url">Compliance Report</a></li>
                                                <li><a class="dropdown-item" :href="s.analytics_url">Course Analytics</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filtered.length === 0">
                                <td colspan="10" class="text-secondary text-center py-4">
                                    <template x-if="allStats.length === 0">
                                        <span>No locations have been created yet. <a href="{{ $manageUrl }}">Add locations</a> to get started.</span>
                                    </template>
                                    <template x-if="allStats.length > 0">
                                        <span>No locations match the current filters. <a href="#" @click.prevent="resetFilters()">Reset filters</a></span>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light" x-show="filtered.length > 0">
                            <tr class="fw-semibold">
                                <td>
                                    <span x-text="isFiltered() ? 'Filtered Total' : 'Trust Total'"></span>
                                </td>
                                <td class="text-center" x-text="kpi.users"></td>
                                <td class="text-center">&mdash;</td>
                                <td class="text-center">&mdash;</td>
                                <td class="text-center">
                                    <span class="badge"
                                          :class="kpi.completionRate >= 80 ? 'bg-success-subtle text-success' : (kpi.completionRate >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger')"
                                          x-text="kpi.completionRate + '%'"></span>
                                </td>
                                <td class="text-center" x-text="kpi.overdue"></td>
                                <td class="text-center" colspan="4">&mdash;</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
function locationComparison(stats) {
    return {
        allStats: stats,
        search: '',
        schoolFilter: '',
        roleFilter: '',
        teamFilter: '',
        completionFilter: '',
        overdueFilter: '',
        sortCol: 'name',
        sortAsc: true,

        get availableRoles() {
            const roles = new Set();
            this.allStats.forEach(s => s.roles.forEach(r => roles.add(r)));
            return [...roles].sort();
        },

        get availableTeams() {
            const teams = new Set();
            this.allStats.forEach(s => s.teams.forEach(t => teams.add(t)));
            return [...teams].sort();
        },

        get filtered() {
            let rows = this.allStats;

            if (this.search) {
                const q = this.search.toLowerCase();
                rows = rows.filter(s =>
                    s.name.toLowerCase().includes(q) ||
                    s.employees.some(e => e.toLowerCase().includes(q))
                );
            }

            if (this.schoolFilter) {
                rows = rows.filter(s => s.slug === this.schoolFilter);
            }

            if (this.roleFilter) {
                rows = rows.filter(s => s.roles.includes(this.roleFilter));
            }

            if (this.teamFilter) {
                rows = rows.filter(s => s.teams.includes(this.teamFilter));
            }

            if (this.completionFilter === 'high') {
                rows = rows.filter(s => s.completion_rate >= 80);
            } else if (this.completionFilter === 'medium') {
                rows = rows.filter(s => s.completion_rate >= 50 && s.completion_rate < 80);
            } else if (this.completionFilter === 'low') {
                rows = rows.filter(s => s.completion_rate < 50);
            }

            if (this.overdueFilter === 'overdue') {
                rows = rows.filter(s => s.overdue_count > 0);
            } else if (this.overdueFilter === 'clean') {
                rows = rows.filter(s => s.overdue_count === 0);
            }

            // Sort
            const col = this.sortCol;
            const asc = this.sortAsc;
            rows = [...rows].sort((a, b) => {
                let av = a[col], bv = b[col];
                if (typeof av === 'string') {
                    return asc ? av.localeCompare(bv) : bv.localeCompare(av);
                }
                return asc ? av - bv : bv - av;
            });

            return rows;
        },

        get kpi() {
            const f = this.filtered;
            const users = f.reduce((sum, s) => sum + s.user_count, 0);
            const assigned = f.reduce((sum, s) => sum + s.course_assigned, 0);
            const completed = f.reduce((sum, s) => sum + s.course_completed, 0);
            const overdue = f.reduce((sum, s) => sum + s.overdue_count, 0);
            const completionRate = assigned > 0 ? Math.round((completed / assigned) * 1000) / 10 : 0;
            return { users, completionRate, overdue };
        },

        isFiltered() {
            return this.search !== '' || this.schoolFilter !== '' || this.roleFilter !== '' || this.teamFilter !== '' || this.completionFilter !== '' || this.overdueFilter !== '';
        },

        resetFilters() {
            this.search = '';
            this.schoolFilter = '';
            this.roleFilter = '';
            this.teamFilter = '';
            this.completionFilter = '';
            this.overdueFilter = '';
        },

        toggleSort(col) {
            if (this.sortCol === col) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortCol = col;
                this.sortAsc = col === 'name';
            }
        },

        sortIcon(col) {
            if (this.sortCol !== col) return '';
            return this.sortAsc ? ' ↑' : ' ↓';
        },

        schoolLabel() {
            const s = this.allStats.find(s => s.slug === this.schoolFilter);
            return s ? s.name : '';
        },

        completionLabel() {
            return { high: 'High (80%+)', medium: 'Medium (50-79%)', low: 'Low (<50%)' }[this.completionFilter] || '';
        },

        overdueLabel() {
            return { overdue: 'Has overdue', clean: 'No overdue' }[this.overdueFilter] || '';
        },
    };
}
</script>
@endsection
