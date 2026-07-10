<?php

namespace App\Policies;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;

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
            return $complaint->user->society_id === $user->society_id
            && $complaint->status !== ComplaintStatus::RESOLVED->value;
        }

        if ($user->isResident() || $user->isGatekeeper()) {
            return $complaint->user_id === $user->id
                && $complaint->status === ComplaintStatus::OPEN->value;
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
            && in_array($complaint->status, [ComplaintStatus::OPEN->value, ComplaintStatus::RESOLVED->value]);
    }

    public function restore(User $user, Complaint $complaint): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }
}
