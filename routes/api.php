<?php

use App\Http\Controllers\Api\Courier\AuthController;
use App\Http\Controllers\Api\Courier\ParcelController;
use Illuminate\Support\Facades\Route;

Route::prefix('rider')->name('rider.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('courier.api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::get('parcels', [ParcelController::class, 'index'])->name('parcels.index');
        Route::post('parcels/{parcel}/events', [ParcelController::class, 'storeEvent'])->name('parcels.events.store');
    });
});
