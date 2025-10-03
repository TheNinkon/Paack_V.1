<?php

namespace App\Http\Controllers\App\Dashboard;

use App\Http\Controllers\Controller;
use App\Support\ClientContext;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ClientContext $clientContext)
    {
        $this->middleware('verified');
    }

    public function __invoke(): View
    {
        $user = auth()->user();

        $modules = collect();

        if ($user && $user->can('users.manage')) {
            $modules->push([
                'label' => __('Usuarios'),
                'description' => __('Administra accesos y roles internos.'),
                'route' => 'app.users.index',
                'icon' => 'ti tabler-users',
                'badge' => 'bg-label-primary',
            ]);
        }

        if ($user && ($user->can('providers.manage') || $user->can('barcodes.manage'))) {
            $modules->push([
                'label' => __('Proveedores'),
                'description' => __('Configura transportistas y patrones de códigos.'),
                'route' => 'app.providers.index',
                'icon' => 'ti tabler-truck-delivery',
                'badge' => 'bg-label-success',
            ]);
        }

        if ($user && $user->can('zones.manage')) {
            $modules->push([
                'label' => __('Zonas'),
                'description' => __('Define áreas operativas y códigos internos.'),
                'route' => 'app.zones.index',
                'icon' => 'ti tabler-map-search',
                'badge' => 'bg-label-warning',
            ]);
        }

        if ($user && ($user->can('couriers.manage') || $user->can('scan.view'))) {
            $modules->push([
                'label' => __('Repartidores'),
                'description' => __('Vincula repartidores con zonas y vehículos.'),
                'route' => 'app.couriers.index',
                'icon' => 'ti tabler-motorbike',
                'badge' => 'bg-label-info',
            ]);
        }

        if ($user && ($user->can('scan.create') || $user->can('scan.view'))) {
            $modules->push([
                'label' => __('Prerrecepción'),
                'description' => __('Escanea bultos y clasifícalos por proveedor automáticamente.'),
                'route' => 'app.scans.index',
                'icon' => 'ti tabler-barcode',
                'badge' => 'bg-label-primary',
            ]);
        }

        return view('App.Dashboard.index', [
            'currentClient' => $this->clientContext->client(),
            'modules' => $modules,
            'showScanPreview' => $user && $user->can('scan.create'),
        ]);
    }
}
