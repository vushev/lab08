<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DevicePolicy
{
    private function owns(User $user, Device $device): bool
    {
        $deviceOwner = DB::table('device_user')
            ->where('device_id', $device->id)
            ->where('is_active', 1)
            ->first();

        if(! $deviceOwner || ($deviceOwner->user_id === $user->id)) {
            return true;
        }
        
        return false;
    }

    public function attach(User $user, Device $device): bool
    {
        return $this->owns($user, $device);
    }

    public function detach(User $user, Device $device): bool
    {
        return $this->owns($user, $device);
    }

    public function update(User $user, Device $device): bool
    {
        return $this->owns($user, $device);
    }

    public function delete(User $user, Device $device): bool
    {
        return $this->owns($user, $device);
    }
}
