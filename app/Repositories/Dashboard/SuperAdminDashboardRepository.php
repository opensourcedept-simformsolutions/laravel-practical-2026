<?php

namespace App\Repositories\Dashboard;

use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Resident;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\Society;

class SuperAdminDashboardRepository
{
    public function getStats(): array
    {
        return [
            'total_societies' => Society::count(),

            'total_users' => User::count(),

            'total_residents' => Resident::count(),

            'total_gatekeepers' => User::whereHas(
                'role',
                fn ($q) => $q->where('name', 'gatekeeper')
            )->count(),

            'visitors_today' => VisitorLog::whereDate(
                'visit_date',
                today()
            )->count(),

            'active_visitors' => VisitorLog::where(
                'status',
                'entered'
            )->whereNull(
                'exit_time'
            )->count(),

            'open_complaints' => Complaint::whereIn(
                'status',
                ['open', 'in_progress']
            )->count(),

            'pending_deliveries' => Delivery::where(
                'status',
                'received'
            )->count(),
        ];
    }
}
