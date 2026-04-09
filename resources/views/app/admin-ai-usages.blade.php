@extends('layouts.learninguiux')

@section('title', 'AI Usage Records - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .ai-usage-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .ai-usage-band {
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
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">AI Operations</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">AI Usage Records</h1>
                        <p class="mt-3 text-base text-slate-600">Filtered operational records for AI provider calls across ranking, mentor, and ingestion flows.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('app.admin.ai-usages.export', array_filter($filters, fn ($value) => $value !== '')) }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Export CSV</a>
                        <a href="{{ url('/api/admin/ai/usages?'.http_build_query(array_filter($filters, fn ($value) => $value !== ''))) }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Open JSON</a>
                        <a href="{{ route('app.admin.assignments') }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Back to Admin Assignments</a>
                    </div>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            <section class="ai-usage-card overflow-hidden">
                <div class="ai-usage-band border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Filters</h3>
                    <p class="mt-1 text-sm text-slate-600">Filter provider calls by provider, capability, request, success state, and date range.</p>
                </div>
                <div class="p-6">
                <form method="GET" action="{{ route('app.admin.ai-usages') }}" class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                    <div>
                        <label for="provider" class="block text-sm font-medium text-gray-700">Provider</label>
                        <input id="provider" name="provider" value="{{ $filters['provider'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label for="capability" class="block text-sm font-medium text-gray-700">Capability</label>
                        <input id="capability" name="capability" value="{{ $filters['capability'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label for="request_id" class="block text-sm font-medium text-gray-700">Request ID</label>
                        <input id="request_id" name="request_id" value="{{ $filters['request_id'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label for="success" class="block text-sm font-medium text-gray-700">Success</label>
                        <select id="success" name="success" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="" {{ $filters['success'] === '' ? 'selected' : '' }}>All</option>
                            <option value="1" {{ $filters['success'] === '1' ? 'selected' : '' }}>Success</option>
                            <option value="0" {{ $filters['success'] === '0' ? 'selected' : '' }}>Failure</option>
                        </select>
                    </div>
                    <div>
                        <label for="from" class="block text-sm font-medium text-gray-700">From</label>
                        <input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label for="to" class="block text-sm font-medium text-gray-700">To</label>
                        <input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label for="limit" class="block text-sm font-medium text-gray-700">Per Page</label>
                        <input id="limit" type="number" min="1" max="100" name="limit" value="{{ $filters['limit'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div class="md:col-span-2 xl:col-span-5 flex items-end gap-2">
                        <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Apply Filters</button>
                        <a href="{{ route('app.admin.ai-usages') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Clear</a>
                    </div>
                </form>
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 text-sm text-gray-600">
                    <span class="font-medium text-gray-900">Active Filters</span>
                    @if ($activeFilters === [])
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">none</span>
                    @else
                        @foreach ($activeFilters as $key => $value)
                            <span class="rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $key }}={{ $value }}</span>
                        @endforeach
                        <a href="{{ route('app.admin.ai-usages', ['limit' => $filters['limit']]) }}" class="text-xs font-semibold text-indigo-700 hover:text-indigo-600">Clear active filters</a>
                    @endif
                </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <a href="{{ route('app.admin.ai-usages', array_filter($filters, fn ($value) => $value !== '')) }}" class="ai-usage-card admin-feed-kpi block p-5 transition hover:border-indigo-200 hover:bg-indigo-50/30">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4"><i class="bi bi-cpu fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['total'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Total</div>
                        <div class="mt-auto pt-3 small text-secondary">Rows in scope</div>
                    </div>
                </a>
                <a href="{{ route('app.admin.ai-usages', array_filter([
                    'provider' => $filters['provider'],
                    'capability' => $filters['capability'],
                    'request_id' => $filters['request_id'],
                    'success' => '1',
                    'from' => $filters['from'],
                    'to' => $filters['to'],
                    'limit' => $filters['limit'],
                ], fn ($value) => $value !== '')) }}" class="ai-usage-card admin-feed-kpi block p-5 transition hover:border-green-300 hover:bg-green-100/70">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#0f766e;background:linear-gradient(135deg, rgba(213, 250, 229, 0.96), rgba(220, 252, 231, 0.96));"><i class="bi bi-check2-circle fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['successes'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Success</div>
                        <div class="mt-auto pt-3 small text-secondary">Provider calls</div>
                    </div>
                </a>
                <a href="{{ route('app.admin.ai-usages', array_filter([
                    'provider' => $filters['provider'],
                    'capability' => $filters['capability'],
                    'request_id' => $filters['request_id'],
                    'success' => '0',
                    'from' => $filters['from'],
                    'to' => $filters['to'],
                    'limit' => $filters['limit'],
                ], fn ($value) => $value !== '')) }}" class="ai-usage-card admin-feed-kpi block p-5 transition hover:border-amber-300 hover:bg-amber-100/70">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#b45309;background:linear-gradient(135deg, rgba(254, 243, 199, 0.98), rgba(255, 237, 213, 0.98));"><i class="bi bi-exclamation-circle fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ $summary['failures'] }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Failure</div>
                        <div class="mt-auto pt-3 small text-secondary">Provider calls</div>
                    </div>
                </a>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="ai-usage-card overflow-hidden">
                    <div class="ai-usage-band border-b border-slate-200 px-5 py-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Top Providers</div>
                    </div>
                    <div class="p-5">
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('app.admin.ai-usages', array_filter([
                            'capability' => $filters['capability'],
                            'request_id' => $filters['request_id'],
                            'success' => $filters['success'],
                            'from' => $filters['from'],
                            'to' => $filters['to'],
                            'limit' => $filters['limit'],
                        ], fn ($value) => $value !== '')) }}"
                            class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $filters['provider'] === '' ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/50' }}">
                            All providers
                        </a>
                        @forelse ($summary['providers'] as $provider)
                            <a href="{{ route('app.admin.ai-usages', array_filter([
                                'provider' => $provider['label'],
                                'capability' => $filters['capability'],
                                'request_id' => $filters['request_id'],
                                'success' => $filters['success'],
                                'from' => $filters['from'],
                                'to' => $filters['to'],
                                'limit' => $filters['limit'],
                            ], fn ($value) => $value !== '')) }}"
                                class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $filters['provider'] === $provider['label'] ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/50' }}">
                                {{ $provider['label'] }} {{ $provider['count'] }}
                            </a>
                        @empty
                            <div class="text-sm text-gray-500">No provider rows in current scope.</div>
                        @endforelse
                    </div>
                    </div>
                </div>
                <div class="ai-usage-card overflow-hidden">
                    <div class="ai-usage-band border-b border-slate-200 px-5 py-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Top Capabilities</div>
                    </div>
                    <div class="p-5">
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('app.admin.ai-usages', array_filter([
                            'provider' => $filters['provider'],
                            'request_id' => $filters['request_id'],
                            'success' => $filters['success'],
                            'from' => $filters['from'],
                            'to' => $filters['to'],
                            'limit' => $filters['limit'],
                        ], fn ($value) => $value !== '')) }}"
                            class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $filters['capability'] === '' ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/50' }}">
                            All capabilities
                        </a>
                        @forelse ($summary['capabilities'] as $capability)
                            <a href="{{ route('app.admin.ai-usages', array_filter([
                                'provider' => $filters['provider'],
                                'capability' => $capability['label'],
                                'request_id' => $filters['request_id'],
                                'success' => $filters['success'],
                                'from' => $filters['from'],
                                'to' => $filters['to'],
                                'limit' => $filters['limit'],
                            ], fn ($value) => $value !== '')) }}"
                                class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $filters['capability'] === $capability['label'] ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/50' }}">
                                {{ $capability['label'] }} {{ $capability['count'] }}
                            </a>
                        @empty
                            <div class="text-sm text-gray-500">No capability rows in current scope.</div>
                        @endforelse
                    </div>
                    </div>
                </div>
            </section>

            <section class="ai-usage-card overflow-hidden">
                <div class="ai-usage-band border-b border-gray-200 px-6 py-4">
                    <div class="text-sm font-semibold text-gray-900">Results</div>
                    <div class="mt-1 text-sm text-gray-500">{{ $rows->total() }} matching records</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                <th class="px-4 py-3">When</th>
                                <th class="px-4 py-3">Provider</th>
                                <th class="px-4 py-3">Capability</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Latency</th>
                                <th class="px-4 py-3">Request ID</th>
                                <th class="px-4 py-3">Model</th>
                                <th class="px-4 py-3">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->created_at?->format('Y-m-d H:i:s') ?? 'n/a' }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->provider }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->capability }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $row->success ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $row->success ? 'Success' : 'Failure' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->latency_ms !== null ? $row->latency_ms.' ms' : 'n/a' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->request_id ?: 'n/a' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->model ?: 'n/a' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->metadata['message'] ?? $row->error_message ?? 'n/a' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">No AI usage records match the current filter set.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $rows->links() }}
                </div>
            </section>
        </div>
    </div>
        </div>
    </main>
</div>
@endsection
