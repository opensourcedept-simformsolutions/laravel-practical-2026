<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Resident\VisitorPassController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

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
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::get('passes/{visitorLog}', 'show')->name('show');
        Route::patch('passes/{visitorLog}/cancel', 'cancel')->name('cancel');
        Route::get('visitor-report', 'report')->name('report');
    });

require __DIR__ . '/auth.php';
