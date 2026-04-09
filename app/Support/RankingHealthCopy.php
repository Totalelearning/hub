<?php

namespace App\Support;

class RankingHealthCopy
{
    public static function rankingPage(): array
    {
        return [
            'provider_readiness_heading' => 'Provider Readiness',
            'provider_readiness_body' => 'Confirms whether the selected ranking provider can run before requests hit runtime fallback.',
            'last_successful_probe_heading' => 'Last Successful Probe',
            'last_successful_probe_body' => 'Most recent successful provider test in the current probe window.',
            'last_probe_heading' => 'Last Probe Result',
            'last_probe_body' => 'Most recent provider test recorded in AI ops logs.',
            'recent_probe_history_heading' => 'Recent Probe History',
            'recent_probe_history_body' => 'Last 10 provider test attempts for quick health trend checks.',
            'failure_summary_heading' => 'Top Failure Reasons',
            'severity_transitions_heading' => 'Recent Severity Transitions',
            'severity_transitions_body' => 'Latest ranking health state changes recorded in audit logs.',
        ];
    }

    public static function dashboardPage(): array
    {
        return [
            'health_heading' => 'AI Ranking Health',
            'health_body' => 'Recent probe health for the selected ranking provider.',
            'failure_summary_heading' => 'Top Failure Reasons',
            'severity_transitions_heading' => 'Recent Severity Transitions',
            'severity_transitions_body' => 'Latest ranking health state changes recorded in audit logs.',
        ];
    }
}
