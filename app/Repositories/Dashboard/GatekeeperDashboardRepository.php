<?php

namespace App\Repositories\Dashboard;
use App\Models\Delivery;
use App\Models\VisitorLog;

class GatekeeperDashboardRepository
{
    public function getStats($user): array
    {
        $societyId = $user->society_id;

        return [
            'visitors_expected_today' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->whereDate('visit_date', today())
                ->count(),

            // Entries Today
            'entries_today' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->whereDate('visit_date', today())
                ->where('status', 'entered')
                ->count(),

            // Exits Today
            'exits_today' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->whereDate('visit_date', today())
                ->where('status', 'exited')
                ->count(),

            // Visitors Inside Now
            'visitors_inside_now' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->where('status', 'entered')
                ->whereNull('exit_time')
                ->count(),

            // Pending Deliveries
            'pending_deliveries' => Delivery::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->where('status', 'pending')
                ->count(),

            // Deliveries Received Today
            'deliveries_received' => Delivery::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->whereDate('created_at', today())
                ->count(),

            // Passes Verified
            'passes_verified' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->where('status', 'entered')
                ->count(),

            // Rejected Entries
            'rejected_entries' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )
                ->where('status', 'rejected')
                ->count(),
        ];

    }
}
