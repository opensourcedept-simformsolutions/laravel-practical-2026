<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Resident\VisitorPassController;
use App\Http\Controllers\VisitorLogController;
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

Route::middleware(['auth', 'role:gatekeeper'])->name('gatekeeper.')->group(function () {
    Route::get('/pending-pass', [VisitorLogController::class, 'pending'])->name('visitor-logs.pending');
    Route::patch('/visitor-logs/{visitorLog}/mark-entry', [VisitorLogController::class, 'markEntry'])->name('visitor-logs.mark-entry');
    Route::patch('/visitor-logs/{visitorLog}/mark-exit', [VisitorLogController::class, 'markExit'])->name('visitor-logs.mark-exit');
    Route::get('/visitor-logs/exited',[VisitorLogController::class,'exited'])->name('visitor-logs.exited');
});

Route::middleware(['auth', 'role:resident,gatekeeper,admin,super_admin'])->group(function () {
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');

    Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
    Route::patch('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:resident,gatekeeper'])
    ->controller(VisitorPassController::class)
    ->name('passes.')
    ->group(function () {

        Route::get('passes', 'index')->name('index');
        Route::get('passes/data', 'data')->name('data');

        Route::get('passes/create', 'create')->name('create');
        Route::post('passes', 'store')->name('store');

        Route::get('passes/{visitorLog}/edit', 'edit')->name('edit');
        Route::put('passes/{visitorLog}', 'update')->name('update');
        Route::delete('passes/{visitorLog}', 'destroy')->name('destroy');

        Route::get('passes/{visitorLog}', 'show')->name('show');
        Route::patch('passes/{visitorLog}/cancel', 'cancel')->name('cancel');

        Route::get('visitor-report', 'report')->name('report');
    });

require __DIR__.'/auth.php';
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

require __DIR__.'/auth.php';
