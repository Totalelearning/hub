@extends('layouts.learninguiux')

@section('title', 'Learning Events Report - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .events-report-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .events-report-band {
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
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Operational Evidence</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">{{ __('Learning Events Report') }}</h1>
                        <p class="mt-3 text-base text-slate-600">Operational view of learner interaction telemetry across feed, modules, learning paths, and SCORM runtime.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('app.admin.scorm.index') }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm hover:bg-white hover:text-slate-900">
                            SCORM Overview
                        </a>
                        <a href="{{ route('app.admin.events.export', array_filter([
                            'event_type' => $filters['event_type'] ?? null,
                            'entity_type' => $filters['entity_type'] ?? null,
                            'user_id' => $filters['user_id'] ?? null,
                            'from' => $filters['from'] ?? null,
                            'to' => $filters['to'] ?? null,
                        ])) }}" class="admin-feed-action inline-flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm hover:bg-white hover:text-slate-900">
                            Export CSV
                        </a>
                    </div>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            <div class="events-report-card overflow-hidden">
                <div class="events-report-band border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Filters</h3>
                    <p class="mt-1 text-sm text-slate-600">Slice telemetry by event type, entity, learner, and date to isolate reporting evidence.</p>
                </div>
                <div class="p-5">
                <form method="GET" action="{{ route('app.admin.events.index') }}" class="grid gap-4 md:grid-cols-5 md:items-end">
                    <div>
                        <label for="event_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                        <select id="event_type" name="event_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">All types</option>
                            @foreach ($eventTypes as $eventType)
                                <option value="{{ $eventType }}" @selected(($filters['event_type'] ?? null) === $eventType)>{{ $eventType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="entity_type" class="block text-sm font-medium text-gray-700">Entity Type</label>
                        <select id="entity_type" name="entity_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">All entities</option>
                            @foreach ($entityTypes as $entityType)
                                <option value="{{ $entityType }}" @selected(($filters['entity_type'] ?? null) === $entityType)>{{ $entityType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                        <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">All users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((int) ($filters['user_id'] ?? 0) === (int) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="from" class="block text-sm font-medium text-gray-700">From</label>
                        <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label for="to" class="block text-sm font-medium text-gray-700">To</label>
                        <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    </div>
                    <div class="md:col-span-5 flex gap-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Apply Filters</button>
                        <a href="{{ route('app.admin.events.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Reset</a>
                    </div>
                </form>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="events-report-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4"><i class="bi bi-activity fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['total'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Total Events</div>
                        <div class="mt-auto pt-3 small text-secondary">Telemetry rows</div>
                    </div>
                </div>
                <div class="events-report-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#0369a1;background:linear-gradient(135deg, rgba(224, 242, 254, 0.96), rgba(219, 234, 254, 0.96));"><i class="bi bi-people fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['unique_users'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Unique Users</div>
                        <div class="mt-auto pt-3 small text-secondary">Learners in scope</div>
                    </div>
                </div>
                <div class="events-report-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#4f46e5;background:linear-gradient(135deg, rgba(224, 231, 255, 0.96), rgba(238, 242, 255, 0.96));"><i class="bi bi-journal-text fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['unique_modules'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Unique Modules</div>
                        <div class="mt-auto pt-3 small text-secondary">Module footprint</div>
                    </div>
                </div>
            </div>

            <div class="events-report-card overflow-hidden">
                <div class="events-report-band border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Events by Type</h3>
                </div>
                <div class="px-5 py-4 text-sm text-slate-600">
                    @forelse ($byType as $type => $count)
                        <span class="mr-3 inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $type }}: {{ $count }}</span>
                    @empty
                        No events available.
                    @endforelse
                </div>
            </div>

            <div class="events-report-card overflow-hidden">
                <div class="events-report-band border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Telemetry Stream</h3>
                    <p class="mt-1 text-sm text-slate-600">A chronological view of learner actions and SCORM runtime commits across the workspace.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">When</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">User</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Event</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Entity</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Metadata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($events as $event)
                                <tr>
                                    <td class="px-5 py-3 text-gray-600">{{ $event->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $event->user?->name ?? 'system' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $event->event_type }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        {{ $event->entity_type }} #{{ $event->entity_id }}
                                        @if ($event->entity_type === 'learning_module')
                                            <div class="text-xs text-gray-400">{{ $moduleTitles[(int) $event->entity_id] ?? 'unknown module' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ json_encode($event->metadata ?? []) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-4 text-gray-500">No learning events found.</td>
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
