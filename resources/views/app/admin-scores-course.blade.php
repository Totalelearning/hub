@extends('layouts.learninguiux')

@section('title', 'Scores - ' . $course->title)
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">

            {{-- Hero --}}
            <div class="mb-4 admin-feed-hero">
                <div class="row align-items-center g-0 p-4 p-lg-5">
                    <div class="col-12 col-lg-7 admin-feed-hero-copy mb-3 mb-lg-0">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Score Management</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ $course->title }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.modules.index') }}">Courses</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Scores</li>
                            </ol>
                        </nav>
                        <p class="text-secondary mb-0">View and edit learner enrolments, module progress, and knowledge check scores for this course.</p>
                    </div>
                    <div class="col-12 col-lg-5 d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('app.admin.courses.edit', $course) }}" class="btn btn-outline-theme btn-sm">Edit Course</a>
                        <a href="{{ route('app.admin.modules.index') }}" class="btn btn-outline-theme btn-sm">Back to Courses</a>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            {{-- Summary KPIs --}}
            @php
                $totalLearners = $enrolments->count();
                $completed = $enrolments->where('status', 'completed')->count();
                $inProgress = $enrolments->where('status', 'in_progress')->count();
                $assigned = $enrolments->where('status', 'assigned')->count();
                $completionRate = $totalLearners > 0 ? round($completed / $totalLearners * 100) : 0;
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-3 fw-bold text-primary">{{ $totalLearners }}</div>
                            <div class="small text-secondary">Enrolled</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-3 fw-bold text-success">{{ $completed }}</div>
                            <div class="small text-secondary">Completed</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-3 fw-bold text-warning">{{ $inProgress }}</div>
                            <div class="small text-secondary">In Progress</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-3 fw-bold text-info">{{ $completionRate }}%</div>
                            <div class="small text-secondary">Completion Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modules legend --}}
            @if ($modules->isNotEmpty())
            <div class="card adminuiux-card shadow-sm mb-3">
                <div class="card-header px-4 py-3 border-bottom">
                    <h6 class="mb-0 text-uppercase small fw-semibold text-muted">Course Modules ({{ $modules->count() }})</h6>
                </div>
                <div class="card-body px-4 py-2">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($modules as $i => $mod)
                            <span class="badge bg-light text-dark border">{{ $i + 1 }}. {{ $mod->title }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Learner scores table --}}
            <div class="card adminuiux-card shadow-sm">
                <div class="card-header px-4 py-3 border-bottom">
                    <h6 class="mb-0 text-uppercase small fw-semibold text-muted">Learner Scores</h6>
                </div>
                <div class="card-body p-0">
                    @if ($enrolments->isEmpty())
                        <div class="p-4 text-center text-secondary">No learners enrolled in this course.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Learner</th>
                                    <th>Team</th>
                                    <th>Status</th>
                                    <th>Completed</th>
                                    @foreach ($modules as $i => $mod)
                                        <th class="text-center" title="{{ $mod->title }}">M{{ $i + 1 }}</th>
                                    @endforeach
                                    <th>Quiz Score</th>
                                    <th style="width:60px;"></th>
                                </tr>
                            </thead>
                                @foreach ($enrolments as $enrolment)
                                    @php
                                        $userProgress = $moduleProgress->get($enrolment->user_id, collect());
                                        $userAttempts = $reinforcementAttempts->get($enrolment->user_id, collect());
                                        $latestAttempt = $userAttempts->first();
                                    @endphp
                            <tbody x-data="{ open: false }">
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('app.admin.scores.user', $enrolment->user_id) }}" class="text-decoration-none fw-medium">
                                                {{ $enrolment->name }}
                                            </a>
                                            <div class="small text-muted">{{ $enrolment->email }}</div>
                                        </td>
                                        <td><span class="small">{{ $enrolment->team ?? '-' }}</span></td>
                                        <td>
                                            <span class="badge bg-{{ $enrolment->status === 'completed' ? 'success' : ($enrolment->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $enrolment->status)) }}
                                            </span>
                                        </td>
                                        <td class="small">{{ $enrolment->completed_at ? \Carbon\Carbon::parse($enrolment->completed_at)->format('d M Y') : '-' }}</td>
                                        @foreach ($modules as $mod)
                                            @php
                                                $mp = $userProgress->firstWhere('learning_module_id', $mod->id);
                                                $pct = $mp?->percent_complete ?? 0;
                                                $color = $pct >= 100 ? 'success' : ($pct > 0 ? 'warning' : 'secondary');
                                            @endphp
                                            <td class="text-center">
                                                <span class="badge bg-{{ $color }}" title="{{ $mod->title }}: {{ $pct }}%">{{ $pct }}</span>
                                            </td>
                                        @endforeach
                                        <td>
                                            @if ($latestAttempt)
                                                <span class="fw-medium {{ $latestAttempt->status === 'completed' ? 'text-success' : 'text-danger' }}">
                                                    {{ $latestAttempt->score_percent }}%
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-xs btn-outline-secondary" @click="open = !open">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr x-show="open" x-cloak>
                                        <td colspan="{{ 5 + $modules->count() + 2 }}" class="p-0">
                                            <div class="border-top bg-light">

                                                {{-- Enrolment --}}
                                                <form method="POST" action="{{ route('app.admin.scores.update-enrolment', [$enrolment->user_id, $course]) }}" class="score-edit-row">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="score-edit-label">Enrolment</div>
                                                    <div class="score-edit-fields">
                                                        <div class="score-edit-field">
                                                            <label class="score-edit-field-label">Status</label>
                                                            <select name="status" class="form-select form-select-sm">
                                                                <option value="assigned" @selected($enrolment->status === 'assigned')>Assigned</option>
                                                                <option value="in_progress" @selected($enrolment->status === 'in_progress')>In Progress</option>
                                                                <option value="completed" @selected($enrolment->status === 'completed')>Completed</option>
                                                            </select>
                                                        </div>
                                                        <div class="score-edit-field">
                                                            <label class="score-edit-field-label">Completed at</label>
                                                            <input type="datetime-local" name="completed_at" class="form-control form-control-sm"
                                                                   value="{{ $enrolment->completed_at ? \Carbon\Carbon::parse($enrolment->completed_at)->format('Y-m-d\TH:i') : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="score-edit-actions">
                                                        <button type="submit" class="btn btn-sm btn-theme">Save</button>
                                                        <button type="reset" class="btn btn-sm btn-outline-secondary">Reset</button>
                                                    </div>
                                                </form>

                                                {{-- Modules --}}
                                                @foreach ($modules as $mod)
                                                    @php
                                                        $mp = $userProgress->firstWhere('learning_module_id', $mod->id);
                                                        $modStatus = $mp?->status ?? 'not_started';
                                                        $modPct = $mp?->percent_complete ?? 0;
                                                    @endphp
                                                    <form method="POST" action="{{ route('app.admin.scores.update-module', [$enrolment->user_id, $mod->id]) }}" class="score-edit-row">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="score-edit-label" title="{{ $mod->title }}">{{ $mod->title }}</div>
                                                        <div class="score-edit-fields">
                                                            <div class="score-edit-field">
                                                                <label class="score-edit-field-label">Status</label>
                                                                <select name="status" class="form-select form-select-sm">
                                                                    <option value="not_started" @selected($modStatus === 'not_started')>Not Started</option>
                                                                    <option value="in_progress" @selected($modStatus === 'in_progress')>In Progress</option>
                                                                    <option value="completed" @selected($modStatus === 'completed')>Completed</option>
                                                                </select>
                                                            </div>
                                                            <div class="score-edit-field">
                                                                <label class="score-edit-field-label">Progress</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" name="percent_complete" class="form-control form-control-sm" style="width:60px;" min="0" max="100" value="{{ $modPct }}">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="score-edit-actions">
                                                            <button type="submit" class="btn btn-sm btn-theme">Save</button>
                                                            <button type="reset" class="btn btn-sm btn-outline-secondary">Reset</button>
                                                        </div>
                                                    </form>
                                                @endforeach

                                                {{-- Knowledge Check --}}
                                                @if ($latestAttempt)
                                                    <form method="POST" action="{{ route('app.admin.scores.update-reinforcement', $latestAttempt) }}" class="score-edit-row">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="score-edit-label">Knowledge Check</div>
                                                        <div class="score-edit-fields">
                                                            <div class="score-edit-field">
                                                                <label class="score-edit-field-label">Result</label>
                                                                <select name="status" class="form-select form-select-sm">
                                                                    <option value="pending" @selected($latestAttempt->status === 'pending')>Pending</option>
                                                                    <option value="sent" @selected($latestAttempt->status === 'sent')>Sent</option>
                                                                    <option value="completed" @selected($latestAttempt->status === 'completed')>Completed</option>
                                                                    <option value="gaps_found" @selected($latestAttempt->status === 'gaps_found')>Gaps Found</option>
                                                                </select>
                                                            </div>
                                                            <div class="score-edit-field">
                                                                <label class="score-edit-field-label">Score</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" name="score_percent" class="form-control form-control-sm" style="width:60px;" min="0" max="100" step="0.01" value="{{ $latestAttempt->score_percent }}">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="score-edit-actions">
                                                            <button type="submit" class="btn btn-sm btn-theme">Save</button>
                                                            <button type="reset" class="btn btn-sm btn-outline-secondary">Reset</button>
                                                        </div>
                                                    </form>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                            </tbody>
                                @endforeach
                        </table>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
    .btn-xs { font-size: .75rem; padding: .2rem .5rem; }

    .score-edit-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .625rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .score-edit-row:last-child { border-bottom: none; }

    .score-edit-label {
        flex: 0 0 160px;
        font-weight: 600;
        font-size: .8rem;
        color: #555;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .score-edit-fields {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex: 1;
    }

    .score-edit-field {
        display: flex;
        align-items: center;
        gap: .375rem;
    }

    .score-edit-field-label {
        font-size: .72rem;
        color: #888;
        white-space: nowrap;
        margin: 0;
    }

    .score-edit-field select.form-select-sm {
        width: auto;
        min-width: 120px;
    }

    .score-edit-actions {
        display: flex;
        gap: .375rem;
        flex-shrink: 0;
    }
</style>
@endpush
