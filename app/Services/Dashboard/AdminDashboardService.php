<?php

namespace App\Services\Dashboard;

use App\Repositories\Dashboard\AdminDashboardRepository;

class AdminDashboardService
{
    public function __construct(
        private AdminDashboardRepository $repository
    ) {}

    public function getDashboard($user): array
    {
        return [

            'role' => 'admin',

            'stats' => $this->repository->getStats($user),
        ];
    }
}
