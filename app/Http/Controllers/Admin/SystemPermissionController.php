<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StoreSystemPermissionRequest;
use App\Http\Requests\Permission\UpdateSystemPermissionRequest;
use App\Models\Permission;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SystemPermissionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('is-super-admin');

        $query = Permission::withCount(['roles', 'users']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('group', 'like', "%{$search}%");
            });
        }

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        $permissions = $query->latest()->paginate(15)->withQueryString();
        $groups = Permission::distinct()->pluck('group');

        return view('admin.system-permissions.index', compact('permissions', 'groups'));
    }

    public function create()
    {
        $this->authorize('is-super-admin');

        $existingGroups = Permission::distinct()->pluck('group');

        return view('admin.system-permissions.create', compact('existingGroups'));
    }

    public function store(StoreSystemPermissionRequest $request)
    {
        $this->authorize('is-super-admin');

        $validated = $request->validated();
        $groupInput = trim($validated['group']);
        $existingGroup = Permission::whereRaw('LOWER(`group`) = ?', [strtolower($groupInput)])->value('group');
        $finalGroup = $existingGroup ?: ucwords(strtolower($groupInput));

        $permission = Permission::create([
            'name' => $validated['name'],
            'slug' => strtolower($validated['slug']),
            'group' => $finalGroup,
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLogger::log('permission_created', null, "Created new system permission: {$permission->name} ({$permission->slug})");

        return redirect()->route('admin.system-permissions.index')->with([
            'message' => "System permission '{$permission->name}' created successfully.",
            'status' => 'success',
        ]);
    }

    public function edit(Permission $permission)
    {
        $this->authorize('is-super-admin');

        $existingGroups = Permission::distinct()->pluck('group');

        return view('admin.system-permissions.edit', [
            'permission' => $permission,
            'existingGroups' => $existingGroups,
        ]);
    }

    public function update(UpdateSystemPermissionRequest $request, Permission $permission)
    {
        $this->authorize('is-super-admin');

        $validated = $request->validated();
        $groupInput = trim($validated['group']);
        $existingGroup = Permission::whereRaw('LOWER(`group`) = ?', [strtolower($groupInput)])->value('group');
        $finalGroup = $existingGroup ?: ucwords(strtolower($groupInput));

        $permission->update([
            'name' => $validated['name'],
            'slug' => strtolower($validated['slug']),
            'group' => $finalGroup,
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLogger::log('permission_updated', null, "Updated system permission: {$permission->name}");

        return redirect()->route('admin.system-permissions.index')->with([
            'message' => "System permission '{$permission->name}' updated successfully.",
            'status' => 'success',
        ]);
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('is-super-admin');

        $assignedRolesCount = $permission->roles()->count();
        $assignedUsersCount = $permission->users()->count();

        if ($assignedRolesCount > 0 || $assignedUsersCount > 0) {
            return redirect()->back()->with([
                'message' => "Cannot delete permission '{$permission->name}' because it is currently assigned to {$assignedRolesCount} role(s) and {$assignedUsersCount} user(s). Please unassign it from all roles and users first.",
                'status' => 'error',
            ]);
        }

        $permissionName = $permission->name;
        $permission->delete();

        ActivityLogger::log('permission_deleted', null, "Deleted system permission: {$permissionName}");

        return redirect()->route('admin.system-permissions.index')->with([
            'message' => "System permission '{$permissionName}' deleted successfully.",
            'status' => 'success',
        ]);
    }
}
