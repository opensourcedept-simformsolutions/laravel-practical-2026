<?php

namespace App\Policies;

use App\Enums\VisitorStatus;
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
        if (($user->isAdmin() || $user->isGatekeeper()) && $visitorLog->created_by === $user->id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value])) {
            return true;
        }
        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value])) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, VisitorLog $visitorLog): bool
    {
        if (!in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value])) {
            return false;
        }

        if ($user->isAdmin() && $visitorLog->flat->society_id === $user->society_id) {
            return true;
        }

        return $user->isResident()
            && $user->resident
            && $visitorLog->flat_id === $user->resident->flat_id;
    }

    public function delete(User $user, VisitorLog $visitorLog): bool
    {
        if (($user->isAdmin() || $user->isGatekeeper()) && $visitorLog->created_by === $user->id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value, VisitorStatus::REJECTED->value])) {
            return true;
        }

        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value, VisitorStatus::REJECTED->value])) {
            return true;
        }

        return false;
    }

    public function forceDelete(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, VisitorLog $visitorLog): bool
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

        return false;
    }

    public function markEntry(User $user, VisitorLog $visitorLog): bool
    {
        return ($user->isAdmin() || $user->isGatekeeper())
        && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::APPROVED->value])
        && $visitorLog->flat->society_id === $user->society_id;
    }

    public function markExit(User $user, VisitorLog $visitorLog): bool
    {
        return ($user->isAdmin() || $user->isGatekeeper())
        && $visitorLog->status === VisitorStatus::ENTERED->value
        && $visitorLog->flat->society_id === $user->society_id;
    }
}
