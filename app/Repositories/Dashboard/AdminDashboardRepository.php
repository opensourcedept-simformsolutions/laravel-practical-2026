<?php

namespace App\Repositories\Dashboard;

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
