<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ResidentWelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Resident::class);

        if ($request->ajax()) {

            $query = Resident::with([
                'user' => function ($q) {
                    $q->withTrashed();
                },
                'flat',
            ]);

            if (! auth()->user()->isSuperAdmin()) {
                $query->whereHas('flat', function ($q) {
                    $q->where('society_id', auth()->user()->society_id);
                });
            }

            return DataTables::of($query)

                ->addColumn('name', fn($row) => $row->user?->name ?? '-')
                ->addColumn('email', fn($row) => $row->user?->email ?? '-')
                ->addColumn('phone', fn($row) => $row->user?->phone ?? '-')

                ->addColumn('flat', fn($row) => $row->flat?->flat_number ?? '-')
                ->addColumn('wing', fn($row) => $row->flat?->wing ?? '-')

                ->addColumn('type', function ($row) {
                    return $row->resident_type === 'owner'
                        ? '<span class="badge bg-success">Owner</span>'
                        : '<span class="badge bg-info">Tenant</span>';
                })

                ->addColumn('actions', function ($row) {

                    $editUrl = route('residents.edit', $row->id);
                    $deleteUrl = route('residents.destroy', $row->id);

                    return '
                    <a href="' . $editUrl . '" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>

                    <form action="' . $deleteUrl . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm(\'Delete this resident?\')">
                              <i class="bi bi-trash"></i>
                        </button>
                    </form>
                ';
                })

                ->rawColumns(['type', 'actions'])
                ->make(true);
        }

        return view('residents.index');
    }

    public function create()
    {
        $flats = Flat::where(
            'society_id',
            auth()->user()->society_id
        )->get();

        return view('residents.create', compact('flats'));
    }

    public function store(StoreResidentRequest $request)
    {
        $this->authorize('create', Resident::class);

        try {

            $data = $request->validated();

            $user = null;

            $residentRoleId = Role::where(
                'name',
                'resident'
            )->value('id');

            DB::transaction(function () use (
                $data,
                $residentRoleId,
                &$user
            ) {

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => Str::password(32),
                    'role_id' => $residentRoleId,
                    'society_id' => auth()->user()->society_id,
                ]);

                Resident::create([
                    'user_id' => $user->id,
                    'flat_id' => $data['flat_id'],
                    'resident_type' => $data['resident_type'],
                ]);
            });

            DB::afterCommit(function () use ($user) {

                if ($user) {
                    $user->notify(
                        new ResidentWelcomeNotification($user)
                    );
                }
            });

            Session::flash('message', 'Resident created successfully.');
            Session::flash('status', 'success');

            return redirect()->route('residents.index');
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            Session::flash('message', 'Unable to create resident.' . $e->getMessage());
            Session::flash('status', 'error');

            return back()->withInput();
        }
    }

    public function edit(Resident $resident)
    {
        $this->authorize('update', $resident);

        $flats = Flat::where(
            'society_id',
            auth()->user()->society_id
        )->get();

        return view('residents.edit', compact('resident', 'flats'));
    }

    public function update(
        UpdateResidentRequest $request,
        Resident $resident
    ) {
        $this->authorize('update', $resident);

        try {

            $data = $request->validated();

            DB::transaction(function () use (
                $resident,
                $data
            ) {

                $resident->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ]);

                $resident->update([
                    'flat_id' => $data['flat_id'],
                    'resident_type' => $data['resident_type'],
                ]);
            });

            Session::flash('message', 'Resident updated successfully.');
            Session::flash('status', 'success');

            return redirect()->route('residents.index');
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            Session::flash('message', 'Unable to update resident.');
            Session::flash('status', 'error');

            return back()->withInput();
        }
    }

    public function destroy(Resident $resident)
    {
        $this->authorize('delete', $resident);

        try {

            DB::transaction(function () use ($resident) {

                $user = $resident->user;
                $this->authorize('create', Resident::class);

                $resident->delete();

                if ($user) {
                    $user->delete();
                }
            });

            return redirect()
                ->route('residents.index')
                ->with([
                    'message' => 'Resident deleted successfully.',
                    'status' => 'success',
                ]);
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            return back()->with([
                'message' => 'Unable to delete resident.',
                'status' => 'error',
            ]);
        }
    }
}
