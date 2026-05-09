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
                        <h1 class="fs-3 fw-semibold mb-2">Course Analytics</h1>
                        <p class="text-secondary mb-0">Completion rates, knowledge check performance, and gap trends across all published courses.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('app.admin.course-analytics.export') }}" class="btn btn-theme btn-sm me-2"><i class="bi bi-download me-1"></i>Export CSV</a>
                        <a href="{{ route('app.admin.modules.index') }}" class="btn btn-outline-theme btn-sm">Manage Courses</a>
                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="analytics-stat-card">
                        <div class="small text-secondary">Published Courses</div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ $summary['total_courses'] }}</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="analytics-stat-card">
                        <div class="small text-secondary">Total Enrolments</div>
                        <div class="fs-3 fw-bold mt-1">{{ number_format($summary['total_assigned']) }}</div>
                        <div class="small text-secondary">{{ number_format($summary['total_completed']) }} completed</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="analytics-stat-card">
                        <div class="small text-secondary">Completion Rate</div>
                        <div class="fs-3 fw-bold {{ $summary['overall_completion_rate'] >= 75 ? 'text-success' : ($summary['overall_completion_rate'] >= 50 ? 'text-warning' : 'text-danger') }} mt-1">{{ $summary['overall_completion_rate'] }}%</div>
                        <div class="analytics-progress-bar mt-2">
                            <div class="bar {{ $summary['overall_completion_rate'] >= 75 ? 'bg-success' : ($summary['overall_completion_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $summary['overall_completion_rate'] }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="analytics-stat-card">
                        <div class="small text-secondary">Avg Knowledge Check Score</div>
                        <div class="fs-3 fw-bold {{ ($summary['overall_avg_score'] ?? 0) >= 75 ? 'text-success' : (($summary['overall_avg_score'] ?? 0) >= 50 ? 'text-warning' : 'text-danger') }} mt-1">
                            {{ $summary['overall_avg_score'] !== null ? $summary['overall_avg_score'] . '%' : '—' }}
                        </div>
                        <div class="small text-secondary">{{ number_format($summary['total_reinforcement_attempts']) }} quizzes taken</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Course Performance Table --}}
                <div class="col-12" x-data="ajaxPager('{{ route('app.admin.course-analytics.courses-json') }}', 'courses')" x-init="load()">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="analytics-section-title mb-3">Course Performance</div>
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
                                                            <span x-text="c.total_attempts"></span>
                                                            <div class="small text-secondary" x-text="c.passed + 'P / ' + c.failed + 'F'"></div>
                                                        </div>
                                                    </template>
                                                    <template x-if="c.total_attempts === 0">
                                                        <span class="text-secondary">&mdash;</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 text-center">
                                                    <template x-if="c.pass_rate !== null">
                                                        <span class="badge" :class="c.pass_rate >= 75 ? 'bg-success-subtle text-success' : (c.pass_rate >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger')" x-text="c.pass_rate + '%'"></span>
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
                        </div>
                    </div>
                </div>

                {{-- Knowledge Gap Hotspots --}}
                <div class="col-lg-6" x-data="ajaxPager('{{ route('app.admin.course-analytics.hotspots-json') }}', 'hotspots')" x-init="load()">
                    <div class="card analytics-card h-100">
                        <div class="card-body p-4">
                            <div class="analytics-section-title mb-1">Knowledge Gap Hotspots</div>
                            <p class="small text-secondary mb-3">Modules with the most incorrect answers across all knowledge checks to date. Useful for identifying content that may need improving.</p>

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
                            <template x-for="(gap, i) in items" :key="i">
                                <div class="d-flex align-items-center gap-3 py-2" :class="{ 'border-bottom': i < items.length - 1 }">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger flex-shrink-0" style="width:36px;height:36px;">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small" x-text="gap.module_title"></div>
                                    </div>
                                    <div>
                                        <span class="badge bg-danger-subtle text-danger" x-text="gap.incorrect_count + ' wrong'"></span>
                                    </div>
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
                        </div>
                    </div>
                </div>

                {{-- Learners Needing Attention --}}
                <div class="col-lg-6" x-data="ajaxPager('{{ route('app.admin.course-analytics.gaps') }}', 'gaps')" x-init="load()">
                    <div class="card analytics-card h-100">
                        <div class="card-body p-4">
                            <div class="analytics-section-title mb-1">Learners Needing Attention</div>
                            <p class="small text-secondary mb-3">Learners who failed a knowledge check and haven't yet re-completed the course.</p>

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
                            <template x-for="(learner, i) in items" :key="i">
                                <div class="d-flex align-items-center gap-3 py-2" :class="{ 'border-bottom': i < items.length - 1 }">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning flex-shrink-0" style="width:36px;height:36px;">
                                        <i class="bi bi-person-exclamation"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small" x-text="learner.name"></div>
                                        <div class="small text-secondary" x-text="learner.course_title"></div>
                                    </div>
                                    <template x-if="learner.attempt_url">
                                        <a :href="learner.attempt_url" class="btn btn-sm btn-outline-danger me-1">View Gaps</a>
                                    </template>
                                    <a :href="learner.course_url" class="btn btn-sm btn-outline-secondary">View</a>
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
                        </div>
                    </div>
                </div>

                {{-- Recent Knowledge Check Results --}}
                <div class="col-12" x-data="ajaxPager('{{ route('app.admin.course-analytics.attempts-json') }}', 'attempts')" x-init="load()">
                    <div class="card analytics-card mb-4">
                        <div class="card-body p-4">
                            <div class="analytics-section-title mb-3">Recent Knowledge Check Results</div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2">Learner</th>
                                            <th class="px-3 py-2">Course</th>
                                            <th class="px-3 py-2 text-center">Score</th>
                                            <th class="px-3 py-2 text-center">Result</th>
                                            <th class="px-3 py-2">Completed</th>
                                            <th class="px-3 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="loading">
                                            <tr><td colspan="6" class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm me-1" role="status"></div> Loading&hellip;</td></tr>
                                        </template>
                                        <template x-if="!loading && items.length === 0">
                                            <tr><td colspan="6" class="text-center py-4 text-secondary">No knowledge check results yet.</td></tr>
                                        </template>
                                        <template x-for="a in items" :key="a.id">
                                            <tr>
                                                <td class="px-3">
                                                    <div class="fw-medium" x-text="a.learner_name"></div>
                                                    <div class="small text-secondary" x-text="a.learner_email"></div>
                                                </td>
                                                <td class="px-3" x-text="a.course_title"></td>
                                                <td class="px-3 text-center">
                                                    <span class="fw-bold" :class="a.score_percent >= 75 ? 'text-success' : (a.score_percent >= 50 ? 'text-warning' : 'text-danger')" x-text="a.score_percent + '%'"></span>
                                                </td>
                                                <td class="px-3 text-center">
                                                    <template x-if="a.status === 'completed'">
                                                        <span class="badge bg-success-subtle text-success">Passed</span>
                                                    </template>
                                                    <template x-if="a.status !== 'completed'">
                                                        <span class="badge bg-danger-subtle text-danger">Gaps Found</span>
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
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
function ajaxPager(url, name) {
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

        load() {
            this.fetchPage(1);
        },

        goTo(p) {
            if (p < 1 || p > this.lastPage || this.loading) return;
            this.fetchPage(p);
        },

        async fetchPage(p) {
            this.loading = true;
            try {
                const sep = this._url.includes('?') ? '&' : '?';
                const res = await fetch(`${this._url}${sep}page=${p}`, {
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
