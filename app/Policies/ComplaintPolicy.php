<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use App\Enums\ComplaintStatus;

class ComplaintPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return $complaint->user->society_id === $user->society_id;
        }

        if ($user->isResident() || $user->isGatekeeper()) {
            return $complaint->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isResident() || $user->isGatekeeper();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return $complaint->user->society_id === $user->society_id;
        }

        if ($user->isResident() || $user->isGatekeeper()) {
            return $complaint->user_id === $user->id
                && $complaint->status !== ComplaintStatus::RESOLVED->value;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return $complaint->user->society_id === $user->society_id;
        }

        return $complaint->user_id === $user->id
            && $complaint->status === ComplaintStatus::RESOLVED->value;
    }
}
