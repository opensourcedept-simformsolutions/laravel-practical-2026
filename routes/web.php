<?php

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

    Route::get('/admin/dashboard', function () {
        return 'Admin Dashboard';
    });

});

Route::middleware(['auth', 'role:resident'])->group(function () {

    Route::get('/resident/dashboard', function () {
        return 'Resident Dashboard';
    });

});

Route::middleware(['auth', 'role:gatekeeper'])->group(function () {

    Route::get('/gatekeeper/dashboard', function () {
        return 'Gatekeeper Dashboard';
    });

});

require __DIR__.'/auth.php';
