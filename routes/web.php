<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->name('admin.dashboard');
});

Route::middleware(['auth', 'role:resident'])->group(function () {
    Route::get('/resident/dashboard', [DashboardController::class, 'resident'])
        ->name('resident.dashboard');
});

Route::middleware(['auth', 'role:gatekeeper'])->group(function () {
    Route::get('/gatekeeper/dashboard', [DashboardController::class, 'gatekeeper'])
        ->name('gatekeeper.dashboard');
});

Route::get('deliveries/data', [DeliveryController::class, 'data'])
    ->middleware(['auth', 'role:gatekeeper,admin'])
    ->name('deliveries.data');

Route::resource('/deliveries', DeliveryController::class)
    ->middleware(['auth', 'role:gatekeeper,admin']);

Route::patch('deliveries/{delivery}/deliver', [DeliveryController::class, 'markDelivered'])
    ->middleware(['auth', 'role:gatekeeper,admin'])
    ->name('deliveries.deliver');

Route::view('/test-form', 'testform');
Route::view('/test-tables', 'testtable');

require __DIR__.'/auth.php';
