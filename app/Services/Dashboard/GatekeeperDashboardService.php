<?php

namespace App\Services\Dashboard;

use App\Repositories\Dashboard\GatekeeperDashboardRepository;

class GatekeeperDashboardService
{
    public function __construct(
        private GatekeeperDashboardRepository $repository
    ) {}

    public function getDashboard($user): array
    {

        return [
            'role' => 'gatekeeper',

            'stats' => $this->repository->getStats($user),

        ];

    }
}
