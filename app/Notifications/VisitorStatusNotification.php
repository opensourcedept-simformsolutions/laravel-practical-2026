<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use App\Models\VisitorLog;
use Illuminate\Notifications\Notification;

class VisitorStatusNotification extends Notification
{
    public function __construct(public VisitorLog $visitorLog)
    {
        $this->visitorLog->load(['visitor', 'flat', 'approver']);
    }

    public function via(object $notifiable): array
    {
        return [CustomDatabaseChannel::class];
    }

    public function toTitle(object $notifiable): string
    {
        $status = ucfirst($this->visitorLog->status);

        return "Visitor Entry Request {$status}";
    }

    public function toMessage(object $notifiable): string
    {
        $status = $this->visitorLog->status;
        $visitorName = $this->visitorLog->visitor->name;
        $approverName = $this->visitorLog->approver?->name ?? 'Unknown Resident';
        $flatName = $this->visitorLog->flat->wing.'-'.$this->visitorLog->flat->flat_number;

        return "Visitor {$visitorName} has been {$status} by {$approverName} for flat {$flatName}.";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'visitor_log_id' => $this->visitorLog->id,
            'visitor_name' => $this->visitorLog->visitor->name,
            'status' => $this->visitorLog->status,
            'approver_name' => $this->visitorLog->approver?->name ?? 'Unknown Resident',
            'flat_name' => $this->visitorLog->flat->wing.'-'.$this->visitorLog->flat->flat_number,
        ];
    }
}
