<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AlertRepositoryInterface;
use App\Repositories\MeasurementRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final class MeasurementService
{
    public function __construct(
        private readonly MeasurementRepositoryInterface $measurements,
        private readonly AlertRepositoryInterface $alerts,
    ) {}

    public function create(
        int $deviceId,
        float $temperatureC,
        ?\DateTimeInterface $recordedAt = null,
        ?array $payload = null,
    ): int {
        $recordedAt ??= now();

        $measurementId = $this->measurements->store(
            deviceId: $deviceId,
            recordedAt: $recordedAt,
            temperatureC: $temperatureC,
            payload: $payload
        );

        $ownerId = $this->resolveActiveOwner($deviceId);

        if ($temperatureC < 0.0 || $temperatureC > 30.0) {
            $msg = sprintf(
                'Temperature %.2f°C out of threshold [0.00..30.00]',
                $temperatureC
            );

            $this->alerts->create(
                [
                    "device_id" => $deviceId,
                    "user_id" => $ownerId,
                    "measurement_id" => $measurementId,
                    "type" => 'temperature_threshold',
                    "message" => $msg,
                ]
            );
        }

        return $measurementId;
    }

    public function listForUser(
        int $userId,
        ?int $deviceId = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
    ) {
        return $this->measurements->forUser($userId, $deviceId, $from, $to);
    }

    private function resolveActiveOwner(int $deviceId): ?int
    {
        $row = DB::table('device_user')
            ->where('device_id', $deviceId)
            ->where('is_active', 1)
            ->select('user_id')
            ->first();

        return $row ? (int) $row->user_id : null;
    }
}
