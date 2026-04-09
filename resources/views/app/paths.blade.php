@extends('layouts.learninguiux')

@section('title', 'Paths - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .learner-path-panel,
    .learner-path-summary {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .learner-path-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

    .learner-path-summary {
        min-height: 132px;
    }
</style>
@endpush

@section('content')
@php
    $activePathNextStep = $activePath->next_step ?? null;
@endphp
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'paths'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4">
            <div class="card learner-path-panel mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="learner-path-title d-inline-block mb-2">Paths</span>
                            <h3 class="mb-1">Follow the right sequence for your role.</h3>
                            <p class="text-secondary mb-0">Published path rules, live completion state, and the next unlocked step all meet here in one course-space view.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">Back to dashboard</a>
                            <a href="{{ route('app.reminders') }}" class="btn btn-theme btn-sm">Open reminders</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="card learner-path-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Visible paths</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $paths->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card learner-path-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Completed paths</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $paths->filter(fn ($path) => ($path->summary['completed_steps'] ?? 0) === ($path->summary['total_steps'] ?? 0) && ($path->summary['total_steps'] ?? 0) > 0)->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card learner-path-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">With overdue steps</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $paths->filter(fn ($path) => ($path->summary['overdue_steps'] ?? 0) > 0)->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card learner-path-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">With due soon steps</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $paths->filter(fn ($path) => ($path->summary['due_soon_steps'] ?? 0) > 0)->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($latestCompletedProgress)
                <div class="card learner-path-panel mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <span class="learner-path-title d-inline-block mb-2">Milestone</span>
                                <h3 class="mb-1">Recent learning completion recorded</h3>
                                <p class="text-secondary mb-0">
                                    Your learner record shows a completed module from {{ $latestCompletedProgress->completed_at?->format('M d, Y H:i') ?? 'recent activity' }}.
                                    Paths will now unlock the next available step based on that completion state.
                                </p>
                                @if ($activePathNextStep)
                                    <div class="rounded-4 border bg-light p-3 mt-3">
                                        <div class="small text-secondary">What to do next</div>
                                        <div class="fw-semibold mt-1">{{ $activePathNextStep['module']->title }}</div>
                                        <div class="small text-secondary mt-1">
                                            This is the next unlocked step in your active path after your recent completion.
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            <a href="{{ route('app.modules.show', ['module' => $activePathNextStep['module']->id]) }}" class="btn btn-theme btn-sm">Open next step</a>
                                            <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">Return to dashboard</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="rounded-4 border bg-light px-4 py-3 align-self-start">
                                <div class="small text-secondary">Last completion</div>
                                <div class="fw-semibold mt-1">{{ $latestCompletedProgress->percent_complete }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @forelse ($paths as $path)
                <div class="card learner-path-panel mb-4">
                    <div class="card-body p-4 p-lg-5">
                        @php
                            $nextOpenStep = collect($path->step_states)->first(fn ($state) => ! $state['is_completed'] && $state['is_unlocked']);
                        @endphp
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                            <div>
                                <span class="learner-path-title d-inline-block mb-2">Sequence</span>
                                <h3 class="mb-1">{{ $path->title }}</h3>
                                <p class="text-secondary mb-0">{{ $path->description }}</p>
                            </div>
                            <div class="text-lg-end">
                                <div class="small text-secondary">{{ $path->summary['completed_steps'] }}/{{ $path->summary['total_steps'] }} complete</div>
                                <div class="fs-3 fw-semibold">{{ $path->summary['percent_complete'] }}%</div>
                                @if (($path->summary['overdue_steps'] ?? 0) > 0)
                                    <div class="small text-danger mt-1">{{ $path->summary['overdue_steps'] }} overdue step(s)</div>
                                @elseif (($path->summary['due_soon_steps'] ?? 0) > 0)
                                    <div class="small text-warning mt-1">{{ $path->summary['due_soon_steps'] }} due soon step(s)</div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-4 border bg-light p-4 mb-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <div class="small text-secondary">Next best action</div>
                                    <div class="fw-semibold fs-5 mt-1">
                                        {{ $nextOpenStep['module']->title ?? 'This path is fully complete' }}
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        @if ($nextOpenStep)
                                            Step {{ $nextOpenStep['step']->position }} is unlocked and ready.
                                        @else
                                            There are no unlocked incomplete steps left in this path.
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-self-start">
                                    @if ($nextOpenStep)
                                        <a href="{{ route('app.modules.show', ['module' => $nextOpenStep['module']->id]) }}" class="btn btn-theme btn-sm">Open next step</a>
                                    @endif
                                    <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">Back to dashboard</a>
                                </div>
                            </div>
                        </div>

                        <div class="progress mb-4" role="progressbar" aria-label="Path progress" aria-valuenow="{{ $path->summary['percent_complete'] }}" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $path->summary['percent_complete'] }}%"></div>
                        </div>

                        <div class="row g-3">
                            @foreach ($path->step_states as $stepState)
                                @php
                                    $step = $stepState['step'];
                                    $module = $stepState['module'];
                                    $isLocked = ! $stepState['is_unlocked'] && ! $stepState['is_completed'];
                                    $assignment = $stepState['assignment'] ?? [];
                                    $isOverdue = (bool) ($assignment['is_overdue'] ?? false);
                                    $isDueSoon = ! $isOverdue && (bool) ($assignment['is_due_soon'] ?? false);
                                @endphp
                                <div class="col-xl-6">
                                    <div class="rounded-4 border {{ $isOverdue ? 'border-danger-subtle bg-danger-subtle' : ($isDueSoon ? 'border-warning-subtle bg-warning-subtle' : 'bg-light') }} p-4 h-100">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $step->position }}. {{ $module?->title }}</div>
                                                <div class="small text-secondary mt-1">
                                                    {{ $module?->difficulty ?: 'any level' }}
                                                    @if ($module?->topic)
                                                        | {{ $module->topic }}
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="badge rounded-pill {{ $stepState['is_completed'] ? 'text-bg-success' : ($isLocked ? 'text-bg-warning' : 'text-bg-light border') }}">
                                                {{ $stepState['is_completed'] ? 'Completed' : ($isLocked ? 'Locked' : 'Open') }}
                                            </span>
                                        </div>

                                        <div class="small text-secondary mt-3">
                                            @if ($stepState['is_completed'])
                                                Completed in your learner record.
                                            @elseif ($isLocked)
                                                Locked
                                                @if ($stepState['locked_until'])
                                                    until {{ $stepState['locked_until']->format('Y-m-d H:i') }}
                                                @else
                                                    until the previous step is complete.
                                                @endif
                                            @elseif (($stepState['delay_days'] ?? 0) > 0)
                                                Unlock delay: {{ $stepState['delay_days'] }} day(s) after the previous step completes.
                                            @else
                                                Ready to start now.
                                            @endif
                                        </div>

                                        @if ($isOverdue || $isDueSoon)
                                            <div class="small mt-3 {{ $isOverdue ? 'text-danger-emphasis' : 'text-warning-emphasis' }}">
                                                {{ $isOverdue ? 'This step needs attention immediately.' : 'This step is approaching its due date.' }}
                                            </div>
                                        @endif

                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            @if ($isOverdue)
                                                <span class="badge rounded-pill text-bg-danger">Overdue</span>
                                            @elseif ($isDueSoon)
                                                <span class="badge rounded-pill text-bg-warning">Due soon</span>
                                            @endif
                                            @if ($module?->source_type)
                                                <span class="badge rounded-pill text-bg-light border">{{ strtoupper($module->source_type) }}</span>
                                            @endif
                                        </div>

                                        @if ($module && ! $isLocked)
                                            <div class="mt-3">
                                                <a href="{{ route('app.modules.show', ['module' => $module->id]) }}" class="btn {{ $isOverdue ? 'btn-danger' : ($isDueSoon ? 'btn-warning' : 'btn-theme') }} btn-sm">Open module</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="card learner-path-panel">
                    <div class="card-body p-4 p-lg-5 text-secondary">
                        No learning paths are assigned to your current role yet.
                    </div>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection
