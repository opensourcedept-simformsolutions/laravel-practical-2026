<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                    fn ($q) => $q->where(
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

            match ($request->get('filter', 'active')) {
                'deleted' => $query->onlyTrashed(),
                'all' => $query->withTrashed(),
                default => null,
            };

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn(
                    'role',
                    fn ($row) => ucfirst($row->role_name)
                )
                ->addColumn(
                    'society',
                    fn ($row) => $row->society_name ?? '-'
                )
                ->addColumn('actions', function ($row) {

                    $editUrl = route('admin.users.edit', $row->id);
                    $deleteUrl = route('admin.users.destroy', $row->id);
                    $impersonateUrl = route('impersonate.start', $row->id);
                    $restoreUrl = route('admin.users.restore', $row->id);

                    $html = '<div class="text-center d-flex justify-content-center gap-2">';

                    if (!$row->trashed()) {
                        $html .= '
                            <a href="'.$editUrl.'" class="btn btn-primary btn-sm text-white" title="Edit User">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        ';
                        
                        $html .= '
                            <form action="'.$impersonateUrl.'" method="POST" class="d-inline">
                                '.csrf_field().'
                                <button type="submit" class="btn btn-dark btn-sm text-white" title="Impersonate User">
                                    <i class="bi bi-person-check-fill"></i>
                                </button>
                            </form>
                        ';

                        $html .= '
                            <button class="btn btn-danger text-white btn-action btn-sm"
                                data-url="'.$deleteUrl.'"
                                data-method="DELETE"
                                data-title="Delete User?"
                                data-text="This user will be soft-deleted."
                                data-confirm="Yes, Delete"
                                data-success="User deleted successfully"
                                title="Delete User">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        ';
                    } else {
                        $html .= '
                            <button class="btn btn-secondary text-white btn-action btn-sm"
                                data-url="'.$restoreUrl.'"
                                data-method="PATCH"
                                data-title="Restore User?"
                                data-text="This user will be restored."
                                data-confirm="Yes, Restore"
                                title="Restore User">
                                <i class="bi bi-arrow-up-left-circle-fill"></i>
                            </button>
                        ';
                    }

                    $html .= '</div>';
                    return $html;
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

        try {
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
        } catch (Exception $e) {
            Log::error('User create page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with(['message' => 'Something went wrong.', 'status' => 'error']);
        }
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        try {
            $validated = $request->validated();

            if (auth()->user()->isAdmin()) {
                $validated['society_id'] = auth()->user()->society_id;
            }

            $user = User::create($validated);

            ActivityLogger::log('create', $user, "User {$user->name} (".($user->role?->name ?? 'unknown').') was created.');

            Session::flash('message', 'User created successfully.');
            Session::flash('status', 'success');

            return redirect()->route('admin.users.index');
        } catch (Exception $e) {
            Log::error('User store error: '.$e->getMessage(), ['exception' => $e]);

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        try {
            $roles = Role::whereIn('name', ['resident', 'admin', 'gatekeeper'])->get();

            $societies = auth()->user()->isSuperAdmin()
                ? Society::orderBy('name')->get()
                : collect();

            return view(
                'admin.users.edit',
                compact('user', 'roles', 'societies')
            );
        } catch (Exception $e) {
            Log::error('User edit page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with(['message' => 'Something went wrong.', 'status' => 'error']);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        try {
            $validated = $request->validated();

            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            if (auth()->user()->isAdmin()) {
                unset($validated['society_id']);
            }

            $user->update($validated);

            ActivityLogger::log('update', $user, "User {$user->name} was updated.");

            Session::flash('message', 'User updated successfully.');
            Session::flash('status', 'success');

            return redirect()->route('admin.users.index');
        } catch (Exception $e) {
            Log::error('User update error: '.$e->getMessage(), ['exception' => $e]);

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            ActivityLogger::log('delete', $user, "User {$user->name} was deleted.");
            $user->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully.',
                ]);
            }

            Session::flash('message', 'User deleted successfully.');
            Session::flash('status', 'success');

            return redirect()->route('admin.users.index');
        } catch (Exception $e) {
            Log::error('User delete error: '.$e->getMessage(), ['exception' => $e]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user.',
                ], 500);
            }

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back();
        }
    }

    public function restore(User $user)
    {
        $this->authorize('restore', $user);

        try {
            $user->restore();

            ActivityLogger::log('restore', $user, 'User restored.');

            return response()->json([
                'success' => true,
                'message' => 'User restored successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('User restore error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore user.',
            ]);
        }
    }
}
