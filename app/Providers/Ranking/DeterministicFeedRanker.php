<?php

namespace App\Providers\Ranking;

use App\Contracts\FeedRanker;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Services\FeedScoringService;

class DeterministicFeedRanker implements FeedRanker
{
    public function __construct(
        private readonly FeedScoringService $scoring,
    ) {
    }

    public function rank(User $user, LearningModule $module, ?ModuleProgress $progress): array
    {
        return $this->scoring->scoreWithBreakdown($user, $module, $progress);
    }

    public function providerName(): string
    {
        return 'deterministic';
    }
}

