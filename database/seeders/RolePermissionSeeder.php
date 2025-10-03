<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'clients.manage',
            'users.manage',
            'providers.manage',
            'zones.manage',
            'couriers.manage',
            'barcodes.manage',
            'scan.create',
            'scan.view',
        ])->map(function (string $permission) {
            return Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        });

        $roles = [
            'super_admin' => $permissions->pluck('name')->all(),
            'client_admin' => [
                'users.manage',
                'providers.manage',
                'zones.manage',
                'couriers.manage',
                'barcodes.manage',
                'scan.view',
            ],
            'zone_manager' => [
                'zones.manage',
                'couriers.manage',
                'scan.view',
            ],
            'support' => [
                'scan.view',
            ],
            'warehouse_clerk' => [
                'scan.create',
                'scan.view',
            ],
            'courier' => [],
        ];

        foreach ($roles as $role => $rolePermissions) {
            $roleModel = Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web']
            );

            $roleModel->syncPermissions($rolePermissions);
        }

        $superAdmin = User::firstOrCreate(
            ['email' => config('app.super_admin_email')],
            [
                'name' => config('app.super_admin_name'),
                'password' => Hash::make(config('app.super_admin_password')),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        if ($superAdmin->wasRecentlyCreated) {
            $superAdmin->client_id = null;
            $superAdmin->is_active = true;
            $superAdmin->save();
        } else {
            $superAdmin->forceFill([
                'client_id' => null,
                'is_active' => true,
            ])->save();
        }

        $superAdmin->assignRole('super_admin');
    }
}
