<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Device;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class DeviceTokenOrPat
{
    public function handle(Request $request, Closure $next)
    {
        $param = $request->route('device');

        $device = $param instanceof Device
            ? $param
            : (is_numeric($param)
                ? Device::find((int) $param)
                : (is_string($param) && $param !== '' ? Device::where('uuid', $param)->first() : null));

        if (! $device) {
            return ApiResponse::error('Device required', 'DEVICE_REQUIRED', 422);
        }

        if ($t = $request->header('X-Device-Token')) {
            if (hash_equals((string) $device->api_token, (string) $t)) {
                return $next($request);
            }
            return ApiResponse::error('Invalid device token', 'INVALID_DEVICE_TOKEN', 401);
        }

        if ($bearer = $request->bearerToken()) {
            if ($pat = PersonalAccessToken::findToken($bearer)) {
                $user = $pat->tokenable;
                if ($user && Tenancy::userOwnsDevice((int)$user->getAuthIdentifier(), $device->id)) {

                    $request->setUserResolver(fn() => $user);
                    Auth::setUser($user);
                    return $next($request);
                }
            }
        }

        return ApiResponse::error('Unauthorized', 'UNAUTHORIZED', 401);
    }
}
