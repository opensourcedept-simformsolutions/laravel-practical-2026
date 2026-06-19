<?php

namespace App\Providers;


use App\Models\Complaint;
use App\Policies\ComplaintPolicy;
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

        Gate::before(function ($user) {
            if ($user->role->name === 'super_admin') {
                return true;
            }
        });

        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::define('is-admin', function ($user) {
            return $user->role->name === 'admin';
        });

        Gate::define('is-gatekeeper', function ($user) {
            return $user->role->name === 'gatekeeper';
        });

        Gate::define('is-resident', function ($user) {
            return $user->role->name === 'resident';
        });
    }
}
