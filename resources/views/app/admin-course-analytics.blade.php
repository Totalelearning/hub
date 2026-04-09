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
                <div class="col-12">
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
                                        @forelse ($courses as $course)
                                            @php
                                                $stats = $course->stats;
                                                $reinf = $course->reinforcement;
                                            @endphp
                                            <tr>
                                                <td class="px-3">
                                                    <a href="{{ route('app.admin.courses.edit', $course) }}" class="fw-semibold text-decoration-none">{{ $course->title }}</a>
                                                    <div class="small text-secondary">{{ $course->modules_count }} modules</div>
                                                </td>
                                                <td class="px-3 text-center">{{ $stats['assigned'] }}</td>
                                                <td class="px-3 text-center">
                                                    <span class="fw-semibold">{{ $stats['completed'] }}</span>
                                                    @if ($stats['in_progress'] > 0)
                                                        <div class="small text-secondary">{{ $stats['in_progress'] }} in progress</div>
                                                    @endif
                                                </td>
                                                <td class="px-3 text-center">
                                                    @if ($stats['assigned'] > 0)
                                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                                            <div class="analytics-progress-bar flex-grow-1" style="max-width:80px;">
                                                                <div class="bar {{ $stats['completion_rate'] >= 75 ? 'bg-success' : ($stats['completion_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $stats['completion_rate'] }}%"></div>
                                                            </div>
                                                            <span class="fw-medium">{{ $stats['completion_rate'] }}%</span>
                                                        </div>
                                                    @else
                                                        <span class="text-secondary">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 text-center">
                                                    @if ($reinf['total_attempts'] > 0)
                                                        {{ $reinf['total_attempts'] }}
                                                        <div class="small text-secondary">{{ $reinf['passed'] }}P / {{ $reinf['failed'] }}F</div>
                                                    @else
                                                        <span class="text-secondary">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 text-center">
                                                    @if ($reinf['pass_rate'] !== null)
                                                        <span class="badge {{ $reinf['pass_rate'] >= 75 ? 'bg-success-subtle text-success' : ($reinf['pass_rate'] >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }}">{{ $reinf['pass_rate'] }}%</span>
                                                    @else
                                                        <span class="text-secondary">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 text-center">
                                                    @if ($reinf['avg_score'] !== null)
                                                        <span class="fw-medium">{{ $reinf['avg_score'] }}%</span>
                                                    @else
                                                        <span class="text-secondary">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center py-4 text-secondary">No published courses yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Knowledge Gap Hotspots & Learners Needing Attention --}}
                <div class="col-lg-6">
                    <div class="card analytics-card h-100">
                        <div class="card-body p-4">
                            <div class="analytics-section-title mb-1">Knowledge Gap Hotspots</div>
                            <p class="small text-secondary mb-3">Modules with the most incorrect answers across all knowledge checks to date. Useful for identifying content that may need improving.</p>

                            @forelse ($topGapModules as $gap)
                                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger flex-shrink-0" style="width:36px;height:36px;">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small">{{ $gap['module_title'] }}</div>
                                    </div>
                                    <div>
                                        <span class="badge bg-danger-subtle text-danger">{{ $gap['incorrect_count'] }} wrong</span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-3 bg-light p-4 text-secondary text-center">
                                    No knowledge check data yet. Gap hotspots will appear once learners take quizzes.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card analytics-card h-100">
                        <div class="card-body p-4">
                            <div class="analytics-section-title mb-1">Learners Needing Attention</div>
                            <p class="small text-secondary mb-3">Learners who failed a knowledge check and haven't yet re-completed the course.</p>

                            @forelse ($learnersWithGaps as $learner)
                                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning flex-shrink-0" style="width:36px;height:36px;">
                                        <i class="bi bi-person-exclamation"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small">{{ $learner->name }}</div>
                                        <div class="small text-secondary">{{ $learner->course_title }}</div>
                                    </div>
                                    <a href="{{ route('app.admin.courses.edit', $learner->course_id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </div>
                            @empty
                                <div class="rounded-3 bg-light p-4 text-secondary text-center">
                                    No learners with knowledge gaps right now.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Recent Knowledge Check Results --}}
                <div class="col-12">
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentAttempts as $attempt)
                                            <tr>
                                                <td class="px-3">
                                                    <div class="fw-medium">{{ $attempt->user?->name ?? 'Unknown' }}</div>
                                                    <div class="small text-secondary">{{ $attempt->user?->email ?? '' }}</div>
                                                </td>
                                                <td class="px-3">{{ $attempt->course?->title ?? 'Unknown' }}</td>
                                                <td class="px-3 text-center">
                                                    <span class="fw-bold {{ ($attempt->score_percent ?? 0) >= 75 ? 'text-success' : (($attempt->score_percent ?? 0) >= 50 ? 'text-warning' : 'text-danger') }}">
                                                        {{ $attempt->score_percent }}%
                                                    </span>
                                                </td>
                                                <td class="px-3 text-center">
                                                    @if ($attempt->status === 'completed')
                                                        <span class="badge bg-success-subtle text-success">Passed</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Gaps Found</span>
                                                    @endif
                                                </td>
                                                <td class="px-3">
                                                    <span class="small text-secondary">{{ $attempt->completed_at?->diffForHumans() ?? '—' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center py-4 text-secondary">No knowledge check results yet.</td></tr>
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
