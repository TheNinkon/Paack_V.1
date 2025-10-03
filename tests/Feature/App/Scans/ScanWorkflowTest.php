<?php

namespace Tests\Feature\App\Scans;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Parcel;
use App\Models\ParcelEvent;
use App\Models\Provider;
use App\Models\ProviderBarcode;
use App\Models\Scan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_warehouse_clerk_can_record_matching_scan(): void
    {
        $client = Client::factory()->create();
        $provider = Provider::factory()->for($client)->create();
        $barcode = ProviderBarcode::factory()->for($provider)->create([
            'pattern_regex' => '^123\d{7}$',
            'label' => 'Test pattern',
            'priority' => 1,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'warehouse@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $this->actingAs($user)
            ->post(route('app.scans.store'), ['code' => '1234567890'])
            ->assertRedirect(route('app.scans.index'));

        $this->assertDatabaseHas('parcels', [
            'code' => '1234567890',
            'client_id' => $client->id,
            'provider_id' => $provider->id,
        ]);

        $this->assertDatabaseHas('scans', [
            'code' => '1234567890',
            'provider_id' => $provider->id,
            'provider_barcode_id' => $barcode->id,
            'is_valid' => true,
            'created_by' => $user->id,
        ]);

        $scan = Scan::where('code', '1234567890')->first();

        $this->assertNotNull($scan);
        $this->assertNotNull($scan->parcel_id);
        $this->assertTrue(
            Parcel::where('id', $scan->parcel_id)
                ->where('code', '1234567890')
                ->exists()
        );

        $this->assertDatabaseHas('parcel_events', [
            'parcel_id' => $scan->parcel_id,
            'event_type' => 'parcel_created',
        ]);

        $this->assertDatabaseHas('parcel_events', [
            'parcel_id' => $scan->parcel_id,
            'event_type' => 'scan_matched',
        ]);

        $activity = ActivityLog::where('subject_type', Scan::class)
            ->where('subject_id', $scan->id)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('scan', $activity->log_name);
        $this->assertSame($user->id, $activity->causer_id);
    }

    public function test_scan_without_match_is_registered_as_invalid(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'warehouse2@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $this->actingAs($user)
            ->post(route('app.scans.store'), ['code' => 'NO-MATCH'])
            ->assertRedirect(route('app.scans.index'));

        $this->assertDatabaseHas('parcels', [
            'code' => 'NO-MATCH',
            'client_id' => $client->id,
            'provider_id' => null,
        ]);

        $this->assertDatabaseHas('scans', [
            'code' => 'NO-MATCH',
            'is_valid' => false,
            'provider_id' => null,
            'created_by' => $user->id,
        ]);

        $parcel = Parcel::where('code', 'NO-MATCH')->first();

        $this->assertNotNull($parcel);

        $this->assertDatabaseHas('parcel_events', [
            'parcel_id' => $parcel->id,
            'event_type' => 'scan_unmatched',
        ]);
    }

    public function test_support_cannot_create_scan(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'support@example.com',
        ]);
        $user->assignRole('support');

        $this->actingAs($user)
            ->post(route('app.scans.store'), ['code' => 'TEST'])
            ->assertForbidden();
    }

    public function test_async_scan_returns_json_feedback(): void
    {
        $client = Client::factory()->create();
        $provider = Provider::factory()->for($client)->create();
        $barcode = ProviderBarcode::factory()->for($provider)->create([
            'pattern_regex' => '^SCAN\d{4}$',
            'label' => 'Async pattern',
            'priority' => 1,
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'async@example.com',
        ]);
        $user->assignRole('warehouse_clerk');

        $response = $this->actingAs($user)
            ->postJson(route('app.scans.store'), ['code' => 'SCAN1234']);

        $response->assertOk()
            ->assertJsonStructure([
                'feedback' => ['status', 'code', 'scan_id'],
                'scan' => ['id', 'code', 'provider', 'provider_barcode'],
                'parcel' => ['id', 'code', 'scans_count'],
                'alerts',
            ]);

        $this->assertDatabaseHas('scans', [
            'code' => 'SCAN1234',
            'provider_id' => $provider->id,
            'provider_barcode_id' => $barcode->id,
        ]);
    }
}
