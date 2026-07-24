<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\UpdateRolePermissionsRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;

class RolePermissionController extends Controller
{
    public function index()
    {
        $this->authorize('is-super-admin');

        $roles = Role::with('permissions')->get();
        $totalPermissionsCount = Permission::count();

        return view('admin.role-permissions.index', compact('roles', 'totalPermissionsCount'));
    }

    public function edit(Role $role)
    {
        $this->authorize('is-super-admin');

        $groupedPermissions = Permission::all()->groupBy('group');
        $assignedPermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.role-permissions.edit', compact('role', 'groupedPermissions', 'assignedPermissionIds'));
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role)
    {
        $this->authorize('is-super-admin');

        $permissionIds = $request->validated()['permissions'] ?? [];
        $role->permissions()->sync($permissionIds);

        ActivityLogger::log('role_permissions_updated', null, "Updated default permissions for role: {$role->name}");

        return redirect()->route('admin.role-permissions.index')->with([
            'message' => "Default permissions for role '" . ucfirst($role->name) . "' updated successfully.",
            'status' => 'success',
        ]);
    }
}
