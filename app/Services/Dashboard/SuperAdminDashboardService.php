<?php

namespace App\Services\Dashboard;
use App\Repositories\Dashboard\SuperAdminDashboardRepository;

class SuperAdminDashboardService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private SuperAdminDashboardRepository $repository
    ) {}

    public function getDashboard() : array
    {
        return [

            'role' => 'super_admin',

            'stats' => $this->repository->getStats(),
        ];

    }
}
