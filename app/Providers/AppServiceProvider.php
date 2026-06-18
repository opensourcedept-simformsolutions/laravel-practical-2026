<?php

namespace App\Providers;

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

        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::define('is-admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('is-gatekeeper', function ($user) {
            return $user->isResident();
        });

        Gate::define('is-resident', function ($user) {
            return $user->isGatekeeper();
        });
    }
}
