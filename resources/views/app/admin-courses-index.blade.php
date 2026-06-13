@extends('layouts.learninguiux')

@section('title', 'Courses - Learning')
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
                    <div class="col-12 col-lg-8 admin-feed-hero-copy">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Course Builder</div>
                        <h1 class="fs-3 fw-semibold mb-2">Courses</h1>
                        <p class="text-secondary mb-3">Group modules into short courses. A typical course is ~15 minutes, made up of 2&ndash;3 minute modules.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.admin.modules.index') }}" class="btn btn-outline-theme btn-sm">Manage Modules</a>
                            <a href="{{ route('app.admin.courses.create') }}" class="btn btn-theme btn-sm"><i class="bi bi-plus me-1"></i>Create Course</a>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- KPI cards --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3">
                                <i class="bi bi-collection fs-3"></i>
                            </div>
                            <div class="fs-2 fw-semibold">{{ $courses->count() }}</div>
                            <div class="fw-medium mb-1">Total Courses</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3" style="color:#0f766e;background:linear-gradient(135deg,rgba(213,250,229,.96),rgba(220,252,231,.96));">
                                <i class="bi bi-check2-circle fs-3"></i>
                            </div>
                            <div class="fs-2 fw-semibold">{{ $courses->where('status', 'published')->count() }}</div>
                            <div class="fw-medium mb-1">Published</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3" style="color:#b45309;background:linear-gradient(135deg,rgba(254,243,199,.98),rgba(255,237,213,.98));">
                                <i class="bi bi-pencil-square fs-3"></i>
                            </div>
                            <div class="fs-2 fw-semibold">{{ $courses->where('status', 'draft')->count() }}</div>
                            <div class="fw-medium mb-1">Drafts</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Course table --}}
            <div class="card adminuiux-card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 py-3 text-primary small fw-bold">Title</th>
                                    <th class="py-3 text-primary small fw-bold">Modules</th>
                                    <th class="py-3 text-primary small fw-bold">Duration</th>
                                    <th class="py-3 text-primary small fw-bold">Status</th>
                                    <th class="py-3 text-primary small fw-bold">Owner</th>
                                    <th class="py-3 text-primary small fw-bold">Updated</th>
                                    <th class="pe-4 py-3 text-primary small fw-bold text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courses as $course)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <a href="{{ route('app.admin.courses.edit', $course) }}" class="fw-semibold text-decoration-none">{{ $course->title }}</a>
                                            @if ($course->topic)
                                                <div class="small text-secondary">{{ ucfirst($course->topic) }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-primary-subtle text-primary">{{ $course->modules_count }} module{{ $course->modules_count === 1 ? '' : 's' }}</span>
                                        </td>
                                        <td class="py-3">
                                            @if ($course->estimated_minutes)
                                                {{ $course->estimated_minutes }} min
                                            @else
                                                <span class="text-secondary">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $statusClass = match($course->status) {
                                                    'published' => 'bg-success-subtle text-success',
                                                    'archived' => 'bg-secondary-subtle text-secondary',
                                                    default => 'bg-warning-subtle text-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ ucfirst($course->status) }}</span>
                                        </td>
                                        <td class="py-3 small text-secondary">{{ $course->owner?->name ?? '—' }}</td>
                                        <td class="py-3 small text-secondary">{{ $course->updated_at->format('M d, Y') }}</td>
                                        <td class="pe-4 py-3 text-end">
                                            <a href="{{ route('app.admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                            <form action="{{ route('app.admin.courses.destroy', $course) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this course?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-secondary">
                                            <i class="bi bi-collection fs-1 d-block mb-2"></i>
                                            No courses yet. Create your first course to group modules together.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
