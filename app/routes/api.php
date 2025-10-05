<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Support\ApiResponse;
use App\Models\Device;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\MeasurementController;
use App\Http\Controllers\Api\V1\MeController;

// Sanctum test user
Route::middleware('auth:sanctum')->get('/user', fn(Request $r) => response()->json($r->user()));

//{device} id or uuid
Route::bind('device', function (string $value) {
    return Device::whereKey($value)->orWhere('uuid', $value)->firstOrFail();
});

Route::prefix('v1')->group(function () {
    // Users
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('auth:sanctum');

    // Devices (owner actions)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/devices', [DeviceController::class, 'store']); // returns api_token
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy']);
        Route::post('/devices/{device}/attach', [DeviceController::class, 'attach']);
        Route::post('/devices/{device}/detach', [DeviceController::class, 'detach']);
    });

    // (X-Device-Token OR Bearer of active owner)
    Route::post('/devices/{device}/measurements', [MeasurementController::class, 'store'])
        ->middleware('dual');

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('auth:sanctum')->get('me/devices', function (Request $request) {
            $uid = (int) $request->user()->getAuthIdentifier();

            $devices = Device::query()
                ->select(
                    'devices.id',
                    'devices.uuid',
                    'devices.name',
                    'devices.serial',
                    'devices.status',
                    'devices.created_at',
                    'devices.updated_at'
                )
                ->join('device_user', 'device_user.device_id', '=', 'devices.id')
                ->where('device_user.user_id', $uid)
                ->where('device_user.is_active', 1)
                ->orderBy('devices.id')
                ->get();

            return ApiResponse::data($devices);
        });
        Route::get('/me/measurements', [MeController::class, 'measurements']);
        Route::get('/me/alerts', [MeController::class, 'alerts']);
    });

    // health check
    Route::post('/devices/{device}/ping', fn() => ApiResponse::data(['ok' => true]))->middleware('dual');
});
