<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RankingHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRankingHealthController extends Controller
{
    public function __invoke(Request $request, RankingHealthService $health): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:25'],
            'provider' => ['nullable', 'in:all,deterministic,local_ai,external_ai'],
            'trigger' => ['nullable', 'in:all,ranking_settings_updated,ranking_settings_reset,ranking_provider_tested'],
        ]);

        $limit = (int) ($validated['limit'] ?? 10);
        $provider = $validated['provider'] ?? 'all';
        $trigger = $validated['trigger'] ?? 'all';
        $snapshot = $health->snapshot($limit, $provider, $trigger);

        return response()->json([
            'data' => [
                'enabled' => (bool) ($snapshot['settings']['enabled'] ?? false),
                'provider' => (string) ($snapshot['settings']['provider'] ?? 'deterministic'),
                'selected_provider' => $snapshot['selected_provider'] ?? null,
                'selected_severity_trigger' => $snapshot['selected_severity_trigger'] ?? null,
                'override_keys' => $snapshot['override_keys'],
                'override_count' => $snapshot['override_count'],
                'provider_status' => $snapshot['provider_status'],
                'last_probe' => $snapshot['last_probe'],
                'last_successful_probe' => $snapshot['last_successful_probe'],
                'success_gap' => $snapshot['success_gap'],
                'recent_probes' => $snapshot['recent_probes'],
                'recent_live_failures' => $snapshot['recent_live_failures'],
                'probe_summary' => $snapshot['probe_summary'],
                'live_failure_summary' => $snapshot['live_failure_summary'],
                'latency_summary' => $snapshot['latency_summary'],
                'failure_summary' => $snapshot['failure_summary'],
                'severity_trigger_summary' => $snapshot['severity_trigger_summary'],
                'recent_severity_transitions' => $snapshot['recent_severity_transitions'],
                'severity' => $snapshot['severity'],
            ],
            'meta' => [
                'probe_limit' => $limit,
                'provider_filter' => $provider,
                'trigger_filter' => $trigger,
            ],
        ]);
    }
}
