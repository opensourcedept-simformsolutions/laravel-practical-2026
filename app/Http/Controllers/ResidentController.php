<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Resident::with(['user', 'flat']);

            return DataTables::of($query)

                ->addColumn('name', fn ($row) => $row->user->name)
                ->addColumn('email', fn ($row) => $row->user->email)
                ->addColumn('phone', fn ($row) => $row->user->phone)

                ->addColumn('flat', fn ($row) => $row->flat->flat_number)
                ->addColumn('wing', fn ($row) => $row->flat->wing)

                ->addColumn('type', function ($row) {
                    return $row->resident_type === 'owner'
                        ? '<span class="badge bg-success">Owner</span>'
                        : '<span class="badge bg-info">Tenant</span>';
                })

                ->addColumn('actions', function ($row) {

                    $editUrl = route('residents.edit', $row->id);
                    $deleteUrl = route('residents.destroy', $row->id);

                    return '
                    <a href="'.$editUrl.'" class="btn btn-warning btn-sm">Edit</a>

                    <form action="'.$deleteUrl.'" method="POST" class="d-inline">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                        <button class="btn btn-danger btn-sm"
                            onclick="return confirm(\'Delete this resident?\')">
                            Delete
                        </button>
                    </form>
                ';
                })

                ->rawColumns(['type', 'actions'])
                ->make(true);
        }

        return view('residents.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $flats = Flat::where('society_id', auth()->user()->society_id)->get();

        return view('residents.create', compact('flats'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreResidentRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt('password123'),
                'role_id' => 3,
                'society_id' => auth()->user()->society_id,
            ]);
            Resident::create([
                'user_id' => $user->id,
                'flat_id' => $validated['flat_id'],
                'resident_type' => $validated['resident_type'],
            ]);

            Session::flash('message', 'Resident Created Successfully.');
            Session::flash('status', 'success');

            return redirect()->route('residents.index');

        } catch (Exception $e) {
            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show() {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resident $resident)
    {
        $flats = Flat::where(
            'society_id',
            auth()->user()->society_id
        )->get();

        return view('residents.edit', compact('resident', 'flats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        $data = $request->validated();
        $resident->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $resident->update([
            'flat_id' => $data['flat_id'],
            'resident_type' => $data['resident_type'],
        ]);

        return redirect()->route('residents.index')
            ->with('success', 'Resident updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resident $resident)
    {
        $resident->user()->delete();

        return redirect()->route('residents.index')
            ->with('success', 'Resident deleted successfully');
    }
}
