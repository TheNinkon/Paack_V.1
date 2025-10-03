<?php

namespace Tests\Feature\Admin\Couriers;

use App\Models\Client;
use App\Models\Courier;
use App\Models\User;
use App\Models\Zone;
use App\Support\ClientContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ActivityLog;
use Tests\TestCase;

class CourierManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(ClientContext::class)->reset();
    }

    public function test_client_admin_sees_only_their_own_couriers(): void
    {
        $clientA = Client::factory()->create(['name' => 'Client Alpha']);
        $clientB = Client::factory()->create(['name' => 'Client Beta']);

        $userA = User::factory()->create([
            'client_id' => $clientA->id,
            'email' => 'courier-a@example.com',
        ]);
        $userB = User::factory()->create([
            'client_id' => $clientB->id,
            'email' => 'courier-b@example.com',
        ]);

        $courierA = Courier::factory()->forClient($clientA)->forUser($userA)->create();
        Courier::factory()->forClient($clientB)->forUser($userB)->create();

        $clientAdmin = User::factory()->create([
            'client_id' => $clientA->id,
            'email' => 'admin@alpha.test',
        ]);
        $clientAdmin->assignRole('client_admin');

        $response = $this
            ->actingAs($clientAdmin)
            ->get(route('app.couriers.index'));

        $response->assertOk();
        $response->assertSee($courierA->user->email);
        $response->assertDontSee($userB->email);
    }

    public function test_super_admin_creates_courier_and_assigns_role(): void
    {
        $client = Client::factory()->create();
        $candidate = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'candidate@example.com',
        ]);
        $zone = Zone::factory()->for($client)->create(['name' => 'Zona Test']);

        $superAdmin = User::factory()->create([
            'email' => 'super@example.com',
        ]);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->withSession(['selected_client_id' => $client->id])
            ->post(route('admin.couriers.store'), [
                'client_id' => $client->id,
                'user_id' => $candidate->id,
                'vehicle_type' => 'van',
                'external_code' => 'VAN-001',
                'active' => true,
                'zone_id' => $zone->id,
            ])
            ->assertRedirect(route('admin.couriers.index'));

        $this->assertDatabaseHas('couriers', [
            'client_id' => $client->id,
            'user_id' => $candidate->id,
            'vehicle_type' => 'van',
            'created_by' => $superAdmin->id,
            'zone_id' => $zone->id,
        ]);

        $this->assertTrue($candidate->fresh()->hasRole('courier'));

        $courierId = Courier::where('user_id', $candidate->id)->value('id');

        $activity = ActivityLog::where('subject_type', Courier::class)
            ->where('subject_id', $courierId)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('courier', $activity->log_name);
        $this->assertSame($superAdmin->id, $activity->causer_id);
    }

    public function test_client_admin_is_forbidden_from_admin_courier_routes(): void
    {
        $client = Client::factory()->create();
        $clientAdmin = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'client-admin@example.com',
        ]);
        $clientAdmin->assignRole('client_admin');

        $this
            ->actingAs($clientAdmin)
            ->get(route('admin.couriers.index'))
            ->assertForbidden();
    }
}
