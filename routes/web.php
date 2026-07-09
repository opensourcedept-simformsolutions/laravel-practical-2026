<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\FlatController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\ResidentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Common\DashboardController;
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\ProfileController;
use App\Http\Controllers\Complaint\ComplaintController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\Gatekeeper\VisitorLogController;
use App\Http\Controllers\SuperAdmin\SocietyController;
use App\Http\Controllers\SuperAdmin\WingController;
use App\Http\Controllers\VisitorPass\VisitorPassController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->group(function () {
            Route::get('/', 'index')->name('profile');
            Route::get('/edit', 'edit')->name('profile.edit');
            Route::patch('/', 'update')->name('profile.update');
            Route::get('/password', 'passwordEdit')->name('profile.password.edit');
        });

    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::controller(NotificationController::class)
            ->prefix('notifications')
            ->name('notifications.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::patch('/{id}/read', 'markAsRead')->name('read');
                Route::post('/read-all', 'markAllAsRead')->name('read-all');
                Route::delete('/{id}', 'destroy')->name('destroy');
            });

        Route::middleware(['role:admin'])
            ->group(function () {

                Route::prefix('admin')
                    ->name('admin.')
                    ->group(function () {

                        Route::resource('users', UserController::class)->except('show');

                        Route::controller(UserController::class)
                            ->prefix('users')
                            ->name('users.')
                            ->group(function () {
                                Route::patch('{user}/restore', 'restore')
                                    ->withTrashed()
                                    ->name('restore');
                            });

                        Route::controller(ActivityLogController::class)
                            ->prefix('activity-logs')
                            ->name('activity-logs.')
                            ->group(function () {
                                Route::get('/', 'index')->name('index');
                                Route::get('data', 'data')->name('data');
                            });

                    });

                Route::controller(FlatController::class)
                    ->prefix('flats')
                    ->name('flats.')
                    ->group(function () {
                        Route::get('export', 'export')->name('export');
                        Route::patch('{flat}/restore', 'restore')
                            ->withTrashed()
                            ->name('restore');
                    });

                Route::resource('flats', FlatController::class);

                Route::controller(ResidentController::class)
                    ->prefix('residents/import')
                    ->name('residents.import.')
                    ->group(function () {
                        Route::get('/sample', 'downloadSampleCsv')->name('sample');
                        Route::get('/errors/{import}', 'downloadErrorReport')->name('errors');
                        Route::get('', 'showImportForm')->name('form');
                        Route::post('/upload', 'handleUpload')->name('upload');
                        Route::post('/map', 'showValidationPreview')->name('map');
                        Route::post('/process', 'processImport')->name('process');
                    });

                Route::controller(ResidentController::class)
                    ->prefix('residents')
                    ->name('residents.')
                    ->group(function () {
                        Route::patch('{resident}/restore', 'restore')
                            ->withTrashed()
                            ->name('restore');
                    });

                Route::resource('residents', ResidentController::class);
            });

        Route::middleware(['role:admin,gatekeeper'])
            ->controller(VisitorLogController::class)
            ->name('gatekeeper.')
            ->group(function () {
                Route::get('/pending-pass', 'pending')
                    ->withTrashed()
                    ->name('visitor-logs.pending');
                Route::patch('/visitor-logs/{visitorLog}/mark-entry', 'markEntry')
                    ->name('visitor-logs.mark-entry');
                Route::patch('/visitor-logs/{visitorLog}/mark-exit', 'markExit')
                    ->name('visitor-logs.mark-exit');
                Route::patch('/visitor-logs/{visitorLog}/restore', 'restore')
                    ->withTrashed()
                    ->name('visitor-logs.restore');
                Route::get('/visitor-logs/exited', 'exited')
                    ->name('visitor-logs.exited');
            });

        Route::middleware(['role:admin,resident,gatekeeper'])
            ->controller(ComplaintController::class)
            ->prefix('complaints')
            ->name('complaints.')
            ->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/', 'index')->name('index');
                Route::patch('/{complaint}/restore', 'restore')->withTrashed()->name('restore');
                Route::get('/{complaint}', 'show')->withTrashed()->name('show');
                Route::get('/{complaint}/edit', 'edit')->name('edit');
                Route::patch('/{complaint}', 'update')->name('update');
                Route::delete('/{complaint}', 'destroy')->name('destroy');
            });

        Route::middleware(['role:admin,resident,gatekeeper'])
            ->controller(VisitorPassController::class)
            ->prefix('passes')
            ->name('passes.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('data', 'data')->name('data');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{visitorLog}/edit', 'edit')->name('edit');
                Route::put('{visitorLog}', 'update')->name('update');
                Route::delete('{visitorLog}', 'destroy')->name('destroy');
                Route::patch('{visitorLog}/restore', 'restore')->withTrashed()->name('restore');
                Route::get('report', 'report')->name('report');
                Route::get('report/data', 'report')->name('report.data');
                Route::get('{visitorLog}', 'show')->name('show');
                Route::patch('{visitorLog}/cancel', 'cancel')->name('cancel');
                Route::patch('{visitorLog}/approve', 'approve')->name('approve');
                Route::patch('{visitorLog}/reject', 'reject')->name('reject');
            });

        Route::controller(SocietyController::class)
            ->prefix('societies')
            ->name('societies.')
            ->middleware('role:super_admin')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::get('data', 'data')->name('data');

                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');

                Route::get('{society}/flats', 'flats')->name('flats');

                Route::get('{society}', 'show')->name('show');
                Route::get('{society}/edit', 'edit')->name('edit');
                Route::put('{society}', 'update')->name('update');

                Route::delete('{society}', 'destroy')->name('destroy');
                Route::patch('{society}/restore', 'restore')->name('restore');

                Route::get('{society}/delete-preview', 'deletePreview')
                    ->name('delete-preview');
            });

        Route::controller(WingController::class)->group(function () {
            Route::get('societies/{society}/wings', 'bySociety')
                ->name('societies.wings');

            Route::get('wings/{wing}/flats', 'flats')
                ->name('wings.flats');
        });

        // Wings (super-admin + society admin)
        Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
            Route::resource('wings', WingController::class)->except(['show']);
        });

        Route::controller(DeliveryController::class)
            ->prefix('deliveries')
            ->name('deliveries.')
            ->group(function () {
                Route::get('data', 'data')->name('data');
                Route::patch('{delivery}/deliver', 'markDelivered')->name('deliver');
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{delivery}', 'show')->withTrashed()->name('show');
                Route::get('{delivery}/edit', 'edit')->name('edit');
                Route::put('{delivery}', 'update')->name('update');
                Route::delete('{delivery}', 'destroy')->name('destroy');
                Route::patch('{delivery}/restore', 'restore')->withTrashed()->name('restore');
            });

        Route::prefix('reports')
            ->name('reports.')
            ->group(function () {
                Route::prefix('deliveries')
                    ->name('deliveries.')
                    ->controller(DeliveryController::class)
                    ->group(function () {
                        Route::get('/', 'report')->name('');
                        Route::get('/data', 'reportData')->name('data');
                        Route::get('/export', 'export')->name('export');
                    });

                Route::prefix('passes')
                    ->name('passes.')
                    ->controller(VisitorPassController::class)
                    ->group(function () {
                        Route::get('/', 'report')->name('');
                        Route::get('/data', 'reportData')->name('data');
                        Route::get('/export', 'export')->name('export');
                    });

                Route::prefix('complaints')
                    ->name('complaints.')
                    ->controller(ComplaintController::class)
                    ->group(function () {
                        Route::get('/', 'report')->name('');
                        Route::get('/data', 'reportData')->name('data');
                        Route::get('/export', 'export')->name('export');
                    });
            });

        Route::middleware(['role:admin,gatekeeper'])
            ->controller(VisitorLogController::class)
            ->prefix('gatekeeper')
            ->name('gatekeeper.')
            ->group(function () {
                Route::get('/scan', 'scanPage')->name('scan');
                Route::post('/find-pass', 'findPass')->name('find-pass');
                Route::post('/mark-entry/{visitorLog}', 'markEntry')->name('mark-entry');
            });

        Route::controller(ImpersonationController::class)
            ->prefix('impersonate')
            ->name('impersonate.')
            ->group(function () {
                Route::post('leave', 'stop')->name('stop');
                Route::post('{user}', 'start')->name('start');
            });
    });
});

require __DIR__.'/auth.php';
