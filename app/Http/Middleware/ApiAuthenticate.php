<?php

namespace App\Http\Middleware;

use App\Events\ApiKeyLimitExceeded;
use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$scopes): Response
    {
        $apiKeyString = $request->header('X-API-KEY');
        $apiSecretString = $request->header('X-API-SECRET');

        if (!$apiKeyString || !$apiSecretString) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API Key (X-API-KEY) and API Secret (X-API-SECRET) headers are required.'
            ], 401);
        }

        // Find the API Key
        $apiKey = ApiKey::where('key', $apiKeyString)->first();

        if (!$apiKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API Key.'
            ], 401);
        }

        // Validate Status
        if ($apiKey->status === 'revoked') {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'API Key has been revoked.'
            ], 403);
        }

        if ($apiKey->status === 'suspended') {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'API Key is suspended.'
            ], 403);
        }

        // Validate Secret
        if (!Hash::check($apiSecretString, $apiKey->secret_hash)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API Secret.'
            ], 401);
        }

        // Validate Expiration & Grace Period
        if ($apiKey->isExpired()) {
            $inGracePeriod = $apiKey->rotation_grace_expires_at && $apiKey->rotation_grace_expires_at->isFuture();
            if (!$inGracePeriod) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'API Key has expired.'
                ], 401);
            }
        }

        // Validate IP Whitelist
        if (!$apiKey->isValidIp($request->ip())) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => "IP Address {$request->ip()} is not whitelisted for this API Key."
            ], 403);
        }

        // Validate Scopes
        foreach ($scopes as $scope) {
            if (!$apiKey->hasScope($scope)) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => "API Key does not possess the required scope: '{$scope}'."
                ], 403);
            }
        }

        // Validate Quota
        if (!$apiKey->hasRemainingQuota()) {
            event(new ApiKeyLimitExceeded($apiKey, 'quota'));
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'API Key monthly quota limit exceeded.'
            ], 429);
        }

        // Rate Limiting (Redis with Graceful Fallback)
        $rateLimitAllowed = $this->checkRateLimit($apiKey, $request);
        if (!$rateLimitAllowed) {
            event(new ApiKeyLimitExceeded($apiKey, 'rate_limit'));
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'API Key rate limit exceeded. Please try again later.'
            ], 429);
        }

        // Record usage & last_used_at
        $apiKey->updateQuietly([
            'last_used_at' => now(),
            'quota_used' => $apiKey->quota_used + 1,
        ]);

        // Log in the user associated with this key for the duration of request
        Auth::login($apiKey->user);
        
        // Attach key to request for logger to access in terminate()
        $request->attributes->set('auth_api_key', $apiKey);
        $request->attributes->set('request_start_time', microtime(true));

        return $next($request);
    }

    /**
     * Terminate request execution to log api usage.
     */
    public function terminate(Request $request, Response $response): void
    {
        $apiKey = $request->attributes->get('auth_api_key');
        $startTime = $request->attributes->get('request_start_time');

        if ($apiKey instanceof ApiKey) {
            $durationMs = $startTime ? (int) round((microtime(true) - $startTime) * 1000) : 0;

            try {
                ApiKeyLog::create([
                    'api_key_id' => $apiKey->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'request_method' => $request->method(),
                    'request_uri' => $request->getRequestUri(),
                    'response_status' => $response->getStatusCode(),
                    'duration_ms' => $durationMs,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log API Key request: ' . $e->getMessage());
            }
        }
    }

    /**
     * Implements Redis rate limiting with standard cache fallback.
     */
    protected function checkRateLimit(ApiKey $apiKey, Request $request): bool
    {
        $limit = $apiKey->rate_limit_limit; // Max requests per minute
        if ($limit === 0) {
            return true; // No rate limit
        }

        $keyName = "api_rate_limit:{$apiKey->id}:" . now()->format('YmdHi');

        // Try Redis first
        try {
            if (class_exists(\Redis::class) || class_exists(\Predis\Client::class)) {
                $redis = Redis::connection();
                $current = $redis->get($keyName);

                if ($current && (int)$current >= $limit) {
                    return false;
                }

                $redis->pipeline(function ($pipe) use ($keyName) {
                    $pipe->incr($keyName);
                    $pipe->expire($keyName, 60);
                });

                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Redis Rate Limiter failed: ' . $e->getMessage() . '. Falling back to Cache Limiter.');
        }

        // Fallback to cache store
        try {
            $current = (int) Cache::get($keyName, 0);

            if ($current >= $limit) {
                return false;
            }

            Cache::put($keyName, $current + 1, 60);
            return true;
        } catch (\Exception $e) {
            Log::error('Fallback Rate Limiter failed: ' . $e->getMessage());
            return true; // Safe fallback: fail-open if rate limiting fails completely
        }
    }
}
