<?php

namespace Tests\Feature\Admin\Activity;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_view_activity_log(): void
    {
        $superAdmin = User::factory()->create(['email' => 'super@example.com']);
        $superAdmin->assignRole('super_admin');

        ActivityLog::factory()->create([
            'log_name' => 'client',
            'description' => 'Client created',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.activity.index'))
            ->assertOk()
            ->assertSee('Client created');
    }

    public function test_non_super_admin_cannot_view_activity_log(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'client_id' => $client->id,
            'email' => 'client-admin@example.com',
        ]);
        $user->assignRole('client_admin');

        $this->actingAs($user)
            ->get(route('admin.activity.index'))
            ->assertForbidden();
    }
}
