<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Models\Wing;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the roles
        $this->seed(RoleSeeder::class);
    }

    public function test_visitor_pass_data_is_scoped_by_society(): void
    {
        // 1. Setup two societies
        $societyA = Society::factory()->create();
        $societyB = Society::factory()->create();

        // 2. Setup roles
        $residentRole = Role::where('name', 'resident')->first();

        // 3. Create users
        $userA = User::factory()->create([
            'society_id' => $societyA->id,
            'role_id' => $residentRole->id,
        ]);
        $userB = User::factory()->create([
            'society_id' => $societyB->id,
            'role_id' => $residentRole->id,
        ]);

        // 4. Create flats
        $flatA = Flat::factory()->create(['society_id' => $societyA->id]);
        $flatB = Flat::factory()->create(['society_id' => $societyB->id]);

        // 5. Create residents
        $residentA = Resident::factory()->create([
            'user_id' => $userA->id,
            'flat_id' => $flatA->id,
        ]);
        $residentB = Resident::factory()->create([
            'user_id' => $userB->id,
            'flat_id' => $flatB->id,
        ]);

        // 6. Create visitor logs
        $visitorA = Visitor::factory()->create();
        $visitorLogA = VisitorLog::create([
            'visitor_id' => $visitorA->id,
            'flat_id' => $flatA->id,
            'purpose' => 'Meeting',
            'status' => 'pending',
            'visit_date' => now()->toDateString(),
            'passcode' => 'PASS123',
            'created_by' => $userA->id,
        ]);

        $visitorB = Visitor::factory()->create();
        $visitorLogB = VisitorLog::create([
            'visitor_id' => $visitorB->id,
            'flat_id' => $flatB->id,
            'purpose' => 'Delivery',
            'status' => 'pending',
            'visit_date' => now()->toDateString(),
            'passcode' => 'PASS456',
            'created_by' => $userB->id,
        ]);

        // 7. Login as User A and query visitor pass data
        $response = $this->actingAs($userA)->getJson(route('passes.data'));

        $response->assertOk();
        $response->assertJsonFragment(['purpose' => 'Meeting']);
        $response->assertJsonMissing(['purpose' => 'Delivery']);

        // 8. Query report data
        $responseReport = $this->actingAs($userA)->getJson(route('reports.passes.data'));
        $responseReport->assertOk();
        $responseReport->assertJsonFragment(['purpose' => 'Meeting']);
        $responseReport->assertJsonMissing(['purpose' => 'Delivery']);
    }

    public function test_flat_creation_unique_validation(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
        ]);

        $societyA = Society::factory()->create();
        $societyB = Society::factory()->create();

        // Create Wing A in Society A
        $wingA = Wing::create([
            'society_id' => $societyA->id,
            'name' => 'A',
            'total_floors' => 5,
            'flats_per_floor' => 200,
        ]);

        // Create Wing A in Society B
        $wingB = Wing::create([
            'society_id' => $societyB->id,
            'name' => 'A',
            'total_floors' => 5,
            'flats_per_floor' => 200,
        ]);

        // Create Flat in Society A
        Flat::create([
            'society_id' => $societyA->id,
            'wing_id' => $wingA->id,
            'floor' => 1,
            'flat_number' => '101',
        ]);

        // Trying to create the same flat in Society A should fail
        $responseFail = $this->actingAs($superAdmin)->post(route('flats.store'), [
            'society_id' => $societyA->id,
            'wing_id' => $wingA->id,
            'floor' => 1,
            'flat_number' => '101',
        ]);

        $responseFail->assertSessionHasErrors(['flat_number']);

        // Creating the same flat in Society B should succeed
        $responseSuccess = $this->actingAs($superAdmin)->post(route('flats.store'), [
            'society_id' => $societyB->id,
            'wing_id' => $wingB->id,
            'floor' => 1,
            'flat_number' => '101',
        ]);

        $responseSuccess->assertRedirect(route('flats.index'));
        $this->assertDatabaseHas('flats', [
            'society_id' => $societyB->id,
            'wing_id' => $wingB->id,
            'floor' => 1,
            'flat_number' => '101',
        ]);
    }

    public function test_delivery_resident_scoping_validation(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $residentRole = Role::where('name', 'resident')->first();

        $societyA = Society::factory()->create();
        $societyB = Society::factory()->create();

        $adminA = User::factory()->create([
            'role_id' => $adminRole->id,
            'society_id' => $societyA->id,
        ]);

        $flatA = Flat::factory()->create(['society_id' => $societyA->id]);
        $flatB = Flat::factory()->create(['society_id' => $societyB->id]);

        // Resident B is in Society B
        $userB = User::factory()->create([
            'role_id' => $residentRole->id,
            'society_id' => $societyB->id,
        ]);
        $residentB = Resident::factory()->create([
            'user_id' => $userB->id,
            'flat_id' => $flatB->id,
        ]);

        // Admin A tries to create a delivery for Flat A, but selecting Resident B (who belongs to Society B)
        $response = $this->actingAs($adminA)->post(route('deliveries.store'), [
            'flat_id' => $flatA->id,
            'resident_id' => $residentB->id,
            'vendor' => 'Amazon',
            'package_details' => 'Box',
        ]);

        $response->assertSessionHasErrors(['resident_id']);
    }

    public function test_gatekeeper_qr_scan_authorization(): void
    {
        $gatekeeperRole = Role::where('name', 'gatekeeper')->first();
        $residentRole = Role::where('name', 'resident')->first();

        $societyA = Society::factory()->create();
        $societyB = Society::factory()->create();

        // Gatekeeper in Society B
        $gatekeeperB = User::factory()->create([
            'role_id' => $gatekeeperRole->id,
            'society_id' => $societyB->id,
        ]);

        // Resident in Society A
        $userA = User::factory()->create([
            'role_id' => $residentRole->id,
            'society_id' => $societyA->id,
        ]);
        $flatA = Flat::factory()->create(['society_id' => $societyA->id]);
        $residentA = Resident::factory()->create([
            'user_id' => $userA->id,
            'flat_id' => $flatA->id,
        ]);

        // Visitor Log in Society A
        $visitor = Visitor::factory()->create();
        $visitorLogA = VisitorLog::create([
            'visitor_id' => $visitor->id,
            'flat_id' => $flatA->id,
            'purpose' => 'Courier',
            'status' => 'pending',
            'visit_date' => now()->toDateString(),
            'passcode' => 'PASS789',
            'created_by' => $userA->id,
        ]);

        // Encrypt the log ID to simulate QR token
        $qrToken = encrypt($visitorLogA->id);

        // Gatekeeper B (Society B) tries to find the pass of Society A
        $responseFind = $this->actingAs($gatekeeperB)->postJson(route('gatekeeper.find-pass'), [
            'qr_code' => $qrToken,
        ]);

        $responseFind->assertStatus(403); // Forbidden

        // Gatekeeper B tries to mark entry for Society A's log
        Storage::fake('public');
        $photo = UploadedFile::fake()->image('visitor.jpg');

        $responseMark = $this->actingAs($gatekeeperB)->postJson(route('gatekeeper.mark-entry', $visitorLogA->id), [
            'photo' => $photo,
        ]);

        $responseMark->assertStatus(403); // Forbidden
    }
}
