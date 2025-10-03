<?php

namespace App\Menus;

class MenuAdmin
{
    public static function vertical(): array
    {
        return [
            [
                'name' => __('Dashboard'),
                'icon' => 'menu-icon icon-base ti tabler-layout-dashboard',
                'route' => 'dashboard',
            ],
            [
                'name' => __('Profile'),
                'icon' => 'menu-icon icon-base ti tabler-user-cog',
                'route' => 'profile.edit',
            ],
            [
                'menuHeader' => __('Management')
            ],
            [
                'name' => __('Clients'),
                'icon' => 'menu-icon icon-base ti tabler-building-community',
                'route' => 'admin.clients.index',
            ],
            [
                'name' => __('Users'),
                'icon' => 'menu-icon icon-base ti tabler-users',
                'route' => 'admin.users.index',
            ],
            [
                'name' => __('Providers'),
                'icon' => 'menu-icon icon-base ti tabler-truck-delivery',
                'route' => 'admin.providers.index',
                'submenu' => [
                    [
                        'name' => __('Provider Barcodes'),
                        'icon' => 'menu-icon icon-base ti tabler-barcode',
                        'route' => 'admin.providers.barcodes.index',
                    ],
                ],
            ],
            [
                'name' => __('Zones'),
                'icon' => 'menu-icon icon-base ti tabler-map-search',
                'route' => 'admin.zones.index',
            ],
            [
                'name' => __('Couriers'),
                'icon' => 'menu-icon icon-base ti tabler-motorbike',
                'route' => 'admin.couriers.index',
            ],
            [
                'name' => __('Activity Log'),
                'icon' => 'menu-icon icon-base ti tabler-news',
                'route' => 'admin.activity.index',
            ],
        ];
    }

    public static function horizontal(): array
    {
        return [];
    }
}
