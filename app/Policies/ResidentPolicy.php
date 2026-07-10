<?php

namespace App\Policies;

use App\Models\Resident;
use App\Models\User;

class ResidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Resident $resident): bool
    {
        return $resident->user->society_id === $user->society_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Resident $resident): bool
    {
        return $resident->user->society_id === $user->society_id;
    }

    public function delete(User $user, Resident $resident): bool
    {
        return $resident->user->society_id === $user->society_id;
    }

    public function restore(User $user, Resident $resident): bool
    {
        return false;
    }

    public function forceDelete(User $user, Resident $resident): bool
    {
        return false;
    }
}
