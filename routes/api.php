<?php

use App\Http\Controllers\Api\ApiKeyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Keys Management CRUD (protected by standard user authentication)
Route::middleware(['web', 'auth'])->prefix('api-keys')->group(function () {
    Route::get('/', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::get('/{id}', [ApiKeyController::class, 'show'])->name('api-keys.show');
    Route::put('/{id}', [ApiKeyController::class, 'update'])->name('api-keys.update');
    Route::delete('/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::post('/{id}/suspend', [ApiKeyController::class, 'suspend'])->name('api-keys.suspend');
    Route::post('/{id}/activate', [ApiKeyController::class, 'activate'])->name('api-keys.activate');
    Route::post('/{id}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');
    Route::post('/{id}/rotate', [ApiKeyController::class, 'rotate'])->name('api-keys.rotate');
});

// Demo/Secure API Endpoints authenticated via custom X-API-KEY and X-API-SECRET
Route::middleware('api.auth')->prefix('v1')->group(function () {
    Route::get('/ping', function () {
        return response()->json([
            'message' => 'pong',
            'api_key' => request()->attributes->get('auth_api_key')->key
        ]);
    })->name('api.v1.ping');

    Route::get('/user', function () {
        return response()->json([
            'user' => request()->user()->only('id', 'name', 'email')
        ]);
    })->name('api.v1.user');

    // Endpoint requiring specific "write" scope/permission
    Route::middleware('api.auth:write')->post('/test-write', function () {
        return response()->json([
            'message' => 'Write scope authorized successfully.'
        ]);
    })->name('api.v1.write');
});
