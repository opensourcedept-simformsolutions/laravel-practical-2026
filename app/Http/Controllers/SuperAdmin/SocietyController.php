<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Society;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Society\StoreSocietyRequest;
use App\Http\Requests\Society\UpdateSocietyRequest;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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
                        <a href="' . route('societies.show', $society->id) . '"
                            class="btn btn-info text-white" title="View Society">
                            <i class="bi bi-eye"></i>
                        </a>
                    ';

                    if (!$society->trashed()) {

                        $actions .= '
                            <a href="' . route('societies.edit', $society->id) . '"
                                class="btn btn-primary" title="Edit Society">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        ';

                        $actions .= '
                            <button
                                class="btn btn-danger btn-action"
                                data-url="' . route('societies.destroy', $society->id) . '"
                                data-method="DELETE"
                                data-title="Delete Society?"
                                data-text="This action can be restored later."
                                data-confirm="Yes, Delete"
                                data-success="Society deleted successfully"
                                title="Delete Society">
                                <i class="bi bi-trash"></i>
                            </button>
                        ';
                    } else {

                        $actions .= '
                            <button
                                class="btn btn-success btn-action"
                                data-url="' . route('societies.restore', $society->id) . '"
                                data-method="PATCH"
                                data-title="Restore Society?"
                                data-text="Society will become active again."
                                data-confirm="Yes, Restore"
                                data-success="Society restored successfully"
                                title="Restore Society">
                                <i class="bi bi-arrow-counterclockwise"></i>
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

        try {

            $society = Society::create($request->validated());

            ActivityLogger::log('create', $society, "Society {$society->name} was created.");

            return redirect()
                ->route('societies.index')
                ->with([
                    'status' => 'success',
                    'message' => 'Society created successfully.'
                ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return back()
                ->withInput()
                ->with([
                    'status' => 'error',
                    'message' => 'Something went wrong.'
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
                'message' => 'Something went wrong.'
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
                    'message' => 'Society updated successfully.'
                ]);
        } catch (Exception $e) {

            Log::error($e->getMessage());

            return back()
                ->withInput()
                ->with([
                    'status' => 'error',
                    'message' => 'Something went wrong.'
                ]);
        }
    }

    public function destroy(Society $society)
    {
        $this->authorize('delete', $society);

        try {

            ActivityLogger::log('delete', $society, "Society {$society->name} was deleted.");
            $society->delete();

            return response()->json([
                'success' => true,
                'message' => 'Society deleted successfully.'
            ]);
        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function restore(Society $society)
    {
        $this->authorize('restore', $society);

        try {

            $society->restore();

            ActivityLogger::log('restore', $society, "Society {$society->name} was restored.");

            return response()->json([
                'success' => true,
                'message' => 'Society restored successfully.'
            ]);
        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
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
            Log::error('Error loading flats: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load flats.',
            ], 500);
        }
    }
}
