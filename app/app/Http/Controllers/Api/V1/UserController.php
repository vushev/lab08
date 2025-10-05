<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Models\User;
use App\Services\UserService;
use App\Support\ApiResponse;

final class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function store(StoreUserRequest $request)
    {
        $u = $this->users->create(
            $request->string('name')->toString(),
            $request->string('email')->toString(),
        );

        $token = $u->createToken('postman')->plainTextToken;

        return ApiResponse::data([
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'token' => $token,
        ], 201);
    }

    public function destroy(User $user)
    {
        $this->users->delete($user);
        return ApiResponse::data(['deleted' => true]);
    }
}
