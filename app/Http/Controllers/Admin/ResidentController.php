<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreResidentRequest;
use App\Http\Requests\Resident\UpdateResidentRequest;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Notifications\ResidentWelcomeNotification;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', resident::class);

        try {

            if ($request->ajax()) {

                $user = auth()->user();

                $query = Resident::query()
                    ->select([
                        'residents.*',
                        'users.name as user_name',
                        'users.email as user_email',
                        'users.phone as user_phone',
                        'flats.flat_number as flat_number',
                        'flats.wing as flat_wing',
                    ])
                    ->leftJoin('users', 'residents.user_id', '=', 'users.id')
                    ->leftJoin('flats', 'residents.flat_id', '=', 'flats.id');

                if (! $user->isSuperAdmin()) {
                    $query->where('flats.society_id', $user->society_id);
                }

                if ($user->isSuperAdmin() && $request->filled('society_id')) {
                    $query->where('flats.society_id', $request->society_id);
                }
                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    match ($request->get('filter', 'active')) {
                        'deleted' => $query->onlyTrashed(),
                        'all' => $query->withTrashed(),
                        default => null,
                    };
                }

                return DataTables::of($query)
                    ->addIndexColumn()

                    ->addColumn('name', fn ($row) => $row->user_name ?? '-')
                    ->addColumn('email', fn ($row) => $row->user_email ?? '-')
                    ->addColumn('phone', fn ($row) => $row->user_phone ?? '-')

                    ->addColumn('flat', fn ($row) => $row->flat_number ?? '-')
                    ->addColumn('wing', fn ($row) => $row->flat_wing ?? '-')

                    ->addColumn('type', function ($row) {
                        return $row->resident_type === 'owner'
                            ? '<span class="badge bg-success">Owner</span>'
                            : '<span class="badge bg-info">Tenant</span>';
                    })

                    ->addColumn('actions', function ($row) {
 
                        $editUrl = route('residents.edit', $row->id);
                        $deleteUrl = route('residents.destroy', $row->id);
                        $restore = route('residents.restore', $row->id);
 
                        $html = '';
                        if (! $row->trashed()) {
                            $html .= '
                                  <a href="'.$editUrl.'" class="btn btn-primary btn-sm" title="Edit Resident"><i class="bi bi-pencil-square"></i></a>

                                  <button
                                      class="btn btn-danger btn-action"
                                      data-url="'.$deleteUrl.'"
                                      data-method="DELETE"
                                      data-title="Delete Resident Details?"
                                      data-text="This action cannot be undone."
                                      data-confirm="Yes, Delete"
                                      data-success="Resident deleted successfully"
                                      title="Delete Flat">
                                      <i class="bi bi-trash"></i>
                                  </button>
                            ';
                        } else {
                            $html .= '
                                  <button
                                      class="btn btn-info btn-action"
                                      data-url="'.$restore.'"
                                      data-method="PATCH"
                                      data-title="Restore Delivery?"
                                      data-text="This delivery will be restored."
                                      data-confirm="Yes, Restore"
                                      title="Restore Delivery">
                                          <i class="bi bi-arrow-counterclockwise"></i>
                                  </button>
                            ';
                        }
                      
                        return $html;
                    })

                    ->rawColumns(['type', 'actions'])
                    ->make(true);
            }
          
            $societies = auth()->user()->isSuperAdmin()
                ? Society::orderBy('name')->get()
                : collect();

            return view('residents.index', compact('societies'));

        } catch (Exception $e) {

            Log::error('Resident listing error: '.$e->getMessage());

            return back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function create()
    {
        $this->authorize('create', resident::class);

        try {
            $user = auth()->user();

            $societies = $user->isSuperAdmin()
                ? Society::all()
                : collect();

            $flats = $user->isSuperAdmin()
                ? collect()
                : Flat::where('society_id', $user->society_id)->get();

            return view('residents.create', compact('societies', 'flats'));
        } catch (Throwable $e) {
            Log::error('Resident create page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function store(StoreResidentRequest $request)
    {
        $this->authorize('create', resident::class);

        try {
            $data = $request->validated();

            $user = null;
            $resident = null;

            $residentRoleId = Role::where('name', 'resident')->value('id');

            $societyId = auth()->user()->isSuperAdmin()
                ? $data['society_id']
                : auth()->user()->society_id;

            DB::transaction(function () use (
                $data,
                $residentRoleId,
                &$user,
                &$resident,
                $societyId
            ) {

                $flatBelongsToSociety = Flat::where('id', $data['flat_id'])
                    ->where('society_id', $societyId)
                    ->exists();

                if (! $flatBelongsToSociety) {
                    throw new Exception('Invalid flat for selected society.');
                }

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => Str::password(32),
                    'role_id' => $residentRoleId,
                    'society_id' => $societyId,
                ]);

                $resident = resident::create([
                    'user_id' => $user->id,
                    'flat_id' => $data['flat_id'],
                    'resident_type' => $data['resident_type'],
                ]);
            });

            if ($resident) {
                ActivityLogger::log('create', $resident, "Resident {$resident->user->name} was added to flat {$resident->flat->wing}-{$resident->flat->flat_number}.");
            }

            DB::afterCommit(function () use ($user) {
                if ($user) {
                    $user->notify(new ResidentWelcomeNotification($user));
                }
            });

            Session::flash('message', 'Resident created successfully.');
            Session::flash('status', 'success');

            return redirect()->route('residents.index');
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            Session::flash('message', 'Unable to create resident.'.$e->getMessage());
            Session::flash('status', 'error');

            return back()->withInput();
        }
    }

    public function edit(resident $resident)
    {
        $this->authorize('update', $resident);

        try {
            $user = auth()->user();

            $societies = $user->isSuperAdmin()
                ? Society::all()
                : collect();

            $flats = Flat::where(
                'society_id',
                $resident->flat->society_id
            )->get();

            return view('residents.edit', compact('resident', 'flats', 'societies'));
        } catch (Throwable $e) {
            Log::error('Resident edit page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function update(
        UpdateResidentRequest $request,
        resident $resident
    ) {
        $this->authorize('update', $resident);

        try {

            $data = $request->validated();

            DB::transaction(function () use (
                $resident,
                $data
            ) {
                $flat = Flat::findOrFail($data['flat_id']);

                $resident->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'society_id' => $flat->society_id,
                ]);

                $resident->update([
                    'flat_id' => $data['flat_id'],
                    'resident_type' => $data['resident_type'],
                ]);
            });

            Session::flash('message', 'Resident updated successfully.');
            Session::flash('status', 'success');

            ActivityLogger::log('update', $resident, "Resident {$resident->user->name} details were updated.");

            return redirect()->route('residents.index');
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            Session::flash('message', 'Unable to update resident.');
            Session::flash('status', 'error');

            return back()->withInput();
        }
    }

    public function destroy(resident $resident)
    {
        $this->authorize('delete', $resident);

        try {

            DB::transaction(function () use ($resident) {

                $user = $resident->user;
                $this->authorize('delete', resident::class);

                ActivityLogger::log('delete', $resident, "Resident {$resident->user->name} was removed.");

                $resident->delete();
            });

            return response()->json([
                'message' => 'Resident deleted successfully.',
                'success' => true,
            ]);
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function restore(Resident $resident)
    {
        $this->authorize('restore', $resident);

        try {

            $resident->restore();

            ActivityLogger::log('restore', $resident, 'resident restored.');

            return response()->json([
                'success' => true,
                'message' => 'Resident restored successfully.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore resident.',
            ]);
        }
    }
}
