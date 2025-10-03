<?php

namespace Tests\Unit\Menus;

use App\Menus\MenuClient;
use App\Menus\MenuRegistry;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_client_admin_menu_lists_all_operations(): void
    {
        $user = $this->createUserWithRole('client_admin');
        $this->be($user);

        $menu = collect(MenuClient::vertical());

        $this->assertTrue($menu->contains(fn ($item) => ($item['route'] ?? null) === 'app.users.index'));
        $this->assertTrue($menu->contains(fn ($item) => ($item['route'] ?? null) === 'app.providers.index'));
        $this->assertTrue($menu->contains(fn ($item) => ($item['route'] ?? null) === 'app.zones.index'));
        $this->assertTrue($menu->contains(fn ($item) => ($item['route'] ?? null) === 'app.couriers.index'));
        $this->assertTrue($menu->contains(fn ($item) => ($item['route'] ?? null) === 'app.parcels.index'));
    }

    public function test_support_menu_only_includes_couriers(): void
    {
        $user = $this->createUserWithRole('support', 'support@example.com');
        $this->be($user);

        $menu = collect(MenuClient::vertical());
        $routes = $menu->pluck('route')->filter()->values();

        $this->assertSame(['app.dashboard', 'app.couriers.index', 'app.scans.index', 'app.parcels.index'], $routes->all());
    }

    public function test_warehouse_menu_has_only_dashboard(): void
    {
        $user = $this->createUserWithRole('warehouse_clerk', 'warehouse@example.com');
        $this->be($user);

        $menu = collect(MenuClient::vertical());
        $routes = $menu->pluck('route')->filter()->values();

        $this->assertSame(['app.dashboard', 'app.scans.index', 'app.parcels.index'], $routes->all());
    }

    protected function createUserWithRole(string $role, string $email = 'client-admin@example.com'): User
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => $email,
        ]);
        $user->assignRole($role);

        return $user;
    }
}
