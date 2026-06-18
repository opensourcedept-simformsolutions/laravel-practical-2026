<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Resident\VisitorPassController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ResidentProfileController;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {

        Route::resource('users', UserController::class);
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

Route::view('/test-tables', 'testtable');

Route::view('/test-form', 'testform');
Route::view('/test-tables', 'testtable');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('flats', FlatController::class);
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('residents', ResidentController::class);
});

Route::view('/test-form', 'testform');
Route::view('/test-tables', 'testtable');

Route::view('/test-form', 'testform');
Route::view('/test-tables', 'testtable');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('flats', FlatController::class);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('residents', ResidentController::class);
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');
});

require __DIR__ . '/auth.php';
