<?php

namespace App\Services;

use App\Models\AssignmentAuditEvent;
use App\Models\AiProviderUsage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RankingHealthService
{
    public const PROVIDER_FILTERS = ['deterministic', 'local_ai', 'external_ai'];
    public const SEVERITY_TRIGGER_FILTERS = ['ranking_settings_updated', 'ranking_settings_reset', 'ranking_provider_tested'];
    private const SEVERITY_TRANSITION_SUMMARY_LIMIT = 25;

    public function __construct(
        private readonly RankingSettingsService $settings,
        private readonly RankingProviderStatusService $status,
    ) {
    }

    public function snapshot(int $probeLimit = 10, ?string $provider = null, ?string $severityTrigger = null): array
    {
        $settings = $this->settings->all();
        $defaults = $this->settings->defaults();
        $providerStatus = $this->status->snapshot($this->settings);
        $overrideKeys = collect($settings)
            ->filter(fn ($value, $key) => $value !== ($defaults[$key] ?? $value))
            ->keys()
            ->values()
            ->all();
        $selectedProvider = $this->normalizeProviderFilter($provider);
        $selectedSeverityTrigger = $this->normalizeSeverityTriggerFilter($severityTrigger);
        $probes = $this->recentProbes($probeLimit, $selectedProvider);
        $liveFailures = $this->recentLiveRankingFailures($probeLimit, $selectedProvider);
        $lastProbe = $probes->first();
        $lastSuccessfulProbe = $probes->first(fn (AiProviderUsage $probe) => (bool) $probe->success);

        return [
            'settings' => $settings,
            'selected_provider' => $selectedProvider,
            'selected_severity_trigger' => $selectedSeverityTrigger,
            'provider_status' => $providerStatus,
            'override_keys' => $overrideKeys,
            'override_count' => count($overrideKeys),
            'last_probe' => $lastProbe ? $this->serializeProbe($lastProbe) : null,
            'last_successful_probe' => $lastSuccessfulProbe ? $this->serializeProbe($lastSuccessfulProbe) : null,
            'success_gap' => $this->successGap($lastSuccessfulProbe),
            'recent_probes' => $probes->map(fn (AiProviderUsage $probe) => $this->serializeProbe($probe))->values()->all(),
            'recent_live_failures' => $liveFailures->map(fn (AiProviderUsage $failure) => $this->serializeLiveFailure($failure))->values()->all(),
            'probe_summary' => [
                'successes' => $probes->where('success', true)->count(),
                'failures' => $probes->where('success', false)->count(),
            ],
            'live_failure_summary' => [
                'count' => $liveFailures->count(),
            ],
            'latency_summary' => $this->latencySummary($probes),
            'failure_summary' => $this->failureSummary($probes, $liveFailures),
            'severity_trigger_summary' => $this->severityTriggerSummary(),
            'recent_severity_transitions' => $this->recentSeverityTransitions(5, $selectedSeverityTrigger),
            'severity' => $this->severity($providerStatus, $lastProbe, $lastSuccessfulProbe),
        ];
    }

    public function recentProbeModels(int $limit = 10, ?string $provider = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Collection
    {
        return $this->recentProbes($limit, $this->normalizeProviderFilter($provider), $from, $to);
    }

    public function recentSeverityTransitionModels(int $limit = 10, ?string $trigger = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Collection
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return collect();
        }

        return $this->severityTransitionQuery($this->normalizeSeverityTriggerFilter($trigger), $from, $to)
            ->limit($limit)
            ->get();
    }

    private function recentProbes(int $limit, ?string $provider, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Collection
    {
        if (! Schema::hasTable('ai_provider_usages')) {
            return collect();
        }

        return AiProviderUsage::query()
            ->where('capability', 'feed_ranking_probe')
            ->when($provider !== null, fn ($query) => $query->where('provider', $provider))
            ->when($from !== null, fn ($query) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($to !== null, fn ($query) => $query->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    private function recentLiveRankingFailures(int $limit, ?string $provider, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Collection
    {
        if (! Schema::hasTable('ai_provider_usages')) {
            return collect();
        }

        return AiProviderUsage::query()
            ->where('capability', 'feed_ranking')
            ->where('success', false)
            ->when($provider !== null, fn ($query) => $query->where('provider', $provider))
            ->when($from !== null, fn ($query) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($to !== null, fn ($query) => $query->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    private function normalizeProviderFilter(?string $provider): ?string
    {
        $normalized = strtolower(trim((string) $provider));

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        return in_array($normalized, self::PROVIDER_FILTERS, true) ? $normalized : null;
    }

    private function normalizeSeverityTriggerFilter(?string $trigger): ?string
    {
        $normalized = strtolower(trim((string) $trigger));

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        return in_array($normalized, self::SEVERITY_TRIGGER_FILTERS, true) ? $normalized : null;
    }

    private function recentSeverityTransitions(int $limit = 5, ?string $trigger = null): array
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return [];
        }

        return $this->severityTransitionQuery($trigger)
            ->limit($limit)
            ->get()
            ->map(fn (AssignmentAuditEvent $event) => $this->serializeSeverityTransition($event))
            ->values()
            ->all();
    }

    private function severityTriggerSummary(): array
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return [];
        }

        $rows = $this->severityTransitionQuery()
            ->limit(self::SEVERITY_TRANSITION_SUMMARY_LIMIT)
            ->get();

        return collect(['ranking_provider_tested', 'ranking_settings_updated', 'ranking_settings_reset'])
            ->map(function (string $trigger) use ($rows): array {
                return [
                    'trigger' => $trigger,
                    'count' => $rows->filter(fn (AssignmentAuditEvent $event) => ($event->meta['trigger'] ?? null) === $trigger)->count(),
                ];
            })
            ->all();
    }

    private function severityTransitionQuery(?string $trigger = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null)
    {
        return AssignmentAuditEvent::query()
            ->with('actor')
            ->where('action', 'ranking_severity_changed')
            ->when($trigger !== null, fn ($query) => $query->where('meta->trigger', $trigger))
            ->when($from !== null, fn ($query) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($to !== null, fn ($query) => $query->where('created_at', '<=', $to->endOfDay()))
            ->latest('id');
    }

    private function serializeProbe(AiProviderUsage $probe): array
    {
        return [
            'provider' => $probe->provider,
            'success' => (bool) $probe->success,
            'model' => $probe->model,
            'request_id' => $probe->request_id,
            'latency_ms' => $probe->latency_ms,
            'error_type' => $probe->error_type,
            'error_message' => $probe->error_message,
            'message' => $probe->metadata['message'] ?? $probe->error_message,
            'created_at' => $probe->created_at?->toIso8601String(),
        ];
    }

    private function serializeLiveFailure(AiProviderUsage $failure): array
    {
        return [
            'provider' => $failure->provider,
            'model' => $failure->model,
            'request_id' => $failure->request_id,
            'latency_ms' => $failure->latency_ms,
            'error_type' => $failure->error_type,
            'error_message' => $failure->error_message,
            'message' => $failure->metadata['message'] ?? $failure->error_message,
            'created_at' => $failure->created_at?->toIso8601String(),
        ];
    }

    private function serializeSeverityTransition(AssignmentAuditEvent $event): array
    {
        $meta = $event->meta ?? [];

        return [
            'before_level' => $meta['before_level'] ?? null,
            'before_label' => $meta['before_label'] ?? ($meta['before_level'] ?? 'unknown'),
            'before_reason' => $meta['before_reason'] ?? null,
            'after_level' => $meta['after_level'] ?? null,
            'after_label' => $meta['after_label'] ?? ($meta['after_level'] ?? 'unknown'),
            'after_reason' => $meta['after_reason'] ?? null,
            'trigger' => $meta['trigger'] ?? null,
            'actor_name' => $event->actor?->name ?? 'system',
            'created_at' => $event->created_at?->toIso8601String(),
        ];
    }

    private function latencySummary(Collection $probes): array
    {
        $latencies = $probes
            ->pluck('latency_ms')
            ->filter(fn ($latency) => $latency !== null)
            ->map(fn ($latency) => (int) $latency)
            ->values();

        if ($latencies->isEmpty()) {
            return [
                'avg_ms' => null,
                'min_ms' => null,
                'max_ms' => null,
                'trend' => 'n/a',
            ];
        }

        $avg = (int) round($latencies->avg());
        $firstHalf = $latencies->slice(0, (int) ceil($latencies->count() / 2));
        $secondHalf = $latencies->slice((int) ceil($latencies->count() / 2));

        $trend = 'stable';
        if ($firstHalf->isNotEmpty() && $secondHalf->isNotEmpty()) {
            $firstAvg = (float) $firstHalf->avg();
            $secondAvg = (float) $secondHalf->avg();
            $delta = $firstAvg - $secondAvg;

            if ($delta >= 75) {
                $trend = 'worsening';
            } elseif ($delta <= -75) {
                $trend = 'improving';
            }
        }

        return [
            'avg_ms' => $avg,
            'min_ms' => $latencies->min(),
            'max_ms' => $latencies->max(),
            'trend' => $trend,
        ];
    }

    private function failureSummary(Collection $probes, Collection $liveFailures): array
    {
        $probeRows = $probes
            ->where('success', false)
            ->map(function (AiProviderUsage $probe): array {
                $label = trim((string) ($probe->error_type ?: ($probe->metadata['message'] ?? $probe->error_message ?? 'Unknown failure')));

                if ($label === '') {
                    $label = 'Unknown failure';
                }

                return [
                    'label' => $label,
                    'message' => trim((string) ($probe->metadata['message'] ?? $probe->error_message ?? $label)),
                    'provider' => $probe->provider,
                    'source' => 'probe',
                ];
            });

        $liveRows = $liveFailures
            ->map(function (AiProviderUsage $failure): array {
                $label = trim((string) ($failure->error_type ?: ($failure->metadata['message'] ?? $failure->error_message ?? 'Unknown failure')));

                if ($label === '') {
                    $label = 'Unknown failure';
                }

                return [
                    'label' => $label,
                    'message' => trim((string) ($failure->metadata['message'] ?? $failure->error_message ?? $label)),
                    'provider' => $failure->provider,
                    'source' => 'live_ranking',
                ];
            });

        return $probeRows
            ->concat($liveRows)
            ->groupBy('label')
            ->map(function (Collection $rows, string $label): array {
                $first = $rows->first();

                return [
                    'label' => $label,
                    'message' => $first['message'] ?? $label,
                    'count' => $rows->count(),
                    'providers' => $rows->pluck('provider')->filter()->unique()->values()->all(),
                    'sources' => $rows->pluck('source')->filter()->unique()->values()->all(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(3)
            ->all();
    }

    private function successGap(?AiProviderUsage $probe): ?array
    {
        if (! $probe?->created_at) {
            return null;
        }

        $lastSuccessAt = CarbonImmutable::parse($probe->created_at);
        $minutes = max(0, now()->diffInMinutes($lastSuccessAt));

        if ($minutes < 60) {
            $label = sprintf('%d minute%s', $minutes, $minutes === 1 ? '' : 's');
        } elseif ($minutes < 1440) {
            $hours = intdiv($minutes, 60);
            $label = sprintf('%d hour%s', $hours, $hours === 1 ? '' : 's');
        } else {
            $days = intdiv($minutes, 1440);
            $label = sprintf('%d day%s', $days, $days === 1 ? '' : 's');
        }

        return [
            'minutes' => $minutes,
            'label' => $label,
        ];
    }

    private function severity(array $providerStatus, ?AiProviderUsage $lastProbe, ?AiProviderUsage $lastSuccessfulProbe): array
    {
        $ready = (bool) ($providerStatus['active_provider_ready'] ?? false);
        $gap = $this->successGap($lastSuccessfulProbe);
        $gapMinutes = $gap['minutes'] ?? null;

        $level = 'healthy';
        $label = 'Healthy';
        $reason = 'Provider is ready and recent probe health is good.';

        if (! $ready) {
            $level = 'critical';
            $label = 'Critical';
            $reason = 'Active provider is not ready.';
        } elseif ($lastProbe && ! $lastProbe->success) {
            $level = 'degraded';
            $label = 'Degraded';
            $reason = 'Most recent probe failed.';
        }

        if ($gapMinutes !== null && $gapMinutes >= 240) {
            $level = 'critical';
            $label = 'Critical';
            $reason = 'No recent successful probe in the last 4 hours.';
        } elseif ($gapMinutes !== null && $gapMinutes >= 60 && $level === 'healthy') {
            $level = 'degraded';
            $label = 'Degraded';
            $reason = 'No recent successful probe in the last hour.';
        }

        if ($lastSuccessfulProbe === null) {
            $level = 'critical';
            $label = 'Critical';
            $reason = 'No successful probe has been recorded.';
        }

        return [
            'level' => $level,
            'label' => $label,
            'reason' => $reason,
        ];
    }
}
