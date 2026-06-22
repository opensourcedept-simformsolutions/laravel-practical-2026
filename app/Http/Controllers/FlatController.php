<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlatRequest;
use App\Http\Requests\UpdateFlatRequest;
use App\Models\Flat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Exceptions\Exception;
use Yajra\DataTables\Facades\DataTables;

class FlatController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            return DataTables::of(Flat::query())
                ->addColumn('actions', function ($row) {

                    $editUrl = route('flats.edit', $row->id);
                    $deleteUrl = route('flats.destroy', $row->id);

                    return '
                    <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>

                    <form action="'.$deleteUrl.'" method="POST" style="display:inline-block;">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm(\'Are you sure?\')">
                            Delete
                        </button>
                    </form>
                ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('flats.index');
    }

    public function create()
    {
        return view('flats.create');
    }

    public function store(StoreFlatRequest $request)
    {
        try {
            $validated = $request->validated();

            Flat::create([
                'wing' => $validated['wing'],
                'floor' => $validated['floor'],
                'flat_number' => $validated['flat_number'],
                'society_id' => auth()->user()->society_id,
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
