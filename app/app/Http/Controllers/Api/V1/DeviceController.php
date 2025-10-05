<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AttachDeviceRequest;
use App\Http\Requests\Api\V1\DetachDeviceRequest;
use App\Http\Requests\Api\V1\StoreDeviceRequest;
use App\Models\Device;
use App\Models\User;
use App\Services\DeviceService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class DeviceController extends Controller
{
    public function __construct(private readonly DeviceService $devices) {}

    public function store(StoreDeviceRequest $request)
    {
        /** @var User $owner */
        $owner = $request->user();

        ['device' => $device, 'api_token' => $token] = $this->devices->create(
            $owner,
            $request->string('name')->toString(),
            $request->string('serial')->toString()
        );

        return ApiResponse::data([
            'id'        => $device->id,
            'uuid'      => $device->uuid,
            'name'      => $device->name,
            'serial'    => $device->serial,
            'api_token' => $token,
        ], 201);
    }

    public function destroy(Request $request, Device $device)
    {
        $this->authorize('delete', $device);

        $this->devices->delete($device);

        return ApiResponse::data(['deleted' => true]);
    }

    public function attach(AttachDeviceRequest $request, Device $device)
    {
        $this->authorize('attach', $device);

        $user = User::findOrFail((int) $request->input('user_id'));

        $this->devices->attach($device, $user);

        return ApiResponse::data(['attached_to' => $user->id], 200);
    }

    public function detach(DetachDeviceRequest $request, Device $device)
    {
        $this->authorize('detach', $device);

        $user = User::findOrFail((int) $request->input('user_id'));

        $this->devices->detach($device, $user);

        return ApiResponse::data(['detached_from' => $user->id], 200);

    }
}