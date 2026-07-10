<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Models\Wing;
use App\Services\SocietyDeletionService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CascadeSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_deleting_and_restoring_wing_cascades_to_flats_and_relations(): void
    {
        $society = Society::factory()->create();
        $wing = Wing::create([
            'society_id' => $society->id,
            'name' => 'A',
            'total_floors' => 5,
            'flats_per_floor' => 4,
        ]);

        $flat = Flat::create([
            'society_id' => $society->id,
            'wing_id' => $wing->id,
            'floor' => 1,
            'flat_number' => 101,
        ]);

        $residentRole = Role::where('name', 'resident')->first();
        $user = User::factory()->create([
            'society_id' => $society->id,
            'role_id' => $residentRole->id,
        ]);

        $resident = Resident::create([
            'user_id' => $user->id,
            'flat_id' => $flat->id,
            'resident_type' => 'owner',
        ]);

        $delivery = Delivery::create([
            'flat_id' => $flat->id,
            'resident_id' => $resident->id,
            'vendor' => 'Amazon',
            'package_details' => 'Box',
            'status' => 'received',
        ]);

        $visitor = Visitor::create([
            'name' => 'John Guest',
            'phone' => '9999999999',
        ]);

        $visitorLog = VisitorLog::create([
            'visitor_id' => $visitor->id,
            'flat_id' => $flat->id,
            'purpose' => 'Delivery',
            'status' => 'pending',
            'visit_date' => now()->toDateString(),
        ]);

        // 1. Delete Wing
        $wing->delete();

        $this->assertTrue($wing->trashed());
        $this->assertTrue($flat->fresh()->trashed());
        $this->assertTrue($resident->fresh()->trashed());
        $this->assertTrue($user->fresh()->trashed()); // Resident deletion soft deletes its user
        $this->assertTrue($delivery->fresh()->trashed());
        $this->assertTrue($visitorLog->fresh()->trashed());

        // 2. Restore Wing
        $wing->restore();

        $this->assertFalse($wing->fresh()->trashed());
        $this->assertFalse($flat->fresh()->trashed());
        $this->assertFalse($resident->fresh()->trashed());
        $this->assertFalse($user->fresh()->trashed());
        $this->assertFalse($delivery->fresh()->trashed());
        $this->assertFalse($visitorLog->fresh()->trashed());
    }

    public function test_society_deletion_service_soft_deletes_and_restores_correctly(): void
    {
        $society = Society::factory()->create();
        $wing = Wing::create([
            'society_id' => $society->id,
            'name' => 'B',
            'total_floors' => 2,
            'flats_per_floor' => 2,
        ]);

        $flat = Flat::create([
            'society_id' => $society->id,
            'wing_id' => $wing->id,
            'floor' => 1,
            'flat_number' => 101,
        ]);

        $residentRole = Role::where('name', 'resident')->first();
        $user = User::factory()->create([
            'society_id' => $society->id,
            'role_id' => $residentRole->id,
        ]);

        $resident = Resident::create([
            'user_id' => $user->id,
            'flat_id' => $flat->id,
            'resident_type' => 'tenant',
        ]);

        $complaint = Complaint::create([
            'user_id' => $user->id,
            'category' => 'water',
            'description' => 'Leaking tap',
            'status' => 'open',
        ]);

        $service = app(SocietyDeletionService::class);

        // Soft Delete Society
        $service->softDelete($society);

        $this->assertTrue($society->fresh()->trashed());
        $this->assertTrue($wing->fresh()->trashed());
        $this->assertTrue($flat->fresh()->trashed());
        $this->assertTrue($user->fresh()->fresh()->trashed());
        $this->assertTrue($resident->fresh()->trashed());
        $this->assertTrue($complaint->fresh()->trashed());

        // Restore Society
        $service->restore($society);

        $this->assertFalse($society->fresh()->trashed());
        $this->assertFalse($wing->fresh()->trashed());
        $this->assertFalse($flat->fresh()->trashed());
        $this->assertFalse($user->fresh()->trashed());
        $this->assertFalse($resident->fresh()->trashed());
        $this->assertFalse($complaint->fresh()->trashed());
    }
}
