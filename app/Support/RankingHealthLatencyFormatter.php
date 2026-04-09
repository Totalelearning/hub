<?php

namespace App\Support;

use Illuminate\Support\Collection;

class RankingHealthLatencyFormatter
{
    public static function summarize(iterable $rows): string
    {
        $latencies = Collection::make($rows)
            ->pluck('latency_ms')
            ->filter(fn ($value) => $value !== null);

        if ($latencies->isEmpty()) {
            return 'avg n/a ms, min n/a ms, max n/a ms, trend n/a';
        }

        return sprintf(
            'avg %s ms, min %s ms, max %s ms, trend %s',
            (int) round($latencies->avg()),
            $latencies->min(),
            $latencies->max(),
            $latencies->count() > 1 ? 'stable' : 'n/a',
        );
    }
}
