<?php

namespace Tests\Unit\Support;

use App\Support\RankingHealthOptions;
use Tests\TestCase;

class RankingHealthOptionsTest extends TestCase
{
    public function test_it_returns_provider_setting_labels(): void
    {
        $this->assertSame([
            'deterministic' => 'Deterministic',
            'local_ai' => 'Local AI Heuristic',
            'external_ai' => 'External AI Provider',
        ], RankingHealthOptions::providerSettingLabels());
    }

    public function test_it_returns_provider_options(): void
    {
        $this->assertSame([
            'all' => 'All providers',
            'deterministic' => 'Deterministic',
            'local_ai' => 'Local AI',
            'external_ai' => 'External AI',
        ], RankingHealthOptions::providerOptions());
    }

    public function test_it_returns_severity_trigger_options(): void
    {
        $this->assertSame([
            'all' => 'All triggers',
            'ranking_provider_tested' => 'Provider Tested',
            'ranking_settings_updated' => 'Settings Updated',
            'ranking_settings_reset' => 'Settings Reset',
        ], RankingHealthOptions::severityTriggerOptions());
    }
}
