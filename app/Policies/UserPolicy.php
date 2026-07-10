<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role->name, ['admin', 'super_admin']);

    }

    public function update(User $user, User $model): bool
    {
        return $user->society_id === $model->society_id
        && $model->role->name !== 'super_admin'
        && $user->id !== $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->society_id === $model->society_id
        && $model->role->name !== 'super_admin'
        && $user->id !== $model->id;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin()
            && $user->society_id === $model->society_id
            && $model->role->name !== 'super_admin'
            && $user->id !== $model->id;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
