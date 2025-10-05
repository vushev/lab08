<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TemperatureAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_alert_when_temperature_is_below_zero(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->create();

        DB::table('device_user')->insert([
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'is_active' => 1,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$device->id}/measurements", [
                'temperature_c' => -5.5,
                'recorded_at'   => now()->toIso8601String(),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'type'      => 'temperature_threshold',
            'status'    => 'open',
        ]);

        $alert = Alert::where('device_id', $device->id)->first();
        $this->assertNotNull($alert);
        $this->assertStringContainsString('-5.5', $alert->message);
        $this->assertStringContainsString('threshold', $alert->message);
    }

    public function test_creates_alert_when_temperature_is_above_thirty(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->create();

        DB::table('device_user')->insert([
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'is_active' => 1,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$device->id}/measurements", [
                'temperature_c' => 35.8,
                'recorded_at'   => now()->toIso8601String(),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'type'      => 'temperature_threshold',
            'status'    => 'open',
        ]);

        $alert = Alert::where('device_id', $device->id)->first();
        $this->assertNotNull($alert);
        $this->assertStringContainsString('35.8', $alert->message);
    }

    public function test_does_not_create_alert_when_temperature_is_within_range(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->create();

        DB::table('device_user')->insert([
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'is_active' => 1,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$device->id}/measurements", [
                'temperature_c' => 22.5,
                'recorded_at'   => now()->toIso8601String(),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->id,
        ]);

        $alertsCount = Alert::where('device_id', $device->id)->count();
        $this->assertEquals(0, $alertsCount);
    }

    public function test_creates_alert_at_exact_boundaries(): void
    {
        $user = User::factory()->create();
        $device1 = Device::factory()->create();
        $device2 = Device::factory()->create();

        DB::table('device_user')->insert([
            ['device_id' => $device1->id, 'user_id' => $user->id, 'is_active' => 1],
            ['device_id' => $device2->id, 'user_id' => $user->id, 'is_active' => 1],
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response1 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$device1->id}/measurements", [
                'temperature_c' => 0.0,
                'recorded_at'   => now()->toIso8601String(),
            ]);
        $response1->assertStatus(201);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$device2->id}/measurements", [
                'temperature_c' => 30.0,
                'recorded_at'   => now()->toIso8601String(),
            ]);
        $response2->assertStatus(201);

        $this->assertEquals(0, Alert::where('device_id', $device1->id)->count());
        $this->assertEquals(0, Alert::where('device_id', $device2->id)->count());
    }
}
