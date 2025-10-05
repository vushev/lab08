<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use App\Repositories\DeviceRepository;

final class DeviceService
{
    public function __construct(private readonly DeviceRepository $devices) {}

    /** @return array{device:Device, api_token:string} */
    public function create(User $owner, string $name, string $serial): array
    {
        [$device, $token] = $this->devices->create($name, $serial);
        $this->devices->attach($device, $owner, $owner->id);

        return ['device' => $device, 'api_token' => $token];
    }

    public function delete(Device $device): void
    {
        $this->devices->delete($device);
    }

    public function attach(Device $device, User $user): bool
    {
        return $this->devices->attach($device, $user);
    }

    public function detach(Device $device, User $user): bool
    {
        $d = $this->devices->detach($device, $user);
        if ($d) {
            return true;
        }
        return false;
    }

    public function currentOwnerId(Device $device): ?int
    {
        return $this->devices->currentOwnerId($device->id);
    }
}
