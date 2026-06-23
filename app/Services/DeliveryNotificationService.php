<?php

namespace App\Services;

use App\Models\Delivery;
use Illuminate\Support\Facades\Log;

/**
 * Handle delivery-related notification and error logging.
 *
 * Currently acts as a notification stub by writing delivery
 * events and failures to the delivery log channel.
 */
class DeliveryNotificationService
{
    /**
     * Log a delivery event.
     *
     * @param Delivery $delivery The delivery associated with the event.
     * @param string $event The event description.
     * @param array<string, mixed> $context Additional event context.
     */
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

    /**
     * Log a failed delivery operation.
     *
     * @param string $action The action being performed.
     * @param \Throwable $exception The exception that occurred.
     * @param array<string, mixed> $context Additional failure context.
     */
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
