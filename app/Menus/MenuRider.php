<?php

namespace App\Menus;

class MenuRider
{
    public static function vertical(): array
    {
        return [
            [
                'name' => __('Mis entregas'),
                'icon' => 'menu-icon icon-base ti tabler-road',
                'route' => 'courier.dashboard',
            ],
            [
                'name' => __('Perfil'),
                'icon' => 'menu-icon icon-base ti tabler-user',
                'route' => 'profile.edit',
            ],
        ];
    }

    public static function horizontal(): array
    {
        return [];
    }
}
