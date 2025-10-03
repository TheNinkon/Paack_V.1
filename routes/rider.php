<?php

use App\Http\Controllers\Courier\DashboardController as CourierDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:courier'])
    ->prefix('courier')
    ->name('courier.')
    ->group(function () {
        Route::get('/', CourierDashboardController::class)->name('dashboard');
    });
