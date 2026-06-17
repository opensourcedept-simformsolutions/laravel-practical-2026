<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VisitorController;
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

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
        Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
        Route::patch('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    });

Route::middleware(['auth', 'role:resident'])->group(function () {
    Route::get('resident/dashboard', [DashboardController::class, 'resident'])->name('resident.dashboard');
});

Route::middleware(['auth', 'role:gatekeeper'])->prefix('gatekeeper')->name('gatekeeper.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'gatekeeper'])->name('dashboard');
    Route::get('/visitors' , [VisitorController::class, 'index'])->name('visitors.index');
    Route::get('/visitors/create', [VisitorController::class,'create'])->name('visitors.create');
    Route::post('/visitors',[VisitorController::class,'store'])->name('visitor.store');
});

Route::middleware(['auth', 'role:resident,gatekeeper'])->group(function () {
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
});

require __DIR__.'/auth.php';
