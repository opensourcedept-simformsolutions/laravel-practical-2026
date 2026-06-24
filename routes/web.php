<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\GateKeeperController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Resident\VisitorPassController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SocietyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitorLogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware('auth')
    ->controller(ProfileController::class)
    ->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::get('/profile/edit', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
    });

Route::middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::resource('users', UserController::class)
                    ->except(['show']);
            });
        Route::resource('flats', FlatController::class);
        Route::resource('residents', ResidentController::class);
    });

Route::middleware(['auth', 'role:admin,gatekeeper'])
    ->controller(VisitorLogController::class)
    ->name('gatekeeper.')
    ->group(function () {
        Route::get('/pending-pass', 'pending')->name('visitor-logs.pending');
        Route::patch('/visitor-logs/{visitorLog}/mark-entry', 'markEntry')->name('visitor-logs.mark-entry');
        Route::patch('/visitor-logs/{visitorLog}/mark-exit', 'markExit')->name('visitor-logs.mark-exit');
        Route::get('/visitor-logs/exited', 'exited')->name('visitor-logs.exited');
    });

Route::middleware(['auth', 'role:admin,resident,gatekeeper'])
    ->controller(ComplaintController::class)
    ->name('complaints.')
    ->group(function () {
        Route::get('/complaints/create', 'create')->name('create');
        Route::post('/complaints', 'store')->name('store');
        Route::get('/complaints', 'index')->name('index');
        Route::get('/complaints/{complaint}', 'show')->name('show');

        Route::get('/complaints/{complaint}/edit', 'edit')->name('edit');
        Route::patch('/complaints/{complaint}', 'update')->name('update');
        Route::delete('/complaints/{complaint}', 'destroy')->name('destroy');
    });

Route::middleware(['auth', 'role:admin,resident,gatekeeper'])
    ->controller(VisitorPassController::class)
    ->name('passes.')
    ->group(function () {

        Route::get('passes', 'index')->name('index');
        Route::get('passes/data', 'data')->name('data');
        Route::get('passes/create', 'create')->name('create');
        Route::post('passes', 'store')->name('store');
        Route::get('/passes/{visitorLog}/edit', 'edit')->name('edit');
        Route::put('/passes/{visitorLog}', 'update')->name('update');
        Route::delete('passes/{visitorLog}', 'destroy')->name('destroy');
        Route::get('passes/report', 'report')->name('report');
        Route::get('passes/report/data', 'report')->name('report.data');
        Route::get('passes/{visitorLog}', 'show')->name('show');
        Route::patch('passes/{visitorLog}/cancel', 'cancel')->name('cancel');
    });

Route::middleware(['auth', 'role:super_admin'])
    ->controller(SocietyController::class)
    ->name('societies.')
    ->group(function () {

        Route::get('societies', 'index')->name('index');
        Route::get('societies/data', 'data')->name('data');

        Route::get('societies/create', 'create')->name('create');
        Route::post('societies', 'store')->name('store');

        Route::get('societies/{society}', 'show')->name('show');
        Route::get('societies/{society}/edit', 'edit')->name('edit');
        Route::put('societies/{society}', 'update')->name('update');

        Route::delete('societies/{society}', 'destroy')->name('destroy');

        Route::patch(
            'societies/{society}/restore',
            'restore'
        )->name('restore');
    });

Route::middleware('auth')
    ->controller(DeliveryController::class)
    ->group(function () {
        Route::get('deliveries/data', 'data')
            ->name('deliveries.data');

        Route::resource('deliveries', DeliveryController::class);

        Route::patch('deliveries/{delivery}/deliver', 'markDelivered')
            ->name('deliveries.deliver');
    });

Route::prefix('reports')
    ->name('reports.')
    ->middleware('auth')
    ->group(function () {

        Route::prefix('deliveries')
            ->name('deliveries.')
            ->controller(DeliveryController::class)
            ->group(function () {
                Route::get('/', 'report')->name('');
                Route::get('/data', 'reportData')->name('data');
                Route::get('/export', 'export')->name('export');
            });

        Route::prefix('passes')
            ->name('passes.')
            ->controller(VisitorPassController::class)
            ->group(function () {
                Route::get('/', 'report')->name('');
                Route::get('/data', 'reportData')->name('data');
                Route::get('/export', 'export')->name('export');
            });

        Route::prefix('complaints')
            ->name('complaints.')
            ->controller(ComplaintController::class)
            ->group(function () {
                Route::get('/', 'report')->name('');
                Route::get('/data', 'reportData')->name('data');
                Route::get('/export', 'export')->name('export');
            });
    });

Route::middleware(['auth'])->controller(GateKeeperController::class)->group(function () {

    Route::get(
        '/gatekeeper/scan', 'scanPage'
    )->name('gatekeeper.scan');

    Route::post(
        '/gatekeeper/find-pass', 'findPass'
    )->name('gatekeeper.find-pass');

    Route::post(
        '/gatekeeper/mark-entry/{visitorLog}', 'markEntry'
    )->name('gatekeeper.mark-entry');
});

require __DIR__.'/auth.php';
