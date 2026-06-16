<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

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

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', function () {
        return 'Admin Dashboard';
    });

    Route::get('/complaints', [ComplaintController::class, 'index'])
        ->name('admin.complaints.index');

    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])
        ->name('admin.complaints.show');

    Route::patch('/complaints/{complaint}', [ComplaintController::class, 'update'])
        ->name('admin.complaints.update');
});

Route::middleware(['auth', 'role:resident'])->prefix('resident')->group(function () {

    Route::get('/dashboard', function () {
        return 'Resident Dashboard';
    });

    Route::get('/complaints/create', [ComplaintController::class, 'create'])
        ->name('resident.complaints.create');

    Route::post('/complaints', [ComplaintController::class, 'store'])
        ->name('resident.complaints.store');

    Route::get('/complaints', [ComplaintController::class, 'index'])
        ->name('resident.complaints.index');

    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])
        ->name('resident.complaints.show');
});

Route::middleware(['auth', 'role:gatekeeper'])->group(function () {
    Route::get('/gatekeeper/dashboard', [DashboardController::class, 'gatekeeper'])
        ->name('gatekeeper.dashboard');
});

require __DIR__ . '/auth.php';
