<?php

namespace App\Services;

use App\Models\SocialPost;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SocialFeedService
{
    public function rankedFeed(int $limit = 20, ?Carbon $asOf = null): Collection
    {
        $limit = max(1, min($limit, 50));
        $asOf = $asOf?->copy() ?? now();
        $asOfTimestamp = $asOf->format('Y-m-d H:i:s');

        $engagementFormula = '(likes_count * 3 + comments_count * 4 + shares_count * 5)';
        $recencyFormula = 'GREATEST(0, 72 - (EXTRACT(EPOCH FROM (?::timestamp - COALESCE(published_at, created_at))) / 3600))';
        $scoreFormula = "({$engagementFormula}) + ({$recencyFormula})";

        return SocialPost::query()
            ->select([
                'id',
                'learning_module_id',
                'body',
                'likes_count',
                'comments_count',
                'shares_count',
                'published_at',
                'created_at',
            ])
            ->selectRaw("{$scoreFormula} as ranking_score", [$asOfTimestamp])
            ->published()
            ->orderByDesc('ranking_score')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}

