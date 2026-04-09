@extends('layouts.learninguiux')

@section('title', 'Knowledge Check - ' . $course->title)
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content" style="max-width:760px;">

            <div class="card border-0 shadow-sm mb-4" style="border-radius:24px;overflow:hidden;">
                <div class="card-body p-0">
                    <div style="background:linear-gradient(135deg,#3b82f6,#6366f1);padding:2rem 2.5rem;color:#fff;">
                        <div class="text-uppercase fw-semibold mb-1" style="font-size:.72rem;letter-spacing:.2em;opacity:.8;">Knowledge Check</div>
                        <h2 class="fw-semibold mb-1">{{ $course->title }}</h2>
                        <p class="mb-0" style="opacity:.85;">Answer the questions below. If you get any wrong, the relevant module will be reassigned so you can brush up.</p>
                    </div>
                    <div class="p-4">
                        <div class="d-flex gap-3 mb-0">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">{{ $questions->count() }} question{{ $questions->count() === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('course-reinforcement.submit', ['token' => $attempt->token]) }}">
                @csrf

                @foreach ($questions as $index => $question)
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-light border text-dark">Q{{ $index + 1 }}</span>
                                <span class="small text-secondary">{{ $question->source_module_title }}</span>
                            </div>
                            <h6 class="fw-semibold mb-3">{{ $question->question_text }}</h6>

                            @if (is_array($question->options))
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($question->options as $key => $optionText)
                                        <label class="d-flex align-items-center gap-3 border rounded-3 p-3" style="cursor:pointer;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" class="form-check-input mt-0" style="min-width:18px;">
                                            <span><strong class="me-1">{{ $key }}.</strong> {{ $optionText }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if ($questions->isNotEmpty())
                    <div class="d-flex justify-content-end mb-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5" style="border-radius:12px;">Submit Answers</button>
                    </div>
                @else
                    <div class="card border-0 shadow-sm" style="border-radius:16px;">
                        <div class="card-body p-4 text-center text-secondary">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                            No questions are available for this course. You're all set!
                        </div>
                    </div>
                @endif
            </form>

        </div>
    </main>
</div>
@endsection
