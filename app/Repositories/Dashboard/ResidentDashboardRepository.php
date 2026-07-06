<?php

namespace App\Repositories\Dashboard;

use Illuminate\Support\Facades\DB;

class ResidentDashboardRepository
{
    public function getStats($user): array
    {
        return (array) DB::selectOne(
            'CALL sp_resident_dashboard(?,?)',
            [
                $user->id,
                $user->resident?->id,
            ]
        );
    }
}
