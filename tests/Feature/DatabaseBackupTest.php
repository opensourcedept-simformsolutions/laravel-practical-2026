<?php

namespace Tests\Feature;

use App\Models\DatabaseBackup;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_artisan_db_backup_command_creates_full_backup()
    {
        $this->artisan('db:backup --no-cloud')
            ->assertExitCode(0);

        $this->assertDatabaseHas('database_backups', [
            'table_name' => null,
            'disk' => 'local',
        ]);
    }

    public function test_artisan_db_backup_command_creates_single_table_backup()
    {
        $this->artisan('db:backup --table=users --no-cloud')
            ->assertExitCode(0);

        $this->assertDatabaseHas('database_backups', [
            'table_name' => 'users',
            'disk' => 'local',
        ]);
    }

    public function test_super_admin_can_access_database_backups_ui_and_see_tables()
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('admin.backups.index'));
        $response->assertStatus(200);
        $response->assertSee('users');
        $response->assertSee('societies');
        $response->assertSee('Full Database (All Tables)');
    }

    public function test_super_admin_can_create_backup_via_post_request()
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.backups.store'), [
            'table_name' => 'flats',
            'upload_cloud' => 0,
        ]);

        $response->assertRedirect(route('admin.backups.index'));
        $this->assertDatabaseHas('database_backups', [
            'table_name' => 'flats',
            'created_by' => $superAdmin->id,
        ]);
    }

    public function test_super_admin_can_restore_and_delete_backup()
    {
        $superAdmin = User::factory()->superAdmin()->create();

        // Create backup
        $this->actingAs($superAdmin)->post(route('admin.backups.store'), [
            'table_name' => 'roles',
            'upload_cloud' => 0,
        ]);

        $backup = DatabaseBackup::latest()->first();
        $this->assertNotNull($backup);

        // Restore backup
        $restoreResponse = $this->actingAs($superAdmin)->post(route('admin.backups.restore', $backup));
        $restoreResponse->assertRedirect(route('admin.backups.index'));

        // Delete backup
        $deleteResponse = $this->actingAs($superAdmin)->delete(route('admin.backups.destroy', $backup));
        $deleteResponse->assertRedirect(route('admin.backups.index'));

        $this->assertDatabaseMissing('database_backups', ['id' => $backup->id]);
    }

    public function test_non_super_admin_cannot_access_backups()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.backups.index'));
        $response->assertRedirect();
    }
}
