<?php

namespace Tests\Unit\Support;

use App\Support\RankingHealthCopy;
use Tests\TestCase;

class RankingHealthCopyTest extends TestCase
{
    public function test_it_returns_ranking_page_copy(): void
    {
        $copy = RankingHealthCopy::rankingPage();

        $this->assertSame('Provider Readiness', $copy['provider_readiness_heading']);
        $this->assertSame('Last Successful Probe', $copy['last_successful_probe_heading']);
        $this->assertSame('Recent Probe History', $copy['recent_probe_history_heading']);
        $this->assertSame('Top Failure Reasons', $copy['failure_summary_heading']);
        $this->assertSame('Recent Severity Transitions', $copy['severity_transitions_heading']);
    }

    public function test_it_returns_dashboard_page_copy(): void
    {
        $copy = RankingHealthCopy::dashboardPage();

        $this->assertSame('AI Ranking Health', $copy['health_heading']);
        $this->assertSame('Recent probe health for the selected ranking provider.', $copy['health_body']);
        $this->assertSame('Top Failure Reasons', $copy['failure_summary_heading']);
        $this->assertSame('Recent Severity Transitions', $copy['severity_transitions_heading']);
    }
}
