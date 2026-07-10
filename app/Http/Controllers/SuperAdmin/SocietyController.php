<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Society\StoreSocietyRequest;
use App\Http\Requests\Society\UpdateSocietyRequest;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Models\Wing;
use App\Notifications\ResidentWelcomeNotification;
use App\Services\ActivityLogger;
use App\Services\SocietyDeletionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class SocietyController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Society::class);

        return view('societies.index');
    }

    public function data(Request $request)
    {
        $this->authorize('viewAny', Society::class);

        try {

            $societies = Society::withTrashed();

            return DataTables::of($societies)

                ->addIndexColumn()

                ->editColumn('created_at', function ($society) {
                    return $society->created_at->format('d M Y');
                })

                ->addColumn('status', function ($society) {

                    return $society->deleted_at
                        ? '<span class="badge bg-danger">Deleted</span>'
                        : '<span class="badge bg-success">Active</span>';
                })

                ->addColumn('actions', function ($society) {

                    $actions = '<div class="d-flex justify-content-center gap-2">';

                    $actions .= '
                        <a href="'.route('societies.show', $society->id).'"
                            class="btn btn-info text-white btn-sm" title="View Society">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                    ';

                    if (! $society->trashed()) {

                        $actions .= '
                            <a href="'.route('societies.edit', $society->id).'"
                                class="btn btn-primary btn-sm text-white" title="Edit Society">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        ';

                        $actions .= '
                            <button
                                class="btn btn-danger text-white btn-delete-society btn-sm"
                                data-id="'.$society->id.'"
                                title="Delete Society">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        ';
                    } else {

                        $actions .= '
                            <button
                                class="btn btn-secondary text-white btn-action btn-sm"
                                data-url="'.route('societies.restore', $society->id).'"
                                data-method="PATCH"
                                data-title="Restore Society?"
                                data-text="Society will become active again."
                                data-confirm="Yes, Restore"
                                data-success="Society restored successfully"
                                title="Restore Society">
                                <i class="bi bi-arrow-up-left-circle-fill"></i>
                            </button>
                        ';
                    }

                    $actions .= '</div>';

                    return $actions;
                })

                ->rawColumns(['actions', 'status'])
                ->make(true);
        } catch (Exception $e) {

            Log::error($e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong while loading visitor logs.',
                ], 500);
            }
        }
    }

    public function create()
    {
        $this->authorize('create', Society::class);

        return view('societies.create');
    }

    public function store(StoreSocietyRequest $request)
    {
        $this->authorize('create', Society::class);

        DB::beginTransaction();
        try {
            $society = Society::create($request->safe()->only(['name', 'address', 'city', 'state', 'pincode']));

            $createdWings = [];
            $createdFlats = [];

            $wingsData = $request->input('wings', []);
            foreach ($wingsData as $wData) {
                $wing = Wing::create([
                    'society_id' => $society->id,
                    'name' => $wData['name'],
                    'total_floors' => $wData['total_floors'],
                    'flats_per_floor' => $wData['flats_per_floor'],
                ]);
                $createdWings[$wing->name] = $wing;

                $inserts = [];
                for ($f = 1; $f <= $wing->total_floors; $f++) {
                    for ($n = 1; $n <= $wing->flats_per_floor; $n++) {
                        $flatNumber = ($f * 100) + $n;
                        $inserts[] = [
                            'society_id' => $society->id,
                            'wing_id' => $wing->id,
                            'wing' => $wing->name,
                            'floor' => $f,
                            'flat_number' => $flatNumber,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (! empty($inserts)) {
                    Flat::insert($inserts);
                }

                $flats = Flat::where('wing_id', $wing->id)->get();
                foreach ($flats as $flat) {
                    $createdFlats["{$wing->name}-{$flat->flat_number}"] = $flat;
                }
            }

            $residentsData = $request->input('residents', []);
            $residentRoleId = Role::where('name', 'resident')->value('id');

            foreach ($residentsData as $rData) {
                $flatKey = "{$rData['wing']}-{$rData['flat_number']}";
                $flat = $createdFlats[$flatKey] ?? null;

                if ($flat) {
                    $user = User::create([
                        'name' => $rData['name'],
                        'email' => $rData['email'],
                        'phone' => $rData['phone'],
                        'password' => Str::password(32),
                        'role_id' => $residentRoleId,
                        'society_id' => $society->id,
                    ]);

                    Resident::create([
                        'user_id' => $user->id,
                        'flat_id' => $flat->id,
                        'resident_type' => $rData['resident_type'],
                    ]);

                    try {
                        $user->notify(new ResidentWelcomeNotification($user));
                    } catch (Throwable $e) {
                        Log::error("Failed to notify imported resident user {$user->id} during society creation: ".$e->getMessage());
                    }
                }
            }

            ActivityLogger::log('create', $society, "Society {$society->name} was created via wizard.");

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('societies.index'),
                    'message' => 'Society, wings, flats, and residents created successfully.',
                ]);
            }

            return redirect()
                ->route('societies.index')
                ->with([
                    'status' => 'success',
                    'message' => 'Society, wings, flats, and residents created successfully.',
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong: '.$e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->with([
                    'status' => 'error',
                    'message' => 'Something went wrong: '.$e->getMessage(),
                ]);
        }
    }

    public function show(Society $society)
    {
        $this->authorize('view', $society);

        try {

            $society = Society::withTrashed()
                ->findOrFail($society->id);

            return view('societies.show', compact('society'));
        } catch (Exception $e) {

            Log::error($e->getMessage());

            return back()->with([
                'status' => 'error',
                'message' => 'Something went wrong.',
            ]);
        }
    }

    public function edit(Society $society)
    {
        $this->authorize('update', $society);

        return view('societies.edit', compact('society'));
    }

    public function update(UpdateSocietyRequest $request, Society $society)
    {
        $this->authorize('update', $society);

        try {

            $society->update(
                $request->validated()
            );

            ActivityLogger::log('update', $society, "Society {$society->name} was updated.");

            return redirect()
                ->route('societies.index')
                ->with([
                    'status' => 'success',
                    'message' => 'Society updated successfully.',
                ]);
        } catch (Exception $e) {

            Log::error($e->getMessage());

            return back()
                ->withInput()
                ->with([
                    'status' => 'error',
                    'message' => 'Something went wrong.',
                ]);
        }
    }

    public function destroy(Request $request, Society $society)
    {
        $this->authorize('delete', $society);

        $validated = $request->validate([
            'mode' => ['required', 'in:soft,force'],
        ]);

        try {

            $service = app(SocietyDeletionService::class);

            if ($validated['mode'] === 'soft') {

                $service->softDelete($society);
            } else {

                $service->forceDelete($society);
            }

            return response()->json([
                'success' => true,
                'message' => 'Society deleted successfully.',
            ]);
        } catch (Throwable $e) {

            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Deletion failed. Everything has been rolled back.',
            ], 500);
        }
    }

    public function restore(Society $society)
    {
        $this->authorize('restore', $society);

        try {
            $service = app(SocietyDeletionService::class);
            $service->restore($society);

            ActivityLogger::log('restore', $society, "Society {$society->name} was restored.");

            return response()->json([
                'success' => true,
                'message' => 'Society restored successfully.',
            ]);
        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function flats(Society $society)
    {
        $this->authorize('viewFlats', $society);

        try {
            $flats = Flat::where('society_id', $society->id)
                ->orderBy('wing')
                ->orderBy('floor')
                ->orderBy('flat_number')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $flats,
            ]);
        } catch (Exception $e) {
            Log::error('Error loading flats: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load flats.',
            ], 500);
        }
    }

    public function deletePreview(Society $society)
    {
        $this->authorize('delete', $society);

        try {

            $summary = app(SocietyDeletionService::class)
                ->preview($society);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (Exception $e) {

            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate delete preview.',
            ], 500);
        }
    }
}
