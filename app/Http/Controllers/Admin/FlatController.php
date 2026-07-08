<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flat\StoreFlatRequest;
use App\Http\Requests\Flat\UpdateFlatRequest;
use App\Models\Flat;
use App\Models\Society;
use App\Models\Wing;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class FlatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Flat::class);

        try {
            if ($request->ajax()) {
                $user = auth()->user();
                $query = Flat::query()
                    ->select([
                        'flats.*',
                        'societies.name as society_name',
                        'wings.name as wing',
                    ])
                    ->leftJoin('societies', 'flats.society_id', '=', 'societies.id')
                    ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id');

                if (! $user->isSuperAdmin()) {
                    $query->where('flats.society_id', auth()->user()->society_id);
                }

                if ($user->isSuperAdmin() && $request->filled('society_id')) {
                    $query->where('flats.society_id', $request->society_id);
                }

                if ($request->filled('wing_id')) {
                    $query->where('flats.wing_id', $request->wing_id);
                }

                if ($request->filled('floor')) {
                    $query->where('flats.floor', $request->floor);
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

                    ->addColumn('society', function ($row) {
                        return $row->society_name ?? '-';
                    })

                    ->editColumn('wing', fn ($row) => $row->wing ?? '-')
                    ->editColumn('floor', fn ($row) => $row->floor ?? '-')
                    ->editColumn('flat_number', fn ($row) => $row->flat_number ?? '-')

                    ->addColumn('actions', function ($row) {

                        $showUrl = route('flats.show', $row->id);
                        $editUrl = route('flats.edit', $row->id);
                        $deleteUrl = route('flats.destroy', $row->id);
                        $restore = route('flats.restore', $row->id);

                        $html = '<div class="text-center d-flex justify-content-center gap-2">';

                        $html .= '
                            <a href="'.$showUrl.'" class="btn btn-sm btn-info text-white" title="View Flat">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        ';

                        if (! $row->trashed()) {
                            $html .= '
                                <a href="'.$editUrl.'" class="btn btn-sm btn-primary text-white" title="Edit Flat">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
 
                                <button
                                    class="btn btn-danger text-white btn-action btn-sm"
                                    data-url="'.$deleteUrl.'"
                                    data-method="DELETE"
                                    data-title="Delete Flat?"
                                    data-text="This action cannot be undone."
                                    data-confirm="Yes, Delete"
                                    data-success="Flat deleted successfully"
                                    title="Delete Flat">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            ';
                        } else {
                            $html .= '<button
                                class="btn btn-secondary text-white btn-action btn-sm"
                                data-url="'.$restore.'"
                                data-method="PATCH"
                                data-title="Restore Flat?"
                                data-text="This flat will be restored."
                                data-confirm="Yes, Restore"
                                title="Restore Flat">
                                <i class="bi bi-arrow-up-left-circle-fill"></i>
                            </button>';
                        }

                        return $html .= '</div>';
                    })

                    ->rawColumns(['actions'])
                    ->make(true);
            }

            $societies = auth()->user()->isSuperAdmin()
                ? Society::orderBy('name')->get()
                : collect();

            $wings = auth()->user()->isSuperAdmin()
                ? collect()
                : Wing::where('society_id', auth()->user()->society_id)->orderBy('name')->get();

            $query = Flat::query();
            if (! auth()->user()->isSuperAdmin()) {
                $query->where('society_id', auth()->user()->society_id);
            }
            $floors = $query->orderBy('floor')->pluck('floor')->unique()->values();

            return view('flats.index', compact('societies', 'wings', 'floors'));

        } catch (Exception $e) {

            Log::error('Flat listing error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load flats.',
                ], 500);
            }

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function show(Flat $flat)
    {
        $this->authorize('view', $flat);

        try {
            $flat->load(['wingRelation', 'society', 'residents.user']);

            return view('flats.show', compact('flat'));
        } catch (Exception $e) {
            Log::error('Flat show page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function create()
    {
        $this->authorize('create', Flat::class);

        try {
            $societies = auth()->user()->isSuperAdmin()
                ? Society::all()
                : collect();

            $wings = collect();
            if (! auth()->user()->isSuperAdmin()) {
                $wings = Wing::where('society_id', auth()->user()->society_id)->orderBy('name')->get();
            }

            return view('flats.create', compact('societies', 'wings'));
        } catch (Exception $e) {
            Log::error('Flat create page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function store(StoreFlatRequest $request)
    {
        $this->authorize('create', Flat::class);

        try {

            $validated = $request->validated();

            $data = [
                'wing_id' => $validated['wing_id'],
                'floor' => $validated['floor'],
                'flat_number' => $validated['flat_number'],
            ];

            $data['society_id'] = auth()->user()->isSuperAdmin()
                ? ($validated['society_id'] ?? null)
                : auth()->user()->society_id;

            $flat = Flat::create($data);

            ActivityLogger::log('create', $flat, "Flat {$flat->wing}-{$flat->flat_number} was created.");

            Session::flash('message', 'Flat Created Successfully.');
            Session::flash('status', 'success');

            return redirect()->route('flats.index');
        } catch (Exception $e) {
            Log::error('Flat store error: '.$e->getMessage(), ['exception' => $e]);

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function edit(Flat $flat)
    {
        $this->authorize('update', $flat);

        try {
            $societies = auth()->user()->isSuperAdmin()
                ? Society::all()
                : collect();

            $wings = Wing::where('society_id', $flat->society_id)->orderBy('name')->get();

            return view('flats.edit', compact('flat', 'societies', 'wings'));
        } catch (Exception $e) {
            Log::error('Flat edit page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function update(UpdateFlatRequest $request, Flat $flat)
    {
        $this->authorize('update', $flat);

        try {
            $data = $request->validated();
            $data['society_id'] = auth()->user()->isSuperAdmin()
                ? ($data['society_id'] ?? $flat->society_id)
                : auth()->user()->society_id;

            $flat->update($data);
            ActivityLogger::log('update', $flat, "Flat {$flat->wing}-{$flat->flat_number} was updated.");
            Session::flash('message', 'Flat updated successfully.');
            Session::flash('status', 'success');

            return redirect()->route('flats.index')
                ->with('success', 'Flat updated successfully');
        } catch (Exception $e) {
            Log::error('Flat update error: '.$e->getMessage(), ['exception' => $e]);

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Flat $flat)
    {
        $this->authorize('delete', $flat);

        try {
            ActivityLogger::log('delete', $flat, "Flat {$flat->wing}-{$flat->flat_number} was deleted.");
            $flat->delete();

            return response()->json([
                'message' => 'Flat deleted successfully.',
                'success' => true,
            ]);
        } catch (Exception $e) {
            Log::error('Flat delete error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function restore(Flat $flat)
    {
        $this->authorize('restore', $flat);

        try {
            $flat->restore();

            ActivityLogger::log('restore', $flat, 'flat restored.');

            return response()->json([
                'success' => true,
                'message' => 'flat restored successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore flat.',
            ]);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', Flat::class);

        try {

            $user = auth()->user();

            $query = Flat::query()
                ->select([
                    'flats.*',
                    'societies.name as society_name',
                    'wings.name as wing',
                ])
                ->leftJoin('societies', 'societies.id', '=', 'flats.society_id')
                ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id');

            if (! $user->isSuperAdmin()) {

                $query->where('flats.society_id', $user->society_id);
            } elseif ($request->filled('society_id')) {

                $query->where('flats.society_id', $request->society_id);
            }

            if ($request->filled('search')) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('societies.name', 'like', "%{$search}%")
                        ->orWhere('wings.name', 'like', "%{$search}%")
                        ->orWhere('flats.floor', 'like', "%{$search}%")
                        ->orWhere('flats.flat_number', 'like', "%{$search}%");
                });
            }

            return response()->streamDownload(function () use ($query, $user) {

                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Society',
                    'Wing',
                    'Floor',
                    'Flat Number',
                ]);

                foreach ($query->cursor() as $flat) {

                    fputcsv($handle, [
                        $flat->id,
                        $user->isSuperAdmin() ? $flat->society_name : '',
                        $flat->wing,
                        $flat->floor,
                        $flat->flat_number,
                    ]);
                }

                fclose($handle);
            }, 'flats.csv', [
                'Content-Type' => 'text/csv',
            ]);
        } catch (\Throwable $e) {

            Log::error('Flat export error: '.$e->getMessage(), ['exception' => $e]);

            return back()->with([
                'status' => 'error',
                'message' => 'Failed to export flat details.',
            ]);
        }
    }
}
