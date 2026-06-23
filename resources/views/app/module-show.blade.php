@extends('layouts.learninguiux')

@section('title', 'Booster Details - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .learner-module-hero,
    .learner-module-card,
    .learner-module-panel {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .learner-module-hero {
        background: linear-gradient(135deg, rgba(244, 250, 255, 0.98), rgba(248, 247, 255, 0.98));
        overflow: hidden;
    }

    .learner-module-band {
        border-radius: 20px;
        background: rgba(246, 249, 255, 0.98);
    }

    .learner-module-ring {
        --progress: 0;
        --ring-color: #1463cf;
        width: 126px;
        height: 126px;
        border-radius: 50%;
        background:
            radial-gradient(closest-side, #fff 72%, transparent 73% 100%),
            conic-gradient(var(--ring-color) calc(var(--progress) * 1%), rgba(212, 228, 255, 0.85) 0);
        box-shadow: inset 0 0 0 1px rgba(20, 99, 207, 0.08);
    }

    .learner-module-section-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

</style>
@endpush

@section('content')
@php
    $progressPercent = (int) ($progress?->percent_complete ?? ($latestScormRuntime['percent_complete'] ?? 0));
    $progressStatus = (string) ($progress?->status ?? ($latestScormRuntime['status'] ?? 'not_started'));
    $launchCount = (int) ($scormActivitySummary['launch_count'] ?? 0);
    $attemptCount = (int) ($scormActivitySummary['attempt_count'] ?? 0);
    $statusLabel = ucfirst(str_replace('_', ' ', $progressStatus));
    $assignmentDueAt = $assignment['renewal']['due_at'] ?? ($renewal['due_at'] ?? null);
    $isDue = (bool) ($renewal['is_due'] ?? false);
    $isDueSoon = ! $isDue && (bool) ($renewal['is_due_soon'] ?? false);
    $heroTone = $isDue
        ? 'background: linear-gradient(135deg, rgba(255, 232, 232, 0.98), rgba(255, 245, 238, 0.98));'
        : ($isDueSoon
            ? 'background: linear-gradient(135deg, rgba(255, 246, 222, 0.98), rgba(255, 250, 236, 0.98));'
            : 'background: linear-gradient(135deg, rgba(236, 249, 255, 0.98), rgba(247, 245, 255, 0.98));');
    $bandTone = $isDue
        ? 'background: linear-gradient(135deg, rgba(255, 239, 239, 0.95), rgba(255, 247, 242, 0.95));'
        : ($isDueSoon
            ? 'background: linear-gradient(135deg, rgba(255, 247, 229, 0.95), rgba(255, 251, 241, 0.95));'
            : 'background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));');
    $primaryButtonClass = $isDue ? 'btn-danger' : ($isDueSoon ? 'btn-warning' : 'btn-theme');
@endphp

@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4">
            @if (!empty($courseContext))
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;background:linear-gradient(135deg,rgba(244,250,255,0.95),rgba(248,247,255,0.95));">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('app.courses.show', $courseContext['course']) }}" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;" title="Back to course">
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                                <div>
                                    <div class="small text-secondary">Part of</div>
                                    <a href="{{ route('app.courses.show', $courseContext['course']) }}" class="fw-semibold text-decoration-none text-dark">{{ $courseContext['course']->title }}</a>
                                </div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill">Module {{ $courseContext['current_index'] + 1 }} of {{ $courseContext['total_modules'] }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if ($courseContext['prev_module'])
                                    <a href="{{ route('app.modules.show', ['module' => $courseContext['prev_module']->id, 'course' => $courseContext['course']->id]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="{{ $courseContext['prev_module']->title }}">
                                        <i class="bi bi-chevron-left me-1"></i> Previous
                                    </a>
                                @endif
                                @if ($courseContext['next_module'])
                                    <a href="{{ route('app.modules.show', ['module' => $courseContext['next_module']->id, 'course' => $courseContext['course']->id]) }}" class="btn btn-sm btn-primary rounded-pill px-3" title="{{ $courseContext['next_module']->title }}">
                                        Next <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row align-items-center mb-4">
                    <div class="col-12 col-md">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-3 mb-md-0">
                                <li class="breadcrumb-item bi"><a href="{{ route('app.feed') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active bi" aria-current="page">Module</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-12 col-md-auto">
                        <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            @endif

            <div class="card learner-module-hero mb-4" id="module-summary" style="{{ $heroTone }}">
                <div class="card-body p-4 p-lg-5">
                    <div class="row g-4 align-items-start">
                        <div class="col-xl-8">
                            <span class="learner-module-section-title d-inline-block mb-2">Booster Detail</span>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge rounded-pill text-bg-light border text-uppercase fw-semibold">booster</span>
                                @if ($module->is_required)
                                    <span class="badge rounded-pill text-bg-danger">Required</span>
                                @endif
                                @if ($renewal['is_due'] ?? false)
                                    <span class="badge rounded-pill text-bg-danger">Refresh Due</span>
                                @elseif ($renewal['is_due_soon'] ?? false)
                                    <span class="badge rounded-pill text-bg-warning">Refresh Soon</span>
                                @endif
                                @if ($module->topic)
                                    <span class="badge rounded-pill text-bg-light border">{{ $module->topic }}</span>
                                @endif
                                @if ($module->difficulty)
                                    <span class="badge rounded-pill text-bg-light border">{{ $module->difficulty }}</span>
                                @endif
                                @if (!empty($roleTargeting['target_roles']))
                                    <span class="badge rounded-pill text-bg-light border">Role: {{ implode(', ', $roleTargeting['target_roles']) }}</span>
                                @endif
                                @if (!empty($complianceTargeting['compliance_area']))
                                    <span class="badge rounded-pill text-bg-light border">Compliance: {{ $complianceTargeting['compliance_area'] }}</span>
                                @endif
                                @if (!empty($prerequisites['required_module_ids']))
                                    <span class="badge rounded-pill text-bg-light border">Prerequisites: {{ count($prerequisites['required_module_ids']) }}</span>
                                @endif
                                @if ($module->requires_acknowledgement)
                                    <span class="badge rounded-pill text-bg-light border">Acknowledgement Required</span>
                                @endif
                                @if ($isSaved ?? false)
                                    <span class="badge rounded-pill text-bg-light border">
                                        <i class="bi bi-bookmark-check me-1"></i> Saved
                                    </span>
                                @endif
                            </div>
                            <h2 class="fw-semibold mb-3">{{ $module->title }}</h2>
                            @if ($module->source_type === 'scorm')
                                <div class="mb-3 d-flex flex-wrap gap-2">
                                    <span class="badge rounded-pill border border-info-subtle bg-info-subtle text-dark">Demo Scenario: Client Walkthrough</span>
                                    @if (\App\Support\ScormDemoScenario::isPrimaryDemoCourse($module))
                                        <span class="badge rounded-pill text-bg-info">Primary Demo Course</span>
                                    @endif
                                </div>
                            @endif
                            <div class="d-flex flex-wrap gap-3 text-secondary mb-4">
                                <span><i class="bi bi-box-seam me-1"></i>{{ $module->source_type ?: 'manual' }}</span>
                                @if ($module->compliance_area)
                                    <span><i class="bi bi-shield-check me-1"></i>{{ $module->compliance_area }}</span>
                                @endif
                                @if ($module->refresh_interval_days)
                                    <span><i class="bi bi-arrow-repeat me-1"></i>Refresh every {{ $module->refresh_interval_days }} days</span>
                                @endif
                                @if (!empty($renewal['due_at']))
                                    <span><i class="bi bi-clock-history me-1"></i>Due {{ $renewal['due_at']->format('M d, Y') }}</span>
                                @endif
                                <span><i class="bi bi-calendar3 me-1"></i>{{ optional($module->created_at)->format('M d, Y') }}</span>
                            </div>
                            @if ($isDue || $isDueSoon)
                                <div class="rounded-4 border {{ $isDue ? 'border-danger-subtle bg-danger-subtle text-danger-emphasis' : 'border-warning-subtle bg-warning-subtle text-warning-emphasis' }} p-3 mb-4">
                                    <div class="fw-semibold">{{ $isDue ? 'This module needs attention now' : 'This module is coming due soon' }}</div>
                                    <div class="small mt-1">
                                        @if ($assignmentDueAt)
                                            {{ $isDue ? 'Your assignment is overdue.' : 'Your assignment is nearing its due date.' }} Due {{ \Illuminate\Support\Carbon::parse($assignmentDueAt)->format('M d, Y') }}.
                                        @else
                                            {{ $isDue ? 'This required item is currently overdue in your learner record.' : 'This required item should be completed soon.' }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <p class="mb-0 text-secondary">{{ $module->description }}</p>
                        </div>
                        <div class="col-xl-4">
                            <div class="learner-module-band p-4" style="{{ $bandTone }}">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="learner-module-ring d-flex align-items-center justify-content-center flex-shrink-0" style="--progress: {{ max(0, min($progressPercent, 100)) }};">
                                        <div class="text-center">
                                            <div class="fs-2 fw-semibold text-dark">{{ $progressPercent }}%</div>
                                            <div class="small text-secondary">progress</div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-secondary mb-1">Current learner state</div>
                                        <div class="fs-3 fw-semibold">{{ $statusLabel }}</div>
                                        <p class="small text-secondary mb-0">
                                            Feed score: {{ (int) ($score['score'] ?? 0) }}<br>
                                            Goal alignment: {{ $preference?->goal ?: 'none set' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="row g-3 mt-3">
                                    <div class="col-md-6">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="small text-secondary">Risk level</div>
                                            <div class="fw-semibold mt-1">{{ $moduleActionSummary['risk_label'] ?? 'On track' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="small text-secondary">Last learner activity</div>
                                            <div class="fw-semibold mt-1">{{ $moduleActionSummary['last_activity_at'] ? optional($moduleActionSummary['last_activity_at'])->diffForHumans() : 'No activity yet' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    @if ($module->source_type === 'scorm' && $latestScormAsset)
                                        <a href="{{ route('app.modules.scorm.launch', ['module' => $module->id]) }}" class="btn {{ $primaryButtonClass }}">
                                            <i class="bi bi-play-circle me-1"></i> Launch SCORM Prototype
                                        </a>
                                    @endif
                                    @if ($isSaved ?? false)
                                        <form method="POST" action="{{ route('app.feed.unsave', ['module' => $module->id]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-theme">
                                                <i class="bi bi-bookmark-check me-1"></i> Saved
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('app.feed.save', ['module' => $module->id]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-theme">
                                                <i class="bi bi-bookmark me-1"></i> Save module
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('app.reminders', ['module_id' => $module->id]) }}" class="btn btn-outline-theme">
                                        <i class="bi bi-bell me-1"></i> Open reminders
                                    </a>
                                    <a href="{{ route('app.feed') }}" class="btn btn-outline-theme">Back to dashboard</a>
                                </div>
                                <div class="rounded-4 border bg-light p-3 mt-3">
                                    <div class="small text-secondary">Next action</div>
                                    <div class="small text-secondary mt-1">{{ $moduleActionSummary['last_activity_summary'] }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <a href="{{ $moduleActionSummary['next_action_href'] ?? '#module-summary' }}" class="btn {{ $primaryButtonClass }} btn-sm">{{ $moduleActionSummary['next_action_label'] }}</a>
                                        <a href="{{ $moduleActionSummary['reminder_href'] }}" class="btn btn-outline-theme btn-sm">Module reminders</a>
                                    </div>
                                </div>
                                @if (($completionNextActions ?? collect())->isNotEmpty())
                                    <div class="rounded-4 border bg-light p-3 mt-3">
                                        <div class="small text-secondary">After this module</div>
                                        <div class="small text-secondary mt-1">
                                            {{ $progressStatus === 'completed' ? 'Completion is already recorded, so you can move directly into the next learner action.' : 'Once this module is complete, your next learner actions will appear here automatically.' }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
