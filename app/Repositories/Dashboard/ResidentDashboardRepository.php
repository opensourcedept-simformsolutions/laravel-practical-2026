<?php

namespace App\Repositories\Dashboard;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\Complaint;
use App\Models\Delivery;

class ResidentDashboardRepository
{
    public function getStats($user): array
    {
        $userId = $user->id;
        $residentId = $user->resident?->id;

        return [
            // Total visitor passes created by resident
            'my_visitor_passes' => VisitorLog::where(
                'created_by',
                $userId
            )->count(),

            // Visitors expected today
            'visitors_expected_today' => VisitorLog::where(
                'created_by',
                $userId
            )
                ->whereDate(
                    'visit_date',
                    today()
                )
                ->count(),

            // Visitors currently inside
            'active_visitors' => VisitorLog::where(
                'created_by',
                $userId
            )
                ->where(
                    'status',
                    'entered'
                )
                ->whereNull(
                    'exit_time'
                )
                ->count(),

            // All deliveries for this resident
            'my_deliveries' => Delivery::where(
                'resident_id',
                $residentId
            )->count(),

            // Package received at gate but not delivered yet
            'pending_deliveries' => Delivery::where(
                'resident_id',
                $residentId
            )
                ->where(
                    'status',
                    'received'
                )
                ->count(),

            // All complaints raised by this resident
            'my_complaints' => Complaint::where(
                'user_id',
                $userId
            )->count(),

            // Open/In Progress complaints
            'pending_complaints' => Complaint::where(
                'user_id',
                $userId
            )
                ->whereIn(
                    'status',
                    [
                        'open',
                        'in_progress',
                    ]
                )
                ->count(),

            // Resolved complaints
            'resolved_complaints' => Complaint::where(
                'user_id',
                $userId
            )
                ->where(
                    'status',
                    'resolved'
                )
                ->count(),
        ];

    }
}
