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

    .learner-course-title-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .learner-course-avatar {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(35, 93, 255, 0.12), rgba(129, 174, 255, 0.28));
        color: #2454db;
    }

    .learner-course-nav .nav-link {
        border-radius: 999px;
        padding: 0.7rem 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(226, 232, 240, 0.9);
        color: #1e293b;
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

            <div class="card learner-course-title-card mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <div class="learner-course-avatar d-flex align-items-center justify-content-center">
                                <i class="bi {{ $module->source_type === 'scorm' ? 'bi-play-circle' : 'bi-journal-check' }} fs-2"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-2 text-truncated">{{ $module->title }}</h4>
                            <div class="d-flex flex-wrap gap-2 small text-secondary">
                                <span>{{ $module->topic ?: 'General learning' }}</span>
                                <span>{{ ucfirst($progressStatus) }}</span>
                                <span>{{ $progressPercent }}% progress</span>
                            </div>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <span class="badge badge-light text-bg-theme-1 theme-red ms-1 my-1">
                                    <i class="bi bi-bell me-1"></i> {{ $moduleReminderSummary['total'] ?? 0 }}
                                </span>
                                <span class="badge badge-light text-bg-theme-1 theme-orange ms-1 my-1">
                                    <i class="bi bi-play-circle me-1"></i> {{ $launchCount }}
                                </span>
                                <span class="badge badge-light text-bg-theme-1 theme-green ms-1 my-1">
                                    <i class="bi bi-check-circle me-1"></i> {{ $attemptCount }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="position-sticky z-index-1 mb-4" style="top:5.0rem">
                <div class="card learner-module-card shadow-sm">
                    <div class="card-body p-2 overflow-x-auto">
                        <ul class="nav nav-pills learner-course-nav flex-nowrap">
                            <li class="nav-item">
                                <a class="nav-link" href="#module-summary">
                                    <div class="avatar avatar-28 icon"><i class="bi bi-journal-text fs-4"></i></div>
                                    <div class="col text-truncated">
                                        <p class="h6 mb-0">About Course</p>
                                    </div>
                                </a>
                            </li>
                            @if ($module->source_type === 'scorm' && $latestScormAsset)
                                <li class="nav-item">
                                    <a class="nav-link" href="#scorm-activity">
                                        <div class="avatar avatar-28 icon"><i class="bi bi-play-btn fs-4"></i></div>
                                        <div class="col text-truncated">
                                            <p class="h6 mb-0">SCORM Activity</p>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            @if (($completionNextActions ?? collect())->isNotEmpty())
                                <li class="nav-item">
                                    <a class="nav-link" href="#next-actions">
                                        <div class="avatar avatar-28 icon"><i class="bi bi-signpost-split fs-4"></i></div>
                                        <div class="col text-truncated">
                                            <p class="h6 mb-0">Next Actions</p>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" href="#why-in-feed">
                                    <div class="avatar avatar-28 icon"><i class="bi bi-stars fs-4"></i></div>
                                    <div class="col text-truncated">
                                        <p class="h6 mb-0">Why Here</p>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

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
                                    <a href="#why-in-feed" class="btn btn-outline-theme">Why this is in your feed</a>
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

            @if ($module->source_type === 'scorm' && $latestScormAsset)
                <div class="row g-4 mb-4" id="scorm-activity">
                    <div class="col-xl-7">
                        <div class="card learner-module-card h-100">
                            <div class="card-body p-4 p-lg-5">
                                <span class="learner-module-section-title d-inline-block mb-2">SCORM Demo Course</span>
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                    <div>
                                        <h3 class="mb-2">Launch-ready SCORM package</h3>
                                        <p class="text-secondary mb-2">This module opens in the embedded SCORM player used for the client prototype.</p>
                                        <div class="small text-secondary">
                                            Launch activity, completion state, score, session time, and lesson location are written into the admin reporting surfaces.
                                        </div>
                                    </div>
                                    <div class="small text-secondary text-lg-end">
                                        <div><strong>Launch path:</strong> {{ $latestScormAsset->launch_path ?: 'index.html' }}</div>
                                        <div><strong>Package status:</strong> {{ $latestScormAsset->status ?: 'processed' }}</div>
                                    </div>
                                </div>
                                <div class="mt-4 row g-3">
                                    <div class="col-md-4">
                                        <div class="rounded-4 border bg-light p-4 h-100">
                                            <div class="small text-secondary">Launches</div>
                                            <div class="fs-3 fw-semibold mt-1">{{ $launchCount }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rounded-4 border bg-light p-4 h-100">
                                            <div class="small text-secondary">Runtime attempts</div>
                                            <div class="fs-3 fw-semibold mt-1">{{ $attemptCount }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rounded-4 border bg-light p-4 h-100">
                                            <div class="small text-secondary">Latest launch</div>
                                            <div class="fw-semibold mt-1">
                                                @if (!empty($scormActivitySummary['latest_launch_at']))
                                                    {{ $scormActivitySummary['latest_launch_at']->format('M d, Y H:i') }}
                                                @else
                                                    Not launched yet
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card learner-module-card h-100">
                            <div class="card-body p-4 p-lg-5">
                                <span class="learner-module-section-title d-inline-block mb-2">Your Latest SCORM Result</span>
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                    <div>
                                        <h3 class="mb-2">Your SCORM Activity</h3>
                                        <p class="text-secondary mb-0">This mirrors the learner evidence trail behind the admin reporting views.</p>
                                    </div>
                                    @if ($latestScormRuntime)
                                        <div class="small text-secondary text-lg-end">
                                            Recorded {{ optional($latestScormRuntime['recorded_at'])->format('M d, Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                                @if ($latestScormRuntime)
                                    @if ($latestScormRuntime['is_completed'] ?? false)
                                        <div class="mt-3 rounded-4 border border-success-subtle bg-success-subtle p-3 text-success-emphasis">
                                            <div class="fw-semibold">Completion recorded</div>
                                            <div class="small mt-1">
                                                This SCORM run is now stored as completed at
                                                {{ optional($latestScormRuntime['completed_at'] ?? $latestScormRuntime['recorded_at'])->format('M d, Y H:i') ?? 'n/a' }}.
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row mt-3 g-3">
                                        <div class="col-6">
                                            <div class="rounded-4 border bg-light p-3 h-100">
                                                <div class="small text-secondary">Completion state</div>
                                                <div class="fw-semibold mt-1">{{ $latestScormRuntime['status_label'] ?? $latestScormRuntime['status'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="rounded-4 border bg-light p-3 h-100">
                                                <div class="small text-secondary">Score</div>
                                                <div class="fw-semibold mt-1">{{ $latestScormRuntime['score_raw'] ?? 'n/a' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="rounded-4 border bg-light p-3 h-100">
                                                <div class="small text-secondary">Session time</div>
                                                <div class="fw-semibold mt-1">{{ $latestScormRuntime['session_label'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="rounded-4 border bg-light p-3 h-100">
                                                <div class="small text-secondary">Progress</div>
                                                <div class="fw-semibold mt-1">{{ $latestScormRuntime['percent_complete'] }}%</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="rounded-4 border bg-light p-3 h-100">
                                                <div class="small text-secondary">Lesson location</div>
                                                <div class="fw-semibold mt-1 text-break">{{ $latestScormRuntime['lesson_location'] ?: 'n/a' }}</div>
                                                @if (!empty($latestScormRuntime['lesson_location']))
                                                    <div class="small text-secondary mt-2">Location: {{ $latestScormRuntime['lesson_location'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-3 small text-secondary">
                                        No SCORM runtime has been recorded yet. Launch the prototype once to populate learner progress, score, and session reporting.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if (app()->environment(['local', 'testing']))
                    <div class="card learner-module-panel mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <span class="learner-module-section-title d-inline-block mb-2">Demo Handoff</span>
                                    <h3 class="mb-2">Show learner evidence, then switch to admin reporting</h3>
                                    <p class="text-secondary mb-2">After showing the learner launch and result, switch to the admin evidence path for module analytics, compliance, and exports.</p>
                                    <div class="small text-secondary">
                                        Admin review paths:
                                        <code>{{ route('app.admin.scorm.index') }}</code>
                                        |
                                        <code>{{ route('app.admin.compliance', ['source_type' => 'scorm']) }}</code>
                                    </div>
                                </div>
                                @if (auth()->user()?->is_admin)
                                    <div class="d-flex flex-wrap gap-2 align-self-start">
                                        <a href="{{ route('app.admin.scorm.index') }}" class="btn btn-outline-theme btn-sm">Open SCORM Overview</a>
                                        <a href="{{ route('app.admin.compliance', ['source_type' => 'scorm']) }}" class="btn btn-outline-theme btn-sm">Open SCORM Compliance</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            @if (($completionNextActions ?? collect())->isNotEmpty())
                <div class="card learner-module-panel mb-4" id="next-actions">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                            <div>
                                <span class="learner-module-section-title d-inline-block mb-2">Next Actions</span>
                                <h3 class="mb-2">You completed this module. Here is what to do next.</h3>
                                <p class="text-secondary mb-0">These actions are built from your live path, required-learning, and visible-module state.</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            @foreach ($completionNextActions as $action)
                                <div class="col-lg-4">
                                    <div class="rounded-4 border bg-light p-4 h-100">
                                        <div class="d-flex justify-content-between gap-3 align-items-start">
                                            <div>
                                                <div class="small text-secondary">{{ $action['label'] }}</div>
                                                <div class="fw-semibold fs-5 mt-1">{{ $action['title'] }}</div>
                                            </div>
                                            <span class="badge rounded-pill {{ $action['tone'] === 'danger' ? 'text-bg-danger' : ($action['tone'] === 'info' ? 'text-bg-info' : ($action['tone'] === 'neutral' ? 'text-bg-light border' : 'text-bg-primary')) }}">
                                                {{ ucfirst($action['tone']) }}
                                            </span>
                                        </div>
                                        <p class="small text-secondary mt-3 mb-3">{{ $action['summary'] }}</p>
                                        <a href="{{ $action['href'] }}" class="btn btn-theme btn-sm">{{ $action['cta'] }}</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="card learner-module-panel mb-4" id="why-in-feed">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div>
                            <span class="learner-module-section-title d-inline-block mb-2">Recommendation Detail</span>
                            <h3 class="mb-2">Why this is in your feed</h3>
                            <p class="text-secondary mb-0">Deterministic recommendation signals for this module.</p>
                            @if (!empty($score['highlights'] ?? []))
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    @foreach ($score['highlights'] as $highlight)
                                        <span class="badge rounded-pill text-bg-light border">{{ $highlight['label'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-lg-end">
                            <div class="small text-secondary">Feed score</div>
                            <div class="display-6 fw-semibold mb-0">{{ (int) ($score['score'] ?? 0) }}</div>
                            @if (app()->environment('local'))
                                <div class="small text-secondary mt-1">
                                    ranker: {{ $rankingMeta['provider'] ?? 'deterministic' }}
                                    @if (!empty($rankingMeta['fallback_from']))
                                        (fallback {{ $rankingMeta['fallback_from'] }})
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row mt-4 g-3">
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Required module</div>
                                <div class="fw-semibold mt-1">{{ $module->is_required ? 'Yes' : 'No' }}</div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['required_module'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Renewal state</div>
                                <div class="fw-semibold mt-1">
                                    @if ($renewal['is_due'] ?? false)
                                        Due now
                                    @elseif ($renewal['is_due_soon'] ?? false)
                                        Due soon
                                    @elseif (!empty($renewal['due_at']))
                                        Current
                                    @else
                                        Not scheduled
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">Score: {{ ($score['breakdown']['renewal_due'] ?? 0) + ($score['breakdown']['renewal_due_soon'] ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Role targeting</div>
                                <div class="fw-semibold mt-1">
                                    @if (!empty($roleTargeting['target_roles']))
                                        {{ implode(', ', $roleTargeting['target_roles']) }}
                                    @else
                                        All roles
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['role_match'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Compliance assignment</div>
                                <div class="fw-semibold mt-1">
                                    @if (!empty($complianceTargeting['compliance_area']))
                                        {{ $complianceTargeting['compliance_area'] }}
                                    @else
                                        Not scoped
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['compliance_match'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Topic match</div>
                                <div class="fw-semibold mt-1">{{ $module->topic ?: 'none' }}</div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['topic_match'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Difficulty fit</div>
                                <div class="fw-semibold mt-1">{{ $module->difficulty ?: 'none' }}</div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['difficulty_match'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Goal alignment</div>
                                <div class="fw-semibold mt-1">{{ $preference?->goal ?: 'none set' }}</div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['goal_affinity'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Learning path priority</div>
                                <div class="fw-semibold mt-1">
                                    @if (($score['breakdown']['path_next_step'] ?? 0) > 0)
                                        Next unlocked step
                                    @else
                                        Not next in path
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['path_next_step'] ?? 0 }}</div>
                                @if (!empty($score['explanations']['path_next_step_paths'] ?? []))
                                    <div class="small text-muted mt-1">Path(s): {{ implode(', ', $score['explanations']['path_next_step_paths']) }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Progress state</div>
                                <div class="fw-semibold mt-1">{{ $progress?->status ?? 'not_started' }}</div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['not_completed'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Recent module engagement</div>
                                <div class="fw-semibold mt-1">
                                    @if (($score['breakdown']['recent_module_reengagement'] ?? 0) > 0)
                                        Re-engaged recently
                                    @else
                                        No recent activity
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['recent_module_reengagement'] ?? 0 }}</div>
                                @if (($score['explanations']['recent_module_reengagement_days'] ?? null) !== null)
                                    <div class="small text-muted mt-1">Last engaged {{ $score['explanations']['recent_module_reengagement_days'] }} day(s) ago</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Prerequisites</div>
                                <div class="fw-semibold mt-1">
                                    @if (!($prerequisites['has_prerequisites'] ?? false))
                                        None
                                    @elseif ($prerequisites['is_unlocked'] ?? false)
                                        Unlocked
                                    @else
                                        Locked
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['prerequisites_unlocked'] ?? 0 }}</div>
                                @if (!empty($prerequisites['missing_titles']))
                                    <div class="small text-muted mt-1">Missing: {{ implode(', ', $prerequisites['missing_titles']) }}</div>
                                @elseif (($prerequisites['has_prerequisites'] ?? false) && $module->prerequisites->isNotEmpty())
                                    <div class="small text-muted mt-1">Requires: {{ $module->prerequisites->pluck('title')->join(', ') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="rounded-4 border bg-light p-3 h-100">
                                <div class="small text-secondary">Acknowledgement</div>
                                <div class="fw-semibold mt-1">
                                    @if (!($acknowledgement['is_required'] ?? false))
                                        Not required
                                    @elseif ($acknowledgement['is_acknowledged'] ?? false)
                                        Acknowledged
                                    @else
                                        Still required
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">Score: {{ $score['breakdown']['acknowledgement_required'] ?? 0 }}</div>
                                @if (!empty($acknowledgement['acknowledged_at']))
                                    <div class="small text-muted mt-1">Recorded {{ $acknowledgement['acknowledged_at']->format('M d, Y H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-5">
                    <div class="card learner-module-panel h-100">
                        <div class="card-body p-4 p-lg-5">
                            <span class="learner-module-section-title d-inline-block mb-2">Assignment Status</span>
                            <h3 class="mb-3">Your learner assignment state</h3>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="rounded-4 border bg-light p-3 h-100">
                                        <div class="small text-secondary">Required</div>
                                        <div class="fw-semibold mt-1">{{ ($assignment['is_required'] ?? $module->is_required) ? 'Yes' : 'No' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded-4 border {{ $isDue ? 'border-danger-subtle bg-danger-subtle' : ($isDueSoon ? 'border-warning-subtle bg-warning-subtle' : 'bg-light') }} p-3 h-100">
                                        <div class="small text-secondary">Due date</div>
                                        <div class="fw-semibold mt-1">
                                            @if ($assignmentDueAt)
                                                {{ \Illuminate\Support\Carbon::parse($assignmentDueAt)->format('M d, Y') }}
                                            @else
                                                Not scheduled
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded-4 border {{ ($moduleReminderSummary['pending_count'] ?? 0) > 0 ? 'border-info-subtle bg-info-subtle' : 'bg-light' }} p-3 h-100">
                                        <div class="small text-secondary">Pending reminders</div>
                                        <div class="fw-semibold mt-1">{{ $moduleReminderSummary['pending_count'] ?? 0 }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded-4 border bg-light p-3 h-100">
                                        <div class="small text-secondary">Sent reminders</div>
                                        <div class="fw-semibold mt-1">{{ $moduleReminderSummary['sent_count'] ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 small text-secondary">
                                @if (!empty($moduleReminderSummary['latest_due_on']))
                                    Next reminder due {{ \Illuminate\Support\Carbon::parse($moduleReminderSummary['latest_due_on'])->format('M d, Y') }}.
                                @elseif (!empty($moduleReminderSummary['latest_sent_at']))
                                    Last reminder sent {{ \Illuminate\Support\Carbon::parse($moduleReminderSummary['latest_sent_at'])->format('M d, Y H:i') }}.
                                @else
                                    No reminder activity has been recorded for this module yet.
                                @endif
                            </div>
                            @if ($isDue || $isDueSoon)
                                <div class="small mt-3 {{ $isDue ? 'text-danger-emphasis' : 'text-warning-emphasis' }}">
                                    {{ $isDue ? 'This assignment should be reviewed immediately to avoid missing compliance expectations.' : 'This assignment is nearing its due date, so it is a good time to finish it now.' }}
                                </div>
                            @endif
                            <div class="rounded-4 border bg-light p-3 mt-3">
                                <div class="small text-secondary">Action guidance</div>
                                <div class="small text-secondary mt-1">{{ $moduleActionSummary['reminder_summary'] }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <a href="{{ $moduleActionSummary['next_action_href'] ?? '#module-summary' }}" class="btn {{ $primaryButtonClass }} btn-sm">{{ $moduleActionSummary['next_action_label'] }}</a>
                                    <a href="{{ route('app.reminders', ['module_id' => $module->id]) }}" class="btn btn-outline-theme btn-sm">Review module reminders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7">
                    <div class="card learner-module-panel h-100">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                                <div>
                                    <span class="learner-module-section-title d-inline-block mb-2">Recent Activity</span>
                                    <h3 class="mb-1">Your latest learner evidence</h3>
                                    <p class="text-secondary mb-0">This timeline is pulled from the same backend events that support admin reporting.</p>
                                </div>
                            </div>
                            @if (($recentModuleEvents ?? collect())->isNotEmpty())
                                <div class="d-grid gap-3">
                                    @foreach ($recentModuleEvents as $event)
                                        <div class="rounded-4 border bg-light p-3">
                                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                                <div>
                                                    <div class="fw-semibold">
                                                        {{ ucfirst(str_replace('_', ' ', $event['event_type'])) }}
                                                    </div>
                                                    <div class="small text-secondary mt-1">{{ $event['summary'] }}</div>
                                                </div>
                                                <div class="small text-secondary">
                                                    {{ $event['occurred_at']?->format('M d, Y H:i') ?? 'n/a' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-4 border bg-light p-4 text-secondary">
                                    No learner evidence has been recorded for this module yet. Open or launch the module once and activity will appear here.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if (($relatedModules ?? collect())->isNotEmpty())
                <div class="card learner-module-panel mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                            <div>
                                <span class="learner-module-section-title d-inline-block mb-2">Continue Learning</span>
                                <h3 class="mb-2">More modules in your learner flow</h3>
                                <p class="text-secondary mb-0">These are still visible to you and ranked from the same backend feed logic as the dashboard.</p>
                            </div>
                            <a href="{{ route('app.feed') }}" class="btn btn-outline-theme align-self-start">Return to dashboard</a>
                        </div>
                        <div class="row g-3">
                            @foreach ($relatedModules as $relatedModule)
                                @php
                                    $relatedAssignment = $relatedModule->assignment ?? [];
                                @endphp
                                <div class="col-md-6">
                                    <div class="rounded-4 border bg-light p-4 h-100">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold fs-5">{{ $relatedModule->title }}</div>
                                                <div class="small text-secondary mt-1">
                                                    {{ ucfirst(str_replace('_', ' ', $relatedModule->user_progress_status ?? 'not_started')) }}
                                                    | {{ (int) ($relatedModule->user_progress_percent ?? 0) }}%
                                                </div>
                                            </div>
                                            <span class="badge rounded-pill text-bg-light border align-self-start">{{ $relatedModule->source_type ?: 'manual' }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            @if ($relatedAssignment['is_required'] ?? false)
                                                <span class="badge rounded-pill text-bg-danger">Required</span>
                                            @endif
                                            @if ($relatedAssignment['is_overdue'] ?? false)
                                                <span class="badge rounded-pill text-bg-danger">Overdue</span>
                                            @elseif ($relatedAssignment['is_due_soon'] ?? false)
                                                <span class="badge rounded-pill text-bg-warning">Due soon</span>
                                            @endif
                                        </div>
                                        @if ($relatedModule->description)
                                            <p class="small text-secondary mt-3 mb-3">{{ \Illuminate\Support\Str::limit((string) $relatedModule->description, 120) }}</p>
                                        @endif
                                        <div class="d-flex flex-wrap gap-2">
                                            @if ($relatedModule->topic)
                                                <span class="badge rounded-pill text-bg-light border">{{ $relatedModule->topic }}</span>
                                            @endif
                                            @if ($relatedModule->compliance_area)
                                                <span class="badge rounded-pill text-bg-light border">{{ $relatedModule->compliance_area }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-3">
                                            <a href="{{ route('app.modules.show', ['module' => $relatedModule->id]) }}" class="btn btn-theme btn-sm">Open module</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @auth
                <livewire:module-progress-panel :module="$module" />
            @endauth
        </div>
    </main>
</div>
@endsection
