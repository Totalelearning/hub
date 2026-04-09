<?php

namespace Tests\Unit\Support;

use App\Support\RankingHealthLatencyFormatter;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RankingHealthLatencyFormatterTest extends TestCase
{
    public function test_it_summarizes_latency_rows(): void
    {
        $summary = RankingHealthLatencyFormatter::summarize(Collection::make([
            ['latency_ms' => 140],
            ['latency_ms' => 480],
            ['latency_ms' => null],
        ]));

        $this->assertSame('avg 310 ms, min 140 ms, max 480 ms, trend stable', $summary);
    }

    public function test_it_returns_na_when_latency_rows_are_missing(): void
    {
        $summary = RankingHealthLatencyFormatter::summarize([]);

        $this->assertSame('avg n/a ms, min n/a ms, max n/a ms, trend n/a', $summary);
    }
}
