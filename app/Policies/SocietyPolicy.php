<?php

namespace App\Policies;

use App\Models\User;
use App\Models\society;
use Illuminate\Auth\Access\Response;

class SocietyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, society $society): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, society $society): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, society $society): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, society $society): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, society $society): bool
    {
        return $user->isSuperAdmin();
    }
}
