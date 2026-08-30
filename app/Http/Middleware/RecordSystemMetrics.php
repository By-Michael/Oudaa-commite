<?php

namespace App\Http\Middleware;

use App\Models\SystemMetric;
use Closure;
use Throwable;

class RecordSystemMetrics
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        // Never let metrics recording break or slow down the real response.
        try {
            SystemMetric::create([
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
                'status_code' => $response->getStatusCode(),
                'method' => $request->method(),
                'path' => '/'.ltrim($request->path(), '/'),
            ]);
        } catch (Throwable $e) {
            // swallow — metrics are best-effort
        }

        return $response;
    }
}
