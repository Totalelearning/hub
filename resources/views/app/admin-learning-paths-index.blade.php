@extends('layouts.learninguiux')

@section('title', 'Learning Paths - Learning')
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
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Learning Journeys</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ __('Learning Paths') }}</h1>
                        <p class="text-secondary mb-3">Manage ordered role-based learning paths and the sequence learners move through.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.admin.paths.create') }}" class="btn btn-theme btn-sm"><i class="bi bi-plus me-1"></i>Create Path</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status flash --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            {{-- Path Catalogue --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header bg-primary-subtle">
                    <div class="fw-semibold">Path Catalogue</div>
                    <div class="small text-secondary mt-1">Review path status, targeted roles, and the number of ordered steps in each journey.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Target Roles</th>
                                <th>Steps</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paths as $path)
                                <tr>
                                    <td class="fw-medium">{{ $path->title }}</td>
                                    <td>
                                        @if ($path->status === 'active')
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @elseif ($path->status === 'draft')
                                            <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                                        @else
                                            <span class="badge bg-light text-body-secondary">{{ ucfirst($path->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary">{{ collect($path->target_roles)->join(', ') ?: 'All' }}</td>
                                    <td>{{ $path->steps_count }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('app.admin.paths.show', ['path' => $path->id]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="{{ route('app.admin.paths.edit', ['path' => $path->id]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary text-center py-4">No learning paths found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
