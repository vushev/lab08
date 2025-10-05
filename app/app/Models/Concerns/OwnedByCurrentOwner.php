<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait OwnedByCurrentOwner
{
    public function scopeOwnedBy(Builder $query, User|int $user, string $deviceKey = 'device_id'): Builder
    {
        $userId = $user instanceof User ? (int) $user->getAuthIdentifier() : (int) $user;
        $table  = $query->getModel()->getTable();

        return $query
            ->join('device_user', 'device_user.device_id', '=', $table . '.' . $deviceKey)
            ->where('device_user.user_id', $userId)
            ->where('device_user.is_active', 1)
            ->select($table . '.*');
    }
}
