@extends('layouts.learninguiux')

@section('title', 'Scores - ' . $user->name)
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
                        <h1 class="fs-3 fw-semibold mb-2">{{ $user->name }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.users.index') }}">Users</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.users.show', $user) }}">{{ $user->name }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Scores</li>
                            </ol>
                        </nav>
                        <p class="text-secondary mb-0">View and edit course enrolments, module progress, and reinforcement scores.</p>
                    </div>
                    <div class="col-12 col-lg-5 d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('app.admin.users.show', $user) }}" class="btn btn-outline-theme btn-sm">Back to User</a>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            {{-- Summary KPIs --}}
            @php
                $totalCourses = $courseEnrolments->count();
                $completed = $courseEnrolments->where('status', 'completed')->count();
                $inProgress = $courseEnrolments->where('status', 'in_progress')->count();
                $assigned = $courseEnrolments->where('status', 'assigned')->count();
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-3 fw-bold text-primary">{{ $totalCourses }}</div>
                            <div class="small text-secondary">Total Courses</div>
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
                            <div class="fs-3 fw-bold text-secondary">{{ $assigned }}</div>
                            <div class="small text-secondary">Not Started</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Course Enrolments --}}
            @forelse ($courseEnrolments as $enrolment)
                @php
                    $modules = $modulesByCourse->get($enrolment->course_id, collect());
                    $attempts = $reinforcementAttempts->get($enrolment->course_id, collect());
                @endphp
                <div class="card adminuiux-card shadow-sm mb-3" x-data="{ open: false }">
                    <div class="card-header px-4 py-3 border-bottom d-flex align-items-center justify-content-between cursor-pointer" @click="open = !open">
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ $enrolment->course_title }}</h6>
                            <span class="badge bg-{{ $enrolment->status === 'completed' ? 'success' : ($enrolment->status === 'in_progress' ? 'warning' : 'secondary') }} mt-1">
                                {{ ucfirst(str_replace('_', ' ', $enrolment->status)) }}
                            </span>
                            @if ($enrolment->course_status !== 'published')
                                <span class="badge bg-dark mt-1">{{ ucfirst($enrolment->course_status) }}</span>
                            @endif
                        </div>
                        <i class="bi bi-chevron-down" :class="{ 'rotate-180': open }" style="transition: transform .2s;"></i>
                    </div>

                    <div class="card-body px-4 py-3" x-show="open" x-cloak>
                        {{-- Course Enrolment Status --}}
                        <div class="mb-4">
                            <h6 class="text-uppercase small fw-semibold text-muted mb-2">Enrolment Status</h6>
                            <form method="POST" action="{{ route('app.admin.scores.update-enrolment', [$user, $enrolment->course_id]) }}" class="row g-2 align-items-end">
                                @csrf
                                @method('PATCH')
                                <div class="col-auto">
                                    <label class="form-label small mb-1">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="assigned" @selected($enrolment->status === 'assigned')>Assigned</option>
                                        <option value="in_progress" @selected($enrolment->status === 'in_progress')>In Progress</option>
                                        <option value="completed" @selected($enrolment->status === 'completed')>Completed</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small mb-1">Completed At</label>
                                    <input type="datetime-local" name="completed_at" class="form-control form-control-sm"
                                           value="{{ $enrolment->completed_at ? \Carbon\Carbon::parse($enrolment->completed_at)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-theme">Save</button>
                                    <button type="reset" class="btn btn-sm btn-outline-secondary">Reset</button>
                                </div>
                            </form>
                        </div>

                        {{-- Module Progress --}}
                        @if ($modules->isNotEmpty())
                        <div class="mb-4">
                            <h6 class="text-uppercase small fw-semibold text-muted mb-2">Module Progress</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Module</th>
                                            <th style="width:120px;">Status</th>
                                            <th style="width:100px;">Progress</th>
                                            <th style="width:80px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($modules as $mod)
                                            @php
                                                $progress = $moduleProgress->get($mod->module_id);
                                                $modStatus = $progress?->status ?? 'not_started';
                                                $modPercent = $progress?->percent_complete ?? 0;
                                            @endphp
                                            <tr x-data="{ editing: false }">
                                                <td>{{ $mod->module_title }}</td>
                                                <td>
                                                    <span x-show="!editing">
                                                        <span class="badge bg-{{ $modStatus === 'completed' ? 'success' : ($modStatus === 'in_progress' ? 'warning' : 'secondary') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $modStatus)) }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span x-show="!editing">{{ $modPercent }}%</span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-xs btn-outline-secondary" x-show="!editing" @click="editing = true">Edit</button>
                                                </td>
                                                <td colspan="4" x-show="editing" x-cloak>
                                                    <form method="POST" action="{{ route('app.admin.scores.update-module', [$user, $mod->module_id]) }}" class="d-flex gap-2 align-items-center">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="status" class="form-select form-select-sm" style="width:auto;">
                                                            <option value="not_started" @selected($modStatus === 'not_started')>Not Started</option>
                                                            <option value="in_progress" @selected($modStatus === 'in_progress')>In Progress</option>
                                                            <option value="completed" @selected($modStatus === 'completed')>Completed</option>
                                                        </select>
                                                        <div class="input-group input-group-sm" style="width:100px;">
                                                            <input type="number" name="percent_complete" class="form-control form-control-sm" min="0" max="100" value="{{ $modPercent }}">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                        <button type="submit" class="btn btn-xs btn-theme">Save</button>
                                                        <button type="reset" class="btn btn-xs btn-outline-secondary">Reset</button>
                                                        <button type="button" class="btn btn-xs btn-outline-secondary" @click="editing = false">Cancel</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- Reinforcement Attempts --}}
                        @if ($attempts->isNotEmpty())
                        <div>
                            <h6 class="text-uppercase small fw-semibold text-muted mb-2">Knowledge Check Attempts</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Score</th>
                                            <th style="width:80px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attempts as $attempt)
                                            <tr x-data="{ editing: false }">
                                                <td>{{ $attempt->completed_at ? \Carbon\Carbon::parse($attempt->completed_at)->format('d M Y H:i') : '-' }}</td>
                                                <td>
                                                    <span x-show="!editing">
                                                        <span class="badge bg-{{ $attempt->status === 'completed' ? 'success' : ($attempt->status === 'gaps_found' ? 'danger' : 'secondary') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $attempt->status)) }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td><span x-show="!editing">{{ $attempt->score_percent !== null ? $attempt->score_percent . '%' : '-' }}</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-xs btn-outline-secondary" x-show="!editing" @click="editing = true">Edit</button>
                                                </td>
                                                <td colspan="4" x-show="editing" x-cloak>
                                                    <form method="POST" action="{{ route('app.admin.scores.update-reinforcement', $attempt) }}" class="d-flex gap-2 align-items-center">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="status" class="form-select form-select-sm" style="width:auto;">
                                                            <option value="pending" @selected($attempt->status === 'pending')>Pending</option>
                                                            <option value="sent" @selected($attempt->status === 'sent')>Sent</option>
                                                            <option value="completed" @selected($attempt->status === 'completed')>Completed</option>
                                                            <option value="gaps_found" @selected($attempt->status === 'gaps_found')>Gaps Found</option>
                                                        </select>
                                                        <div class="input-group input-group-sm" style="width:110px;">
                                                            <input type="number" name="score_percent" class="form-control form-control-sm" min="0" max="100" step="0.01" value="{{ $attempt->score_percent }}">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                        <button type="submit" class="btn btn-xs btn-theme">Save</button>
                                                        <button type="reset" class="btn btn-xs btn-outline-secondary">Reset</button>
                                                        <button type="button" class="btn btn-xs btn-outline-secondary" @click="editing = false">Cancel</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card adminuiux-card shadow-sm">
                    <div class="card-body p-4 text-center text-secondary">
                        This user is not enrolled in any courses.
                    </div>
                </div>
            @endforelse

        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
    .cursor-pointer { cursor: pointer; }
    .rotate-180 { transform: rotate(180deg); }
    .btn-xs { font-size: .75rem; padding: .2rem .5rem; }
</style>
@endpush
