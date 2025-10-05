<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface MeasurementRepositoryInterface
{
    public function store(
        int $deviceId,
        \DateTimeInterface $recordedAt,
        float $temperatureC,
        ?array $payload = null,
    ): int;

    public function forUser(
        int $userId,
        ?int $deviceId = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
    ): Collection;
}
