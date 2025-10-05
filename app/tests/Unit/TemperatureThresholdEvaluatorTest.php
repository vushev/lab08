<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Alerting\TemperatureThresholdEvaluator;
use App\Models\Device;
use App\Models\Measurement;
use Tests\TestCase;

class TemperatureThresholdEvaluatorTest extends TestCase
{
    public function test_detects_temperature_below_threshold(): void
    {
        $evaluator = new TemperatureThresholdEvaluator(min: 0.0, max: 30.0);
        $device = new Device();
        $measurement = new Measurement(['temperature_c' => -5.0]);
        
        $alerts = $evaluator->evaluate($device, $measurement);
        
        $this->assertCount(1, $alerts);
        $this->assertEquals('temperature_threshold', $alerts[0]['type']);
    }

    public function test_detects_temperature_above_threshold(): void
    {
        $evaluator = new TemperatureThresholdEvaluator(min: 0.0, max: 30.0);
        $device = new Device();
        $measurement = new Measurement(['temperature_c' => 35.0]);
        
        $alerts = $evaluator->evaluate($device, $measurement);

        $this->assertCount(1, $alerts);
        $this->assertEquals('temperature_threshold', $alerts[0]['type']);
    }

    public function test_does_not_detect_temperature_within_threshold(): void
    {
        $evaluator = new TemperatureThresholdEvaluator(min: 0.0, max: 30.0);
        $device = new Device();
        $measurement = new Measurement(['temperature_c' => 25.0]);
        
        $alerts = $evaluator->evaluate($device, $measurement);

        $this->assertCount(0, $alerts);
    }

    public function test_detects_temperature_at_threshold(): void
    {
        $evaluator = new TemperatureThresholdEvaluator(min: 0.0, max: 30.0);
        $device = new Device();
        $measurement = new Measurement(['temperature_c' => 0.0]);
        
        $alerts = $evaluator->evaluate($device, $measurement);

        $this->assertCount(1, $alerts);
        $this->assertEquals('temperature_threshold', $alerts[0]['type']);
    }
}
