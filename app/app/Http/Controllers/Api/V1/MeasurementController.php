<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMeasurementRequest;
use App\Models\Device;
use App\Services\MeasurementService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class MeasurementController extends Controller
{
    public function __construct(private readonly MeasurementService $svc) {}

    public function store(StoreMeasurementRequest $request, Device $device)
    {
        $actor = $request->user();  // X-Device-Token or User
        $m = $this->svc->ingest($device, $request->validated(), $actor);

        return ApiResponse::data([
            'id'            => $m->id,
            'device_id'     => $m->device_id,
            'temperature_c' => (float) $m->temperature_c,
            'recorded_at'   => optional($m->recorded_at)->toJSON(),
        ], 201);
    }
}
