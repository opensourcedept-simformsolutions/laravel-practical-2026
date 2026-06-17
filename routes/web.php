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

Route::middleware(['auth', 'role:super_admin,admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', fn () => 'Admin Dashboard');

        Route::get('/complaints', [ComplaintController::class, 'index'])
            ->name('complaints.index');

        Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])
            ->name('complaints.show');

        Route::patch('/complaints/{complaint}', [ComplaintController::class, 'update'])
            ->name('complaints.update');
    });

Route::middleware(['auth', 'role:resident'])->group(function () {
    Route::get('resident/dashboard', fn () => 'User Dashboard');
});

Route::middleware(['auth', 'role:gatekeeper'])->group(function () {
    Route::get('gatekeeper/dashboard', fn () => 'User Dashboard');
});

Route::middleware(['auth', 'role:resident,gatekeeper'])->group(function () {
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
});

require __DIR__ . '/auth.php';
