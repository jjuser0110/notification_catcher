<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Called by the app once. Needs the enroll key from .env (DEVICE_ENROLL_KEY).
     * Re-registering the same device_uid keeps its history and issues a new token.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enroll_key' => ['required', 'string'],
            'device_uid' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'android_version' => ['nullable', 'string', 'max:20'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ]);

        $expected = (string) config('services.device.enroll_key');
        if ($expected === '' || ! hash_equals($expected, $data['enroll_key'])) {
            return response()->json(['message' => 'Wrong enroll key.'], 403);
        }

        $token = Str::random(60);

        $device = Device::updateOrCreate(
            ['device_uid' => $data['device_uid']],
            [
                ...collect($data)->except('enroll_key')->all(),
                'api_token' => hash('sha256', $token),
                'is_active' => true,
                'last_seen_at' => now(),
            ],
        );

        return response()->json([
            'device_id' => $device->id,
            'token' => $token,
        ], 201);
    }

    /** Heartbeat: the app calls this so the dashboard knows the phone is alive. */
    public function ping(Request $request): JsonResponse
    {
        $device = $request->attributes->get('device');

        return response()->json(['ok' => true, 'device_id' => $device->id, 'name' => $device->name]);
    }
}
