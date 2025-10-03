<?php

namespace Tests\Feature\Admin\Clients;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_client_with_initial_admin(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'super@example.com',
        ]);
        $superAdmin->assignRole('super_admin');

        $payload = [
            'name' => 'Cliente Demo',
            'cif' => 'B00000001',
            'contact_name' => 'Laura Gómez',
            'contact_email' => 'laura@example.com',
            'contact_phone' => '+34 600 000 001',
            'active' => true,
            'create_admin' => true,
            'admin_name' => 'Administrador Demo',
            'admin_email' => 'admin@cliente-demo.test',
            'admin_phone' => '+34 600 000 100',
            'admin_password' => 'Secret123!',
            'admin_password_confirmation' => 'Secret123!',
        ];

        $response = $this
            ->actingAs($superAdmin)
            ->post(route('admin.clients.store'), $payload);

        $response->assertRedirect(route('admin.clients.index'));
        $response->assertSessionHas('status', 'client-created-with-admin');

        $this->assertDatabaseHas('clients', [
            'name' => 'Cliente Demo',
            'cif' => 'B00000001',
        ]);

        $createdAdmin = User::where('email', 'admin@cliente-demo.test')->first();
        $this->assertNotNull($createdAdmin);
        $this->assertNotNull($createdAdmin->client_id);
        $this->assertTrue(Hash::check('Secret123!', $createdAdmin->password));
        $this->assertTrue($createdAdmin->hasRole('client_admin'));
    }
}
