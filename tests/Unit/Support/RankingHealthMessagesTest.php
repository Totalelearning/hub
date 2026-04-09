<?php

namespace Tests\Unit\Support;

use App\Support\RankingHealthMessages;
use Tests\TestCase;

class RankingHealthMessagesTest extends TestCase
{
    public function test_it_returns_provider_mismatch_message(): void
    {
        $this->assertSame(
            'Filtered probe history is showing External AI, while the active ranking provider is Local AI.',
            RankingHealthMessages::providerMismatch('External AI', 'Local AI'),
        );
    }

    public function test_it_returns_probe_history_empty_messages(): void
    {
        $this->assertSame(
            'No probe history recorded yet.',
            RankingHealthMessages::probeHistoryEmpty(null, 'All providers'),
        );

        $this->assertSame(
            'No ranking probes recorded yet.',
            RankingHealthMessages::probeHistoryEmpty(null, 'All providers', true),
        );

        $this->assertSame(
            'No probe history matches provider External AI.',
            RankingHealthMessages::probeHistoryEmpty('external_ai', 'External AI'),
        );
    }

    public function test_it_returns_severity_transition_empty_messages(): void
    {
        $this->assertSame(
            'No severity transitions recorded yet.',
            RankingHealthMessages::severityTransitionsEmpty(null, 'All triggers'),
        );

        $this->assertSame(
            'No severity transitions match trigger Settings Updated.',
            RankingHealthMessages::severityTransitionsEmpty('ranking_settings_updated', 'Settings Updated'),
        );
    }
}
