<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Flat;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_flat_creation_generates_activity_log(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $society = Society::factory()->create();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'society_id' => $society->id,
        ]);

        $this->actingAs($admin)->post(route('flats.store'), [
            'wing' => 'A',
            'floor' => 1,
            'flat_number' => '101',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create',
            'subject_type' => Flat::class,
            'society_id' => $society->id,
            'user_id' => $admin->id,
        ]);

        $log = ActivityLog::first();
        $this->assertStringContainsString('Flat A-101 was created', $log->description);
    }

    public function test_login_event_creates_activity_log(): void
    {
        $residentRole = Role::where('name', 'resident')->first();
        $society = Society::factory()->create();
        $resident = User::factory()->create([
            'role_id' => $residentRole->id,
            'society_id' => $society->id,
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => $resident->email,
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'login',
            'user_id' => $resident->id,
            'society_id' => $society->id,
        ]);
    }

    public function test_activity_log_scoping_by_society_admin(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $residentRole = Role::where('name', 'resident')->first();

        $societyA = Society::factory()->create();
        $societyB = Society::factory()->create();

        $adminA = User::factory()->create([
            'role_id' => $adminRole->id,
            'society_id' => $societyA->id,
        ]);
        $adminB = User::factory()->create([
            'role_id' => $adminRole->id,
            'society_id' => $societyB->id,
        ]);

        // Generate logs for Society A and Society B
        ActivityLog::create([
            'user_id' => $adminA->id,
            'society_id' => $societyA->id,
            'action' => 'create',
            'description' => 'Log in Society A',
        ]);

        ActivityLog::create([
            'user_id' => $adminB->id,
            'society_id' => $societyB->id,
            'action' => 'create',
            'description' => 'Log in Society B',
        ]);

        // Query logs as Admin A
        $responseA = $this->actingAs($adminA)->getJson(route('admin.activity-logs.data'));
        $responseA->assertOk();
        $responseA->assertJsonFragment(['description' => 'Log in Society A']);
        $responseA->assertJsonMissing(['description' => 'Log in Society B']);

        // Query logs as Super Admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
        ]);

        $responseSuper = $this->actingAs($superAdmin)->getJson(route('admin.activity-logs.data'));
        $responseSuper->assertOk();
        $responseSuper->assertJsonFragment(['description' => 'Log in Society A']);
        $responseSuper->assertJsonFragment(['description' => 'Log in Society B']);
    }

    public function test_unauthorized_users_cannot_access_activity_logs(): void
    {
        $residentRole = Role::where('name', 'resident')->first();
        $society = Society::factory()->create();
        $resident = User::factory()->create([
            'role_id' => $residentRole->id,
            'society_id' => $society->id,
        ]);

        $response = $this->actingAs($resident)->get(route('admin.activity-logs.index'));
        $response->assertStatus(302);
    }
}
