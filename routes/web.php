<?php

use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Resident\VisitorPassController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ResidentProfileController;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
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
        Route::get('passes/report', 'report')->name('report');
        Route::get('passes/report/data', 'report')->name('report.data');
        Route::get('passes/{visitorLog}', 'show')->name('show');
        Route::patch('passes/{visitorLog}/cancel', 'cancel')->name('cancel');
    });

Route::get('deliveries/data', [DeliveryController::class, 'data'])
    ->middleware(['auth'])
    ->name('deliveries.data');

Route::resource('/deliveries', DeliveryController::class)
    ->middleware(['auth']);

Route::patch('deliveries/{delivery}/deliver', [DeliveryController::class, 'markDelivered'])
    ->middleware(['auth'])
    ->name('deliveries.deliver');

Route::prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/deliveries', [DeliveryController::class, 'report'])
            ->name('deliveries');
        Route::get('/deliveries/data', [DeliveryController::class, 'reportData'])
            ->name('deliveries.data');
        Route::get('/deliveries/export',[DeliveryController::class, 'export'])
            ->name('deliveries.export');

        Route::get('/complaints', fn() => 'Complaint Report')
            ->name('complaints');

        Route::get('/visitors', fn() => 'Visitor Report')
            ->name('visitors');
    });

Route::view('/test-tables', 'testtable');
Route::view('/test-form', 'testform');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('flats', FlatController::class);
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('residents', ResidentController::class);
});

require __DIR__ . '/auth.php';
