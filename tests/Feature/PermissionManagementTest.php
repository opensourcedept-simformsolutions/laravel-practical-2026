<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_user_inherits_role_permissions_by_default()
    {
        $residentRole = Role::where('name', 'resident')->first();
        $user = User::factory()->create(['role_id' => $residentRole->id]);

        $this->assertTrue($user->hasPermission('passes.create'));
        $this->assertFalse($user->hasPermission('users.create'));
    }

    public function test_super_admin_has_all_permissions()
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $user = User::factory()->create(['role_id' => $superAdminRole->id]);

        $this->assertTrue($user->hasPermission('any.random.permission'));
        $this->assertTrue($user->hasPermission('societies.delete'));
    }

    public function test_direct_user_permission_grant_and_revocation()
    {
        $residentRole = Role::where('name', 'resident')->first();
        $user = User::factory()->create(['role_id' => $residentRole->id]);

        // Direct grant (user originally cannot create users)
        $user->givePermissionTo('users.create');
        $this->assertTrue($user->hasPermission('users.create'));

        // Direct revocation (user originally can create passes)
        $user->revokePermissionTo('passes.create');
        $this->assertFalse($user->hasPermission('passes.create'));
    }

    public function test_super_admin_can_access_permission_management_ui()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($superAdmin)->get(route('admin.permissions.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($superAdmin)->get(route('admin.permissions.edit', $targetUser));
        $response->assertStatus(200);

        $permission = Permission::where('slug', 'users.create')->first();

        $response = $this->actingAs($superAdmin)->put(route('admin.permissions.update', $targetUser), [
            'permissions' => [
                $permission->id => 'granted',
            ],
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $this->assertTrue($targetUser->fresh()->hasPermission('users.create'));
    }

    public function test_society_admin_can_only_manage_their_society_users()
    {
        $society1 = Society::factory()->create();
        $society2 = Society::factory()->create();

        $admin1 = User::factory()->admin()->create(['society_id' => $society1->id]);
        $user1 = User::factory()->resident()->create(['society_id' => $society1->id]);
        $user2 = User::factory()->resident()->create(['society_id' => $society2->id]);

        // Society admin can edit user in same society
        $response = $this->actingAs($admin1)->get(route('admin.permissions.edit', $user1));
        $response->assertStatus(200);

        // Society admin cannot edit user in different society
        $response = $this->actingAs($admin1)->get(route('admin.permissions.edit', $user2));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_edit_their_own_permissions()
    {
        $society = Society::factory()->create();
        $admin = User::factory()->admin()->create(['society_id' => $society->id]);

        $response = $this->actingAs($admin)->get(route('admin.permissions.edit', $admin));
        $response->assertRedirect(route('admin.permissions.index'));
        $response->assertSessionHas('status', 'error');
    }

    public function test_admin_cannot_grant_permission_they_do_not_possess()
    {
        $society = Society::factory()->create();
        $admin = User::factory()->admin()->create(['society_id' => $society->id]);
        $targetUser = User::factory()->resident()->create(['society_id' => $society->id]);

        // Admin lacks 'societies.delete' permission
        $this->assertFalse($admin->hasPermission('societies.delete'));

        $permission = Permission::where('slug', 'societies.delete')->first();

        // Admin attempts to grant 'societies.delete' to target user
        $response = $this->actingAs($admin)->put(route('admin.permissions.update', $targetUser), [
            'permissions' => [
                $permission->id => 'granted',
            ],
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        // Target user should NOT receive the permission
        $this->assertFalse($targetUser->fresh()->hasPermission('societies.delete'));
    }

    public function test_user_with_societies_view_permission_can_access_societies_ui()
    {
        $society = Society::factory()->create();
        $user = User::factory()->admin()->create(['society_id' => $society->id]);

        // Grant societies.view directly
        $user->givePermissionTo('societies.view');

        $response = $this->actingAs($user)->get(route('societies.index'));
        $response->assertStatus(200);
    }
}
