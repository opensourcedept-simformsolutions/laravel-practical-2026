<?php

namespace App\Repositories\Dashboard;
use App\Models\Resident;
use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Support\Facades\DB;


class AdminDashboardRepository
{
    public function getStats($user): array
    {
        return (array) DB::selectOne(
            'CALL sp_admin_dashboard(?)',
            [
                $user->society_id,
            ]
        );
    }
}
