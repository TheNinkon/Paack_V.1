<?php

use App\Http\Controllers\App\Couriers\CouriersController;
use App\Http\Controllers\App\Dashboard\DashboardController;
use App\Http\Controllers\App\Providers\ProviderBarcodesController;
use App\Http\Controllers\App\Providers\ProvidersController;
use App\Http\Controllers\App\Users\UsersController;
use App\Http\Controllers\App\Zones\ZonesController;
use App\Http\Controllers\App\Scans\ScansController;
use App\Http\Controllers\App\Parcels\ParcelHistoryController;
use App\Http\Controllers\App\Parcels\ParcelCheckController;
use App\Http\Controllers\App\Parcels\ParcelStateController;
use App\Http\Controllers\App\Parcels\ParcelsController;
use App\Http\Controllers\App\Parcels\ParcelSummaryController;
use App\Http\Controllers\App\Settings\MapSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:client_admin|zone_manager|support|warehouse_clerk'])
    ->prefix('app')
    ->name('app.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::resource('users', UsersController::class)->except(['show']);
        Route::resource('providers', ProvidersController::class)->except(['show']);
        Route::get('providers/barcodes', [ProviderBarcodesController::class, 'index'])
            ->name('providers.barcodes.index');
        Route::resource('providers.barcodes', ProviderBarcodesController::class)
            ->shallow()
            ->except(['show', 'create', 'edit', 'index']);

        Route::resource('zones', ZonesController::class)->except(['show']);
        Route::resource('couriers', CouriersController::class)->except(['show']);
        Route::get('/scans', [ScansController::class, 'index'])->name('scans.index');
        Route::get('settings/maps', [MapSettingsController::class, 'edit'])->name('settings.maps.edit');
        Route::put('settings/maps', [MapSettingsController::class, 'update'])->name('settings.maps.update');

        Route::post('/scans', [ScansController::class, 'store'])->name('scans.store');
        Route::get('/parcels', [ParcelsController::class, 'index'])->name('parcels.index');
        Route::post('/parcels', [ParcelsController::class, 'store'])->name('parcels.store');
        Route::get('/parcels/{parcel}/edit', [ParcelsController::class, 'edit'])->name('parcels.edit');
        Route::patch('/parcels/{parcel}', [ParcelsController::class, 'update'])->name('parcels.update');
        Route::post('/parcels/check', ParcelCheckController::class)->name('parcels.check');
        Route::patch('/parcels/{parcel}/kill', [ParcelStateController::class, 'kill'])->name('parcels.kill');
        Route::get('/parcels/{code}/summary', ParcelSummaryController::class)->name('parcels.summary');
        Route::get('/parcels/{code}', ParcelHistoryController::class)->name('parcels.show');
    });
