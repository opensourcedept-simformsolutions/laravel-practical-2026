<?php

namespace Tests\Feature;

use App\Jobs\ProcessBulkImport;
use App\Models\BulkImport;
use App\Models\Flat;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Models\Wing;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Society $society;

    protected Wing $wing;

    protected Flat $flat;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(RoleSeeder::class);

        // Setup databases
        $this->society = Society::factory()->create(['name' => 'Paradise Haven']);

        $adminRole = Role::where('name', 'admin')->first();
        $this->admin = User::factory()->create([
            'society_id' => $this->society->id,
            'role_id' => $adminRole->id,
        ]);

        $this->wing = Wing::create([
            'society_id' => $this->society->id,
            'name' => 'A',
            'total_floors' => 2,
            'flats_per_floor' => 2,
        ]);

        $this->flat = new Flat([
            'society_id' => $this->society->id,
            'wing_id' => $this->wing->id,
            'floor' => 1,
            'flat_number' => '101',
        ]);
        $this->flat->wing = 'A';
        $this->flat->save();
    }

    public function test_admin_can_access_import_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('residents.import.form'));

        $response->assertStatus(200);
        $response->assertViewIs('residents.import');
    }

    public function test_admin_can_upload_csv_file(): void
    {
        Storage::fake('local');

        $csvContent = "Name,Email,Phone,Wing Name,Flat Number,Resident Type\n".
                      "Alice Johnson,alice.johnson@example.com,9988776655,A,101,owner\n";

        $file = UploadedFile::fake()->createWithContent('residents.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('residents.import.upload'), [
            'csv_file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('residents.import_map');
        $response->assertViewHas('headers', ['Name', 'Email', 'Phone', 'Wing Name', 'Flat Number', 'Resident Type']);

        $this->assertDatabaseHas('bulk_imports', [
            'user_id' => $this->admin->id,
            'original_filename' => 'residents.csv',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_map_headers_and_see_validation_preview(): void
    {
        Storage::fake('local');

        // Create the import record
        $filename = 'test_import.csv';
        $bulkImport = BulkImport::create([
            'user_id' => $this->admin->id,
            'filename' => $filename,
            'original_filename' => 'residents.csv',
            'status' => 'pending',
        ]);

        $csvContent = "Name,Email,Phone,Wing Name,Flat Number,Resident Type\n".
                      "Alice Johnson,alice.johnson@example.com,9988776655,A,101,owner\n".
                      "Invalid Guy,invalid.email,9988776644,Z,999,tenant\n";

        Storage::put('temp_bulk_uploads/'.$filename, $csvContent);

        $response = $this->actingAs($this->admin)->post(route('residents.import.map'), [
            'import_id' => $bulkImport->id,
            'map_name' => 'Name',
            'map_email' => 'Email',
            'map_phone' => 'Phone',
            'map_wing' => 'Wing Name',
            'map_flat_number' => 'Flat Number',
            'map_resident_type' => 'Resident Type',
            'default_resident_type' => 'tenant',
            'fallback_email_action' => 'generate',
            'fallback_phone_action' => 'generate',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('residents.import_preview');
        $response->assertViewHas('showEditor', true);
        $response->assertViewHas('isValid', false);
        $response->assertViewHas('invalidCount', 1);

        $this->assertDatabaseHas('bulk_imports', [
            'id' => $bulkImport->id,
            'status' => 'validated',
        ]);
    }

    public function test_admin_can_download_error_report_csv(): void
    {
        Storage::fake('local');

        $bulkImport = BulkImport::create([
            'user_id' => $this->admin->id,
            'filename' => 'test_import.csv',
            'original_filename' => 'residents.csv',
            'status' => 'validated',
        ]);

        $validatedRows = [
            [
                'row_number' => 2,
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@example.com',
                'phone' => '9988776655',
                'wing' => 'A',
                'flat_number' => '101',
                'flat_id' => $this->flat->id,
                'resident_type' => 'owner',
                'valid' => true,
                'errors' => [],
            ],
            [
                'row_number' => 3,
                'name' => 'Invalid Guy',
                'email' => 'invalid.email',
                'phone' => '9988776644',
                'wing' => 'Z',
                'flat_number' => '999',
                'flat_id' => null,
                'resident_type' => 'tenant',
                'valid' => false,
                'errors' => ['Email format is invalid.', 'Wing Z does not exist.'],
            ],
        ];

        Storage::put('temp_bulk_uploads/validated_'.$bulkImport->id.'.json', json_encode($validatedRows));

        $response = $this->actingAs($this->admin)->get(route('residents.import.errors', $bulkImport->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=error_report_residents.csv');
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_process_import_dispatch_job(): void
    {
        Storage::fake('local');
        Queue::fake();

        $bulkImport = BulkImport::create([
            'user_id' => $this->admin->id,
            'filename' => 'test_import.csv',
            'original_filename' => 'residents.csv',
            'status' => 'validated',
        ]);

        $validatedRows = [
            [
                'row_number' => 2,
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@example.com',
                'phone' => '9988776655',
                'wing' => 'A',
                'flat_number' => '101',
                'flat_id' => $this->flat->id,
                'resident_type' => 'owner',
                'valid' => true,
                'errors' => [],
            ],
        ];

        Storage::put('temp_bulk_uploads/validated_'.$bulkImport->id.'.json', json_encode($validatedRows));

        $response = $this->actingAs($this->admin)->post(route('residents.import.process'), [
            'import_id' => $bulkImport->id,
            'society_id' => $this->society->id,
        ]);

        $response->assertRedirect(route('residents.index'));
        $response->assertSessionHas('message', 'Resident bulk import has been queued and will process in the background. You will receive a system notification once complete.');

        Queue::assertPushed(ProcessBulkImport::class, function ($job) use ($bulkImport) {
            return $job->bulkImportId === $bulkImport->id && $job->societyId === $this->society->id && $job->operatorId === $this->admin->id;
        });

        $this->assertDatabaseHas('bulk_imports', [
            'id' => $bulkImport->id,
            'status' => 'processing',
        ]);
    }
}
