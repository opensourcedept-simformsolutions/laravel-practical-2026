<?php

namespace App\Repositories\Dashboard;
use App\Models\Resident;
use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\User;
use App\Models\VisitorLog;

class AdminDashboardRepository
{
    public function getStats($user): array
    {
        $societyId = $user->society_id;

        return [

            'residents' => Resident::whereHas(
                'user',
                fn ($q) => $q->where('society_id', $societyId)
            )->count(),

            'flats' => Flat::where(
                'society_id',
                $societyId
            )->count(),

            'visitors_today' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )->whereDate('visit_date', today())
                ->count(),

            'visitors_inside' => VisitorLog::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )->where('status', 'entered')
                ->whereNull('exit_time')
                ->count(),

            'deliveries_today' => Delivery::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )->whereDate('created_at', today())
                ->count(),

            'pending_deliveries' => Delivery::whereHas(
                'flat',
                fn ($q) => $q->where('society_id', $societyId)
            )->where('status', 'pending')
                ->count(),

            'open_complaints' => Complaint::whereHas(
                'user',
                fn ($q) => $q->where('society_id', $societyId)
            )->whereIn(
                'status',
                ['open', 'in_progress']
            )->count(),

            'resolved_complaints' => Complaint::whereHas(
                'user',
                fn ($q) => $q->where('society_id', $societyId)
            )->where('status', 'resolved')
                ->count(),
        ];
    }
}
