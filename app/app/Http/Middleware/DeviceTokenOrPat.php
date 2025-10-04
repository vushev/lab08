<?php

namespace App\Http\Middleware;

use App\Models\Device;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;

class DeviceTokenOrPat
{
    public function handle(Request $request, Closure $next)
    {
        $param = $request->route('device');
        $device = is_numeric($param) ? Device::find((int)$param)
            : (is_string($param) && $param !== '' ? Device::where('uuid', $param)->first() : null);
        if (!$device) return ApiResponse::error('Device required', 'DEVICE_REQUIRED', 422);

        if ($t = $request->header('X-Device-Token')) {
            if (Device::whereKey($device->id)->where('api_token', $t)->exists()) {
                return $next($request);
            }
            return ApiResponse::error('Invalid device token', 'INVALID_DEVICE_TOKEN', 401);
        }

        $u = $request->user();
        if ($u && Tenancy::userOwnsDevice($u->id, $device->id)) {
            return $next($request);
        }

        return ApiResponse::error('Unauthorized', 'UNAUTHORIZED', 401);
    }
}
