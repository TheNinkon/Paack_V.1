<?php

namespace Tests\Feature\Admin\Users;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_client_admin_only_sees_users_within_their_client(): void
    {
        $clientA = Client::factory()->create(['name' => 'Client Alpha']);
        $clientB = Client::factory()->create(['name' => 'Client Beta']);

        $visibleUser = User::factory()->create([
            'client_id' => $clientA->id,
            'email' => 'visible@example.com',
        ]);
        $hiddenUser = User::factory()->create([
            'client_id' => $clientB->id,
            'email' => 'hidden@example.com',
        ]);

        $clientAdmin = User::factory()->create([
            'client_id' => $clientA->id,
            'email' => 'admin@alpha.test',
        ]);
        $clientAdmin->assignRole('client_admin');

        $response = $this
            ->actingAs($clientAdmin)
            ->get(route('app.users.index'));

        $response->assertOk();
        $response->assertSee($visibleUser->email);
        $response->assertDontSee($hiddenUser->email);
    }

    public function test_super_admin_creates_user_with_custom_password(): void
    {
        $client = Client::factory()->create();
        $superAdmin = User::factory()->create([
            'email' => 'super@example.com',
        ]);
        $superAdmin->assignRole('super_admin');

        $payload = [
            'client_id' => $client->id,
            'name' => 'Laura Campos',
            'email' => 'laura@example.com',
            'phone' => '+34 600 000 999',
            'roles' => ['client_admin'],
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'is_active' => true,
        ];

        $this->actingAs($superAdmin)
            ->post(route('admin.users.store'), $payload)
            ->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'laura@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertSame($client->id, $createdUser->client_id);
        $this->assertTrue(Hash::check('Secret123!', $createdUser->password));
        $this->assertTrue($createdUser->hasRole('client_admin'));
    }

    public function test_client_admin_is_forbidden_from_admin_user_routes(): void
    {
        $client = Client::factory()->create();
        $clientAdmin = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'admin@example.com',
        ]);
        $clientAdmin->assignRole('client_admin');

        $this
            ->actingAs($clientAdmin)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
