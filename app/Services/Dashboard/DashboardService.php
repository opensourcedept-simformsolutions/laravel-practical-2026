<?php

namespace App\Services\Dashboard;

class DashboardService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private SuperAdminDashboardService $superAdmin,
        private AdminDashboardService $admin,
        private GatekeeperDashboardService $gatekeeper,
        private ResidentDashboardService $resident
    ) {}

    public function dashboard() : \Illuminate\View\View
    {
        $user = auth()->user();

        $data = match ($user->role->name) {

            'super_admin' => $this->superAdmin->getDashboard(),

            'admin' => $this->admin->getDashboard($user),

            'gatekeeper' => $this->gatekeeper->getDashboard($user),

            'resident' => $this->resident->getDashboard($user),

            default => abort(403)
        };

        return view('dashboard.index',$data);
    }
}
