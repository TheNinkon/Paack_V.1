<?php

namespace Tests\Feature\App\Parcels;

use App\Models\Client;
use App\Models\Parcel;
use App\Models\Provider;
use App\Models\ProviderBarcode;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParcelManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_with_create_permission_can_register_parcel(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'warehouse@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $this->actingAs($user)
            ->post(route('app.parcels.store'), [
                'codes' => "MANUAL-0001\nMANUAL-0002",
            ])
            ->assertRedirect(route('app.parcels.index'))
            ->assertSessionHas('status', 'parcel-created')
            ->assertSessionHas('parcel-created-codes', ['MANUAL-0001', 'MANUAL-0002'])
            ->assertSessionHas('parcel-skipped-codes', []);

        $this->assertDatabaseHas('parcels', [
            'code' => 'MANUAL-0001',
            'client_id' => $client->id,
        ]);

        $this->assertDatabaseHas('parcels', [
            'code' => 'MANUAL-0002',
            'client_id' => $client->id,
        ]);
    }

    public function test_user_without_create_permission_cannot_register_parcel(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'support@example.com',
        ]);
        $user->assignRole('support');

        $this->actingAs($user)
            ->post(route('app.parcels.store'), [
                'codes' => "MANUAL-0003",
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('parcels', [
            'code' => 'MANUAL-0003',
        ]);
    }

    public function test_check_endpoint_reports_existing_code(): void
    {
        $client = Client::factory()->create();
        Parcel::factory()->create([
            'client_id' => $client->id,
            'code' => 'PKG-EXISTING',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'checker@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $this->actingAs($user)
            ->postJson(route('app.parcels.check'), ['code' => 'PKG-EXISTING'])
            ->assertOk()
            ->assertJson([ 'code' => 'PKG-EXISTING', 'status' => 'duplicate', 'exists' => true ]);

        $this->actingAs($user)
            ->postJson(route('app.parcels.check'), ['code' => 'PKG-NEW'])
            ->assertOk()
            ->assertJson([ 'code' => 'PKG-NEW', 'status' => 'pending', 'exists' => false ]);
    }

    public function test_kill_endpoint_marks_parcel_returned(): void
    {
        $client = Client::factory()->create();
        $parcel = Parcel::factory()->create([
            'client_id' => $client->id,
            'status' => 'pending',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'killer@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $this->actingAs($user)
            ->patchJson(route('app.parcels.kill', $parcel))
            ->assertOk()
            ->assertJsonFragment(['status' => 'ok']);

        $this->assertDatabaseHas('parcels', [
            'id' => $parcel->id,
            'status' => 'returned',
        ]);
    }

    public function test_user_with_permission_can_update_parcel_details(): void
    {
        $client = Client::factory()->create();
        $provider = Provider::factory()->create([
            'client_id' => $client->id,
        ]);
        $barcode = ProviderBarcode::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $parcel = Parcel::factory()->create([
            'client_id' => $client->id,
            'provider_id' => $provider->id,
            'provider_barcode_id' => $barcode->id,
            'address_line' => 'Antigua dirección',
            'city' => 'Ciudad vieja',
            'status' => 'pending',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'editor@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $response = $this->actingAs($user)
            ->patch(route('app.parcels.update', $parcel), [
                'provider_id' => $provider->id,
                'provider_barcode_id' => $barcode->id,
                'stop_code' => 'STOP-123',
                'address_line' => 'Dirección nueva',
                'city' => 'Girona',
                'state' => 'Catalunya',
                'postal_code' => '17001',
                'liquidation_code' => 'LQ-001',
                'liquidation_reference' => 'REF-001',
            ]);

        $response
            ->assertRedirect(route('app.parcels.edit', $parcel))
            ->assertSessionHas('status', 'parcel-updated');

        $this->assertDatabaseHas('parcels', [
            'id' => $parcel->id,
            'stop_code' => 'STOP-123',
            'address_line' => 'Dirección nueva',
            'city' => 'Girona',
            'state' => 'Catalunya',
            'postal_code' => '17001',
            'liquidation_code' => 'LQ-001',
            'liquidation_reference' => 'REF-001',
        ]);

        $this->assertDatabaseHas('parcel_events', [
            'parcel_id' => $parcel->id,
            'event_type' => 'parcel_manual_updated',
        ]);
    }

    public function test_user_without_update_permission_cannot_edit_parcel(): void
    {
        $client = Client::factory()->create();
        $parcel = Parcel::factory()->create([
            'client_id' => $client->id,
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'courier@example.com',
        ]);
        $user->assignRole('courier');

        $this->actingAs($user)
            ->get(route('app.parcels.edit', $parcel))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('app.parcels.update', $parcel), [
                'address_line' => 'Cambios no permitidos',
            ])
            ->assertForbidden();
    }
}
