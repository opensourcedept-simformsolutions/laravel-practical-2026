<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('role')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated=$request->validated();
        
        $validated['society_id']=auth()->user()->society_id;    

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'user created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if(empty($validated['password']))
            {
                unset($validated['password']);
            }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'edited successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {

        if(auth()->id() === $user->id)
            {
                return redirect()->back()
                ->with('error','you cant delete your self');
            }
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'deleted successfully');

    }
}
