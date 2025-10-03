<?php

namespace Tests\Feature\App\Parcels;

use App\Models\Client;
use App\Models\Parcel;
use App\Models\ParcelEvent;
use App\Models\Scan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParcelHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_support_user_can_view_parcel_history(): void
    {
        $client = Client::factory()->create();
        $parcel = Parcel::factory()->create([
            'client_id' => $client->id,
            'code' => 'PKG-1234',
        ]);

        $scan = Scan::factory()->create([
            'client_id' => $client->id,
            'parcel_id' => $parcel->id,
            'code' => $parcel->code,
        ]);

        ParcelEvent::factory()->create([
            'parcel_id' => $parcel->id,
            'scan_id' => $scan->id,
            'code' => $parcel->code,
            'event_type' => 'scan_matched',
            'description' => 'Test event',
        ]);

        $supportUser = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'support@example.com',
        ]);
        $supportUser->assignRole('support');

        $this->actingAs($supportUser)
            ->get(route('app.parcels.show', ['code' => $parcel->code]))
            ->assertOk()
            ->assertSee('Test event');
    }

    public function test_parcel_summary_endpoint_returns_json_for_authorized_user(): void
    {
        $client = Client::factory()->create();
        $parcel = Parcel::factory()->create([
            'client_id' => $client->id,
            'code' => 'PKG-9999',
        ]);

        $scan = Scan::factory()->create([
            'client_id' => $client->id,
            'parcel_id' => $parcel->id,
            'code' => $parcel->code,
        ]);

        ParcelEvent::factory()->create([
            'parcel_id' => $parcel->id,
            'scan_id' => $scan->id,
            'code' => $parcel->code,
            'event_type' => 'scan_matched',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'warehouse@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $this->actingAs($user)
            ->getJson(route('app.parcels.summary', ['code' => $parcel->code]))
            ->assertOk()
            ->assertJsonStructure(['html']);
    }
}
