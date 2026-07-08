<?php

namespace App\Policies;

use App\Enums\VisitorStatus;
use App\Models\User;
use App\Models\VisitorLog;
use Carbon\Carbon;

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
        if ($visitorLog->visit_date && Carbon::parse($visitorLog->visit_date)->isBefore(today())) {
            return false;
        }

        if ($user->isAdmin() || $user->isGatekeeper()) {
            return $visitorLog->created_by === $user->id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value]);
        }

        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value])) {
            $isCreatedByGatekeeper = $visitorLog->creator && $visitorLog->creator->isGatekeeper();

            return ! $isCreatedByGatekeeper;
        }

        return false;
    }

    public function cancel(User $user, VisitorLog $visitorLog): bool
    {
        if (! in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value])) {
            return false;
        }

        if ($user->isAdmin() && $visitorLog->flat->society_id === $user->society_id) {
            return true;
        }

        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id) {
            $isCreatedByGatekeeper = $visitorLog->creator && $visitorLog->creator->isGatekeeper();

            return ! $isCreatedByGatekeeper;
        }

        return false;
    }

    public function delete(User $user, VisitorLog $visitorLog): bool
    {
        if ($user->isAdmin() || $user->isGatekeeper()) {
            return $visitorLog->created_by === $user->id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value, VisitorStatus::REJECTED->value]);
        }

        if ($user->isResident() && $user->resident && $visitorLog->flat_id === $user->resident->flat_id && in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value, VisitorStatus::REJECTED->value])) {
            $isCreatedByGatekeeper = $visitorLog->creator && $visitorLog->creator->isGatekeeper();

            return ! $isCreatedByGatekeeper;
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
