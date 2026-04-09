<?php

namespace App\Policies;

use App\Models\ModuleProgress;
use App\Models\User;

class ModuleProgressPolicy
{
    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, ModuleProgress $moduleProgress): bool
    {
        return $user->id === $moduleProgress->user_id;
    }

    public function update(User $user, ModuleProgress $moduleProgress): bool
    {
        return $user->id === $moduleProgress->user_id;
    }
}

