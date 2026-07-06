<?php

namespace App\Events;

use App\Models\VisitorLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorApprovalStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public VisitorLog $visitorLog)
    {
        $this->visitorLog->load(['visitor', 'flat']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('society.'.$this->visitorLog->flat->society_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->visitorLog->id,
            'visitor_name' => $this->visitorLog->visitor->name,
            'status' => $this->visitorLog->status,
            'flat_name' => $this->visitorLog->flat->wing.'-'.$this->visitorLog->flat->flat_number,
        ];
    }

    public function broadcastAs(): string
    {
        return 'visitor.approval.status.updated';
    }
}
