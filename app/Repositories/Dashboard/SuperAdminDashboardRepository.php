<?php

namespace App\Repositories\Dashboard;

use Illuminate\Support\Facades\DB;

class SuperAdminDashboardRepository
{
    public function getStats(): array
    {
        return (array) DB::selectOne(
            'CALL sp_super_admin_dashboard()'
        );
    }
}
