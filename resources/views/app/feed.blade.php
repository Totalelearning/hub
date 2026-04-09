@extends('layouts.learninguiux')

@section('title', 'For You Feed - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .learner-hero-card,
    .learner-panel-card,
    .learner-metric-card,
    .learner-spotlight-card {
        border: 0;
        border-radius: 28px;
        box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
    }

    .learner-hero-card {
        background: linear-gradient(135deg, rgba(236, 249, 255, 0.98), rgba(247, 245, 255, 0.98));
        overflow: hidden;
    }

    .learner-hero-card::after {
        content: '';
        position: absolute;
        inset: auto -10% -35% auto;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(42, 104, 255, 0.18), rgba(42, 104, 255, 0));
        pointer-events: none;
    }

    .learner-hero-band,
    .learner-spotlight-band,
    .learner-mini-band {
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
    }

    .learner-metric-card {
        background: rgba(255, 255, 255, 0.96);
        min-height: 180px;
    }

    .learner-panel-card,
    .learner-spotlight-card {
        background: rgba(255, 255, 255, 0.98);
    }

    .learner-metric-icon {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(35, 93, 255, 0.12), rgba(129, 174, 255, 0.28));
        color: #2454db;
    }

    .learner-progress-ring {
        --progress: 0;
        --ring-color: #1463cf;
        width: 112px;
        height: 112px;
        border-radius: 50%;
        background:
            radial-gradient(closest-side, #fff 72%, transparent 73% 100%),
            conic-gradient(var(--ring-color) calc(var(--progress) * 1%), rgba(212, 228, 255, 0.8) 0);
        box-shadow: inset 0 0 0 1px rgba(20, 99, 207, 0.08);
    }

    .learner-progress-ring-lg {
        width: 132px;
        height: 132px;
    }

    .learner-progress-copy {
        color: #6880a5;
    }

    .learner-activity-item + .learner-activity-item,
    .learner-reminder-item + .learner-reminder-item {
        border-top: 1px solid rgba(120, 145, 185, 0.18);
    }

    .learner-section-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

    .learner-empty-card {
        border-radius: 24px;
        border: 1px dashed rgba(73, 120, 194, 0.3);
        background: rgba(244, 249, 255, 0.9);
    }

    .learner-course-banner {
        border: 0;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        background: linear-gradient(135deg, rgba(225, 239, 255, 0.96), rgba(247, 245, 255, 0.98));
    }

    .learner-course-chip-card {
        border: 0;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
    }

    .learner-course-chip-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(35, 93, 255, 0.12), rgba(129, 174, 255, 0.28));
        color: #2454db;
    }
</style>
@endpush

@section('content')
@php
    $learnerFirstName = \Illuminate\Support\Str::of(auth()->user()->name ?? 'Learner')->before(' ')->toString();
    $nextDueAt = $dashboardSummary['next_due_at'] ?? null;
    $activeProgress = (int) ($activeModule?->user_progress_percent ?? 0);
    $secondaryProgress = (int) ($secondarySpotlightModule?->user_progress_percent ?? 0);
    $averageProgress = (int) ($dashboardSummary['average_progress_percent'] ?? 0);
    $completionRate = (int) ($dashboardSummary['completion_rate_percent'] ?? 0);
    $learnerTopics = collect($preference?->topics ?? [])->filter()->values();
    $activePathNextStep = $activePath->next_step ?? null;
    $categoryIcons = [
        'development' => 'bi-code-square',
        'business' => 'bi-briefcase',
        'finance' => 'bi-cash-coin',
        'accounting' => 'bi-bank',
        'productivity' => 'bi-gear-wide-connected',
        'personal' => 'bi-person-arms-up',
        'design' => 'bi-palette',
        'marketing' => 'bi-newspaper',
        'music' => 'bi-music-note-beamed',
        'it' => 'bi-motherboard',
        'office' => 'bi-building',
        'compliance' => 'bi-shield-check',
        'health' => 'bi-heart-pulse',
        'safety' => 'bi-cone-striped',
        'general' => 'bi-journal-bookmark',
    ];
@endphp

@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            @if ($modules->isEmpty())
                <div class="card learner-empty-card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4 mb-0">
                                <i class="bi bi-inboxes"></i>
                            </div>
                            <div>
                                <h5 class="mb-2">Your feed is empty</h5>
                                <p class="text-secondary mb-3">Create your first booster, or seed sample content for local testing.</p>
                                <div class="bg-light rounded p-3">
                                    <code>docker compose exec -T laravel.test php artisan db:seed</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row align-items-center mb-4">
                    <!-- Welcome section -->
                    <div class="col-12 col-md-6 col-lg-9 mb-4 mb-lg-0">
                        <h1>Hi, <span class="text-theme-1">{{ $learnerFirstName }}</span></h1>
                        <h2 class="mb-4">{{ $activeModule ? 'Keep your learning moving' : 'Start your learning today' }}</h2>
                        <p>"The only way to do great work is to love what you do."<br><span class="text-secondary"> - Steve Jobs</span></p>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @if ($activeModule)
                                <a href="{{ route('app.modules.show', ['module' => $activeModule->id]) }}" class="btn btn-theme">
                                    {{ ($activeModule->user_progress_status ?? 'not_started') === 'in_progress' ? 'Continue Course' : 'Open Course' }}
                                </a>
                            @endif
                            <a href="{{ route('app.feed.required') }}" class="btn btn-outline-theme">Browse Required</a>
                            <a href="{{ route('app.feed.recommended') }}" class="btn btn-outline-theme">Browse Recommended</a>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="badge rounded-pill text-bg-light border">Role: {{ $preference?->role ?: 'not set' }}</span>
                            <span class="badge rounded-pill text-bg-light border">Goal: {{ $preference?->goal ?: 'not set' }}</span>
                            <span class="badge rounded-pill text-bg-light border">Completion: {{ $completionRate }}%</span>
                        </div>
                    </div>
                    <!-- Active course card -->
                    <div class="col-12 col-md-6 col-lg-3">
                        @if ($activeModule)
                            <div class="card adminuiux-card shadow-sm overflow-hidden">
                                <div class="card-body">
                                    <figure class="height-120 w-100 coverimg rounded mb-3">
                                        <img src="{{ asset('vendor/learninguiux/img/learning/banner.png') }}" alt="{{ $activeModule->title }}">
                                    </figure>
                                    <h6 class="mb-0">{{ $activeModule->title }}</h6>
                                    <p class="small text-secondary">{{ ucfirst(str_replace('_', ' ', $activeModule->topic ?? 'Course')) }}</p>
                                    <div class="row gx-3 align-items-center">
                                        <div class="col">
                                            <p class="text-secondary small mb-0"><i class="bi bi-file-play"></i> {{ $activeProgress }}% complete</p>
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ route('app.modules.show', ['module' => $activeModule->id]) }}" class="btn btn-sm btn-square btn-success"><i class="bi bi-play"></i></a>
                                        </div>
                                    </div>
                                    <div class="progress position-absolute w-100 bottom-0 start-0 rounded-0" style="height:5px;" role="progressbar" aria-label="Course progress" aria-valuenow="{{ $activeProgress }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar progress-bar-striped bg-success" style="width: {{ $activeProgress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card adminuiux-card shadow-sm">
                                <div class="card-body text-center py-5">
                                    <div class="avatar avatar-60 rounded-circle bg-theme-1-subtle text-theme-1 h3 mb-3 mx-auto">
                                        <i class="bi bi-mortarboard"></i>
                                    </div>
                                    <p class="small text-secondary mb-0">No active course yet</p>
                                    <a href="{{ route('app.feed.required') }}" class="btn btn-theme btn-sm mt-3">Start a course</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card adminuiux-card shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <div class="avatar avatar-60 rounded-circle bg-primary-subtle text-primary h3 mb-3 mx-auto">
                                    <i class="bi bi-play-circle-fill"></i>
                                </div>
                                <h3 class="mb-0">{{ $dashboardSummary['in_progress_total'] ?? 0 }}</h3>
                                <p class="fw-medium mb-1">In Progress</p>
                                <p class="text-secondary small mb-2">Active courses</p>
                                <span class="badge bg-primary-subtle text-primary">{{ $averageProgress }}% avg progress</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card adminuiux-card shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <div class="avatar avatar-60 rounded-circle bg-warning-subtle text-warning h3 mb-3 mx-auto">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h3 class="mb-0">{{ $assignmentSummary['required_total'] ?? 0 }}</h3>
                                <p class="fw-medium mb-1">Required</p>
                                <p class="text-secondary small mb-2">Mandatory learning</p>
                                <span class="badge bg-warning-subtle text-warning">{{ $assignmentSummary['required_compliance_scoped'] ?? 0 }} compliance</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card adminuiux-card shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <div class="avatar avatar-60 rounded-circle bg-success-subtle text-success h3 mb-3 mx-auto">
                                    <i class="bi bi-patch-check-fill"></i>
                                </div>
                                <h3 class="mb-0">{{ $dashboardSummary['completed_total'] ?? 0 }}</h3>
                                <p class="fw-medium mb-1">Completed</p>
                                <p class="text-secondary small mb-2">Finished modules</p>
                                <span class="badge bg-success-subtle text-success">{{ $completionRate }}% rate</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card adminuiux-card shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <div class="avatar avatar-60 rounded-circle bg-info-subtle text-info h3 mb-3 mx-auto">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <h5 class="mb-0">{{ $nextDueAt ? \Illuminate\Support\Carbon::parse($nextDueAt)->format('M d') : '—' }}</h5>
                                <p class="fw-medium mb-1">Next Due</p>
                                <p class="text-secondary small mb-2">Assignment deadline</p>
                                <span class="badge bg-info-subtle text-info">{{ $dashboardSummary['saved_total'] ?? 0 }} saved</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if (($assignedCourses ?? collect())->isNotEmpty())
                    <div class="card learner-panel-card mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                                <div>
                                    <span class="learner-section-title d-inline-block mb-2">Your Courses</span>
                                    <h3 class="mb-1">Continue your learning</h3>
                                    <p class="text-secondary mb-0">Courses assigned to you based on your role.</p>
                                </div>
                            </div>
                            <div class="row">
                                @foreach ($assignedCourses as $course)
                                    @include('app.partials.feed-course-card', ['course' => $course])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card learner-panel-card mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <span class="learner-section-title d-inline-block mb-2">Recent Activity</span>
                        <h3 class="mb-1">Learning timeline</h3>
                        <p class="text-secondary mb-4">Your latest learning interactions tracked from learner progress records.</p>
                        <div class="row g-3">
                            @forelse ($recentLearningActivity->take(5) as $activity)
                                <div class="col">
                                    <div class="rounded-4 border bg-light p-4 h-100">
                                        <div class="small text-secondary mb-1">{{ optional($activity['occurred_at'])->diffForHumans() }}</div>
                                        <div class="fw-semibold fs-5 mt-1">{{ $activity['module_title'] }}</div>
                                        <p class="small text-secondary mb-0 mt-2">{{ $activity['summary'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="rounded-4 border bg-light p-4 text-secondary">
                                        No recent learner activity has been recorded yet.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>

<footer class="adminuiux-footer has-adminuiux-sidebar mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col py-2">
                <span class="small">Learner dashboard uses live module, progress, reminder, and activity data.</span>
            </div>
        </div>
    </div>
</footer>
@endsection
