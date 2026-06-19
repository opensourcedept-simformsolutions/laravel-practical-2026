<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;
class DeliveryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGatekeeper() || $user->isResident();
    }

    public function view(User $user, Delivery $delivery): bool
    {
        if($user->isResident()){
            return $delivery->flat_id === $user->resident->flat_id;
        }

        return $this->sameSociety($user, $delivery)
            && $user->isAdmin() || $user->isGatekeeper();
    }

    public function create(User $user): bool
    {
        return ($user->isAdmin() || $user->isGatekeeper());
    }

    public function update(User $user, Delivery $delivery): bool
    {
        return $this->sameSociety($user, $delivery)
            && ($user->isAdmin() || $user->isGatekeeper());
    }

    public function delete(User $user, Delivery $delivery): bool
    {
        return $this->sameSociety($user, $delivery)
            && $user->isAdmin();
    }

    public function markDelivered(User $user, Delivery $delivery): bool
    {
        return $this->sameSociety($user, $delivery)
            && ($user->isAdmin() || $user->isGatekeeper());
    }

    private function canManageDeliveries(User $user): bool
    {
        return $user->isAdmin() || $user->isGatekeeper();
    }

    private function sameSociety(User $user, Delivery $delivery): bool
    {
        return $delivery->flat->society_id === $user->society_id;
    }
}
