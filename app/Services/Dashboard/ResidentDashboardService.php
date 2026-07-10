<?php

namespace App\Services\Dashboard;

use App\Repositories\Dashboard\ResidentDashboardRepository;

class ResidentDashboardService
{
    public function __construct(
        private ResidentDashboardRepository $repository
    ) {}

    public function getDashboard($user): array
    {
        return [

            'role' => 'resident',

            'stats' => $this->repository->getStats($user),

        ];

    }
}
