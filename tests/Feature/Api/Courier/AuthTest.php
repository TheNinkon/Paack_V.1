<?php

namespace Tests\Feature\Api\Courier;

use App\Models\Client;
use App\Models\CourierToken;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_courier_can_login_and_receive_token(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'courier@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('courier');
        $user->courier()->create([
            'client_id' => $client->id,
            'vehicle_type' => 'moto',
            'active' => true,
        ]);

        $response = $this->postJson('/api/rider/login', [
            'email' => 'courier@example.com',
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token', 'token_type', 'expires_at', 'user' => ['id', 'name']]);

        $this->assertDatabaseCount('courier_tokens', 1);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->postJson('/api/rider/login', [
            'email' => 'nobody@example.com',
            'password' => 'invalid',
        ]);

        $response->assertStatus(422);
    }

    public function test_logout_revokes_token(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'courier2@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('courier');
        $user->courier()->create([
            'client_id' => $client->id,
            'vehicle_type' => 'moto',
            'active' => true,
        ]);

        $login = $this->postJson('/api/rider/login', [
            'email' => 'courier2@example.com',
            'password' => 'password',
        ])->json();

        $token = $login['token'];

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/rider/logout')
            ->assertOk();

        $this->assertDatabaseCount('courier_tokens', 0);
    }

    public function test_me_endpoint_returns_profile(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'courier3@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('courier');
        $user->courier()->create([
            'client_id' => $client->id,
            'vehicle_type' => 'moto',
            'active' => true,
        ]);

        $token = CourierToken::create([
            'user_id' => $user->id,
            'name' => 'Test',
            'token' => hash('sha256', 'plain-token'),
            'expires_at' => now()->addDay(),
        ]);

        $this->withHeader('Authorization', 'Bearer plain-token')
            ->getJson('/api/rider/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'courier3@example.com');
    }
}
