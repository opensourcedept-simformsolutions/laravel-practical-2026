<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

/**
 * Authorization policy for delivery management.
 *
 * Controls access to delivery records based on user role
 * and society ownership.
 */
class DeliveryPolicy
{
    /**
     * Determine whether the user can view the delivery listing.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific delivery.
     *
     * Residents may only view deliveries for their own flat.
     * Administrators and gatekeepers may view deliveries
     * belonging to their society.
     */
    public function view(User $user, Delivery $delivery): bool
    {
        if ($user->isResident()) {
            return $delivery->flat_id === $user->resident->flat_id;
        }

        return $this->sameSociety($user, $delivery)
            && ($user->isAdmin() || $user->isGatekeeper());
    }

    /**
     * Determine whether the user can create deliveries.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isGatekeeper();
    }

    /**
     * Determine whether the user can update a delivery.
     */
    public function update(User $user, Delivery $delivery): bool
    {
        if ($delivery->status === 'delivered') {
            return false;
        }

        return $this->sameSociety($user, $delivery)
            && ($user->isAdmin() || $user->isGatekeeper());
    }

    /**
     * Determine whether the user can delete a delivery.
     */
    public function delete(User $user, Delivery $delivery): bool
    {
        return false;
    }

    /**
     * Determine whether the user can mark a delivery as delivered.
     */
    public function markDelivered(User $user, Delivery $delivery): bool
    {
        if ($delivery->status === 'delivered') {
            return false;
        }

        if ($user->isResident()) {
            return $delivery->resident_id === $user->resident->id;
        }

        return false;
    }

    /**
     * Determine whether the delivery belongs to the user's society.
     */
    private function sameSociety(User $user, Delivery $delivery): bool
    {
        return $delivery->flat->society_id === $user->society_id;
    }
}
