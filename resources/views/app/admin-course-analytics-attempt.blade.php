@extends('layouts.learninguiux')

@section('title', 'Attempt Details - Course Analytics')
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
    .analytics-section-title {
        font-size: 0.78rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #5f7699;
        font-weight: 700;
    }
    .question-card {
        border-left: 4px solid #e2e8f0;
        border-radius: 12px;
        background: rgba(248, 250, 252, 0.95);
        padding: 1.25rem 1.5rem;
        transition: border-color 200ms;
    }
    .question-card.correct {
        border-left-color: #10b981;
    }
    .question-card.incorrect {
        border-left-color: #ef4444;
    }
    .option-row {
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    .option-row.is-correct {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .option-row.is-selected-wrong {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .option-row.neutral {
        background: transparent;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }
</style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content" style="max-width:900px;">

            {{-- Back link --}}
            <div class="mb-3">
                <a href="{{ route('app.admin.course-analytics') }}" class="text-decoration-none text-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Course Analytics
                </a>
            </div>

            @php
                $passed = $attempt->status === 'completed';
                $score = number_format((float) $attempt->score_percent, 1);
            @endphp

            {{-- Summary hero --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:24px;overflow:hidden;">
                <div class="card-body p-0">
                    <div style="background:linear-gradient(135deg,{{ $passed ? '#059669,#10b981' : '#dc2626,#ef4444' }});padding:2rem 2.5rem;color:#fff;">
                        <div class="text-uppercase fw-semibold mb-1" style="font-size:.72rem;letter-spacing:.2em;opacity:.8;">Knowledge Check Attempt</div>
                        <h2 class="fw-semibold mb-1">{{ $course->title ?? 'Unknown Course' }}</h2>
                        <p class="mb-0" style="opacity:.85;">
                            {{ $learner->name ?? 'Unknown Learner' }}
                            @if ($learner?->email)
                                <span style="opacity:.7;">&mdash; {{ $learner->email }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-6 col-md-3 text-center">
                                <div class="fs-2 fw-bold {{ $passed ? 'text-success' : 'text-danger' }}">{{ $score }}%</div>
                                <div class="small text-secondary">Score</div>
                            </div>
                            <div class="col-6 col-md-3 text-center">
                                <div class="fs-2 fw-bold text-primary">{{ $correctCount }}/{{ $totalQuestions }}</div>
                                <div class="small text-secondary">Correct</div>
                            </div>
                            <div class="col-6 col-md-3 text-center">
                                <div class="fs-2 fw-bold {{ $passed ? 'text-success' : 'text-warning' }}">
                                    <i class="bi {{ $passed ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                                </div>
                                <div class="small text-secondary">{{ $passed ? 'Passed' : 'Gaps Found' }}</div>
                            </div>
                            <div class="col-6 col-md-3 text-center">
                                <div class="fs-5 fw-bold text-secondary">{{ $attempt->completed_at?->format('d M Y') ?? '---' }}</div>
                                <div class="small text-secondary">{{ $attempt->completed_at?->format('H:i') ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Question-by-question breakdown --}}
            <div class="card analytics-card mb-4">
                <div class="card-body p-4">
                    <div class="analytics-section-title mb-3">Question-by-Question Breakdown</div>

                    @forelse ($responses as $index => $response)
                        @php
                            $question = $response->question;
                            $isCorrect = (bool) $response->is_correct;
                            $options = $question?->options ?? [];
                            $correctAnswer = $question?->correct_answer;
                            $selectedAnswer = $response->selected_answer;
                        @endphp

                        <div class="question-card {{ $isCorrect ? 'correct' : 'incorrect' }} mb-3">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 {{ $isCorrect ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}" style="width:32px;height:32px;font-size:.85rem;">
                                    @if ($isCorrect)
                                        <i class="bi bi-check-lg"></i>
                                    @else
                                        <i class="bi bi-x-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">Question {{ $index + 1 }}</div>
                                    <div class="text-secondary mt-1">{{ $question?->question_text ?? 'Question text unavailable' }}</div>
                                </div>
                            </div>

                            {{-- Options --}}
                            <div class="ms-5 mt-2">
                                @foreach ($options as $letter => $text)
                                    @php
                                        $isThisCorrect = strtoupper((string) $letter) === strtoupper((string) $correctAnswer);
                                        $isThisSelected = strtoupper((string) $letter) === strtoupper((string) $selectedAnswer);
                                        $optionClass = 'neutral';
                                        if ($isThisCorrect) {
                                            $optionClass = 'is-correct';
                                        } elseif ($isThisSelected && !$isCorrect) {
                                            $optionClass = 'is-selected-wrong';
                                        }
                                    @endphp
                                    <div class="option-row {{ $optionClass }} d-flex align-items-center gap-2">
                                        <span class="fw-bold" style="min-width:1.5rem;">{{ strtoupper($letter) }}.</span>
                                        <span class="flex-grow-1">{{ $text }}</span>
                                        @if ($isThisCorrect)
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        @elseif ($isThisSelected && !$isCorrect)
                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Explanation --}}
                            @if ($question?->explanation)
                                <div class="ms-5 mt-2 p-2 rounded bg-light">
                                    <div class="small text-secondary"><i class="bi bi-info-circle me-1"></i> {{ $question->explanation }}</div>
                                </div>
                            @endif

                            {{-- Remediation module (only if wrong) --}}
                            @if (!$isCorrect && $response->question?->remediationModule)
                                <div class="ms-5 mt-2">
                                    <a href="{{ route('app.admin.modules.edit', $response->question->remediation_learning_module_id) }}" class="small text-decoration-none text-warning">
                                        <i class="bi bi-arrow-repeat me-1"></i> Remediation: {{ $response->question->remediationModule->title }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-3 bg-light p-4 text-secondary text-center">
                            No question responses recorded for this attempt.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Admin actions --}}
            <div class="d-flex justify-content-center gap-3 mb-4">
                <a href="{{ route('app.admin.course-analytics') }}" class="btn btn-outline-primary px-4" style="border-radius:12px;">
                    <i class="bi bi-arrow-left me-1"></i> Back to Analytics
                </a>
                @if ($learner)
                    <a href="{{ route('app.admin.assignments.user', $learner) }}" class="btn btn-outline-secondary px-4" style="border-radius:12px;">
                        <i class="bi bi-person me-1"></i> View Learner
                    </a>
                @endif
                @if ($course)
                    <a href="{{ route('app.admin.courses.edit', $course) }}" class="btn btn-outline-secondary px-4" style="border-radius:12px;">
                        <i class="bi bi-pencil me-1"></i> Edit Course
                    </a>
                @endif
            </div>

        </div>
    </main>
</div>
@endsection
