@extends('layouts.learninguiux')

@section('title', 'Ranking Settings - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .ranking-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .ranking-band {
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
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">Ranking Settings</h1>
                        <p class="mt-3 text-base text-slate-600">Control AI ranking enablement, provider selection, and local heuristic boosts.</p>
                    </div>
                    <a href="{{ route('app.admin.assignments') }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Back to Admin Assignments</a>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6" data-ranking-health-page="ranking" data-ranking-health-endpoint="{{ url('/api/admin/ai/ranking-health?limit=10') }}">
            @if (session('status'))
                <div class="ranking-card border-green-200 bg-green-50/90 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="ranking-card border-amber-200 bg-amber-50/90 px-4 py-3 text-sm text-amber-800">
                    {{ session('error') }}
                </div>
            @endif

            <section class="ranking-card overflow-hidden">
                <div class="ranking-band border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $copy['provider_readiness_heading'] }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $copy['provider_readiness_body'] }}</p>
                </div>
                <div class="space-y-4 p-6">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Live provider health</div>
                            <div class="mt-1 text-xs text-slate-500">Track readiness, recent exports, and probe severity before adjusting ranking behavior.</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                        <div data-health-refreshed-at class="text-xs text-gray-500">Last updated on page load</div>
                        <span
                            data-health-severity-badge
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ ($severity['level'] ?? 'healthy') === 'critical' ? 'bg-red-100 text-red-800' : (($severity['level'] ?? 'healthy') === 'degraded' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}"
                        >
                            {{ $severity['label'] ?? 'Healthy' }}
                        </span>
                        <button type="button" data-ranking-health-refresh class="rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Refresh now
                        </button>
                        <button type="button" data-ranking-health-copy-url class="rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Copy API URL
                        </button>
                        <a href="{{ route('app.admin.ranking.export.incident-bundle', array_filter([
                            'ranking_provider' => $selectedProbeProvider !== 'all' ? $selectedProbeProvider : null,
                            'ranking_severity_trigger' => $selectedSeverityTrigger !== 'all' ? $selectedSeverityTrigger : null,
                            'ranking_export_from' => $selectedRankingExportFrom?->toDateString(),
                            'ranking_export_to' => $selectedRankingExportTo?->toDateString(),
                        ])) }}" data-ranking-health-export-json data-export-base-url="{{ route('app.admin.ranking.export.incident-bundle') }}" class="rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Export JSON
                        </a>
                        <button type="button" data-ranking-health-clear-filters class="rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Clear filters
                        </button>
                        <a href="{{ url('/api/admin/ai/ranking-health?limit=10') }}" data-ranking-health-open-url class="rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Open API
                        </a>
                        <span data-ranking-health-copy-status class="text-xs text-gray-500" aria-live="polite"></span>
                        <span data-health-readiness-badge class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $providerStatus['active_provider_ready'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $providerStatus['active_provider_ready'] ? 'Ready' : 'Needs attention' }}
                        </span>
                        </div>
                    </div>
                    <p data-health-severity-reason class="text-xs text-gray-500">{{ $severity['reason'] ?? 'Provider is ready and recent probe health is good.' }}</p>
                @if ($lastRankingExport)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                        <div>
                            Last export: {{ $lastRankingExport['label'] }} at {{ $lastRankingExport['created_at']?->format('Y-m-d H:i') }}
                            @if (!empty($lastRankingExport['bundle_id']))
                                ; {{ $lastRankingExport['bundle_id'] }}
                            @endif
                            @if (!empty($lastRankingExport['provider']))
                                ; provider {{ $lastRankingExport['provider'] }}
                            @endif
                            @if (!empty($lastRankingExport['trigger']))
                                ; trigger {{ $lastRankingExport['trigger'] }}
                            @endif
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <a href="{{ route('app.admin.assignments.audit', ['action' => $lastRankingExport['action']]) }}" class="rounded-full border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-white">
                                Open Audit
                            </a>
                            @if (!empty($lastRankingExport['bundle_id']))
                                <button type="button" data-ranking-health-copy-bundle-id="{{ $lastRankingExport['bundle_id'] }}" class="rounded-full border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-white">
                                    Copy bundle ID
                                </button>
                            @endif
                            <span data-ranking-health-copy-bundle-status class="text-xs text-slate-500" aria-live="polite"></span>
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($providerStatus['providers'] as $providerKey => $status)
                        <div class="rounded-3xl border {{ $providerStatus['active_provider'] === $providerKey ? 'border-indigo-300 bg-indigo-50/40' : 'border-gray-200 bg-gray-50/60' }} p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $status['label'] }}</h4>
                                    @if ($providerStatus['active_provider'] === $providerKey)
                                        <p class="mt-1 text-xs font-medium uppercase tracking-wide text-indigo-700">Selected provider</p>
                                    @endif
                                </div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['ready'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $status['ready'] ? 'Ready' : 'Not ready' }}
                                </span>
                            </div>

                            <dl class="mt-3 space-y-2 text-xs text-gray-600">
                                @foreach ($status['details'] as $detailLabel => $detailValue)
                                    <div class="flex items-center justify-between gap-3">
                                        <dt>{{ str($detailLabel)->replace('_', ' ')->title() }}</dt>
                                        <dd class="text-right font-medium text-gray-800">
                                            @if (is_bool($detailValue))
                                                {{ $detailValue ? 'Yes' : 'No' }}
                                            @else
                                                {{ $detailValue }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>

                            @if ($status['issues'] !== [])
                                <div class="mt-3 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                    @foreach ($status['issues'] as $issue)
                                        <div>{{ $issue }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                </div>
            </section>

            <section class="ranking-card overflow-hidden">
                <div class="ranking-band border-b border-slate-200 px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $copy['last_successful_probe_heading'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $copy['last_successful_probe_body'] }}</p>
                        <p data-health-success-gap class="mt-1 text-xs text-gray-500 {{ $successGap ? '' : 'hidden' }}">
                            Last known healthy probe was {{ $successGap['label'] ?? 'n/a' }} ago.
                        </p>
                    </div>
                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Success</span>
                </div>
                </div>

                <div class="space-y-4 p-6">
                <div data-health-last-successful-probe-content class="{{ $lastSuccessfulProbe ? '' : 'hidden' }}">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Provider</div>
                            <div data-health-last-successful-probe-provider class="mt-2 text-sm font-semibold text-gray-900">{{ $lastSuccessfulProbe['provider'] ?? 'n/a' }}</div>
                        </div>
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">When</div>
                            <div data-health-last-successful-probe-when class="mt-2 text-sm font-semibold text-gray-900">{{ !empty($lastSuccessfulProbe['created_at']) ? \Illuminate\Support\Carbon::parse($lastSuccessfulProbe['created_at'])->format('Y-m-d H:i:s') : 'n/a' }}</div>
                        </div>
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Latency</div>
                            <div data-health-last-successful-probe-latency class="mt-2 text-sm font-semibold text-gray-900">{{ isset($lastSuccessfulProbe['latency_ms']) && $lastSuccessfulProbe['latency_ms'] !== null ? $lastSuccessfulProbe['latency_ms'].' ms' : 'n/a' }}</div>
                        </div>
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Request ID</div>
                            <div data-health-last-successful-probe-request-id class="mt-2 text-sm font-semibold text-gray-900">{{ $lastSuccessfulProbe['request_id'] ?? 'n/a' }}</div>
                        </div>
                    </div>

                    <div class="rounded border border-gray-200 bg-gray-50/60 p-4 text-sm text-gray-700 space-y-2">
                        <div><span class="font-semibold text-gray-900">Message:</span> <span data-health-last-successful-probe-message>{{ $lastSuccessfulProbe['message'] ?? 'n/a' }}</span></div>
                        <div><span class="font-semibold text-gray-900">Model:</span> <span data-health-last-successful-probe-model>{{ $lastSuccessfulProbe['model'] ?? 'n/a' }}</span></div>
                    </div>
                </div>
                <div data-health-last-successful-probe-empty class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500 {{ $lastSuccessfulProbe ? 'hidden' : '' }}">
                    No successful provider probe has been recorded yet.
                </div>
                </div>
            </section>

            <section class="ranking-card overflow-hidden">
                <div class="ranking-band border-b border-slate-200 px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $copy['last_probe_heading'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $copy['last_probe_body'] }}</p>
                    </div>
                    <span data-health-last-probe-badge class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $lastProbe ? ($lastProbe['success'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') : 'hidden' }}">
                        {{ $lastProbe ? ($lastProbe['success'] ? 'Success' : 'Failure') : 'Success' }}
                    </span>
                </div>
                </div>

                <div class="space-y-4 p-6">
                <div data-health-last-probe-content class="{{ $lastProbe ? '' : 'hidden' }}">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Provider</div>
                            <div data-health-last-probe-provider class="mt-2 text-sm font-semibold text-gray-900">{{ $lastProbe['provider'] ?? 'n/a' }}</div>
                        </div>
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">When</div>
                            <div data-health-last-probe-when class="mt-2 text-sm font-semibold text-gray-900">{{ !empty($lastProbe['created_at']) ? \Illuminate\Support\Carbon::parse($lastProbe['created_at'])->format('Y-m-d H:i:s') : 'n/a' }}</div>
                        </div>
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Latency</div>
                            <div data-health-last-probe-latency class="mt-2 text-sm font-semibold text-gray-900">{{ isset($lastProbe['latency_ms']) && $lastProbe['latency_ms'] !== null ? $lastProbe['latency_ms'].' ms' : 'n/a' }}</div>
                        </div>
                        <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Request ID</div>
                            <div data-health-last-probe-request-id class="mt-2 text-sm font-semibold text-gray-900">{{ $lastProbe['request_id'] ?? 'n/a' }}</div>
                        </div>
                    </div>

                    <div class="rounded border border-gray-200 bg-gray-50/60 p-4 text-sm text-gray-700 space-y-2">
                        <div><span class="font-semibold text-gray-900">Message:</span> <span data-health-last-probe-message>{{ $lastProbe['message'] ?? 'n/a' }}</span></div>
                        <div><span class="font-semibold text-gray-900">Model:</span> <span data-health-last-probe-model>{{ $lastProbe['model'] ?? 'n/a' }}</span></div>
                        <div data-health-last-probe-error-row class="{{ !empty($lastProbe['error_type']) ? '' : 'hidden' }}"><span class="font-semibold text-gray-900">Error Type:</span> <span data-health-last-probe-error-type>{{ $lastProbe['error_type'] ?? '' }}</span></div>
                    </div>
                </div>
                <div data-health-last-probe-empty class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500 {{ $lastProbe ? 'hidden' : '' }}">
                        No provider probe has been recorded yet.
                </div>
                </div>
            </section>

            <section class="ranking-card overflow-hidden">
                <div class="ranking-band border-b border-slate-200 px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $copy['recent_probe_history_heading'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $copy['recent_probe_history_body'] }}</p>
                        @include('app.partials.ranking-health-summary', [
                            'isFiltered' => $selectedProbeProvider !== 'all' || $selectedSeverityTrigger !== 'all',
                            'filterCount' => (($selectedProbeProvider !== 'all') ? 1 : 0) + (($selectedSeverityTrigger !== 'all') ? 1 : 0),
                            'providerLabel' => $probeProviderOptions[$selectedProbeProvider] ?? 'All providers',
                            'triggerLabel' => $severityTriggerOptions[$selectedSeverityTrigger] ?? 'All triggers',
                            'apiUrl' => url('/api/admin/ai/ranking-health?limit=10'),
                            'auditUrl' => route('app.admin.assignments.audit', array_filter([
                                'action' => 'ranking_severity_changed',
                                'q' => $selectedSeverityTrigger !== 'all' ? $selectedSeverityTrigger : null,
                            ])),
                            'providerMismatchMessage' => $providerMismatchMessage,
                            'latencyDataAttribute' => 'data-health-latency-summary',
                            'latencySummary' => $probeLatencySummary,
                        ])
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold">
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                            <span>Provider</span>
                            <select data-ranking-health-provider-filter class="rounded border-gray-300 py-1 pl-2 pr-8 text-xs">
                                @foreach ($probeProviderOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedProbeProvider === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                            <span>From</span>
                            <input type="date" name="ranking_export_from" value="{{ $selectedRankingExportFrom?->toDateString() }}" data-ranking-health-export-from class="rounded border-gray-300 py-1 px-2 text-xs">
                        </label>
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                            <span>To</span>
                            <input type="date" name="ranking_export_to" value="{{ $selectedRankingExportTo?->toDateString() }}" data-ranking-health-export-to class="rounded border-gray-300 py-1 px-2 text-xs">
                        </label>
                        <a
                            href="{{ route('app.admin.ranking.export.probes', array_filter([
                                'ranking_provider' => $selectedProbeProvider !== 'all' ? $selectedProbeProvider : null,
                                'ranking_export_from' => $selectedRankingExportFrom?->toDateString(),
                                'ranking_export_to' => $selectedRankingExportTo?->toDateString(),
                            ])) }}"
                            data-ranking-health-export-probes
                            data-export-base-url="{{ route('app.admin.ranking.export.probes') }}"
                            class="rounded border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Export CSV
                        </a>
                        <span data-health-history-successes class="inline-flex rounded-full bg-green-100 px-3 py-1 text-green-800">Success {{ $probeHistory['successes'] }}</span>
                        <span data-health-history-failures class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-amber-800">Failure {{ $probeHistory['failures'] }}</span>
                    </div>
                </div>
                </div>

                <div class="space-y-4 p-6">
                <div class="overflow-x-auto {{ $probeHistory['rows']->isEmpty() ? 'hidden' : '' }}" data-health-history-table>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">When</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Provider</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Latency</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Request ID</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Message</th>
                                </tr>
                            </thead>
                            <tbody data-health-history-body class="divide-y divide-gray-100 bg-white">
                                @foreach ($probeHistory['rows'] as $probeRow)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-600">{{ !empty($probeRow['created_at']) ? \Illuminate\Support\Carbon::parse($probeRow['created_at'])->format('Y-m-d H:i:s') : 'n/a' }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $probeRow['provider'] }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $probeRow['success'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $probeRow['success'] ? 'Success' : 'Failure' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $probeRow['latency_ms'] !== null ? $probeRow['latency_ms'].' ms' : 'n/a' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $probeRow['request_id'] ?: 'n/a' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $probeRow['message'] ?? 'n/a' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                <div data-health-history-empty class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500 {{ $probeHistory['rows']->isNotEmpty() ? 'hidden' : '' }}">
                        {{ $probeHistoryEmptyMessage }}
                </div>

                <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $copy['failure_summary_heading'] }}</div>
                    <div class="mt-3 space-y-3" data-health-failure-summary>
                        @forelse ($probeHistory['failureSummary'] as $failure)
                            @include('app.partials.ranking-health-failure-summary-item', ['failure' => $failure])
                        @empty
                            @include('app.partials.ranking-health-empty-state', ['message' => 'No recent failures in this probe window.'])
                        @endforelse
                    </div>
                </div>

                <div class="rounded border border-gray-200 bg-gray-50/60 p-4">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Recent Live Ranking Failures</div>
                    <div class="mt-1 text-sm text-gray-500">Latest runtime `feed_ranking` failures for the selected provider filter.</div>
                    <div class="mt-3 space-y-3" data-health-live-failures>
                        @forelse ($liveRankingFailures as $failure)
                            <div class="rounded border border-rose-200 bg-rose-50 px-3 py-3 text-sm text-rose-900">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-semibold">{{ $failure['provider'] }}</div>
                                    <div class="text-xs text-rose-700">{{ !empty($failure['created_at']) ? \Illuminate\Support\Carbon::parse($failure['created_at'])->format('Y-m-d H:i:s') : 'n/a' }}</div>
                                </div>
                                <div class="mt-1 text-xs text-rose-800">request {{ $failure['request_id'] ?: 'n/a' }}; latency {{ $failure['latency_ms'] !== null ? $failure['latency_ms'].' ms' : 'n/a' }}</div>
                                <div class="mt-1 text-xs text-rose-700">{{ $failure['message'] ?? 'Unknown runtime failure.' }}</div>
                                <div class="mt-2">
                                    <a href="{{ route('app.admin.ai-usages', ['provider' => $failure['provider'] ?? null, 'capability' => 'feed_ranking', 'success' => 0, 'request_id' => $failure['request_id'] ?? null, 'limit' => 10]) }}" class="rounded border border-rose-300 px-2 py-1 text-xs font-semibold text-rose-800 hover:bg-white">
                                        Open Ops
                                    </a>
                                </div>
                            </div>
                        @empty
                            @include('app.partials.ranking-health-empty-state', ['message' => 'No recent live ranking failures in this window.'])
                        @endforelse
                    </div>
                </div>

                <div class="ranking-card overflow-hidden border-slate-200 bg-slate-50/70">
                    <div class="ranking-band border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $copy['severity_transitions_heading'] }}</div>
                            <div class="mt-1 text-sm text-slate-600">{{ $copy['severity_transitions_body'] }}</div>
                            <div class="mt-1 text-xs text-slate-500" data-health-severity-trigger-filter-label-wrapper>Showing {{ $severityTriggerOptions[$selectedSeverityTrigger] ?? 'All triggers' }}.</div>
                            <div class="mt-3 flex flex-wrap gap-2" data-health-severity-trigger-summary>
                                <button type="button" data-ranking-health-trigger-chip data-trigger="all" class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition {{ $selectedSeverityTrigger === 'all' ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-700 hover:border-indigo-200 hover:bg-indigo-50/50' }}">
                                    All triggers {{ $severityTriggerSummary->sum('count') }}
                                </button>
                                @foreach ($severityTriggerSummary as $row)
                                    <button type="button" data-ranking-health-trigger-chip data-trigger="{{ $row['trigger'] }}" class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition {{ $selectedSeverityTrigger !== 'all' && $selectedSeverityTrigger === $row['trigger'] ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-700 hover:border-indigo-200 hover:bg-indigo-50/50' }}">
                                        {{ $severityTriggerOptions[$row['trigger']] ?? $row['trigger'] }} {{ $row['count'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span>Trigger</span>
                                <select data-ranking-health-severity-trigger-filter class="rounded border-gray-300 py-1 pl-2 pr-8 text-xs">
                                    @foreach ($severityTriggerOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $selectedSeverityTrigger === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span>From</span>
                                <input type="date" name="ranking_export_from" value="{{ $selectedRankingExportFrom?->toDateString() }}" data-ranking-health-export-from class="rounded border-gray-300 py-1 px-2 text-xs">
                            </label>
                            <label class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span>To</span>
                                <input type="date" name="ranking_export_to" value="{{ $selectedRankingExportTo?->toDateString() }}" data-ranking-health-export-to class="rounded border-gray-300 py-1 px-2 text-xs">
                            </label>
                            <a
                                href="{{ route('app.admin.ranking.export.severity-transitions', array_filter([
                                    'ranking_severity_trigger' => $selectedSeverityTrigger !== 'all' ? $selectedSeverityTrigger : null,
                                    'ranking_export_from' => $selectedRankingExportFrom?->toDateString(),
                                    'ranking_export_to' => $selectedRankingExportTo?->toDateString(),
                                ])) }}"
                                data-ranking-health-export-severity
                                data-export-base-url="{{ route('app.admin.ranking.export.severity-transitions') }}"
                                class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Export CSV
                            </a>
                            <a href="{{ route('app.admin.assignments.audit', array_filter([
                                'action' => 'ranking_severity_changed',
                                'q' => $selectedSeverityTrigger !== 'all' ? $selectedSeverityTrigger : null,
                            ])) }}" data-ranking-health-open-audit data-audit-base-url="{{ route('app.admin.assignments.audit', ['action' => 'ranking_severity_changed']) }}" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Open Audit
                            </a>
                        </div>
                        </div>
                    </div>
                    <div class="space-y-3 p-6" data-health-severity-transitions>
                        @forelse ($recentSeverityTransitions as $transition)
                            @include('app.partials.ranking-health-severity-transition', [
                                'transition' => $transition,
                                'class' => 'rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 shadow-sm',
                            ])
                        @empty
                            @include('app.partials.ranking-health-empty-state', ['message' => $severityTransitionsEmptyMessage])
                        @endforelse
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('app.admin.ranking.update') }}" id="ranking-settings-update-form" class="ranking-card overflow-hidden">
                @csrf

                <div class="ranking-band border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Provider Controls</h3>
                    <p class="mt-1 text-sm text-slate-600">Tune provider readiness, heuristic boosts, and external retry behavior from one place.</p>
                </div>

                <div class="space-y-6 p-6">
                <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Provider Control</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm">
                            <input type="hidden" name="settings[enabled]" value="0">
                            <input type="checkbox" name="settings[enabled]" value="1" class="mt-1 rounded border-gray-300" {{ old('settings.enabled', $settings['enabled']) ? 'checked' : '' }}>
                            <span>
                                <span class="block text-sm font-medium text-slate-900">Enable AI Ranking Layer</span>
                                <span class="mt-1 block text-xs text-slate-500">When disabled, feed ranking stays fully deterministic.</span>
                            </span>
                        </label>

                        <div>
                            <label for="settings_provider" class="block text-sm font-medium text-slate-700">Ranking Provider</label>
                            <select id="settings_provider" name="settings[provider]" class="mt-1 w-full rounded border-gray-300 text-sm">
                                @foreach ($providers as $value => $label)
                                    <option value="{{ $value }}" {{ old('settings.provider', $settings['provider']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">`local_ai` is the current heuristic scaffold with deterministic fallback.</p>
                            @error('settings.provider')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Local AI Heuristic</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="settings_local_ai_goal_semantic_boost_per_match" class="block text-sm font-medium text-slate-700">Goal Semantic Boost Per Match</label>
                            <input id="settings_local_ai_goal_semantic_boost_per_match" type="number" min="0" max="100" name="settings[local_ai_goal_semantic_boost_per_match]" value="{{ old('settings.local_ai_goal_semantic_boost_per_match', $settings['local_ai_goal_semantic_boost_per_match']) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                            <p class="mt-1 text-xs text-slate-500">Default: {{ $defaults['local_ai_goal_semantic_boost_per_match'] }}</p>
                            @error('settings.local_ai_goal_semantic_boost_per_match')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="settings_local_ai_goal_semantic_boost_max" class="block text-sm font-medium text-slate-700">Goal Semantic Boost Max</label>
                            <input id="settings_local_ai_goal_semantic_boost_max" type="number" min="0" max="100" name="settings[local_ai_goal_semantic_boost_max]" value="{{ old('settings.local_ai_goal_semantic_boost_max', $settings['local_ai_goal_semantic_boost_max']) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                            <p class="mt-1 text-xs text-slate-500">Default: {{ $defaults['local_ai_goal_semantic_boost_max'] }}</p>
                            @error('settings.local_ai_goal_semantic_boost_max')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">External AI Provider</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="settings_external_ai_max_boost" class="block text-sm font-medium text-slate-700">External AI Max Boost</label>
                            <input id="settings_external_ai_max_boost" type="number" min="0" max="100" name="settings[external_ai_max_boost]" value="{{ old('settings.external_ai_max_boost', $settings['external_ai_max_boost']) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                            <p class="mt-1 text-xs text-slate-500">Default: {{ $defaults['external_ai_max_boost'] }}</p>
                            @error('settings.external_ai_max_boost')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="settings_external_ai_attempts" class="block text-sm font-medium text-slate-700">External AI Retry Attempts</label>
                            <input id="settings_external_ai_attempts" type="number" min="1" max="5" name="settings[external_ai_attempts]" value="{{ old('settings.external_ai_attempts', $settings['external_ai_attempts']) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                            <p class="mt-1 text-xs text-slate-500">Default: {{ $defaults['external_ai_attempts'] }}</p>
                            @error('settings.external_ai_attempts')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="settings_external_ai_retry_sleep_ms" class="block text-sm font-medium text-slate-700">External AI Retry Backoff (ms)</label>
                            <input id="settings_external_ai_retry_sleep_ms" type="number" min="0" max="5000" step="50" name="settings[external_ai_retry_sleep_ms]" value="{{ old('settings.external_ai_retry_sleep_ms', $settings['external_ai_retry_sleep_ms']) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                            <p class="mt-1 text-xs text-slate-500">Default: {{ $defaults['external_ai_retry_sleep_ms'] }}</p>
                            @error('settings.external_ai_retry_sleep_ms')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="text-xs text-slate-500 md:self-end">
                            Uses `OPENAI_API_KEY` plus `RANKING_EXTERNAL_AI_*` settings. If the provider fails, the app falls back to deterministic ranking automatically.
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="submit" form="ranking-provider-test-form" class="rounded border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                        Test Provider
                    </button>
                    <button type="submit" form="ranking-settings-reset-form" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset to Defaults
                    </button>
                    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Save Ranking Settings
                    </button>
                </div>
                </div>
            </form>

            <form method="POST" action="{{ route('app.admin.ranking.reset') }}" id="ranking-settings-reset-form" class="hidden">
                @csrf
            </form>

            <form method="POST" action="{{ route('app.admin.ranking.test') }}" id="ranking-provider-test-form" class="hidden">
                @csrf
            </form>
        </div>
    </div>
        </div>
    </main>
</div>
@endsection
