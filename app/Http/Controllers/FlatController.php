<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlatRequest;
use App\Http\Requests\UpdateFlatRequest;
use App\Models\Flat;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class FlatController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if (auth()->user()->isSuperAdmin()) {

                $query = Flat::with('society');

                if ($request->filled('society_id')) {
                    $query->where('society_id', $request->society_id);
                }

            } else {

                $query = Flat::with('society')
                    ->where('society_id', auth()->user()->society_id);
            }

            return DataTables::of($query)
                ->addColumn('society', function ($row) {
                    return $row->society?->name ?? '-';
                })
                ->addColumn('actions', function ($row) {

                    $editUrl = route('flats.edit', $row->id);
                    $deleteUrl = route('flats.destroy', $row->id);

                    return '
                    <div class="text-center">
                    <a href="' . $editUrl . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>

                    <form action="' . $deleteUrl . '" method="POST" style="display:inline-block;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm(\'Are you sure?\')">
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
    }

    public function create()
    {
        $societies = auth()->user()->isSuperAdmin()
            ? Society::all()
            : collect();

        return view('flats.create', compact('societies'));
    }

    public function store(StoreFlatRequest $request)
    {
        try {

            $validated = $request->validated();

            Flat::create([
                'wing' => $validated['wing'],
                'floor' => $validated['floor'],
                'flat_number' => $validated['flat_number'],
                'society_id' => auth()->user()->isSuperAdmin()
                    ? $validated['society_id']
                    : auth()->user()->society_id,
            ]);

            Session::flash('message', 'Flat Created Successfully.');
            Session::flash('status', 'success');

            return redirect()->route('flats.index');
        } catch (Exception $e) {

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function show(string $id) {}

    public function edit(Flat $flat)
    {
        return view('flats.edit', compact('flat'));
    }

    public function update(UpdateFlatRequest $request, Flat $flat)
    {

        $flat->update($request->validated());
        Session::flash('message', 'Visitor Pass updated successfully.');
        Session::flash('status', 'success');

        return redirect()->route('flats.index')
            ->with('success', 'Flat updated successfully');
    }

    public function destroy(Flat $flat)
    {
        $flat->delete();

        Session::flash('message', 'Flat Deleted successfully.');
        Session::flash('status', 'success');

        return redirect()->route('flats.index')
            ->with('success', 'Flat deleted successfully');
    }
}
