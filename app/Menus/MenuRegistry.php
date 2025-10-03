<?php

namespace App\Menus;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MenuRegistry
{
    public static function resolve(): array
    {
        $user = Auth::user();

        $menus = [
            'vertical' => MenuAdmin::vertical(),
            'horizontal' => MenuAdmin::horizontal(),
        ];

        if ($user && method_exists($user, 'hasRole')) {
            if ($user->hasRole('courier')) {
                $menus = [
                    'vertical' => MenuRider::vertical(),
                    'horizontal' => MenuRider::horizontal(),
                ];
            } elseif ($user->hasAnyRole(['client_admin', 'zone_manager', 'support', 'warehouse_clerk'])) {
                $menus = [
                    'vertical' => MenuClient::vertical(),
                    'horizontal' => MenuClient::horizontal(),
                ];
            }
        }

        return static::formatForView($menus);
    }

    protected static function formatForView(array $menus): array
    {
        return [
            ['menu' => array_map([static::class, 'normalizeItem'], $menus['vertical'] ?? [])],
            ['menu' => array_map([static::class, 'normalizeItem'], $menus['horizontal'] ?? [])],
        ];
    }

    protected static function normalizeItem(array $item): array
    {
        if (isset($item['menuHeader'])) {
            return ['menuHeader' => $item['menuHeader']];
        }

        if (!isset($item['slug'])) {
            $item['slug'] = $item['route'] ?? Str::slug($item['name'] ?? 'item');
        }

        if (!isset($item['url'])) {
            $routeName = $item['route'] ?? null;

            if ($routeName && Route::has($routeName)) {
                $item['url'] = route($routeName);
            } elseif (isset($item['path'])) {
                $item['url'] = url($item['path']);
            } else {
                $item['url'] = '#';
            }
        }

        if (!empty($item['submenu']) && is_array($item['submenu'])) {
            $item['submenu'] = array_map([static::class, 'normalizeItem'], $item['submenu']);
        }

        return Arr::only($item, [
            'name',
            'icon',
            'slug',
            'url',
            'target',
            'badge',
            'submenu',
            'menuHeader',
        ]);
    }
}
