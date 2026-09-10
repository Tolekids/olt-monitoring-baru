<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_device_with_encrypted_credentials(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/devices', [
            'name' => 'OLT Lab 01',
            'vendor' => 'zte',
            'model' => 'C320',
            'device_type' => 'olt',
            'management_ip' => '192.0.2.10',
            'credentials' => [
                'protocol' => 'snmp_v2c',
                'port' => 161,
                'community' => 'private-community',
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('devices', ['management_ip' => '192.0.2.10']);
        $this->assertDatabaseMissing('device_credentials', ['encrypted_community' => 'private-community']);
    }

    public function test_user_without_device_management_permission_cannot_create_device(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Teknisi Field');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/devices', [
            'name' => 'OLT Lab 02',
            'vendor' => 'zte',
            'model' => 'C320',
            'device_type' => 'olt',
            'management_ip' => '192.0.2.11',
        ]);

        $response->assertForbidden();
    }
}
