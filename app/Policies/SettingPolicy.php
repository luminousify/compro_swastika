<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    /**
     * Determine whether the user can view settings.
     */
    public function view(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update settings.
     */
    public function update(User $user): bool
    {
        return $user->isAdmin();
    }
}