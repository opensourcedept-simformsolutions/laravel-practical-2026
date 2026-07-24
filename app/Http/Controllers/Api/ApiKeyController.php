<?php

namespace App\Http\Controllers\Api;

use App\Events\ApiKeyCreated;
use App\Events\ApiKeyRevoked;
use App\Events\ApiKeyRotated;
use App\Events\ApiKeySuspended;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiKey\RotateApiKeyRequest;
use App\Http\Requests\ApiKey\StoreApiKeyRequest;
use App\Http\Requests\ApiKey\UpdateApiKeyRequest;
use App\Http\Resources\ApiKeyResource;
use App\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the user's API Keys.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $keys = $request->user()->apiKeys()->latest()->paginate(15);
        return ApiKeyResource::collection($keys);
    }

    /**
     * Store a newly created API Key.
     */
    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $plainSecret = 'as_' . bin2hex(random_bytes(32));
        $publicKey = 'ak_' . bin2hex(random_bytes(16));

        $expiresAt = null;
        if ($request->filled('expires_in_days')) {
            $expiresAt = now()->addDays((int)$validated['expires_in_days']);
        }

        $apiKey = $request->user()->apiKeys()->create([
            'name' => $validated['name'],
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'status' => 'active',
            'scopes' => $validated['scopes'] ?? ['*'],
            'ip_whitelist' => $validated['ip_whitelist'] ?? null,
            'rate_limit_limit' => $validated['rate_limit_limit'] ?? 60,
            'quota_limit' => $validated['quota_limit'] ?? 10000,
            'expires_at' => $expiresAt,
        ]);

        // Attach plain secret only for this single response
        $apiKey->plain_secret = $plainSecret;

        event(new ApiKeyCreated($apiKey));

        return (new ApiKeyResource($apiKey))
            ->response()
            ->setStatusCode(210); // Custom success code to represent key created with exposed secret
    }

    /**
     * Display the specified API Key.
     */
    public function show(Request $request, string $id): ApiKeyResource
    {
        $apiKey = $request->user()->apiKeys()->findOrFail($id);
        return new ApiKeyResource($apiKey);
    }

    /**
     * Update the specified API Key.
     */
    public function update(UpdateApiKeyRequest $request, string $id): ApiKeyResource
    {
        $apiKey = $request->user()->apiKeys()->findOrFail($id);
        $apiKey->update($request->validated());

        return new ApiKeyResource($apiKey);
    }

    /**
     * Remove/Delete the specified API Key.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $apiKey = $request->user()->apiKeys()->findOrFail($id);
        $apiKey->delete();

        return response()->json([
            'message' => 'API Key deleted successfully.'
        ]);
    }

    /**
     * Suspend the specified API Key.
     */
    public function suspend(Request $request, string $id): ApiKeyResource
    {
        $apiKey = $request->user()->apiKeys()->findOrFail($id);
        $apiKey->update(['status' => 'suspended']);

        event(new ApiKeySuspended($apiKey));

        return new ApiKeyResource($apiKey);
    }

    /**
     * Activate the specified API Key.
     */
    public function activate(Request $request, string $id): ApiKeyResource
    {
        $apiKey = $request->user()->apiKeys()->findOrFail($id);
        $apiKey->update(['status' => 'active']);

        return new ApiKeyResource($apiKey);
    }

    /**
     * Revoke the specified API Key.
     */
    public function revoke(Request $request, string $id): ApiKeyResource
    {
        $apiKey = $request->user()->apiKeys()->findOrFail($id);
        $apiKey->update(['status' => 'revoked']);

        event(new ApiKeyRevoked($apiKey));

        return new ApiKeyResource($apiKey);
    }

    /**
     * Rotate the specified API Key.
     */
    public function rotate(RotateApiKeyRequest $request, string $id): JsonResponse
    {
        $oldApiKey = $request->user()->apiKeys()->findOrFail($id);

        if ($oldApiKey->status !== 'active') {
            return response()->json([
                'error' => 'Conflict',
                'message' => 'Only active keys can be rotated.'
            ], 409);
        }

        $graceMinutes = $request->input('grace_period_minutes', 60); // Default 1 hour grace period
        $plainSecret = 'as_' . bin2hex(random_bytes(32));
        $publicKey = 'ak_' . bin2hex(random_bytes(16));

        $newApiKey = DB::transaction(function () use ($oldApiKey, $publicKey, $plainSecret, $graceMinutes) {
            // 1. Create new key mirroring old key configuration
            $newKey = $oldApiKey->user->apiKeys()->create([
                'name' => $oldApiKey->name . ' (Rotated)',
                'key' => $publicKey,
                'secret_hash' => Hash::make($plainSecret),
                'status' => 'active',
                'scopes' => $oldApiKey->scopes,
                'ip_whitelist' => $oldApiKey->ip_whitelist,
                'rate_limit_limit' => $oldApiKey->rate_limit_limit,
                'quota_limit' => $oldApiKey->quota_limit,
                'expires_at' => $oldApiKey->expires_at, // Inherit expiration of old key if set
            ]);

            // 2. Mark old key as expired with rotation grace period details
            $oldApiKey->update([
                'expires_at' => now(), // Force expiration of old key immediately
                'rotated_key_id' => $newKey->id,
                'rotation_grace_expires_at' => now()->addMinutes((int)$graceMinutes),
            ]);

            return $newKey;
        });

        $newApiKey->plain_secret = $plainSecret;

        event(new ApiKeyRotated($oldApiKey, $newApiKey));

        return (new ApiKeyResource($newApiKey))
            ->response()
            ->setStatusCode(210);
    }
}
