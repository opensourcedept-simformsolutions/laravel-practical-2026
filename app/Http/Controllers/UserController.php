<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with('role')
            ->where(
                'society_id',
                auth()->user()->society_id
            )
            ->get();

        return view(
            'admin.users.index',
            compact('users')
        );
    }

    public function create()
    {
        Gate::authorize('create', User::class);

        $roles = Role::whereIn('name', [
            'admin',
            'gatekeeper',
        ])->get();

        return view(
            'admin.users.create',
            compact('roles')
        );
    }

    public function store(StoreUserRequest $request)
    {
        Gate::authorize('create', User::class);

        $validated = $request->validated();

        $validated['society_id'] =
            auth()->user()->society_id;

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User created successfully'
            );
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        $roles = Role::whereIn('name', [
            'resident',
            'admin',
            'gatekeeper',
        ])->get();

        return view(
            'admin.users.edit',
            compact('user', 'roles')
        );
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ) {
        Gate::authorize('update', $user);

        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User updated successfully'
            );
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User deleted successfully'
            );
    }
}
