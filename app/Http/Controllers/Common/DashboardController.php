<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        return $this->dashboardService->dashboard();
    }


}
