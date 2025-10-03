<?php

namespace Tests\Feature\Api\Courier;

use App\Models\Client;
use App\Models\CourierToken;
use App\Models\Parcel;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParcelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_courier_can_list_parcels(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
        ]);
        $user->assignRole('courier');
        $user->courier()->create([
            'client_id' => $client->id,
            'vehicle_type' => 'moto',
            'active' => true,
        ]);

        Parcel::factory()->count(3)->create([
            'client_id' => $client->id,
        ]);

        CourierToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', 'plain-token'),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer plain-token')
            ->getJson('/api/rider/parcels');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_courier_can_update_parcel_status(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
        ]);
        $user->assignRole('courier');
        $user->courier()->create([
            'client_id' => $client->id,
            'vehicle_type' => 'moto',
            'active' => true,
        ]);

        $parcel = Parcel::factory()->create([
            'client_id' => $client->id,
            'status' => 'assigned',
        ]);

        CourierToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', 'plain-token'),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer plain-token')
            ->postJson("/api/rider/parcels/{$parcel->id}/events", [
                'status' => 'delivered',
                'comment' => 'Entregado correctamente',
            ]);

        $response->assertOk();
        $response->assertJsonPath('parcel.status', 'delivered');

        $this->assertDatabaseHas('parcels', [
            'id' => $parcel->id,
            'status' => 'delivered',
        ]);

        $this->assertDatabaseHas('parcel_events', [
            'parcel_id' => $parcel->id,
            'event_type' => 'parcel_status_updated',
        ]);
    }
}
