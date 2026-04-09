<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPreference;

class UserPreferencePolicy
{
    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, UserPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    public function update(User $user, UserPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }
}

