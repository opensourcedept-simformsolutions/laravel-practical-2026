<?php

namespace App\Events;

use App\Models\VisitorLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorApprovalRecalled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'notifications';

    public function __construct(public VisitorLog $visitorLog, public int $oldFlatId)
    {
        $this->visitorLog->load(['visitor']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('flat.'.$this->oldFlatId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->visitorLog->id,
            'visitor_name' => $this->visitorLog->visitor->name,
        ];
    }

    public function broadcastAs(): string
    {
        return 'visitor.approval.recalled';
    }
}
