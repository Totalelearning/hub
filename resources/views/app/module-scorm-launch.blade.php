@extends('layouts.learninguiux')

@section('title', $module->title.' - SCORM Launch')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .learner-launch-panel,
    .learner-launch-frame,
    .learner-launch-summary {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
    }

    .learner-launch-panel,
    .learner-launch-frame,
    .learner-launch-summary {
        background: rgba(255, 255, 255, 0.97);
    }

    .learner-launch-chip {
        border-radius: 999px;
        border: 1px solid rgba(110, 143, 198, 0.22);
        background: rgba(255, 255, 255, 0.82);
        color: #566f96;
    }

    .learner-launch-summary {
        min-height: 132px;
    }
</style>
@endpush

@section('content')
@php
    $progressPercent = (int) ($progress?->percent_complete ?? 0);
    $progressStatus = ucfirst(str_replace('_', ' ', (string) ($progress?->status ?? 'not_started')));
    $runtimeStatus = ucfirst(str_replace('_', ' ', (string) ($latestRuntime?->metadata['status'] ?? $latestRuntime?->metadata['lesson_status'] ?? 'live')));
@endphp
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4">
            <div class="card learner-launch-panel mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-2">SCORM Demo Course</div>
                            <h3 class="mb-1">{{ $module->title }}</h3>
                            <p class="text-secondary mb-0">SCORM prototype launch. Runtime progress is tracked into the existing module progress model.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.modules.show', ['module' => $module->id]) }}" class="btn btn-outline-theme btn-sm">Back to Module</a>
                            <a href="{{ route('app.feed') }}" class="btn btn-theme btn-sm">Learner Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card learner-launch-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Player</div>
                            <div class="fw-semibold fs-5 mt-1">Embedded learner player</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card learner-launch-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Tracking</div>
                            <div class="fw-semibold fs-5 mt-1">Progress and score tracked</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card learner-launch-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Learner status</div>
                            <div class="fw-semibold fs-5 mt-1">{{ $progressStatus }} | {{ $progressPercent }}%</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card learner-launch-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Runtime</div>
                            <div class="fw-semibold fs-5 mt-1">{{ $runtimeStatus }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if (($progress?->status ?? null) === 'completed')
                <div class="card learner-launch-frame mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                            <div>
                                <div class="small text-uppercase text-secondary fw-semibold mb-2">Completion Recorded</div>
                                <h3 class="mb-1">This SCORM course is complete</h3>
                                <p class="text-secondary mb-0">Your learner record already shows this module as complete, so the best next action is to move into the next required or path step.</p>
                            </div>
                            <div class="rounded-4 border bg-light px-4 py-3 align-self-start">
                                <div class="small text-secondary">Progress</div>
                                <div class="fw-semibold mt-1">{{ $progressPercent }}%</div>
                            </div>
                        </div>
                        @if (($completionNextActions ?? collect())->isNotEmpty())
                            <div class="row g-3">
                                @foreach ($completionNextActions as $action)
                                    <div class="col-lg-4">
                                        <div class="rounded-4 border bg-light p-4 h-100">
                                            <div class="small text-secondary">{{ $action['label'] }}</div>
                                            <div class="fw-semibold fs-5 mt-2">{{ $action['title'] }}</div>
                                            <p class="small text-secondary mt-2 mb-3">{{ $action['summary'] }}</p>
                                            <a href="{{ $action['href'] }}" class="btn btn-outline-theme btn-sm">{{ $action['cta'] }}</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card learner-launch-frame">
                <div class="card-body p-2 p-lg-3">
                    <iframe
                        src="{{ $launchUrl }}"
                        title="{{ $module->title }} SCORM content"
                        style="width: 100%; min-height: 720px; border: 0; border-radius: 22px; background: #fff;"
                    ></iframe>
                </div>
            </div>

            <div class="card learner-launch-frame mt-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-2">Learner Tracking</div>
                            <h3 class="mb-2">What happens when you interact with this course</h3>
                            <p class="text-secondary mb-0">Progress, completion, score, session time, and lesson location are written back into the learner record and the admin SCORM reporting trail.</p>
                        </div>
                        <div class="rounded-4 border bg-light px-4 py-3 align-self-start">
                            <div class="small text-secondary">Latest lesson location</div>
                            <div class="fw-semibold mt-1 text-break">{{ $latestRuntime?->metadata['lesson_location'] ?? 'n/a' }}</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-self-start">
                            <a href="{{ route('app.modules.show', ['module' => $module->id]) }}" class="btn btn-outline-theme btn-sm">Module summary</a>
                            @if (auth()->user()?->is_admin)
                                <a href="{{ route('app.admin.scorm.index') }}" class="btn btn-outline-theme btn-sm">SCORM Overview</a>
                                <a href="{{ route('app.admin.compliance', ['source_type' => 'scorm']) }}" class="btn btn-outline-theme btn-sm">SCORM Compliance</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
