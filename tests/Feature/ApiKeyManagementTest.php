<?php

namespace Tests\Feature;

use App\Events\ApiKeyCreated;
use App\Events\ApiKeyLimitExceeded;
use App\Events\ApiKeyRevoked;
use App\Events\ApiKeyRotated;
use App\Events\ApiKeySuspended;
use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiKeyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard user role if not present
        $role = Role::firstOrCreate(['name' => 'admin']);

        $this->user = User::factory()->create([
            'role_id' => $role->id
        ]);
    }

    /** @test */
    public function guests_cannot_manage_api_keys()
    {
        $this->getJson(route('api-keys.index'))->assertStatus(401);
        $this->postJson(route('api-keys.store'))->assertStatus(401);
    }

    /** @test */
    public function user_can_create_api_key_and_secret_is_exposed_only_once()
    {
        Event::fake([ApiKeyCreated::class]);

        $response = $this->actingAs($this->user)
            ->postJson(route('api-keys.store'), [
                'name' => 'Test Access Key',
                'scopes' => ['read', 'write'],
                'ip_whitelist' => ['127.0.0.1'],
                'rate_limit_limit' => 100,
                'quota_limit' => 50000,
                'expires_in_days' => 30
            ]);

        $response->assertStatus(210);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'key',
                'secret',
                'status',
                'scopes',
                'ip_whitelist',
                'rate_limit_limit',
                'quota_limit',
                'expires_at',
                'created_at'
            ]
        ]);

        $data = $response->json('data');
        $this->assertStringStartsWith('ak_', $data['key']);
        $this->assertStringStartsWith('as_', $data['secret']);

        // Verify key exists in database with hashed secret
        $apiKey = ApiKey::find($data['id']);
        $this->assertNotNull($apiKey);
        $this->assertTrue(Hash::check($data['secret'], $apiKey->secret_hash));
        $this->assertNotEquals($data['secret'], $apiKey->secret_hash); // Secret should not be stored in plain text

        Event::assertDispatched(ApiKeyCreated::class, function ($event) use ($apiKey) {
            return $event->apiKey->id === $apiKey->id;
        });

        // Verify subsequent show requests do not expose the plain secret
        $showResponse = $this->actingAs($this->user)
            ->getJson(route('api-keys.show', $apiKey->id));
        
        $showResponse->assertStatus(200);
        $this->assertNull($showResponse->json('data.secret'));
    }

    /** @test */
    public function user_can_list_update_and_delete_their_own_api_keys()
    {
        $apiKey = ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
            'key' => 'ak_test_key_123',
            'secret_hash' => Hash::make('secret'),
            'scopes' => ['*'],
        ]);

        // List
        $indexResponse = $this->actingAs($this->user)
            ->getJson(route('api-keys.index'));
        $indexResponse->assertStatus(200);
        $this->assertCount(1, $indexResponse->json('data'));

        // Update
        $updateResponse = $this->actingAs($this->user)
            ->putJson(route('api-keys.update', $apiKey->id), [
                'name' => 'Updated Name',
                'scopes' => ['read'],
                'quota_limit' => 2000
            ]);
        $updateResponse->assertStatus(200);
        $this->assertEquals('Updated Name', $updateResponse->json('data.name'));
        $this->assertEquals(['read'], $updateResponse->json('data.scopes'));

        // Delete
        $deleteResponse = $this->actingAs($this->user)
            ->deleteJson(route('api-keys.destroy', $apiKey->id));
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('api_keys', ['id' => $apiKey->id]);
    }

    /** @test */
    public function api_authentication_middleware_validates_headers()
    {
        $plainSecret = 'as_' . bin2hex(random_bytes(32));
        $publicKey = 'ak_' . bin2hex(random_bytes(16));

        ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Demo Key',
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'scopes' => ['*']
        ]);

        // No headers
        $this->getJson(route('api.v1.ping'))->assertStatus(401)->assertJson(['error' => 'Unauthorized']);

        // Invalid key
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => 'invalid_key',
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(401)->assertJson(['error' => 'Unauthorized']);

        // Invalid secret
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => 'invalid_secret'
        ])->assertStatus(401)->assertJson(['error' => 'Unauthorized']);

        // Successful authentication
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(200)->assertJson(['message' => 'pong']);
    }

    /** @test */
    public function api_authentication_validates_ip_whitelist()
    {
        $plainSecret = 'as_secret';
        $publicKey = 'ak_key';

        ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'IP Whitelisted Key',
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'scopes' => ['*'],
            'ip_whitelist' => ['1.1.1.1'] // Whitelist doesn't include localhost (127.0.0.1)
        ]);

        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(403)->assertJsonFragment(['error' => 'Forbidden']);
    }

    /** @test */
    public function api_authentication_validates_scopes()
    {
        $plainSecret = 'as_secret';
        $publicKey = 'ak_key';

        ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'ReadOnly Key',
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'scopes' => ['read'] // Missing 'write' scope
        ]);

        // Attempting test-write endpoint (which requires 'write' scope)
        $this->postJson(route('api.v1.write'), [], [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(403)->assertJsonFragment(['error' => 'Forbidden']);
    }

    /** @test */
    public function api_keys_can_be_suspended_and_activated()
    {
        Event::fake([ApiKeySuspended::class]);

        $plainSecret = 'as_secret';
        $publicKey = 'ak_key';

        $apiKey = ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Key to Suspend',
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'scopes' => ['*']
        ]);

        // Suspend
        $this->actingAs($this->user)
            ->postJson(route('api-keys.suspend', $apiKey->id))
            ->assertStatus(200);

        Event::assertDispatched(ApiKeySuspended::class);

        // Access API and expect 403 Forbidden
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(403)->assertJsonFragment(['message' => 'API Key is suspended.']);

        // Activate
        $this->actingAs($this->user)
            ->postJson(route('api-keys.activate', $apiKey->id))
            ->assertStatus(200);

        // Access API and expect 200 OK
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(200);
    }

    /** @test */
    public function api_keys_can_be_revoked()
    {
        Event::fake([ApiKeyRevoked::class]);

        $plainSecret = 'as_secret';
        $publicKey = 'ak_key';

        $apiKey = ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Key to Revoke',
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'scopes' => ['*']
        ]);

        // Revoke
        $this->actingAs($this->user)
            ->postJson(route('api-keys.revoke', $apiKey->id))
            ->assertStatus(200);

        Event::assertDispatched(ApiKeyRevoked::class);

        // Access API and expect 403 Forbidden
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(403)->assertJsonFragment(['message' => 'API Key has been revoked.']);
    }

    /** @test */
    public function api_keys_rotation_maintains_dual_validity_during_grace_period()
    {
        Event::fake([ApiKeyRotated::class]);

        $oldSecret = 'as_old_secret';
        $oldKey = 'ak_old_key';

        $apiKey = ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Rotation Key',
            'key' => $oldKey,
            'secret_hash' => Hash::make($oldSecret),
            'scopes' => ['*']
        ]);

        // Rotate key
        $response = $this->actingAs($this->user)
            ->postJson(route('api-keys.rotate', $apiKey->id), [
                'grace_period_minutes' => 30
            ]);

        $response->assertStatus(210);
        $newKeyData = $response->json('data');

        Event::assertDispatched(ApiKeyRotated::class);

        // 1. New key should work immediately
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $newKeyData['key'],
            'X-API-SECRET' => $newKeyData['secret']
        ])->assertStatus(200);

        // 2. Old key should ALSO work during grace period
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $oldKey,
            'X-API-SECRET' => $oldSecret
        ])->assertStatus(200);

        // 3. Force old key's grace period expiration
        $apiKey->refresh()->update([
            'rotation_grace_expires_at' => now()->subMinute()
        ]);

        // 4. Old key should fail now
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $oldKey,
            'X-API-SECRET' => $oldSecret
        ])->assertStatus(401)->assertJsonFragment(['message' => 'API Key has expired.']);
    }

    /** @test */
    public function scheduled_command_revokes_keys_outside_grace_period()
    {
        $oldSecret = 'as_old_secret';
        $oldKey = 'ak_old_key';

        $apiKey = ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Expired Grace Key',
            'key' => $oldKey,
            'secret_hash' => Hash::make($oldSecret),
            'scopes' => ['*'],
            'expires_at' => now()->subMinutes(60),
            'rotation_grace_expires_at' => now()->subMinutes(10), // grace period passed
        ]);

        $this->assertEquals('active', $apiKey->status);

        // Run scheduler command
        Artisan::call('api-keys:clean');

        $this->assertEquals('revoked', $apiKey->refresh()->status);
    }

    /** @test */
    public function rate_limiting_returns_too_many_requests()
    {
        Event::fake([ApiKeyLimitExceeded::class]);

        $plainSecret = 'as_secret';
        $publicKey = 'ak_key';

        ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Rate Limited Key',
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'scopes' => ['*'],
            'rate_limit_limit' => 2 // Max 2 requests per minute
        ]);

        // Request 1
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(200);

        // Request 2
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(200);

        // Request 3 - Should fail with 429
        $response = $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ]);
        
        $response->assertStatus(429);
        $response->assertJsonFragment(['error' => 'Too Many Requests']);

        Event::assertDispatched(ApiKeyLimitExceeded::class, function ($event) {
            return $event->limitType === 'rate_limit';
        });
    }

    /** @test */
    public function request_logging_tracks_each_api_call()
    {
        $plainSecret = 'as_secret';
        $publicKey = 'ak_key';

        $apiKey = ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Logging Key',
            'key' => $publicKey,
            'secret_hash' => Hash::make($plainSecret),
            'scopes' => ['*']
        ]);

        $this->assertCount(0, ApiKeyLog::all());

        // Make API Call
        $this->getJson(route('api.v1.ping'), [
            'X-API-KEY' => $publicKey,
            'X-API-SECRET' => $plainSecret
        ])->assertStatus(200);

        // Verify request log is created
        $logs = ApiKeyLog::all();
        $this->assertCount(1, $logs);
        
        $log = $logs->first();
        $this->assertEquals($apiKey->id, $log->api_key_id);
        $this->assertEquals('GET', $log->request_method);
        $this->assertEquals('/api/v1/ping', $log->request_uri);
        $this->assertEquals(200, $log->response_status);
        $this->assertGreaterThanOrEqual(0, $log->duration_ms);
    }
}
