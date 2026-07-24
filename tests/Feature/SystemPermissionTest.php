<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_super_admin_can_create_new_system_permission()
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.system-permissions.store'), [
            'name' => 'Custom Maintenance Access',
            'slug' => 'maintenance.custom_access',
            'group' => 'Maintenance',
            'description' => 'Allows managing custom maintenance settings.',
        ]);

        $response->assertRedirect(route('admin.system-permissions.index'));
        $this->assertDatabaseHas('permissions', [
            'slug' => 'maintenance.custom_access',
            'name' => 'Custom Maintenance Access',
        ]);
    }

    public function test_super_admin_can_update_system_permission()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $permission = Permission::first();

        $response = $this->actingAs($superAdmin)->put(route('admin.system-permissions.update', $permission), [
            'name' => 'Updated Permission Name',
            'slug' => $permission->slug,
            'group' => $permission->group,
            'description' => 'Updated description content.',
        ]);

        $response->assertRedirect(route('admin.system-permissions.index'));
        $this->assertEquals('Updated Permission Name', $permission->fresh()->name);
    }

    public function test_cannot_delete_permission_assigned_to_roles_or_users()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $permission = Permission::where('slug', 'passes.create')->first();

        // Check that passes.create is assigned to resident role
        $this->assertGreaterThan(0, $permission->roles()->count());

        $response = $this->actingAs($superAdmin)->delete(route('admin.system-permissions.destroy', $permission));
        $response->assertRedirect();
        $response->assertSessionHas('status', 'error');

        // Verify permission was NOT deleted
        $this->assertDatabaseHas('permissions', ['id' => $permission->id]);
    }

    public function test_can_delete_unassigned_permission()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $permission = Permission::create([
            'name' => 'Unassigned Perm',
            'slug' => 'unassigned.test_perm',
            'group' => 'Test',
        ]);

        $response = $this->actingAs($superAdmin)->delete(route('admin.system-permissions.destroy', $permission));
        $response->assertRedirect(route('admin.system-permissions.index'));

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }
}
