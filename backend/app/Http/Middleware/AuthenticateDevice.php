<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $device = $token
            ? Device::where('api_token', hash('sha256', $token))->first()
            : null;

        if (! $device || ! $device->is_active) {
            return response()->json(['message' => 'Invalid or disabled device token.'], 401);
        }

        $device->forceFill(['last_seen_at' => now()])->saveQuietly();
        $request->attributes->set('device', $device);

        return $next($request);
    }
}
