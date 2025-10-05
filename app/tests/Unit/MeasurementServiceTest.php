<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\AlertRepositoryInterface;
use App\Repositories\MeasurementRepositoryInterface;
use App\Services\MeasurementService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class MeasurementServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_alert_when_temperature_is_below_zero(): void
    {
        $measurementRepo = Mockery::mock(MeasurementRepositoryInterface::class);
        $alertRepo = Mockery::mock(AlertRepositoryInterface::class);

        DB::shouldReceive('table')
            ->with('device_user')
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('device_id', 1)
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('is_active', 1)
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('select')
            ->with('user_id')
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->once()
            ->andReturn((object)['user_id' => 100]);

        $measurementRepo->shouldReceive('store')
            ->once()
            ->withArgs(function ($deviceId, $recordedAt, $temperatureC, $payload) {
                return $deviceId === 1
                    && $recordedAt instanceof \DateTimeInterface
                    && $temperatureC === -5.0
                    && $payload === NULL;
            })
            ->andReturn(999);

        $alertRepo->shouldReceive('create')
            ->once()
            ->withArgs(function ($data) {
                return $data['device_id'] === 1
                    && $data['user_id'] === 100
                    && $data['measurement_id'] === 999
                    && $data['type'] === 'temperature_threshold'
                    && str_contains($data['message'], '-5.00');
            })
            ->andReturn(new \App\Models\Alert());

        $service = new MeasurementService($measurementRepo, $alertRepo);

        $result = $service->create(
            deviceId: 1,
            temperatureC: -5.0,
            recordedAt: now(),
            payload: NULL
        );

        $this->assertEquals(999, $result);

        $this->assertTrue(TRUE);
    }

    public function test_creates_alert_when_temperature_is_above_thirty(): void
    {
        $measurementRepo = Mockery::mock(MeasurementRepositoryInterface::class);
        $alertRepo = Mockery::mock(AlertRepositoryInterface::class);

        DB::shouldReceive('table')->with('device_user')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('device_id', 2)->once()->andReturnSelf();
        DB::shouldReceive('where')->with('is_active', 1)->once()->andReturnSelf();
        DB::shouldReceive('select')->with('user_id')->once()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object)['user_id' => 200]);

        $measurementRepo->shouldReceive('store')
            ->once()
            ->withArgs(function ($deviceId, $recordedAt, $temperatureC, $payload) {
                return $deviceId === 2
                    && $temperatureC === 35.0;
            })
            ->andReturn(888);

        $alertRepo->shouldReceive('create')
            ->once()
            ->withArgs(function ($data) {
                return $data['device_id'] === 2
                    && $data['user_id'] === 200
                    && $data['measurement_id'] === 888
                    && $data['type'] === 'temperature_threshold'
                    && str_contains($data['message'], '35.00');
            })
            ->andReturn(new \App\Models\Alert());

        $service = new MeasurementService($measurementRepo, $alertRepo);

        $result = $service->create(
            deviceId: 2,
            temperatureC: 35.0,
            recordedAt: now(),
            payload: NULL
        );

        $this->assertEquals(888, $result);
    }

    public function test_does_not_create_alert_when_temperature_is_within_range(): void
    {
        $measurementRepo = Mockery::mock(MeasurementRepositoryInterface::class);
        $alertRepo = Mockery::mock(AlertRepositoryInterface::class);

        DB::shouldReceive('table')->with('device_user')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('device_id', 3)->once()->andReturnSelf();
        DB::shouldReceive('where')->with('is_active', 1)->once()->andReturnSelf();
        DB::shouldReceive('select')->with('user_id')->once()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object)['user_id' => 300]);

        $measurementRepo->shouldReceive('store')
            ->once()
            ->andReturn(777);

        $alertRepo->shouldNotReceive('create');

        $service = new MeasurementService($measurementRepo, $alertRepo);

        $result = $service->create(
            deviceId: 3,
            temperatureC: 22.5,
            recordedAt: now(),
            payload: NULL
        );

        $this->assertEquals(777, $result);
    }

    public function test_creates_alert_with_correct_message_format(): void
    {
        $measurementRepo = Mockery::mock(MeasurementRepositoryInterface::class);
        $alertRepo = Mockery::mock(AlertRepositoryInterface::class);

        DB::shouldReceive('table')->with('device_user')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('device_id', 4)->once()->andReturnSelf();
        DB::shouldReceive('where')->with('is_active', 1)->once()->andReturnSelf();
        DB::shouldReceive('select')->with('user_id')->once()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object)['user_id' => 400]);

        $measurementRepo->shouldReceive('store')->once()->andReturn(666);

        $alertRepo->shouldReceive('create')
            ->once()
            ->withArgs(function ($data) {
                $expectedMessage = 'Temperature -10.50°C out of threshold [0.00..30.00]';
                return $data['message'] === $expectedMessage;
            })
            ->andReturn(new \App\Models\Alert());

        $service = new MeasurementService($measurementRepo, $alertRepo);

        $result = $service->create(
            deviceId: 4,
            temperatureC: -10.5,
            recordedAt: now(),
            payload: NULL
        );

        $this->assertEquals(666, $result);
    }

    public function test_handles_boundary_temperatures_correctly(): void
    {
        $measurementRepo = Mockery::mock(MeasurementRepositoryInterface::class);
        $alertRepo = Mockery::mock(AlertRepositoryInterface::class);

        DB::shouldReceive('table')->with('device_user')->twice()->andReturnSelf();
        DB::shouldReceive('where')->with('device_id', 5)->twice()->andReturnSelf();
        DB::shouldReceive('where')->with('is_active', 1)->twice()->andReturnSelf();
        DB::shouldReceive('select')->with('user_id')->twice()->andReturnSelf();
        DB::shouldReceive('first')->twice()->andReturn((object)['user_id' => 500]);

        $measurementRepo->shouldReceive('store')
            ->twice()
            ->andReturn(555, 444);

        $alertRepo->shouldNotReceive('create');

        $service = new MeasurementService($measurementRepo, $alertRepo);

        $result1 = $service->create(
            deviceId: 5,
            temperatureC: 0.0,
            recordedAt: now(),
            payload: NULL
        );

        $result2 = $service->create(
            deviceId: 5,
            temperatureC: 30.0,
            recordedAt: now(),
            payload: NULL
        );

        $this->assertEquals(555, $result1);
        $this->assertEquals(444, $result2);
    }
}
