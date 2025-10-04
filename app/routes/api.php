<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Support\ApiResponse;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

Route::prefix('v1')->group(function () {
    Route::post('/devices/{device}/ping', function () {
        return ApiResponse::data(['ok' => true]);
    })->middleware('dual');
});