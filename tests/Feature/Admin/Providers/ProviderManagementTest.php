<?php

namespace Tests\Feature\Admin\Providers;

use App\Models\Client;
use App\Models\Provider;
use App\Models\User;
use App\Support\ClientContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ActivityLog;
use Tests\TestCase;

class ProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(ClientContext::class)->reset();
    }

    public function test_client_admin_sees_only_their_own_providers(): void
    {
        $clientA = Client::factory()->create(['name' => 'Client A']);
        $clientB = Client::factory()->create(['name' => 'Client B']);

        $providerA = Provider::factory()->for($clientA)->create(['name' => 'Provider A']);
        Provider::factory()->for($clientB)->create(['name' => 'Provider B']);

        $clientAdmin = User::factory()->create([
            'client_id' => $clientA->id,
            'email' => 'admin@client-a.test',
        ]);
        $clientAdmin->assignRole('client_admin');

        app(ClientContext::class)->reset();

        $response = $this
            ->actingAs($clientAdmin)
            ->withSession(['selected_client_id' => $clientA->id])
            ->get(route('app.providers.index'));

        $response->assertOk();
        $response->assertSee($providerA->name);

        $providersInView = $response->viewData('providers');
        $this->assertNotNull($providersInView);
        $this->assertSame(
            ['Provider A'],
            $providersInView->getCollection()->pluck('name')->all()
        );
    }

    public function test_super_admin_creates_provider_for_selected_client(): void
    {
        $client = Client::factory()->create();
        $superAdmin = User::factory()->create([
            'email' => 'super@example.com',
        ]);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->withSession(['selected_client_id' => $client->id])
            ->post(route('admin.providers.store'), [
                'client_id' => $client->id,
                'name' => 'Nuevo Proveedor',
                'slug' => 'nuevo-proveedor',
                'notes' => 'Notas internas',
                'active' => true,
            ])->assertRedirect(route('admin.providers.index'));

        $providerId = Provider::where('name', 'Nuevo Proveedor')->value('id');

        $this->assertDatabaseHas('providers', [
            'id' => $providerId,
            'name' => 'Nuevo Proveedor',
            'client_id' => $client->id,
            'created_by' => $superAdmin->id,
        ]);

        $activity = ActivityLog::where('subject_type', Provider::class)
            ->where('subject_id', $providerId)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('provider', $activity->log_name);
        $this->assertSame($superAdmin->id, $activity->causer_id);
    }

    public function test_client_admin_is_forbidden_from_admin_provider_routes(): void
    {
        $client = Client::factory()->create();
        $clientAdmin = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'client-admin@example.com',
        ]);
        $clientAdmin->assignRole('client_admin');

        $this
            ->actingAs($clientAdmin)
            ->get(route('admin.providers.index'))
            ->assertForbidden();
    }
}
