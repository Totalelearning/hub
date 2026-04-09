@extends('layouts.learninguiux')

@section('title', 'Results - ' . $course->title)
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content" style="max-width:760px;">

            @php
                $passed = $attempt->status === 'completed';
                $score = (int) $attempt->score_percent;
                $totalQuestions = $attempt->metadata['total_questions'] ?? 0;
                $correctCount = $attempt->metadata['correct_count'] ?? 0;
            @endphp

            <div class="card border-0 shadow-sm mb-4" style="border-radius:24px;overflow:hidden;">
                <div class="card-body p-0">
                    <div style="background:linear-gradient(135deg,{{ $passed ? '#059669,#10b981' : '#dc2626,#ef4444' }});padding:2rem 2.5rem;color:#fff;">
                        <div class="text-uppercase fw-semibold mb-1" style="font-size:.72rem;letter-spacing:.2em;opacity:.8;">Knowledge Check Result</div>
                        <h2 class="fw-semibold mb-1">{{ $course->title }}</h2>
                        <p class="mb-0" style="opacity:.85;">
                            @if ($passed)
                                Great work! You passed the knowledge check.
                            @else
                                Some knowledge gaps were found. We've reassigned the relevant modules.
                            @endif
                        </p>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-4 text-center">
                                <div class="fs-2 fw-bold {{ $passed ? 'text-success' : 'text-danger' }}">{{ $score }}%</div>
                                <div class="small text-secondary">Score</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fs-2 fw-bold text-primary">{{ $correctCount }}/{{ $totalQuestions }}</div>
                                <div class="small text-secondary">Correct</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fs-2 fw-bold {{ $passed ? 'text-success' : 'text-warning' }}">
                                    <i class="bi {{ $passed ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                                </div>
                                <div class="small text-secondary">{{ $passed ? 'Passed' : 'Gaps Found' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($reassignedModules->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-1">Reassigned Modules</h5>
                        <p class="text-secondary small mb-3">These modules have been reset so you can review the material and close the knowledge gap.</p>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($reassignedModules as $module)
                                <a href="{{ route('app.modules.show', ['module' => $module->id, 'course' => $course->id]) }}" class="d-flex align-items-center gap-3 border rounded-3 p-3 text-decoration-none">
                                    <div class="avatar avatar-40 rounded bg-warning-subtle text-warning">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $module->title }}</div>
                                        <div class="small text-secondary">Progress has been reset - please complete this module again</div>
                                    </div>
                                    <i class="bi bi-chevron-right ms-auto text-secondary"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-center gap-3 mb-4">
                <a href="{{ route('app.courses.show', $course) }}" class="btn btn-primary btn-lg px-5" style="border-radius:12px;">Back to Course</a>
                <a href="{{ route('app.feed') }}" class="btn btn-outline-primary btn-lg px-5" style="border-radius:12px;">Back to Dashboard</a>
            </div>

        </div>
    </main>
</div>
@endsection
