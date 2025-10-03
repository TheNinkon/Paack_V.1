<?php

use App\Http\Controllers\Admin\Clients\ClientSwitchController;
use App\Http\Controllers\Admin\Clients\ClientsController;
use App\Http\Controllers\Admin\Couriers\CouriersController;
use App\Http\Controllers\Admin\Activity\ActivityLogsController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Profile\ProfileController;
use App\Http\Controllers\Admin\Providers\ProviderBarcodesController;
use App\Http\Controllers\Admin\Providers\ProvidersController;
use App\Http\Controllers\Admin\Users\UsersController;
use App\Http\Controllers\Admin\Zones\ZonesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('verified')
        ->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin')->group(function () {
        Route::resource('clients', ClientsController::class)->except(['show']);
        Route::post('clients/switch', ClientSwitchController::class)->name('clients.switch');

        Route::resource('users', UsersController::class)->except(['show']);
        Route::resource('providers', ProvidersController::class)->except(['show']);
        Route::get('providers/barcodes', [ProviderBarcodesController::class, 'index'])
            ->name('providers.barcodes.index');
        Route::resource('providers.barcodes', ProviderBarcodesController::class)
            ->shallow()
            ->except(['show', 'create', 'edit', 'index']);
        Route::resource('zones', ZonesController::class)->except(['show']);
        Route::resource('couriers', CouriersController::class)->except(['show']);
        Route::get('/activity', [ActivityLogsController::class, 'index'])->name('activity.index');
    });
});
