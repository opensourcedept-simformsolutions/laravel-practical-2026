<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_super_admin_can_view_and_update_role_default_permissions()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $residentRole = Role::where('name', 'resident')->first();
        $permission = Permission::where('slug', 'users.view')->first();

        // Super Admin views role default list
        $response = $this->actingAs($superAdmin)->get(route('admin.role-permissions.index'));
        $response->assertStatus(200);

        // Super Admin views edit page for resident role
        $response = $this->actingAs($superAdmin)->get(route('admin.role-permissions.edit', $residentRole));
        $response->assertStatus(200);

        // Assign users.view permission to resident role
        $response = $this->actingAs($superAdmin)->put(route('admin.role-permissions.update', $residentRole), [
            'permissions' => [$permission->id],
        ]);

        $response->assertRedirect(route('admin.role-permissions.index'));
        $this->assertTrue($residentRole->fresh()->permissions->contains('id', $permission->id));
    }

    public function test_non_super_admin_cannot_access_role_permissions_management()
    {
        $admin = User::factory()->admin()->create();
        $residentRole = Role::where('name', 'resident')->first();

        $response = $this->actingAs($admin)->get(route('admin.role-permissions.index'));
        $this->assertTrue($response->status() === 403 || $response->status() === 302);

        $response = $this->actingAs($admin)->get(route('admin.role-permissions.edit', $residentRole));
        $this->assertTrue($response->status() === 403 || $response->status() === 302);
    }
}
