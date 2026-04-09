@extends('layouts.learninguiux')

@section('title', ($path->title ?? 'Learning Path') . ' - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .path-detail-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .path-detail-band {
            border-radius: 1.5rem;
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }
    </style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            <div class="mb-4 admin-feed-hero">
                <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between lg:p-5">
                    <div class="admin-feed-hero-copy">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Learning Journey</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">{{ $path->title }}</h1>
                        <p class="mt-3 text-base text-slate-600">{{ $path->description }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('app.admin.paths.edit', ['path' => $path->id]) }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Edit Path</a>
                        <a href="{{ route('app.admin.paths.index') }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Back to Paths</a>
                    </div>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="path-detail-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4"><i class="bi bi-signpost-split fs-3"></i></div>
                        <div class="fs-4 fw-semibold text-slate-900">{{ ucfirst($path->status) }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Status</div>
                        <div class="mt-auto pt-3 small text-secondary">Journey state</div>
                    </div>
                </div>
                <div class="path-detail-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#0369a1;background:linear-gradient(135deg, rgba(224, 242, 254, 0.96), rgba(219, 234, 254, 0.96));"><i class="bi bi-people fs-3"></i></div>
                        <div class="fs-4 fw-semibold text-slate-900">{{ collect($path->target_roles)->count() ?: 'All' }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Target Roles</div>
                        <div class="mt-auto pt-3 small text-secondary">{{ collect($path->target_roles)->join(', ') ?: 'All roles' }}</div>
                    </div>
                </div>
                <div class="path-detail-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#4f46e5;background:linear-gradient(135deg, rgba(224, 231, 255, 0.96), rgba(238, 242, 255, 0.96));"><i class="bi bi-list-ol fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $path->steps->count() }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Steps</div>
                        <div class="mt-auto pt-3 small text-secondary">Ordered modules</div>
                    </div>
                </div>
            </div>

            <div class="path-detail-card overflow-hidden">
                <div class="path-detail-band border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Path Steps</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Position</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Module</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Delay (days)</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($path->steps as $step)
                                <tr>
                                    <td class="px-5 py-3 text-gray-600">{{ $step->position }}</td>
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $step->module?->title }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ (int) $step->delay_days }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $step->module?->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="path-detail-card overflow-hidden">
                <div class="path-detail-band border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Learner Coverage</h3>
                    <p class="mt-1 text-sm text-gray-500">Learners eligible for this path and their current completion state.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Learner</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Role</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Complete</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Percent</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Overdue</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Due Soon</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Next Step</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($learnerRows as $row)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $row['user']->name }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ strtolower((string) $row['user']->preference?->role) ?: 'unassigned' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['summary']['completed_steps'] }}/{{ $row['summary']['total_steps'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['summary']['percent_complete'] }}%</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['summary']['overdue_steps'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['summary']['due_soon_steps'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['next_step_title'] ?: 'Complete' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-4 text-gray-500">No eligible learners for this path.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
        </div>
    </main>
</div>
@endsection
