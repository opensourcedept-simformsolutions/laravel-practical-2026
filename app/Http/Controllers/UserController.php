<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        if ($request->ajax()) {

            $query = User::with([
                'role',
                'society',
            ])
                ->select([
                    'users.*',
                    'roles.name as role_name',
                    'societies.name as society_name',
                ])
                ->leftJoin(
                    'roles',
                    'users.role_id',
                    '=',
                    'roles.id'
                )
                ->leftJoin(
                    'societies',
                    'users.society_id',
                    '=',
                    'societies.id'
                )
                ->where('users.id', '!=', auth()->id())
                ->whereHas('role', function ($q) {
                    $q->where('name', '!=', 'super_admin');
                });

            if (! auth()->user()->isSuperAdmin()) {

                $query->where(
                    'users.society_id',
                    auth()->user()->society_id
                );
            }
            if ($request->filled('role')) {

                $query->whereHas(
                    'role',
                    fn($q) => $q->where(
                        'name',
                        $request->role
                    )
                );
            }
            if (
                auth()->user()->isSuperAdmin()
                && $request->filled('society_id')
            ) {

                $query->where(
                    'users.society_id',
                    $request->society_id
                );
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn(
                    'role',
                    fn($row) => ucfirst($row->role_name)
                )
                ->addColumn(
                    'society',
                    fn($row) => $row->society_name ?? '-'
                )
                ->addColumn('actions', function ($row) {

                    $editUrl = route('admin.users.edit', $row->id);
                    $deleteUrl = route('admin.users.destroy', $row->id);

                    return '
                    <div class="text-center">
                <a href="' . $editUrl . '" class="btn btn-warning btn-sm">
                   Edit <i class="bi bi-pencil-square"></i>
                </a>

                <form action="' . $deleteUrl . '"
                      method="POST"
                      class="d-inline">

                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '

                    <button
                        class="btn btn-danger btn-sm"
                        onclick="return confirm(\'Delete this user?\')">
                        <i class="bi bi-trash"></i>
                    </button>

                </form>
            </div>';
                })

                ->rawColumns(['actions'])
                ->make(true);
        }

        $roles = Role::whereNotIn('name', [
            'super_admin',
        ])->get();

        $societies = auth()->user()->isSuperAdmin()
            ? Society::orderBy('name')->get()
            : collect();

        return view(
            'admin.users.index',
            compact(
                'roles',
                'societies'
            )
        );
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::whereIn('name', ['admin', 'gatekeeper'])->get();

        $societies = auth()->user()->isSuperAdmin()
            ? Society::orderBy('name')->get()
            : collect();

        return view(
            'admin.users.create',
            compact(
                'roles',
                'societies'
            )
        );
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        if (auth()->user()->isAdmin()) {

            $validated['society_id'] =
                auth()->user()->society_id;
        }

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
        $this->authorize('update', $user);

        $roles = Role::whereIn('name', ['resident', 'admin', 'gatekeeper'])->get();

        $societies = auth()->user()->isSuperAdmin()
            ? Society::orderBy('name')->get()
            : collect();

        return view(
            'admin.users.edit',
            compact('user', 'roles', 'societies')
        );
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ) {
        $this->authorize('update', $user);

        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        if (auth()->user()->isAdmin()) {
            unset($validated['society_id']);
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
        $this->authorize('delete', $user);

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
