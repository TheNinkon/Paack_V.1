<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['client_admin', 'zone_manager', 'support', 'warehouse_clerk'])) {
            return redirect()->route('app.dashboard');
        }

        return view('Admin.Dashboard.index');
    }
}
