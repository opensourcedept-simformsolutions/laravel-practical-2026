<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
