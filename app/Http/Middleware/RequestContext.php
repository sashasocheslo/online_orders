<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();
        $startedAt = hrtime(true);
        $response = null;

        $request->attributes->set('request_id', $requestId);
        Log::withContext(['request_id' => $requestId]);

        try {
            $response = $next($request);
            $response->headers->set('X-Request-ID', $requestId);

            return $response;
        } finally {
            $durationMs = (hrtime(true) - $startedAt) / 1_000_000;

            if ($durationMs >= (float) config('logging.slow_request_ms', 1000)) {
                Log::warning('Slow HTTP request detected.', [
                    'method' => $request->method(),
                    'route_name' => $request->route()?->getName(),
                    'status_code' => $response?->getStatusCode(),
                    'duration_ms' => round($durationMs, 2),
                ]);
            }
        }
    }
}
