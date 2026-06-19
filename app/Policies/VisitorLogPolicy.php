<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Auth\Access\Response;

class VisitorLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VisitorLog $visitorLog): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin() && $visitorLog->flat->society_id === $user->society_id) {
            return true;
        }

        if (
            $user->isResident() &&
            $user->resident &&
            $visitorLog->flat_id === $user->resident->flat_id
        ) {
            return true;
        }

        if ($user->isGatekeeper()) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isResident() || $user->isGatekeeper();
    }

    public function update(User $user, VisitorLog $visitorLog): bool
    {
        if (
            $user->isResident()
            && $user->resident
            && $visitorLog->flat_id === $user->resident->flat_id
            && $visitorLog->status === 'pending'
        ) {
            return true;
        }

        if ($user->isGatekeeper()) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, VisitorLog $visitorLog): bool
    {
        return $user->isResident()
            && $user->resident
            && $visitorLog->flat_id === $user->resident->flat_id;
    }

    public function delete(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function markEntry(User $user, VisitorLog $visitorLog): bool
    {
        return $user->isGatekeeper()
            && $visitorLog->status === 'pending';
    }

    public function markExit(User $user, VisitorLog $visitorLog): bool
    {
        return $user->isGatekeeper()
            && $visitorLog->status === 'entered';
    }
}
