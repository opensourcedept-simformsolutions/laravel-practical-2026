<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\Delivery;
use App\Policies\ComplaintPolicy;
use App\Policies\DeliveryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        require_once app_path('Helpers/helpers.php');

        Gate::policy(Delivery::class, DeliveryPolicy::class);

        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::define('is-admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('is-gatekeeper', function ($user) {
            return $user->isGatekeeper();
        });

        Gate::define('is-resident', function ($user) {
            return $user->isResident();
        });

    }
}
