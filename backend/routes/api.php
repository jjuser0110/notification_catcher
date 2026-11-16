<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PayinApiController;
use App\Http\Middleware\AuthenticateDevice;
use Illuminate\Support\Facades\Route;

// -- Notification Catcher device API ----------------------------------------
Route::post('/devices/register', [DeviceController::class, 'register'])->middleware('throttle:10,1');

Route::middleware(AuthenticateDevice::class)->group(function () {
    Route::get('/devices/ping', [DeviceController::class, 'ping']);
    Route::post('/messages', [MessageController::class, 'store'])->middleware('throttle:120,1');
});

// -- Payment gateway: merchant-facing payin API -----------------------------
Route::post('/pg/payin', [PayinApiController::class, 'store'])->middleware('throttle:120,1');
