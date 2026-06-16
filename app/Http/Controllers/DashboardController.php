<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller

{
    public function admin()
    {
        return view('admin.dashboard');
    }

    public function resident()
    {
        return view('resident.dashboard');
    }

    public function gatekeeper()
    {
        return view('gatekeeper.dashboard');
    }
}
