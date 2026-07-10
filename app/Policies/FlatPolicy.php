<?php

namespace App\Policies;

use App\Models\Flat;
use App\Models\User;

class FlatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGatekeeper();
    }

    public function view(User $user, Flat $flat): bool
    {
        if ($user->isAdmin() || $user->isGatekeeper()) {
            return $flat->society_id === $user->society_id;
        }

        if ($user->isResident() && $user->resident) {
            return $user->resident->flat_id === $flat->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Flat $flat): bool
    {
        return $user->isAdmin() && $flat->society_id === $user->society_id;
    }

    public function delete(User $user, Flat $flat): bool
    {
        return $user->isAdmin() && $flat->society_id === $user->society_id;
    }

    public function restore(User $user, Flat $flat): bool
    {
        return $user->isAdmin() && $flat->society_id === $user->society_id;
    }
}
