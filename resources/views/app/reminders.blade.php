@extends('layouts.learninguiux')

@section('title', 'Reminders - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .learner-reminder-panel,
    .learner-reminder-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

    .learner-reminder-row + .learner-reminder-row {
        border-top: 1px solid rgba(120, 145, 185, 0.18);
    }
</style>
@endpush

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'reminders'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4">
            <div class="card learner-reminder-panel mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="learner-reminder-title d-inline-block mb-2">Reminders</span>
                            <h3 class="mb-1">Reinforcement follow-ups</h3>
                            <p class="text-secondary mb-0">Keep key learning alive after completion and record evidence that knowledge is still current.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">Back to dashboard</a>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="card learner-reminder-panel mb-4 border-success-subtle bg-success-subtle">
                    <div class="card-body px-4 py-3 text-success-emphasis">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <div class="card learner-reminder-panel">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <span class="learner-reminder-title d-inline-block mb-2">Reinforcement</span>
                            <h3 class="mb-1">Ongoing reinforcement + proof</h3>
                            <p class="text-secondary mb-0">These follow-ups keep key learning alive after completion and record evidence that knowledge is still current.</p>
                        </div>
                    </div>

                    @forelse ($reinforcementTouchpoints as $touchpoint)
                        <div class="learner-reminder-row py-3">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        @if ($touchpoint->module)
                                            <a href="{{ route('app.modules.show', ['module' => $touchpoint->module->id]) }}" class="fw-semibold text-decoration-none">
                                                {{ $touchpoint->module->title }}
                                            </a>
                                        @else
                                            <span class="fw-semibold">{{ $touchpoint->title }}</span>
                                        @endif
                                        <span class="badge rounded-pill text-bg-light border">{{ $touchpoint->interval_days }}-day follow-up</span>
                                        <span class="badge rounded-pill {{ $touchpoint->computed_status === 'completed' ? 'text-bg-success' : ($touchpoint->computed_status === 'due' ? 'text-bg-warning' : 'text-bg-light border') }}">
                                            {{ ucfirst($touchpoint->computed_status) }}
                                        </span>
                                    </div>
                                    <div class="small text-secondary">
                                        Due {{ optional($touchpoint->due_on)->format('Y-m-d') ?? 'n/a' }}
                                        @if (!empty($touchpoint->metadata['source_type']))
                                            | {{ strtoupper((string) $touchpoint->metadata['source_type']) }}
                                        @endif
                                    </div>
                                    <div class="small text-secondary mt-1">{{ $touchpoint->prompt }}</div>
                                    @if ($touchpoint->proof_summary)
                                        <div class="small text-success-emphasis mt-1">{{ $touchpoint->proof_summary }}</div>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    @if ($touchpoint->module)
                                        <a href="{{ route('app.modules.show', ['module' => $touchpoint->module->id]) }}" class="btn btn-outline-theme btn-sm">Open module</a>
                                    @endif
                                    @if ($touchpoint->computed_status !== 'completed')
                                        @if ($touchpoint->reinforcement_question_set_id)
                                            <a href="{{ route('app.reinforcement.show', ['touchpoint' => $touchpoint->id]) }}" class="btn btn-theme btn-sm">Answer questions</a>
                                        @else
                                            <form method="POST" action="{{ route('app.reinforcement.complete', ['touchpoint' => $touchpoint->id]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-theme btn-sm">Record proof</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-4 border bg-light p-4 text-secondary">
                            No reinforcement follow-ups are queued yet. They will appear automatically after completed modules reach their next check-in window.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
