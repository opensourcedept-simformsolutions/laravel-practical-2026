<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Flat;
use App\Models\User;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Resident::with(['user', 'flat']);

        // SEARCH FILTER
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $residents = $query->latest()->get();

        return view('admin.residents.index', compact('residents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $flats = Flat::where('society_id', auth()->user()->society_id)->get();

        return view('admin.residents.create', compact('flats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResidentRequest $request)
    {
        $data = $request->validated();

        // 1. create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt('password123'),
            'role_id' => 3, // resident
            'society_id' => auth()->user()->society_id,
        ]);

        // 2. create resident
        Resident::create([
            'user_id' => $user->id,
            'flat_id' => $data['flat_id'],
            'resident_type' => $data['resident_type'],
        ]);

        return redirect()->route('residents.index')
            ->with('success', 'Resident created successfully');
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

        return view('admin.residents.edit', compact('resident', 'flats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        $data = $request->validated();
//  dd($resident);
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
