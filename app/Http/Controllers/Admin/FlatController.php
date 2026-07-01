<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flat\StoreFlatRequest;
use App\Http\Requests\Flat\UpdateFlatRequest;
use App\Models\Flat;
use App\Models\Society;
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

                if (auth()->user()->isSuperAdmin()) {
                    $query = Flat::query()
                        ->select([
                            'flats.*',
                            'societies.name as society_name',
                        ])
                        ->leftJoin('societies', 'flats.society_id', '=', 'societies.id');
                    if ($request->filled('society_id')) {
                        $query->where('flats.society_id', $request->society_id);
                    }
                } else {
                    $query = Flat::query()
                        ->where('society_id', auth()->user()->society_id);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('society', function ($row) {
                        return $row->society_name ?? '-';
                    })
                    ->addColumn('actions', function ($row) {

                        $editUrl = route('flats.edit', $row->id);
                        $deleteUrl = route('flats.destroy', $row->id);

                        return '
                        <div class="text-center">
                        <a href="'.$editUrl.'" class="btn btn-sm btn-primary" title="Edit Flat"><i class="bi bi-pencil-square"></i></a>

                        <form action="'.$deleteUrl.'" method="POST" style="display:inline-block;">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm(\'Are you sure?\')" title="Delete Flat">
                                  <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        </div>
                    ';
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            }

            $societies = auth()->user()->isSuperAdmin()
                ? Society::orderBy('name')->get()
                : collect();

            return view('flats.index', compact('societies'));
        } catch (Exception $e) {
            Log::error('Flat listing error: '.$e->getMessage(), ['exception' => $e]);

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

    public function create()
    {
        $this->authorize('create', Flat::class);

        try {
            $societies = auth()->user()->isSuperAdmin()
                ? Society::all()
                : collect();

            return view('flats.create', compact('societies'));
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

            $flat = Flat::create([
                'wing' => $validated['wing'],
                'floor' => $validated['floor'],
                'flat_number' => $validated['flat_number'],
                'society_id' => auth()->user()->isSuperAdmin()
                    ? $validated['society_id']
                    : auth()->user()->society_id,
            ]);

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

            return view('flats.edit', compact('flat', 'societies'));
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
            $flat->update($request->validated());
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

            Session::flash('message', 'Flat Deleted successfully.');
            Session::flash('status', 'success');

            return redirect()->route('flats.index')
                ->with('success', 'Flat deleted successfully');
        } catch (Exception $e) {
            Log::error('Flat delete error: '.$e->getMessage(), ['exception' => $e]);

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back();
        }
    }
}
