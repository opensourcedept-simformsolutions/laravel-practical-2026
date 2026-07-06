<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wing;

class WingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, Wing $wing): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $wing->society_id === $user->society_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Wing $wing): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $wing->society_id === $user->society_id;
    }

    public function delete(User $user, Wing $wing): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $wing->society_id === $user->society_id;
    }
}
