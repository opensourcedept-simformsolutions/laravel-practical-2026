<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLoggerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        // Process request
        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2); // duration in milliseconds

        $logData = [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'payload' => $request->except(['password', 'password_confirmation', '_token']),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user_id' => auth()->id(),
        ];

        Log::info("HTTP Request processed: {$request->method()} {$request->path()} -> Status {$response->getStatusCode()} in {$duration}ms", $logData);

        return $response;
    }
}
