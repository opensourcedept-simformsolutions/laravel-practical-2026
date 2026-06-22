<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Resident\VisitorPassController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\VisitorLogController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')
    ->controller(ProfileController::class)
    ->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::get('/profile/edit', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
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

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('flats', FlatController::class);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('residents', ResidentController::class);
});

Route::get('deliveries/data', [DeliveryController::class, 'data'])
    ->middleware(['auth'])
    ->name('deliveries.data');

Route::resource('/deliveries', DeliveryController::class)
    ->middleware(['auth']);

Route::patch('deliveries/{delivery}/deliver', [DeliveryController::class, 'markDelivered'])
    ->middleware(['auth'])
    ->name('deliveries.deliver');

Route::prefix('deliveries')
    ->middleware('auth')
    ->controller(DeliveryController::class)
    ->name('deliveries.')
    ->group(function () {
        Route::get('/data', 'data')->name('data');
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{delivery}', 'show')->name('show');
        Route::get('/{delivery}/edit', 'edit')->name('edit');
        Route::put('/{delivery}', 'update')->name('update');
        Route::delete('/{delivery}', 'destroy')->name('destroy');
        Route::patch('/{delivery}/deliver', 'markDelivered')->name('deliver');
    });

Route::prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/deliveries', fn () => 'Delivery Report')
            ->name('deliveries');

        Route::get('/complaints', fn () => 'Complaint Report')
            ->name('complaints');

        Route::get('/visitors', fn () => 'Visitor Report')
            ->name('visitors');
    });

require __DIR__.'/auth.php';
