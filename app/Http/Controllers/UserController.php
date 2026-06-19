<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        if ($request->ajax()) {

            $query = User::with('role')
                ->select([
                    'users.*',
                    'roles.name as role_name',
                ])
                ->leftJoin(
                    'roles',
                    'users.role_id',
                    '=',
                    'roles.id'
                )
                ->where(
                    'society_id',
                    auth()->user()->society_id
                )
                ->where('users.id', '!=', auth()->id())
                ->whereHas('role', function ($q) {
                    $q->where('name', '!=', 'super_admin');
                });

            return DataTables::of($query)

                ->addColumn(
                    'role',
                    fn ($row) => ucfirst($row->role_name)
                )
                ->addColumn('actions', function ($row) {

                    $editUrl = route('admin.users.edit', $row->id);
                    $deleteUrl = route('admin.users.destroy', $row->id);

                    return '
                <a href="'.$editUrl.'" class="btn btn-warning btn-sm">
                    Edit
                </a>

                <form action="'.$deleteUrl.'"
                      method="POST"
                      class="d-inline">

                    '.csrf_field().'
                    '.method_field('DELETE').'

                    <button
                        class="btn btn-danger btn-sm"
                        onclick="return confirm(\'Delete this user?\')">
                        Delete
                    </button>

                </form>
            ';
                })

                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.users.index');
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
        Session::flash(
            'message',
            'User created successfully.'
        );

        Session::flash(
            'status',
            'success'
        );

        return redirect()
            ->route('admin.users.index');

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

        Session::flash(
            'message',
            'User updated successfully.'
        );

        Session::flash(
            'status',
            'success'
        );

        return redirect()
            ->route('admin.users.index');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        Session::flash(
            'message',
            'User deleted successfully.'
        );

        Session::flash(
            'status',
            'success'
        );

        return redirect()
            ->route('admin.users.index');
    }
}
