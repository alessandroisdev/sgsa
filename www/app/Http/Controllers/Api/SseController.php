<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SseController extends Controller
{
    public function stream(Request $request)
    {
        $areaId = $request->get('area_id');
        $channel = "sgsa_area_{$areaId}";

        return new StreamedResponse(function () use ($channel) {
            // Disable time limit for infinite loop
            set_time_limit(0);

            // Subscribe blocks the script until a message arrives
            Redis::subscribe([$channel], function ($message) {
                echo "data: {$message}\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            });

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Critical for Nginx
        ]);
    }
}
