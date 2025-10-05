<?php

declare(strict_types=1);

namespace App\Alerting;

use App\Models\Device;
use App\Models\Measurement;

final class TemperatureThresholdEvaluator implements AlertEvaluatorInterface
{
    public function __construct(
        private readonly float $min = 0.0,
        private readonly float $max = 30.0,
    ) {}

    public function evaluate(Device $device, Measurement $measurement): array
    {
        $t = (float) $measurement->temperature_c;

        if ($t < $this->min || $t > $this->max) {
            return [[
                'type' => 'temperature_threshold',
                'message' => sprintf('Temperature %.2f°C out of range (%.2f..%.2f)', $t, $this->min, $this->max),
            ]];
        }

        return [];
    }
}
