<?php

namespace Tests\Feature\Admin\Zones;

use App\Models\Client;
use App\Models\Zone;
use App\Models\User;
use App\Support\ClientContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ActivityLog;
use Tests\TestCase;

class ZoneManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(ClientContext::class)->reset();
    }

    public function test_client_admin_sees_only_their_own_zones(): void
    {
        $clientA = Client::factory()->create(['name' => 'Client Alpha']);
        $clientB = Client::factory()->create(['name' => 'Client Beta']);

        $visibleZone = Zone::factory()->for($clientA)->create(['name' => 'Zona Norte']);
        Zone::factory()->for($clientB)->create(['name' => 'Zona Sur']);

        $clientAdmin = User::factory()->create([
            'client_id' => $clientA->id,
            'email' => 'admin@alpha.test',
        ]);
        $clientAdmin->assignRole('client_admin');

        $response = $this
            ->actingAs($clientAdmin)
            ->get(route('app.zones.index'));

        $response->assertOk();
        $response->assertSee($visibleZone->name);
        $response->assertDontSee('Zona Sur');
    }

    public function test_super_admin_creates_zone_for_selected_client(): void
    {
        $client = Client::factory()->create();
        $superAdmin = User::factory()->create([
            'email' => 'super@example.com',
        ]);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->withSession(['selected_client_id' => $client->id])
            ->post(route('admin.zones.store'), [
                'client_id' => $client->id,
                'name' => 'Zona Centro',
                'code' => 'CTR',
                'notes' => 'Cobertura central',
                'active' => true,
            ])
            ->assertRedirect(route('admin.zones.index'));

        $zoneId = Zone::where('name', 'Zona Centro')->value('id');

        $this->assertDatabaseHas('zones', [
            'id' => $zoneId,
            'name' => 'Zona Centro',
            'client_id' => $client->id,
            'created_by' => $superAdmin->id,
        ]);

        $activity = ActivityLog::where('subject_type', Zone::class)
            ->where('subject_id', $zoneId)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('zone', $activity->log_name);
        $this->assertSame($superAdmin->id, $activity->causer_id);
    }

    public function test_client_admin_is_forbidden_from_admin_zone_routes(): void
    {
        $client = Client::factory()->create();
        $clientAdmin = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'admin@example.com',
        ]);
        $clientAdmin->assignRole('client_admin');

        $this
            ->actingAs($clientAdmin)
            ->get(route('admin.zones.index'))
            ->assertForbidden();
    }
}
