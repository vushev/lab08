<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function data(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => ['status' => $status],
        ], $status);
    }

    public static function error(string $message, string $code, int $status): JsonResponse
    {
        return response()->json([
            'error' => ['message' => $message, 'code' => $code],
            'meta'  => ['status' => $status],
        ], $status);
    }
}
