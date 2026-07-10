<?php

namespace App\Policies;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

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

    public function create(User $user): bool
    {
        return $user->isResident() || $user->isGatekeeper();
    }

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
        if ($user->isAdmin()) {
            return $complaint->user->society_id === $user->society_id;
        }

        return false;
    }
}
