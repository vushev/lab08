<?php

declare(strict_types=1);

namespace App\Alerting;

use App\Models\Device;
use App\Models\Measurement;

interface AlertEvaluatorInterface
{
    /**
     * @return array<int, array{type:string, message:string}>
     */
    public function evaluate(Device $device, Measurement $measurement): array;
}
