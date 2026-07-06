<?php

namespace App\Repositories\Dashboard;

use Illuminate\Support\Facades\DB;

class GatekeeperDashboardRepository
{
    public function getStats($user): array
    {
        return (array) DB::selectOne(
            'CALL sp_gatekeeper_dashboard(?)',
            [
                $user->society_id,
            ]
        );

    }
}
