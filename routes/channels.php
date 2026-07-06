<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('flat.{flatId}', function ($user, $flatId) {
    return $user->isResident() && $user->resident && (int) $user->resident->flat_id === (int) $flatId;
});

Broadcast::channel('society.{societyId}', function ($user, $societyId) {
    return ($user->isGatekeeper() || $user->isAdmin()) && (int) $user->society_id === (int) $societyId;
});

