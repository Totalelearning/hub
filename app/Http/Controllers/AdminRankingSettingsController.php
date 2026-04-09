<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAuditEvent;
use App\Services\RankingHealthService;
use App\Services\RankingProviderProbeService;
use App\Services\RankingProviderStatusService;
use App\Services\RankingSeverityAuditService;
use App\Services\RankingSettingsService;
use App\Support\RankingHealthCopy;
use App\Support\RankingHealthLatencyFormatter;
use App\Support\RankingHealthMessages;
use App\Support\RankingHealthOptions;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminRankingSettingsController extends Controller
{
    public function edit(
        Request $request,
        RankingSettingsService $settings,
        RankingProviderStatusService $status,
        RankingHealthService $health,
    ): View
    {
        Gate::authorize('admin-access');

        $selectedProvider = $this->normalizeProviderFilter($request->query('ranking_provider'));
        $selectedTrigger = $this->normalizeTriggerFilter($request->query('ranking_severity_trigger'));
        $selectedExportFrom = $this->normalizeExportDate($request->query('ranking_export_from'));
        $selectedExportTo = $this->normalizeExportDate($request->query('ranking_export_to'));
        $healthSnapshot = $health->snapshot(10, $selectedProvider, $selectedTrigger);
        $probeRows = collect($healthSnapshot['recent_probes']);
        $providerLabels = RankingHealthOptions::providerSettingLabels();
        $providerOptions = RankingHealthOptions::providerOptions();
        $triggerOptions = RankingHealthOptions::severityTriggerOptions();
        $selectedProviderKey = $selectedProvider ?? 'all';
        $selectedTriggerKey = $selectedTrigger ?? 'all';
        $selectedProviderLabel = $providerOptions[$selectedProviderKey] ?? 'All providers';
        $selectedTriggerLabel = $triggerOptions[$selectedTriggerKey] ?? 'All triggers';
        $activeProviderLabel = $providerLabels[$settings->all()['provider'] ?? 'deterministic'] ?? ($settings->all()['provider'] ?? 'Deterministic');
        $lastExport = $this->latestRankingExportSummary();

        return view('app.admin-ranking-settings', [
            'settings' => $settings->all(),
            'defaults' => $settings->defaults(),
            'providers' => $providerLabels,
            'providerStatus' => $status->snapshot($settings),
            'probeProviderOptions' => $providerOptions,
            'severityTriggerOptions' => $triggerOptions,
            'selectedProbeProvider' => $selectedProviderKey,
            'selectedSeverityTrigger' => $selectedTriggerKey,
            'lastProbe' => $healthSnapshot['last_probe'],
            'lastSuccessfulProbe' => $healthSnapshot['last_successful_probe'],
            'successGap' => $healthSnapshot['success_gap'],
            'severity' => $healthSnapshot['severity'],
            'probeHistory' => [
                'rows' => $probeRows,
                'successes' => $healthSnapshot['probe_summary']['successes'],
                'failures' => $healthSnapshot['probe_summary']['failures'],
                'failureSummary' => collect($healthSnapshot['failure_summary']),
            ],
            'liveRankingFailures' => collect($healthSnapshot['recent_live_failures']),
            'probeLatencySummary' => RankingHealthLatencyFormatter::summarize($probeRows),
            'probeHistoryEmptyMessage' => RankingHealthMessages::probeHistoryEmpty($selectedProvider, $selectedProviderLabel),
            'severityTransitionsEmptyMessage' => RankingHealthMessages::severityTransitionsEmpty($selectedTrigger, $selectedTriggerLabel),
            'providerMismatchMessage' => $selectedProvider !== null && $selectedProvider !== ($settings->all()['provider'] ?? 'deterministic')
                ? RankingHealthMessages::providerMismatch($selectedProviderLabel, $activeProviderLabel)
                : null,
            'severityTriggerSummary' => collect($healthSnapshot['severity_trigger_summary']),
            'recentSeverityTransitions' => collect($healthSnapshot['recent_severity_transitions']),
            'copy' => RankingHealthCopy::rankingPage(),
            'selectedRankingExportFrom' => $selectedExportFrom,
            'selectedRankingExportTo' => $selectedExportTo,
            'lastRankingExport' => $lastExport,
        ]);
    }

    public function update(Request $request, RankingSettingsService $settings, RankingHealthService $health, RankingSeverityAuditService $severityAudit): RedirectResponse
    {
        Gate::authorize('admin-access');
        $beforeHealth = $health->snapshot();

        $validated = $request->validate([
            'settings.enabled' => ['nullable', 'boolean'],
            'settings.provider' => ['required', 'in:deterministic,local_ai,external_ai'],
            'settings.local_ai_goal_semantic_boost_per_match' => ['required', 'integer', 'min:0', 'max:100'],
            'settings.local_ai_goal_semantic_boost_max' => ['required', 'integer', 'min:0', 'max:100'],
            'settings.external_ai_attempts' => ['nullable', 'integer', 'min:1', 'max:5'],
            'settings.external_ai_retry_sleep_ms' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'settings.external_ai_max_boost' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $payload = $validated['settings'];
        $payload['enabled'] = (bool) ($payload['enabled'] ?? false);

        if (($payload['provider'] ?? null) === 'external_ai' && ! array_key_exists('external_ai_max_boost', $payload)) {
            $request->validate([
                'settings.external_ai_max_boost' => ['required', 'integer', 'min:0', 'max:100'],
                'settings.external_ai_attempts' => ['required', 'integer', 'min:1', 'max:5'],
                'settings.external_ai_retry_sleep_ms' => ['required', 'integer', 'min:0', 'max:5000'],
            ]);
        }

        $before = $settings->all();
        $settings->update($payload);

        $this->recordAuditEvent('ranking_settings_updated', [
            'changed_keys' => collect($payload)
                ->filter(fn ($value, $key) => $value !== ($before[$key] ?? null))
                ->keys()
                ->values()
                ->all(),
            'values' => $settings->all(),
        ]);

        $severityAudit->recordIfChanged($beforeHealth, $health->snapshot(), 'ranking_settings_updated');

        return redirect()
            ->route('app.admin.ranking.edit')
            ->with('status', 'Ranking settings saved.');
    }

    public function reset(RankingSettingsService $settings, RankingHealthService $health, RankingSeverityAuditService $severityAudit): RedirectResponse
    {
        Gate::authorize('admin-access');
        $beforeHealth = $health->snapshot();

        $before = $settings->all();
        $settings->resetToDefaults();

        $this->recordAuditEvent('ranking_settings_reset', [
            'previous_values' => $before,
        ]);

        $severityAudit->recordIfChanged($beforeHealth, $health->snapshot(), 'ranking_settings_reset');

        return redirect()
            ->route('app.admin.ranking.edit')
            ->with('status', 'Ranking settings reset to defaults.');
    }

    public function testProvider(RankingProviderProbeService $probeService, RankingHealthService $health, RankingSeverityAuditService $severityAudit): RedirectResponse
    {
        Gate::authorize('admin-access');
        $beforeHealth = $health->snapshot();

        $result = $probeService->probe();

        $this->recordAuditEvent('ranking_provider_tested', [
            'provider' => $result['provider'],
            'success' => $result['success'],
            'message' => $result['message'],
            'latency_ms' => $result['latency_ms'] ?? null,
            'request_id' => $result['request_id'] ?? null,
            'model' => $result['model'] ?? null,
            'error_type' => $result['error_type'] ?? null,
        ]);

        $severityAudit->recordIfChanged($beforeHealth, $health->snapshot(), 'ranking_provider_tested', [
            'provider' => $result['provider'],
            'success' => $result['success'],
        ]);

        return redirect()
            ->route('app.admin.ranking.edit')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    public function exportProbes(Request $request, RankingHealthService $health): StreamedResponse
    {
        Gate::authorize('admin-access');

        $selectedProvider = $this->normalizeProviderFilter($request->query('ranking_provider'));
        $exportFrom = $this->normalizeExportDate($request->query('ranking_export_from'));
        $exportTo = $this->normalizeExportDate($request->query('ranking_export_to'));
        $probes = $health->recentProbeModels(100, $selectedProvider, $exportFrom, $exportTo);
        $suffix = $selectedProvider ? "-{$selectedProvider}" : '';
        $filename = 'ranking-probe-history'.$suffix.'-'.now()->format('Ymd_His').'.csv';

        $this->recordAuditEvent('ranking_probe_history_exported', [
            'provider' => $selectedProvider ?? 'all',
            'export_from' => $exportFrom?->toDateString(),
            'export_to' => $exportTo?->toDateString(),
            'probe_count' => $probes->count(),
        ]);

        return response()->streamDownload(function () use ($probes): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['when', 'provider', 'status', 'latency_ms', 'request_id', 'model', 'error_type', 'message']);

            foreach ($probes as $probe) {
                fputcsv($handle, [
                    $probe->created_at?->format('Y-m-d H:i:s'),
                    $probe->provider,
                    $probe->success ? 'success' : 'failure',
                    $probe->latency_ms,
                    $probe->request_id,
                    $probe->model,
                    $probe->error_type,
                    $probe->metadata['message'] ?? $probe->error_message,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportSeverityTransitions(Request $request, RankingHealthService $health): StreamedResponse
    {
        Gate::authorize('admin-access');

        $selectedTrigger = $this->normalizeTriggerFilter($request->query('ranking_severity_trigger'));
        $exportFrom = $this->normalizeExportDate($request->query('ranking_export_from'));
        $exportTo = $this->normalizeExportDate($request->query('ranking_export_to'));
        $events = $health->recentSeverityTransitionModels(100, $selectedTrigger, $exportFrom, $exportTo);
        $suffix = $selectedTrigger ? "-{$selectedTrigger}" : '';
        $filename = 'ranking-severity-transitions'.$suffix.'-'.now()->format('Ymd_His').'.csv';

        $this->recordAuditEvent('ranking_severity_transitions_exported', [
            'trigger' => $selectedTrigger ?? 'all',
            'export_from' => $exportFrom?->toDateString(),
            'export_to' => $exportTo?->toDateString(),
            'severity_transition_count' => $events->count(),
        ]);

        return response()->streamDownload(function () use ($events): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['when', 'actor', 'trigger', 'before_level', 'before_label', 'after_level', 'after_label', 'after_reason']);

            foreach ($events as $event) {
                $meta = $event->meta ?? [];

                fputcsv($handle, [
                    $event->created_at?->format('Y-m-d H:i:s'),
                    $event->actor?->name ?? 'system',
                    $meta['trigger'] ?? null,
                    $meta['before_level'] ?? null,
                    $meta['before_label'] ?? null,
                    $meta['after_level'] ?? null,
                    $meta['after_label'] ?? null,
                    $meta['after_reason'] ?? null,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportIncidentBundle(Request $request, RankingHealthService $health): Response
    {
        Gate::authorize('admin-access');

        $selectedProvider = $this->normalizeProviderFilter($request->query('ranking_provider'));
        $selectedTrigger = $this->normalizeTriggerFilter($request->query('ranking_severity_trigger'));
        $exportFrom = $this->normalizeExportDate($request->query('ranking_export_from'));
        $exportTo = $this->normalizeExportDate($request->query('ranking_export_to'));

        $snapshot = $health->snapshot(10, $selectedProvider, $selectedTrigger);
        $probes = $health->recentProbeModels(100, $selectedProvider, $exportFrom, $exportTo)
            ->map(fn ($probe) => [
                'provider' => $probe->provider,
                'success' => (bool) $probe->success,
                'model' => $probe->model,
                'request_id' => $probe->request_id,
                'latency_ms' => $probe->latency_ms,
                'error_type' => $probe->error_type,
                'error_message' => $probe->error_message,
                'message' => $probe->metadata['message'] ?? $probe->error_message,
                'created_at' => $probe->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();
        $transitions = $health->recentSeverityTransitionModels(100, $selectedTrigger, $exportFrom, $exportTo)
            ->map(fn ($event) => [
                'before_level' => $event->meta['before_level'] ?? null,
                'before_label' => $event->meta['before_label'] ?? ($event->meta['before_level'] ?? 'unknown'),
                'before_reason' => $event->meta['before_reason'] ?? null,
                'after_level' => $event->meta['after_level'] ?? null,
                'after_label' => $event->meta['after_label'] ?? ($event->meta['after_level'] ?? 'unknown'),
                'after_reason' => $event->meta['after_reason'] ?? null,
                'trigger' => $event->meta['trigger'] ?? null,
                'actor_name' => $event->actor?->name ?? 'system',
                'created_at' => $event->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'manifest' => [
                'format' => 'ranking_incident_bundle',
                'version' => 1,
                'source' => [
                    'page' => route('app.admin.ranking.edit', array_filter([
                        'ranking_provider' => $selectedProvider,
                        'ranking_severity_trigger' => $selectedTrigger,
                        'ranking_export_from' => $exportFrom?->toDateString(),
                        'ranking_export_to' => $exportTo?->toDateString(),
                    ])),
                    'route' => 'app.admin.ranking.export.incident-bundle',
                ],
                'counts' => [
                    'recent_probes' => count($probes),
                    'recent_live_failures' => count($snapshot['recent_live_failures'] ?? []),
                    'recent_severity_transitions' => count($transitions),
                    'failure_summary' => count($snapshot['failure_summary'] ?? []),
                    'override_keys' => count($snapshot['override_keys'] ?? []),
                ],
            ],
            'filters' => [
                'provider' => $selectedProvider ?? 'all',
                'trigger' => $selectedTrigger ?? 'all',
                'export_from' => $exportFrom?->toDateString(),
                'export_to' => $exportTo?->toDateString(),
            ],
            'summary' => [
                'provider' => $snapshot['settings']['provider'] ?? 'deterministic',
                'enabled' => (bool) ($snapshot['settings']['enabled'] ?? false),
                'override_keys' => $snapshot['override_keys'] ?? [],
                'override_count' => $snapshot['override_count'] ?? 0,
                'provider_status' => $snapshot['provider_status'] ?? [],
                'last_probe' => $snapshot['last_probe'] ?? null,
                'last_successful_probe' => $snapshot['last_successful_probe'] ?? null,
                'success_gap' => $snapshot['success_gap'] ?? null,
                'probe_summary' => $snapshot['probe_summary'] ?? [],
                'live_failure_summary' => $snapshot['live_failure_summary'] ?? [],
                'latency_summary' => $snapshot['latency_summary'] ?? [],
                'failure_summary' => $snapshot['failure_summary'] ?? [],
                'severity' => $snapshot['severity'] ?? [],
            ],
            'recent_probes' => $probes,
            'recent_live_failures' => $snapshot['recent_live_failures'] ?? [],
            'recent_severity_transitions' => $transitions,
        ];

        $bundleFingerprint = hash('sha256', json_encode([
            'generated_at' => $payload['generated_at'],
            'filters' => $payload['filters'],
            'summary' => $payload['summary'],
            'recent_probes' => $payload['recent_probes'],
            'recent_live_failures' => $payload['recent_live_failures'],
            'recent_severity_transitions' => $payload['recent_severity_transitions'],
        ], JSON_UNESCAPED_SLASHES));

        $payload['manifest']['bundle_id'] = 'ranking-incident-'.substr($bundleFingerprint, 0, 16);
        $payload['manifest']['checksum_sha256'] = $bundleFingerprint;

        $this->recordAuditEvent('ranking_incident_bundle_exported', [
            'bundle_id' => $payload['manifest']['bundle_id'],
            'checksum_sha256' => $payload['manifest']['checksum_sha256'],
            'provider' => $payload['filters']['provider'],
            'trigger' => $payload['filters']['trigger'],
            'export_from' => $payload['filters']['export_from'],
            'export_to' => $payload['filters']['export_to'],
            'probe_count' => $payload['manifest']['counts']['recent_probes'],
            'severity_transition_count' => $payload['manifest']['counts']['recent_severity_transitions'],
        ]);

        $filename = 'ranking-incident-bundle-'.now()->format('Ymd_His').'.json';

        return response(
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            200,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    private function normalizeProviderFilter(mixed $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        return in_array($normalized, RankingHealthService::PROVIDER_FILTERS, true) ? $normalized : null;
    }

    private function normalizeTriggerFilter(mixed $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        return in_array($normalized, RankingHealthService::SEVERITY_TRIGGER_FILTERS, true) ? $normalized : null;
    }

    private function recordAuditEvent(string $action, array $meta = []): void
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => null,
            'learning_module_id' => null,
            'entity_type' => 'ranking_settings',
            'entity_id' => null,
            'action' => $action,
            'meta' => $meta,
        ]);
    }

    private function latestRankingExportSummary(): ?array
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return null;
        }

        $event = AssignmentAuditEvent::query()
            ->whereIn('action', [
                'ranking_probe_history_exported',
                'ranking_severity_transitions_exported',
                'ranking_incident_bundle_exported',
            ])
            ->latest()
            ->first();

        if (! $event) {
            return null;
        }

        $labels = [
            'ranking_probe_history_exported' => 'Probe CSV',
            'ranking_severity_transitions_exported' => 'Severity CSV',
            'ranking_incident_bundle_exported' => 'Incident Bundle',
        ];
        $meta = $event->meta ?? [];

        return [
            'action' => $event->action,
            'label' => $labels[$event->action] ?? $event->action,
            'created_at' => $event->created_at,
            'bundle_id' => $meta['bundle_id'] ?? null,
            'provider' => $meta['provider'] ?? null,
            'trigger' => $meta['trigger'] ?? null,
        ];
    }

    private function normalizeExportDate(mixed $value): ?CarbonImmutable
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
