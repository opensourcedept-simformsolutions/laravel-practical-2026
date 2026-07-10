<?php

namespace App\Providers;

use App\Events\VisitorEntered;
use App\Events\VisitorExited;
use App\Listeners\SendVisitorEntryMail;
use App\Listeners\SendVisitorExitMail;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
            return $user->isGatekeeper();
        });

        Gate::define('is-resident', function ($user) {
            return $user->isResident();
        });

        Event::listen(VisitorEntered::class, SendVisitorEntryMail::class);

        Event::listen(VisitorExited::class, SendVisitorExitMail::class);

        Event::listen(Login::class, function ($event) {
            ActivityLogger::log('login', $event->user, "User {$event->user->name} logged in.");
        });

        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                ActivityLogger::log('logout', $event->user, "User {$event->user->name} logged out.");
            }
        });
    }
}
