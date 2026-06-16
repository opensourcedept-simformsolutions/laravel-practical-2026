<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required | required',
            'phone' => 'required| min:10',
            'password' => 'required',
            'role_id' => 'required',
        ]);

        User::create($request->all());

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
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required | required',
            'phone' => 'required| min:10',
            'role_id' => 'required',
        ]);
        
        
        $user->name = $request->name; 
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role_id = $request->role_id;

        $user->save();
        // dd($user);

        
        return redirect()->route('admin.users.index')
        ->with('success', 'edited successfully');
        }
        
        /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
        ->with('success', 'deleted successfully');
        
    }
}
