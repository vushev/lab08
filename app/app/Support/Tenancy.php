<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class Tenancy
{
    public static function userOwnsDevice(int $userId, int $deviceId): bool
    {
        return DB::table('device_user')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->whereNull('detached_at')
            ->where('is_active', 1)
            ->exists();
    }
}
