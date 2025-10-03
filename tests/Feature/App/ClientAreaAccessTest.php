<?php

namespace Tests\Feature\App;

use App\Models\Client;
use App\Models\User;
use App\Support\ClientContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAreaAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(ClientContext::class)->reset();
    }

    public function test_client_admin_redirected_to_app_dashboard_from_root(): void
    {
        $user = $this->createUserWithRole('client_admin');

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('app.dashboard'));
    }

    public function test_client_admin_redirected_from_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('client_admin');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('app.dashboard'));
    }

    public function test_client_admin_can_access_app_users_index(): void
    {
        $user = $this->createUserWithRole('client_admin');

        $this->actingAs($user)
            ->get(route('app.users.index'))
            ->assertOk();
    }

    public function test_zone_manager_can_access_zones_and_couriers(): void
    {
        $user = $this->createUserWithRole('zone_manager', 'zone-manager@example.com');

        $this->actingAs($user)
            ->get(route('app.zones.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('app.couriers.index'))
            ->assertOk();
    }

    public function test_support_user_can_view_couriers_but_not_users(): void
    {
        $user = $this->createUserWithRole('support', 'support@example.com');

        $this->actingAs($user)
            ->get(route('app.couriers.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('app.scans.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('app.users.index'))
            ->assertForbidden();
    }

    public function test_warehouse_clerk_cannot_access_couriers_module(): void
    {
        $user = $this->createUserWithRole('warehouse_clerk', 'warehouse@example.com');

        $this->actingAs($user)
            ->get(route('app.couriers.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('app.scans.index'))
            ->assertOk();
    }

    public function test_super_admin_keeps_admin_dashboard_entrypoint(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'super@example.com',
        ]);
        $superAdmin->assignRole('super_admin');

        $response = $this->actingAs($superAdmin)->get('/');

        $response->assertRedirect(route('dashboard'));
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
