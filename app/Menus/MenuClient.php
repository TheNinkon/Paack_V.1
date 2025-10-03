<?php

namespace App\Menus;

use Illuminate\Support\Facades\Auth;

class MenuClient
{
    public static function vertical(): array
    {
        $user = Auth::user();

        $items = [
            [
                'name' => __('Dashboard'),
                'icon' => 'menu-icon icon-base ti tabler-layout-dashboard',
                'route' => 'app.dashboard',
            ],
        ];

        $operations = [];

        if ($user && $user->can('users.manage')) {
            $operations[] = [
                'name' => __('Usuarios'),
                'icon' => 'menu-icon icon-base ti tabler-users',
                'route' => 'app.users.index',
            ];
        }

        if ($user && ($user->can('providers.manage') || $user->can('barcodes.manage'))) {
            $operations[] = [
                'name' => __('Proveedores'),
                'icon' => 'menu-icon icon-base ti tabler-truck-delivery',
                'route' => 'app.providers.index',
            ];
        }

        if ($user && $user->can('zones.manage')) {
            $operations[] = [
                'name' => __('Zonas'),
                'icon' => 'menu-icon icon-base ti tabler-map-search',
                'route' => 'app.zones.index',
            ];
        }

        if ($user && ($user->can('couriers.manage') || $user->hasRole('support') || $user->hasRole('zone_manager'))) {
            $operations[] = [
                'name' => __('Repartidores'),
                'icon' => 'menu-icon icon-base ti tabler-motorbike',
                'route' => 'app.couriers.index',
            ];
        }

        if ($user && ($user->can('scan.create') || $user->can('scan.view'))) {
            $operations[] = [
                'name' => __('Prerrecepción'),
                'icon' => 'menu-icon icon-base ti tabler-barcode',
                'route' => 'app.scans.index',
            ];

            $operations[] = [
                'name' => __('Bultos'),
                'icon' => 'menu-icon icon-base ti tabler-packages',
                'route' => 'app.parcels.index',
            ];
        }

        if (! empty($operations)) {
            $items[] = [
                'menuHeader' => __('Operaciones'),
            ];

            $items = array_merge($items, $operations);
        }
        if ($user && $user->hasRole('client_admin')) {
            $items[] = [
                'menuHeader' => __('Configuración'),
            ];

            $items[] = [
                'name' => __('Configuración de mapas'),
                'icon' => 'menu-icon icon-base ti tabler-map',
                'route' => 'app.settings.maps.edit',
            ];
        }

        return $items;
    }

    public static function horizontal(): array
    {
        return [];
    }
}
