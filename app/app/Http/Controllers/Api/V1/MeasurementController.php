<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MeMeasurementsRequest;
use App\Http\Requests\Api\V1\StoreMeasurementRequest;
use App\Models\Device;
use App\Services\MeasurementService;
use App\Support\ApiResponse;

final class MeasurementController extends Controller
{
    public function __construct(private readonly MeasurementService $service) {}

    public function store(StoreMeasurementRequest $request, Device $device)
    {
        $measurementId = $this->service->create(
            deviceId: $device->id,
            temperatureC: (float) $request->input('temperature_c'),
            recordedAt: $request->date('recorded_at') ?: now(),
            payload: $request->input('payload')
        );

        return ApiResponse::data(['id' => $measurementId], 201);
    }

    public function me(MeMeasurementsRequest $request)
    {
        $uid  = (int) $request->user()->getAuthIdentifier();
        $dev  = $request->integer('device_id') ?: null;
        $from = $request->date('from');
        $to   = $request->date('to');

        $rows = $this->service->listForUser($uid, $dev, $from, $to);

        return ApiResponse::data($rows);
    }
}
