<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Device;
use Tests\TestCase;

class AttachUserToDeviceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attaches_the_user_to_the_device(): void
    {
        $user = User::factory()->create(['email' => 'first@example.com']);
        $device = Device::factory()->create();

        $this->assertGreaterThan(0, $user->id);
        $this->assertGreaterThan(0, $device->id);

        $existingRelation = DB::table('device_user')
            ->where('device_id', $device->id)
            ->where('user_id', $user->id)
            ->first();
        
        $this->assertNull($existingRelation);

        DB::table('device_user')->insert([
            'device_id'   => $device->id,
            'user_id'     => $user->id,
            'is_active'   => 1,
        ]);

        $row = DB::table('device_user')
            ->where('device_id', $device->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($row);
        $this->assertEquals(1, $row->is_active);
        $this->assertEquals($device->id, $row->device_id);
        $this->assertEquals($user->id, $row->user_id);
    }

    public function test_attach_device_to_another_user(): void
    {

        $user = User::factory()->create(['email' => 'first@example.com']);
        $user2 = User::factory()->create(['email' => 'second@example.com']);

        $device = Device::factory()->create();

        $this->assertGreaterThan(0, $user->id);
        $this->assertGreaterThan(0, $device->id);

        $existingRelation = DB::table('device_user')
            ->where('device_id', $device->id)
            ->first();
            
        $this->assertNull($existingRelation);

        DB::table('device_user')->insert([
            'device_id'   => $device->id,
            'user_id'     => $user2->id,
            'is_active'   => 1,
        ]);

        $row = DB::table('device_user')
            ->where('device_id', $device->id)
            ->where('user_id', $user2->id)
            ->first();
            
        $this->assertNotNull($row);
        $this->assertEquals(1, $row->is_active);
        $this->assertEquals($device->id, $row->device_id);
        $this->assertEquals($user2->id, $row->user_id);
    }

    public function test_attach_device_to_another_user_when_already_attached_to_another_user(): void
    {

        $user = User::factory()->create(['email' => 'first@example.com']);


        $user2 = User::factory()->create(['email' => 'second@example.com']);

        $device = Device::factory()->create();

        $this->assertGreaterThan(0, $user->id);
        $this->assertGreaterThan(0, $user2->id);
        $this->assertGreaterThan(0, $device->id);

        $existingRelation = DB::table('device_user')
            ->where('device_id', $device->id)
            ->where('user_id', $user2->id)
            ->first();
            
        $this->assertNull($existingRelation);

        DB::table('device_user')->insert([
            'device_id'   => $device->id,
            'user_id'     => $user->id,
            'is_active'   => 1,
        ]);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        DB::table('device_user')->insert([
            'device_id'   => $device->id,
            'user_id'     => $user2->id,
            'is_active'   => 1,
        ]);
    }
}
