<?php

namespace App\Contracts;

use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\User;

interface FeedRanker
{
    /**
     * @return array{
     *   score:int,
     *   breakdown:array<string,int>,
     *   renewal:array<string,mixed>,
     *   role_targeting:array<string,mixed>,
     *   compliance_targeting:array<string,mixed>,
     *   prerequisites:array<string,mixed>,
     *   acknowledgement:array<string,mixed>,
     *   assignment:array<string,mixed>,
     *   explanations:array<string,mixed>,
     *   highlights:array<int,array<string,mixed>>
     * }
     */
    public function rank(User $user, LearningModule $module, ?ModuleProgress $progress): array;

    public function providerName(): string;
}

