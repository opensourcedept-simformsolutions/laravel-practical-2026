<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\FlatController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\ResidentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Common\DashboardController;
use App\Http\Controllers\Common\ProfileController;
use App\Http\Controllers\Complaint\ComplaintController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\Gatekeeper\VisitorLogController;
use App\Http\Controllers\SuperAdmin\SocietyController;
use App\Http\Controllers\VisitorPass\VisitorPassController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->group(function () {
            Route::get('/', 'index')->name('profile');
            Route::get('/edit', 'edit')->name('profile.edit');
            Route::patch('/', 'update')->name('profile.update');
        });

    Route::middleware(['role:admin'])
        ->group(function () {

            Route::prefix('admin')
                ->name('admin.')
                ->group(function () {

                    Route::resource('users', UserController::class)
                        ->except(['show']);
                    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
                    Route::get('activity-logs/data', [ActivityLogController::class, 'data'])->name('activity-logs.data'); 
                });

            Route::resource('flats', FlatController::class)->except('show');
            Route::patch('/{flat}/restore', [FlatController::class, 'restore'])->withTrashed()->name('flats.restore');
            Route::get('/flats/export', [FlatController::class, 'export'])->name('flats.export');

            Route::resource('residents', ResidentController::class);
            Route::patch('/{residents}/restore', [ResidentController::class, 'restore'])->withTrashed()->name('residents.restore');
        });

    Route::middleware(['role:admin,gatekeeper'])
        ->controller(VisitorLogController::class)
        ->name('gatekeeper.')
        ->group(function () {
            Route::get('/pending-pass', 'pending')
                ->withTrashed()
                ->name('visitor-logs.pending');
            Route::patch('/visitor-logs/{visitorLog}/mark-entry', 'markEntry')
                ->name('visitor-logs.mark-entry');
            Route::patch('/visitor-logs/{visitorLog}/mark-exit', 'markExit')
                ->name('visitor-logs.mark-exit');
            Route::patch('/visitor-logs/{visitorLog}/restore', 'restore')
                ->withTrashed()
                ->name('visitor-logs.restore');
            Route::get('/visitor-logs/exited', 'exited')
                ->name('visitor-logs.exited');
        });

    Route::middleware(['role:admin,resident,gatekeeper'])
        ->controller(ComplaintController::class)
        ->prefix('complaints')
        ->name('complaints.')
        ->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/', 'index')->name('index');
            Route::patch('/{complaint}/restore', 'restore')->withTrashed()->name('restore');
            Route::get('/{complaint}', 'show')->withTrashed()->name('show');
            Route::get('/{complaint}/edit', 'edit')->name('edit');
            Route::patch('/{complaint}', 'update')->name('update');
            Route::delete('/{complaint}', 'destroy')->name('destroy');
        });

    Route::middleware(['role:admin,resident,gatekeeper'])
        ->controller(VisitorPassController::class)
        ->prefix('passes')
        ->name('passes.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('data', 'data')->name('data');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{visitorLog}/edit', 'edit')->name('edit');
            Route::put('{visitorLog}', 'update')->name('update');
            Route::delete('{visitorLog}', 'destroy')->name('destroy');
            Route::get('report', 'report')->name('report');
            Route::get('report/data', 'report')->name('report.data');
            Route::get('{visitorLog}', 'show')->name('show');
            Route::patch('{visitorLog}/cancel', 'cancel')->name('cancel');
        });

    Route::middleware(['role:super_admin'])
        ->controller(SocietyController::class)
        ->prefix('societies')
        ->name('societies.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{society?}/flats', 'flats')->name('flats');
            Route::get('/{society}', 'show')->name('show');
            Route::get('/{society}/edit', 'edit')->name('edit');
            Route::put('/{society}', 'update')->name('update');
            Route::delete('/{society}', 'destroy')->name('destroy');
            Route::patch('/{society}/restore', 'restore')->name('restore');
        });

    Route::controller(DeliveryController::class)
        ->prefix('deliveries')
        ->name('deliveries.')
        ->group(function () {
            Route::get('data', 'data')->name('data');
            Route::patch('{delivery}/deliver', 'markDelivered')->name('deliver');
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{delivery}', 'show')->withTrashed()->name('show');
            Route::get('{delivery}/edit', 'edit')->name('edit');
            Route::put('{delivery}', 'update')->name('update');
            Route::delete('{delivery}', 'destroy')->name('destroy');
            Route::patch('{delivery}/restore', 'restore')->withTrashed()->name('restore');
        });

    Route::prefix('reports')
        ->name('reports.')
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

    Route::middleware(['role:admin,gatekeeper'])
        ->controller(VisitorLogController::class)
        ->prefix('gatekeeper')
        ->name('gatekeeper.')
        ->group(function () {
            Route::get('/scan', 'scanPage')->name('scan');
            Route::post('/find-pass', 'findPass')->name('find-pass');
            Route::post('/mark-entry/{visitorLog}', 'markEntry')->name('mark-entry');
        });

    Route::controller(ImpersonationController::class)
        ->prefix('impersonate')
        ->name('impersonate.')
        ->group(function () {
            Route::post('leave', 'stop')->name('stop');
            Route::post('{user}', 'start')->name('start');
        });
});

require __DIR__.'/auth.php';
