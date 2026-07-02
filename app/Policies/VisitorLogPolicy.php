<?php

namespace App\Policies;

use App\Enums\PendingPassStatus;
use App\Models\User;
use App\Models\VisitorLog;

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

        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id) {
            return true;
        }

        if ($user->isGatekeeper() && $visitorLog->flat->society_id === $user->society_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, VisitorLog $visitorLog): bool
    {
        if (($user->isAdmin() || $user->isGatekeeper()) && $visitorLog->created_by === $user->id && $visitorLog->status === PendingPassStatus::PENDING) {
            return true;
        }
        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id && $visitorLog->status === PendingPassStatus::PENDING) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, VisitorLog $visitorLog): bool
    {
        if ($user->isAdmin() && $visitorLog->flat->society_id === $user->society_id) {
            return true;
        }

        return $user->isResident()
            && $user->resident
            && $visitorLog->flat_id === $user->resident->flat_id;
    }

    public function delete(User $user, VisitorLog $visitorLog): bool
    {
        if (($user->isAdmin() || $user->isGatekeeper()) && $visitorLog->created_by === $user->id && $visitorLog->status === PendingPassStatus::PENDING) {
            return true;
        }

        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id && $visitorLog->status === PendingPassStatus::PENDING) {
            return true;
        }

        return false;
    }

    public function forceDelete(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function markEntry(User $user, VisitorLog $visitorLog): bool
    {
        return ($user->isAdmin() || $user->isGatekeeper())
        && $visitorLog->status === PendingPassStatus::PENDING
        && $visitorLog->flat->society_id === $user->society_id;
    }

    public function markExit(User $user, VisitorLog $visitorLog): bool
    {
        return ($user->isAdmin() || $user->isGatekeeper())
        && $visitorLog->status === PendingPassStatus::ENTERED
        && $visitorLog->flat->society_id === $user->society_id;
    }
}
