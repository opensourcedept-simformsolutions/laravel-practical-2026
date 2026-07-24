<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\UpdateUserPermissionsRequest;
use App\Models\Permission;
use App\Models\Society;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('permissions.manage');

        $currentUser = auth()->user();
        $query = User::with(['role', 'society', 'permissions']);

        // Scope by society if user is a Society Admin
        if (! $currentUser->isSuperAdmin()) {
            $query->where('society_id', $currentUser->society_id)
                  ->whereHas('role', function ($q) {
                      $q->where('name', '!=', 'super_admin');
                  });
        } elseif ($currentUser->isSuperAdmin() && $request->filled('society_id')) {
            $query->where('society_id', $request->society_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $societies = $currentUser->isSuperAdmin() ? Society::all() : collect();

        return view('admin.permissions.index', compact('users', 'societies'));
    }

    public function edit(User $user)
    {
        $this->authorize('permissions.manage');

        $currentUser = auth()->user();

        // Security check: Admins cannot edit their own permissions
        if ($user->id === $currentUser->id && ! $currentUser->isSuperAdmin()) {
            return redirect()->route('admin.permissions.index')->with([
                'message' => 'You cannot manage your own permissions.',
                'status' => 'error',
            ]);
        }

        // Society Admin can only edit users in their society and cannot edit Super Admins
        if (! $currentUser->isSuperAdmin()) {
            if ($user->society_id !== $currentUser->society_id || $user->isSuperAdmin()) {
                abort(403, 'You do not have permission to manage permissions for this user.');
            }
        }

        $allPermissions = Permission::all();

        // If not super admin, determine which permissions the current admin possesses so they can only delegate what they have
        $allowedPermissionIds = $currentUser->isSuperAdmin()
            ? $allPermissions->pluck('id')->toArray()
            : $allPermissions->filter(fn ($p) => $currentUser->hasPermission($p->slug))->pluck('id')->toArray();

        $groupedPermissions = $allPermissions->groupBy('group');
        $userDirectPermissions = $user->permissions->keyBy('id');
        $rolePermissionIds = $user->role ? $user->role->permissions->pluck('id')->toArray() : [];

        return view('admin.permissions.edit', compact(
            'user',
            'groupedPermissions',
            'userDirectPermissions',
            'rolePermissionIds',
            'allowedPermissionIds'
        ));
    }

    public function update(UpdateUserPermissionsRequest $request, User $user)
    {
        $this->authorize('permissions.manage');

        $currentUser = auth()->user();

        // Security check: Admins cannot edit their own permissions
        if ($user->id === $currentUser->id && ! $currentUser->isSuperAdmin()) {
            return redirect()->route('admin.permissions.index')->with([
                'message' => 'You cannot manage your own permissions.',
                'status' => 'error',
            ]);
        }

        if (! $currentUser->isSuperAdmin()) {
            if ($user->society_id !== $currentUser->society_id || $user->isSuperAdmin()) {
                abort(403, 'You do not have permission to manage permissions for this user.');
            }
        }

        $allPermissions = Permission::all();
        $allowedPermissionIds = $currentUser->isSuperAdmin()
            ? $allPermissions->pluck('id')->toArray()
            : $allPermissions->filter(fn ($p) => $currentUser->hasPermission($p->slug))->pluck('id')->toArray();

        $permissionData = $request->validated()['permissions'] ?? [];
        $existingDirect = $user->permissions->keyBy('id');
        $syncData = [];

        // Retain existing direct permissions for any items the current admin is NOT allowed to change
        foreach ($existingDirect as $permId => $pivotRecord) {
            if (! in_array($permId, $allowedPermissionIds)) {
                $syncData[$permId] = ['is_granted' => $pivotRecord->pivot->is_granted];
            }
        }

        // Apply new changes only for permissions the admin possesses
        foreach ($permissionData as $permissionId => $setting) {
            if (in_array($permissionId, $allowedPermissionIds)) {
                if ($setting === 'granted') {
                    $syncData[$permissionId] = ['is_granted' => true];
                } elseif ($setting === 'revoked') {
                    $syncData[$permissionId] = ['is_granted' => false];
                }
            }
        }

        $user->permissions()->sync($syncData);

        ActivityLogger::log('permissions_updated', $user, "Updated permissions for user {$user->name}");

        return redirect()->route('admin.permissions.index')->with([
            'message' => "Permissions for {$user->name} updated successfully.",
            'status' => 'success',
        ]);
    }

    public function reset(User $user)
    {
        $this->authorize('permissions.manage');

        $currentUser = auth()->user();

        if ($user->id === $currentUser->id && ! $currentUser->isSuperAdmin()) {
            return redirect()->route('admin.permissions.index')->with([
                'message' => 'You cannot manage your own permissions.',
                'status' => 'error',
            ]);
        }

        if (! $currentUser->isSuperAdmin()) {
            if ($user->society_id !== $currentUser->society_id || $user->isSuperAdmin()) {
                abort(403, 'You do not have permission to reset permissions for this user.');
            }
        }

        $user->permissions()->detach();

        ActivityLogger::log('permissions_reset', $user, "Reset permissions to role defaults for user {$user->name}");

        return redirect()->route('admin.permissions.index')->with([
            'message' => "Permissions for {$user->name} reset to role defaults.",
            'status' => 'success',
        ]);
    }
}
