<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Society;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\VisitorLog;
use App\Models\Visitor;
use App\Models\Delivery;
use App\Models\Complaint;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->name;

        return match ($role) {
            'super_admin' => $this->superAdmin(),
            'admin'       => $this->admin($user),
            'gatekeeper'  => $this->gatekeeper(),
            'resident'    => $this->resident($user),
            default       => abort(403),
        };
    }

    private function superAdmin()
    {
        return view('dashboard.index', [
            'role' => 'super_admin',

            'stats' => [
                'societies'  => Society::count(),
                'users'      => User::count(),
                'residents'  => Resident::count(),
                'flats'      => Flat::count(),
                'visitors'   => Visitor::count(),
                'deliveries' => Delivery::count(),
                'complaints' => Complaint::count(),
            ],

            'latest_societies' => Society::latest()->take(5)->get(),
            'latest_complaints' => Complaint::latest()->take(5)->get(),
            'latest_visitors' => VisitorLog::latest()->take(5)->get(),
        ]);
    }

    private function admin($user)
    {
        $societyId = $user->society_id;

        return view('dashboard.index', [
            'role' => 'admin',

            'stats' => [
                'residents'  => Resident::whereHas('user', fn($q) => $q->where('society_id', $societyId))->count(),
                'gatekeepers' => User::where('society_id', $societyId)->whereHas('role', fn($q) => $q->where('name', 'gatekeeper'))->count(),
                'flats'      => Flat::where('society_id', $societyId)->count(),
                'complaints' => Complaint::whereHas('user', fn($q) => $q->where('society_id', $societyId))->count(),
                'deliveries' => Delivery::whereHas('flat', fn($q) => $q->where('society_id', $societyId))->count(),
                'visitors_today' => VisitorLog::whereDate('created_at', today())->count(),
            ],

            'pending_complaints' => Complaint::whereHas('user', fn($q) => $q->where('society_id', $societyId))
                ->where('status', 'open')
                ->latest()
                ->take(5)
                ->get(),

            'today_visitors' => VisitorLog::whereDate('created_at', today())
                ->latest()
                ->take(5)
                ->get(),

            'recent_deliveries' => Delivery::whereHas('flat', fn($q) => $q->where('society_id', $societyId))
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    private function gatekeeper()
    {
        return view('dashboard.index', [
            'role' => 'gatekeeper',

            'stats' => [
                'today_visitors' => VisitorLog::whereDate('created_at', today())->count(),
                'entered'        => VisitorLog::whereDate('created_at', today())->where('status', 'entered')->count(),
                'exited'         => VisitorLog::whereDate('created_at', today())->where('status', 'exited')->count(),
                'pending'        => VisitorLog::where('status', 'pending')->count(),
                'deliveries'     => Delivery::whereDate('created_at', today())->count(),
            ],

            'active_visitors' => VisitorLog::where('status', 'entered')
                ->latest()
                ->take(10)
                ->get(),

            'today_entries' => VisitorLog::whereDate('created_at', today())
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    private function resident($user)
    {
        $userId = $user->id;

        return view('dashboard.index', [
            'role' => 'resident',

            'stats' => [
                'visitors'   => VisitorLog::where('created_by', $userId)->count(),
                'pending'    => VisitorLog::where('created_by', $userId)->where('status', 'pending')->count(),
                'deliveries' => Delivery::where('resident_id', $userId)->count(),
                'complaints' => Complaint::where('user_id', $userId)->count(),
            ],

            'my_visitors' => VisitorLog::where('created_by', $userId)
                ->latest()
                ->take(5)
                ->get(),

            'my_deliveries' => Delivery::where('resident_id', $userId)
                ->latest()
                ->take(5)
                ->get(),

            'my_complaints' => Complaint::where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
