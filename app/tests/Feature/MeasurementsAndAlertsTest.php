<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Measurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MeasurementsAndAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_measurements(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->create();

        DB::table('device_user')->insert([
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'is_active' => 1,
        ]);

        $measurement1 = Measurement::create([
            'device_id'     => $device->id,
            'temperature_c' => 20.5,
            'recorded_at'   => now()->subHours(2),
            'created_at'    => now()->subHours(2),
        ]);

        $measurement2 = Measurement::create([
            'device_id'     => $device->id,
            'temperature_c' => 25.3,
            'recorded_at'   => now()->subHour(),
            'created_at'    => now()->subHour(),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->getJson('/api/v1/me/measurements');

        $response->assertOk()
            ->assertJsonPath('data.data.0.id', $measurement2->id)
            ->assertJsonPath('data.data.1.id', $measurement1->id)
            ->assertJsonPath('data.data.0.temperature_c', '25.30')
            ->assertJsonPath('data.data.1.temperature_c', '20.50');
    }

    public function test_user_cannot_see_measurements_from_other_users_devices(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $device1 = Device::factory()->create();
        $device2 = Device::factory()->create();

        DB::table('device_user')->insert([
            'device_id' => $device1->id,
            'user_id'   => $user1->id,
            'is_active' => 1,
        ]);

        DB::table('device_user')->insert([
            'device_id' => $device2->id,
            'user_id'   => $user2->id,
            'is_active' => 1,
        ]);

        Measurement::create([
            'device_id'     => $device1->id,
            'temperature_c' => 20.0,
            'recorded_at'   => now(),
            'created_at'    => now(),
        ]);

        Measurement::create([
            'device_id'     => $device2->id,
            'temperature_c' => 25.0,
            'recorded_at'   => now(),
            'created_at'    => now(),
        ]);

        $token1 = $user1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->withHeader('Accept', 'application/json')
            ->getJson('/api/v1/me/measurements');

        $response->assertOk();
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals($device1->id, $data[0]['device_id']);
    }

    public function test_user_can_filter_measurements_by_device(): void
    {
        $user = User::factory()->create();
        $device1 = Device::factory()->create();
        $device2 = Device::factory()->create();

        DB::table('device_user')->insert([
            ['device_id' => $device1->id, 'user_id' => $user->id, 'is_active' => 1],
            ['device_id' => $device2->id, 'user_id' => $user->id, 'is_active' => 1],
        ]);

        Measurement::create([
            'device_id'     => $device1->id,
            'temperature_c' => 20.0,
            'recorded_at'   => now(),
            'created_at'    => now(),
        ]);

        Measurement::create([
            'device_id'     => $device2->id,
            'temperature_c' => 25.0,
            'recorded_at'   => now(),
            'created_at'    => now(),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->getJson("/api/v1/me/measurements?device_id={$device1->id}");

        $response->assertOk();
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals($device1->id, $data[0]['device_id']);
    }

    public function test_user_can_get_their_alerts(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->create();

        DB::table('device_user')->insert([
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'is_active' => 1,
        ]);

        $measurement = Measurement::create([
            'device_id'     => $device->id,
            'temperature_c' => -10.0,
            'recorded_at'   => now(),
            'created_at'    => now(),
        ]);

        $alert = Alert::create([
            'device_id'      => $device->id,
            'user_id'        => $user->id,
            'measurement_id' => $measurement->id,
            'type'           => 'temperature_threshold',
            'message'        => 'Temperature -10.0°C out of threshold',
            'status'         => 'open',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->getJson('/api/v1/me/alerts');

        $response->assertOk()
            ->assertJsonPath('data.data.0.id', $alert->id)
            ->assertJsonPath('data.data.0.type', 'temperature_threshold')
            ->assertJsonPath('data.data.0.status', 'open')
            ->assertJsonPath('data.data.0.device_id', $device->id);
    }

    public function test_user_can_filter_alerts_by_status(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->create();

        DB::table('device_user')->insert([
            'device_id' => $device->id,
            'user_id'   => $user->id,
            'is_active' => 1,
        ]);

        $measurement = Measurement::create([
            'device_id'     => $device->id,
            'temperature_c' => -10.0,
            'recorded_at'   => now(),
            'created_at'    => now(),
        ]);

        Alert::create([
            'device_id'      => $device->id,
            'user_id'        => $user->id,
            'measurement_id' => $measurement->id,
            'type'           => 'temperature_threshold',
            'message'        => 'Alert 1',
            'status'         => 'open',
        ]);

        Alert::create([
            'device_id'      => $device->id,
            'user_id'        => $user->id,
            'measurement_id' => $measurement->id,
            'type'           => 'temperature_threshold',
            'message'        => 'Alert 2',
            'status'         => 'closed',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->getJson('/api/v1/me/alerts?status=open');

        $response->assertOk();
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals('open', $data[0]['status']);
    }
}
