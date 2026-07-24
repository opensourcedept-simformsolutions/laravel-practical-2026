<?php

namespace App\Policies;

use App\Models\Society;
use App\Models\User;

class SocietyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('societies.view');
    }

    public function view(User $user, Society $society): bool
    {
        return $user->hasPermission('societies.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('societies.create');
    }

    public function update(User $user, Society $society): bool
    {
        return $user->hasPermission('societies.edit');
    }

    public function delete(User $user, Society $society): bool
    {
        return $user->hasPermission('societies.delete');
    }

    public function restore(User $user, Society $society): bool
    {
        return $user->hasPermission('societies.delete');
    }

    public function forceDelete(User $user, Society $society): bool
    {
        return $user->hasPermission('societies.delete');
    }

    public function viewFlats(User $user, Society $society): bool
    {
        return $user->hasPermission('flats.view');
    }
}
