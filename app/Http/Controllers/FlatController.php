<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreFlatRequest;
use App\Http\Requests\UpdateFlatRequest;
use App\Models\Flat;

class FlatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $flats = Flat::latest()->get();
        return view('admin.flats.index', compact('flats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.flats.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFlatRequest $request)
    {
        $data = $request->validated();
        $data['society_id'] = auth()->user()->society_id;
        Flat::create($data);
        return redirect()->route('flats.index')
            ->with('success', 'Flat created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Flat $flat)
    {
        return view('admin.flats.edit', compact('flat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFlatRequest $request, Flat $flat)
    {


        $flat->update($request->validated());

        return redirect()->route('flats.index')
            ->with('success', 'Flat updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Flat $flat)
    {
        $flat->delete();

        return redirect()->route('flats.index')
            ->with('success', 'Flat deleted successfully');
    }
}
