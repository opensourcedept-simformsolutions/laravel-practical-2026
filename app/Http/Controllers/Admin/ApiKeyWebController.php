<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyWebController extends Controller
{
    /**
     * Display the API Key management page.
     */
    public function index(Request $request): View
    {
        return view('admin.api-keys.index');
    }
}
