<?php

namespace App\Services;

use App\Models\Delivery;
use Illuminate\Support\Facades\Log;

class DeliveryNotificationService
{
    public function notify(Delivery $delivery, string $event, array $context = []): void
    {
        Log::channel('delivery')->info("Delivery notification stub: {$event}",
            [
                'delivery_id' => $delivery->id,
                'flat_id' => $delivery->flat_id,
                'resident_id' => $delivery->resident_id,
                'vendor' => $delivery->vendor,
                'status' => $delivery->status,
                'timestamp' => now()->toDateTimeString(),
                ...$context,
            ]
        );
    }

    public function failed(string $action, \Throwable $exception, array $context = []): void
    {
        Log::channel('delivery')->error(
            "Delivery operation failed: {$action}",
            [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                ...$context,
            ]
        );
    }
}
