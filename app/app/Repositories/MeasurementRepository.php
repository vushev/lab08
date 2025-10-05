<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class MeasurementRepository
{
    public function store(
        int $deviceId,
        \DateTimeInterface $recordedAt,
        float $temperatureC,
        ?array $payload = null,
    ): int {
        return (int) DB::table('measurements')->insertGetId([
            'device_id'     => $deviceId,
            'recorded_at'   => $recordedAt,
            'temperature_c' => $temperatureC,
            'payload'       => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'created_at'    => now(),
        ]);
    }

    public function forUser(
        int $userId,
        ?int $deviceId = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
    ): Collection {
        $q = DB::table('measurements')
            ->join('device_user', 'device_user.device_id', '=', 'measurements.device_id')
            ->where('device_user.user_id', $userId)
            ->where('device_user.is_active', 1)
            ->select('measurements.*');

        if ($deviceId !== null) {
            $q->where('measurements.device_id', $deviceId);
        }
        if ($from) {
            $q->where('measurements.recorded_at', '>=', $from);
        }
        if ($to) {
            $q->where('measurements.recorded_at', '<=', $to);
        }

        return $q->orderBy('measurements.recorded_at', 'desc')->get();
    }
}
