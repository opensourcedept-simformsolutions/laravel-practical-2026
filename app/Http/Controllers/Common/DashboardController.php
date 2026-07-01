<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Society;
use App\Models\User;
use App\Models\VisitorLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->name;

        return match ($role) {
            'super_admin' => $this->superAdmin(),
            'admin' => $this->admin($user),
            'gatekeeper' => $this->gatekeeper(),
            'resident' => $this->resident($user),
            default => abort(403),
        };
    }

    private function superAdmin()
    {
        return view('dashboard.index', [

            'role' => 'super_admin',

            'stats' => [

                'total_societies' => Society::count(),

                'total_users' => User::count(),

                'total_residents' => Resident::count(),

                'total_gatekeepers' => User::whereHas(
                    'role',
                    fn ($q) => $q->where('name', 'gatekeeper')
                )->count(),

                'visitors_today' => VisitorLog::whereDate(
                    'visit_date',
                    today()
                )->count(),

                'active_visitors' => VisitorLog::where(
                    'status',
                    'entered'
                )->whereNull(
                    'exit_time'
                )->count(),

                'open_complaints' => Complaint::whereIn(
                    'status',
                    ['open', 'in_progress']
                )->count(),

                'pending_deliveries' => Delivery::where(
                    'status',
                    'received'
                )->count(),
            ],
        ]);

    }

    private function admin($user)
    {
        $societyId = $user->society_id;

        return view('dashboard.index', [

            'role' => 'admin',

            'stats' => [

                'residents' => Resident::whereHas(
                    'user',
                    fn ($q) => $q->where(
                        'society_id',
                        $societyId
                    )
                )->count(),

                'flats' => Flat::where(
                    'society_id',
                    $societyId
                )->count(),

                'visitors_today' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where(
                        'society_id',
                        $societyId
                    )
                )->whereDate(
                    'visit_date',
                    today()
                )->count(),

                'visitors_inside' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where(
                        'society_id',
                        $societyId
                    )
                )->where(
                    'status',
                    'entered'
                )->whereNull(
                    'exit_time'
                )->count(),

                'deliveries_today' => Delivery::whereHas(
                    'flat',
                    fn ($q) => $q->where(
                        'society_id',
                        $societyId
                    )
                )->whereDate(
                    'created_at',
                    today()
                )->count(),

                'pending_deliveries' => Delivery::whereHas(
                    'flat',
                    fn ($q) => $q->where(
                        'society_id',
                        $societyId
                    )
                )->where(
                    'status',
                    'pending'
                )->count(),

                'open_complaints' => Complaint::whereHas(
                    'user',
                    fn ($q) => $q->where(
                        'society_id',
                        $societyId
                    )
                )->whereIn(
                    'status',
                    ['open', 'in_progress']
                )->count(),

                'resolved_complaints' => Complaint::whereHas(
                    'user',
                    fn ($q) => $q->where(
                        'society_id',
                        $societyId
                    )
                )->where(
                    'status',
                    'resolved'
                )->count(),
            ],
        ]);
    }

    private function gatekeeper()
    {

        $societyId = auth()->user()->society_id;

        return view('dashboard.index', [
            'role' => 'gatekeeper',

            'stats' => [

                // Visitors Expected Today
                'visitors_expected_today' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->whereDate('visit_date', today())
                    ->count(),

                // Entries Today
                'entries_today' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->whereDate('visit_date', today())
                    ->where('status', 'entered')
                    ->count(),

                // Exits Today
                'exits_today' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->whereDate('visit_date', today())
                    ->where('status', 'exited')
                    ->count(),

                // Visitors Inside Now
                'visitors_inside_now' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->where('status', 'entered')
                    ->whereNull('exit_time')
                    ->count(),

                // Pending Deliveries
                'pending_deliveries' => Delivery::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->where('status', 'pending')
                    ->count(),

                // Deliveries Received Today
                'deliveries_received' => Delivery::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->whereDate('created_at', today())
                    ->count(),

                // Passes Verified
                'passes_verified' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->where('status', 'entered')
                    ->count(),

                // Rejected Entries
                'rejected_entries' => VisitorLog::whereHas(
                    'flat',
                    fn ($q) => $q->where('society_id', $societyId)
                )
                    ->where('status', 'rejected')
                    ->count(),
            ],

        ]);

    }

    private function resident($user)
    {

        $userId = $user->id;
        $residentId = $user->resident?->id;

        return view('dashboard.index', [

            'role' => 'resident',

            'stats' => [

                // Total visitor passes created by resident
                'my_visitor_passes' => VisitorLog::where(
                    'created_by',
                    $userId
                )->count(),

                // Visitors expected today
                'visitors_expected_today' => VisitorLog::where(
                    'created_by',
                    $userId
                )
                    ->whereDate(
                        'visit_date',
                        today()
                    )
                    ->count(),

                // Visitors currently inside
                'active_visitors' => VisitorLog::where(
                    'created_by',
                    $userId
                )
                    ->where(
                        'status',
                        'entered'
                    )
                    ->whereNull(
                        'exit_time'
                    )
                    ->count(),

                // All deliveries for this resident
                'my_deliveries' => Delivery::where(
                    'resident_id',
                    $residentId
                )->count(),

                // Package received at gate but not delivered yet
                'pending_deliveries' => Delivery::where(
                    'resident_id',
                    $residentId
                )
                    ->where(
                        'status',
                        'received'
                    )
                    ->count(),

                // All complaints raised by this resident
                'my_complaints' => Complaint::where(
                    'user_id',
                    $userId
                )->count(),

                // Open/In Progress complaints
                'pending_complaints' => Complaint::where(
                    'user_id',
                    $userId
                )
                    ->whereIn(
                        'status',
                        [
                            'open',
                            'in_progress',
                        ]
                    )
                    ->count(),

                // Resolved complaints
                'resolved_complaints' => Complaint::where(
                    'user_id',
                    $userId
                )
                    ->where(
                        'status',
                        'resolved'
                    )
                    ->count(),
            ],

        ]);

    }
}
