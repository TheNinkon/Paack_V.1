<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if ($user && method_exists($user, 'hasRole') && $user->hasRole('courier')) {
        return redirect()->route('courier.dashboard');
    }

    if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['client_admin', 'zone_manager', 'support', 'warehouse_clerk'])) {
        return redirect()->route('app.dashboard');
    }

    return redirect()->route('dashboard');
});

require __DIR__ . '/admin.php';
require __DIR__ . '/app.php';
require __DIR__ . '/rider.php';
require __DIR__ . '/auth.php';
