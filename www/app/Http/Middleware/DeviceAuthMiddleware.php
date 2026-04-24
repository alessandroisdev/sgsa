<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Totem;
use App\Models\Tv;

class DeviceAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->header('X-Device-ID');
        $deviceType = $request->header('X-Device-Type'); // 'totem' or 'tv'

        if (!$deviceId || !in_array($deviceType, ['totem', 'tv'])) {
            return response()->json(['error' => 'Device ID or Type missing/invalid'], 401);
        }

        $device = null;

        if ($deviceType === 'totem') {
            $device = Totem::where('device_identifier', $deviceId)
                           ->where('active', true)
                           ->first();
        } else {
            $device = Tv::where('device_identifier', $deviceId)
                        ->where('active', true)
                        ->first();
        }

        if (!$device) {
            return response()->json(['error' => 'Device not authorized or inactive'], 401);
        }

        // Inject the authenticated device and its area into the request attributes
        $request->attributes->add([
            'device' => $device,
            'device_type' => $deviceType,
            'area_id' => $device->area_id
        ]);

        return $next($request);
    }
}
