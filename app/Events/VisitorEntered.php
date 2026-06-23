<?php

namespace App\Events;

use App\Models\VisitorLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VisitorEntered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public VisitorLog $visitorLog)
    {
        Log::info('VisitorEntered Event Created', [
            'visitor_log_id' => $visitorLog->id,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
