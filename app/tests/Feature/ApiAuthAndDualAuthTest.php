<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiAuthAndDualAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_current_user_via_api_user_with_pat(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $resp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->get('/api/user');

        $resp->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email);
    }

    public function test_allows_access_with_valid_device_token(): void
    {
        $token = bin2hex(random_bytes(32));
        $deviceId = DB::table('devices')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'name'       => 'PingDev',
            'serial'     => strtoupper(Str::random(10)),
            'status'     => 'active',
            'api_token'  => $token,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resp = $this->withHeader('X-Device-Token', $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$deviceId}/ping");

        $resp->assertOk()->assertJsonPath('data.ok', true);
    }

    public function test_rejects_invalid_device_token(): void
    {
        $real = bin2hex(random_bytes(32));
        $deviceId = DB::table('devices')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'name'       => 'PingDev2',
            'serial'     => strtoupper(Str::random(10)),
            'status'     => 'active',
            'api_token'  => $real,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resp = $this->withHeader('X-Device-Token', 'wrong-token')
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$deviceId}/ping");

        $resp->assertStatus(401)->assertJsonPath('error.code', 'INVALID_DEVICE_TOKEN');
    }

    public function test_allows_pat_when_user_owns_device_and_denies_after_detach(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $deviceId = DB::table('devices')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'name'       => 'Ownable',
            'serial'     => strtoupper(Str::random(10)),
            'status'     => 'active',
            'api_token'  => bin2hex(random_bytes(32)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('device_user')->insert([
            'device_id'   => $deviceId,
            'user_id'     => $user->id,
            'is_active'   => 1
        ]);

        $ok = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$deviceId}/ping");

        $ok->assertOk()->assertJsonPath('data.ok', true);

        DB::table('device_user')
            ->where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->update(['is_active' => null]);

        $deny = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', 'application/json')
            ->postJson("/api/v1/devices/{$deviceId}/ping");

        $deny->assertStatus(401)->assertJsonPath('error.code', 'UNAUTHORIZED');
    }
}
