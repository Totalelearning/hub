<?php

namespace App\Support;

class RankingHealthOptions
{
    public static function providerSettingLabels(): array
    {
        return [
            'deterministic' => 'Deterministic',
            'local_ai' => 'Local AI Heuristic',
            'external_ai' => 'External AI Provider',
        ];
    }

    public static function providerOptions(): array
    {
        return [
            'all' => 'All providers',
            'deterministic' => 'Deterministic',
            'local_ai' => 'Local AI',
            'external_ai' => 'External AI',
        ];
    }

    public static function severityTriggerOptions(): array
    {
        return [
            'all' => 'All triggers',
            'ranking_provider_tested' => 'Provider Tested',
            'ranking_settings_updated' => 'Settings Updated',
            'ranking_settings_reset' => 'Settings Reset',
        ];
    }
}
