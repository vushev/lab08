<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DeviceRepository
{
    /** @return array{0:Device,1:string} */
    public function create(string $name, string $serial): array
    {
        $device = new Device();
        $device->uuid = (string) Str::uuid();
        $device->name = $name;
        $device->serial = $serial;
        $device->status = 'active';
        $plainToken = bin2hex(random_bytes(32));
        $device->api_token = $plainToken;
        $device->save();

        return [$device, $plainToken];
    }

    public function delete(Device $device): void
    {
        $device->delete();
    }

    public function currentOwnerId(int $deviceId): ?int
    {
        return DB::table('device_user')
            ->where('device_id', $deviceId)
            ->where('is_active', 1)
            ->value('user_id');
    }

    public function attach(Device $device, User $user): bool
    {
        DB::transaction(function () use ($device, $user) {
            DB::table('device_user')
                ->where('device_id', $device->id)
                ->where('is_active', 1)
                ->update(['is_active' => null]);


            DB::table('device_user')->updateOrInsert(
                [
                    'device_id' => $device->id,
                    'user_id' => $user->id,
                    'is_active' => null,
                ],
                [
                    'is_active' => 1,
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                ]
            );

            return true;
        });

        //TODO log the attachment

        return false;
    }

    public function detach(Device $device, User $user): bool
    {
        $d = DB::table('device_user')
            ->where('device_id', $device->id)
            ->where('user_id', $user->id)
            ->where('is_active', 1)
            ->update(['is_active' => null]);

        return $d > 0;
    }
}
