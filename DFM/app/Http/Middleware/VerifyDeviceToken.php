<?php

namespace App\Http\Middleware;

use App\Models\HardwareDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Edge API guard (PRD §2.C): authenticates IoT gateway requests via a Bearer
 * token that is matched against the SHA-256 hash stored on the hardware device.
 * The resolved device is attached to the request as `device`.
 */
class VerifyDeviceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Missing device bearer token.'], 401);
        }

        $device = HardwareDevice::where('api_token_hash', hash('sha256', $token))->first();

        if (! $device) {
            return response()->json(['message' => 'Invalid device token.'], 401);
        }

        if ($device->status !== 'active') {
            return response()->json(['message' => 'Device is not active.'], 403);
        }

        $request->attributes->set('device', $device);

        return $next($request);
    }
}
