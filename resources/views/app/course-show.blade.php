@extends('layouts.learninguiux')

@section('title', $course->title . ' - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .course-show-panel {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .course-show-hero {
        background: linear-gradient(135deg, rgba(244, 250, 255, 0.98), rgba(248, 247, 255, 0.98));
        overflow: hidden;
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
    }

    .course-progress-ring {
        --progress: 0;
        --ring-color: #1463cf;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background:
            radial-gradient(closest-side, #fff 72%, transparent 73% 100%),
            conic-gradient(var(--ring-color) calc(var(--progress) * 1%), rgba(212, 228, 255, 0.85) 0);
        box-shadow: inset 0 0 0 1px rgba(20, 99, 207, 0.08);
    }

    .course-module-step {
        transition: background-color 150ms ease, border-color 150ms ease;
    }

    .course-module-step:hover {
        background-color: rgba(244, 250, 255, 0.95) !important;
    }

    .course-section-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }
</style>
@endpush

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content" style="max-width:860px;">

            {{-- Hero --}}
            <div class="card course-show-hero mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg-8">
                            <a href="{{ route('app.feed') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">
                                <i class="bi bi-arrow-left me-1"></i> Back to dashboard
                            </a>
                            @if ($course->topic)
                                <div class="badge bg-primary-subtle text-primary rounded-pill mb-2">{{ ucfirst(str_replace('_', ' ', $course->topic)) }}</div>
                            @endif
                            <h2 class="fw-bold mb-2">{{ $course->title }}</h2>
                            @if ($course->description)
                                <p class="text-secondary mb-3">{{ $course->description }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill text-bg-light border">
                                    <i class="bi bi-collection me-1"></i> {{ $totalModules }} module{{ $totalModules === 1 ? '' : 's' }}
                                </span>
                                @if ($course->estimated_minutes)
                                    <span class="badge rounded-pill text-bg-light border">
                                        <i class="bi bi-clock me-1"></i> {{ $course->estimated_minutes }} min
                                    </span>
                                @endif
                                @if ($courseStatus === 'completed')
                                    <span class="badge rounded-pill bg-success-subtle text-success">
                                        <i class="bi bi-check-circle me-1"></i> Completed{{ $courseCompletedAt ? ' ' . $courseCompletedAt->diffForHumans() : '' }}
                                    </span>
                                @elseif ($courseStatus === 'in_progress')
                                    <span class="badge rounded-pill bg-info-subtle text-info">
                                        <i class="bi bi-play-circle me-1"></i> In progress
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 text-center">
                            <div class="course-progress-ring d-flex align-items-center justify-content-center mx-auto" style="--progress:{{ $overallPercent }};--ring-color:{{ $overallPercent === 100 ? '#059669' : '#1463cf' }};">
                                <div>
                                    <div class="fs-3 fw-bold {{ $overallPercent === 100 ? 'text-success' : 'text-primary' }}">{{ $overallPercent }}%</div>
                                    <div class="small text-secondary">Complete</div>
                                </div>
                            </div>
                            <div class="small text-secondary mt-2">{{ $completedModules }}/{{ $totalModules }} modules done</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reinforcement status banner --}}
            @if ($reinforcementStatus === 'sent' && $latestAttempt && in_array($latestAttempt->status, ['sent', 'in_progress']))
                <div class="card course-show-panel mb-4 border-warning border-opacity-25">
                    <div class="card-body p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-clipboard-check text-warning fs-5"></i>
                                <h6 class="fw-semibold mb-0">Knowledge Check Available</h6>
                            </div>
                            <p class="text-secondary small mb-0">A knowledge check has been sent to reinforce your learning for this course. Complete it to confirm your understanding.</p>
                        </div>
                        <a href="{{ route('course-reinforcement.show', ['token' => $latestAttempt->token]) }}" class="btn btn-warning btn-sm text-nowrap">Take Knowledge Check</a>
                    </div>
                </div>
            @elseif ($reinforcementStatus === 'gaps_found')
                <div class="card course-show-panel mb-4 border-danger border-opacity-25">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-triangle text-danger fs-5"></i>
                            <h6 class="fw-semibold mb-0">Knowledge Gaps Found</h6>
                        </div>
                        <p class="text-secondary small mb-0">Some modules have been reassigned based on your knowledge check results. Complete the highlighted modules below to close the gaps.</p>
                    </div>
                </div>
            @elseif ($reinforcementStatus === 'passed' && $courseStatus === 'completed')
                <div class="card course-show-panel mb-4 border-success border-opacity-25">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                            <h6 class="fw-semibold mb-0">Knowledge Verified</h6>
                        </div>
                        <p class="text-secondary small mb-0">You passed the most recent knowledge check. Another check will be sent in {{ $course->reinforcement_delay_days ?? 30 }} days to keep your learning fresh.</p>
                    </div>
                </div>
            @endif

            {{-- Continue / Start action --}}
            @if ($nextModule && $courseStatus !== 'completed')
                <div class="card course-show-panel mb-4">
                    <div class="card-body p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="course-section-title d-inline-block mb-1">{{ $nextModule['status'] === 'in_progress' ? 'Continue where you left off' : 'Up next' }}</span>
                            <h5 class="fw-semibold mb-1">{{ $nextModule['module']->title }}</h5>
                            @if ($nextModule['status'] === 'in_progress')
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;max-width:200px;">
                                        <div class="progress-bar" style="width:{{ $nextModule['percent_complete'] }}%"></div>
                                    </div>
                                    <span class="small text-secondary">{{ $nextModule['percent_complete'] }}%</span>
                                </div>
                            @else
                                <p class="small text-secondary mb-0">Ready to start</p>
                            @endif
                        </div>
                        <a href="{{ route('app.modules.show', ['module' => $nextModule['module']->id, 'course' => $course->id]) }}" class="btn btn-primary btn-lg px-4" style="border-radius:14px;">
                            {{ $nextModule['status'] === 'in_progress' ? 'Continue' : 'Start' }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Module list --}}
            <div class="card course-show-panel mb-4">
                <div class="card-body p-4">
                    <span class="course-section-title d-inline-block mb-3">Course Modules</span>

                    <div class="d-flex flex-column gap-2">
                        @foreach ($modulesWithProgress as $index => $item)
                            @php
                                $isCompleted = $item['status'] === 'completed';
                                $isInProgress = $item['status'] === 'in_progress';
                                $isNotStarted = $item['status'] === 'not_started';
                            @endphp
                            <a href="{{ route('app.modules.show', ['module' => $item['module']->id, 'course' => $course->id]) }}"
                               class="d-flex align-items-center gap-3 rounded-3 border p-3 text-decoration-none course-module-step {{ $isCompleted ? 'border-success border-opacity-25' : '' }}">

                                {{-- Step indicator --}}
                                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                     style="width:44px;height:44px;
                                     {{ $isCompleted ? 'background:#059669;color:#fff;' : ($isInProgress ? 'background:rgba(20,99,207,0.12);color:#1463cf;' : 'background:rgba(148,163,184,0.15);color:#94a3b8;') }}">
                                    @if ($isCompleted)
                                        <i class="bi bi-check-lg fs-5"></i>
                                    @elseif ($isInProgress)
                                        <i class="bi bi-play-fill fs-5"></i>
                                    @else
                                        <span class="fw-semibold">{{ $index + 1 }}</span>
                                    @endif
                                </div>

                                {{-- Module info --}}
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark">{{ $item['module']->title }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        @if ($isCompleted)
                                            <span class="badge bg-success-subtle text-success">Completed</span>
                                            @if ($item['completed_at'])
                                                <span class="small text-secondary">{{ \Carbon\Carbon::parse($item['completed_at'])->diffForHumans() }}</span>
                                            @endif
                                        @elseif ($isInProgress)
                                            <div class="progress" style="height:5px;width:100px;">
                                                <div class="progress-bar" style="width:{{ $item['percent_complete'] }}%"></div>
                                            </div>
                                            <span class="small text-secondary">{{ $item['percent_complete'] }}%</span>
                                        @else
                                            <span class="small text-secondary">Not started</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Arrow --}}
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Course completed celebration --}}
            @if ($courseStatus === 'completed' && $overallPercent === 100)
                <div class="card course-show-panel mb-4" style="background:linear-gradient(135deg,rgba(5,150,105,0.06),rgba(16,185,129,0.06));">
                    <div class="card-body p-4 text-center">
                        <div class="fs-1 mb-2">
                            <i class="bi bi-trophy-fill text-success"></i>
                        </div>
                        <h4 class="fw-bold text-success mb-1">Course Completed!</h4>
                        <p class="text-secondary mb-3">You've completed all {{ $totalModules }} modules in this course.{{ $course->reinforcement_delay_days ? ' A knowledge check will be sent in ' . $course->reinforcement_delay_days . ' days to reinforce your learning.' : '' }}</p>
                        <a href="{{ route('app.feed') }}" class="btn btn-outline-success px-4" style="border-radius:12px;">Back to Dashboard</a>
                    </div>
                </div>
            @endif

        </div>
    </main>
</div>
@endsection
