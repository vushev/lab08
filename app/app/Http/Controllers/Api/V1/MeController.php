<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MeAlertsRequest;
use App\Http\Requests\Api\V1\MeMeasurementsRequest;
use App\Models\Alert;
use App\Models\Measurement;
use App\Support\ApiResponse;

final class MeController extends Controller
{
    public function measurements(MeMeasurementsRequest $request)
    {
        $me = $request->user();
        $tbl = (new Measurement())->getTable();

        $q = Measurement::query()
            ->ownedBy($me)
            ->when($request->integer('device_id'), fn($qq, $id) => $qq->where($tbl . '.device_id', $id))
            ->when($request->input('from'), fn($qq, $v) => $qq->where($tbl . '.recorded_at', '>=', $v))
            ->when($request->input('to'),   fn($qq, $v) => $qq->where($tbl . '.recorded_at', '<=', $v))
            ->orderByDesc($tbl . '.recorded_at');

        return ApiResponse::data($q->paginate(50));
    }

    public function alerts(MeAlertsRequest $request)
    {
        $me = $request->user();

        $q = Alert::ownedBy($me)
            ->when($request->input('status'), fn($qq, $s) => $qq->where('status', $s))
            ->when($request->input('from'),   fn($qq, $v) => $qq->where('created_at', '>=', $v))
            ->when($request->input('to'),     fn($qq, $v) => $qq->where('created_at', '<=', $v))
            ->orderByDesc('created_at');

        return ApiResponse::data($q->paginate(50));
    }
}
