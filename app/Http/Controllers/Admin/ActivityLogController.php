<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $societies = auth()->user()->isSuperAdmin()
            ? Society::orderBy('name')->get()
            : collect();

        return view('admin.activity-logs.index', compact('societies'));
    }

    public function data(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        try {
            $user = auth()->user();

            $query = ActivityLog::with(['user.role'])
                ->select([
                    'activity_logs.*',
                    'users.name as operator_name',
                    'societies.name as society_name',
                ])
                ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                ->leftJoin('societies', 'activity_logs.society_id', '=', 'societies.id');

            // Scope based on roles
            if (! $user->isSuperAdmin()) {
                $query->where('activity_logs.society_id', $user->society_id);
            } else {
                if ($request->filled('society_id')) {
                    $query->where('activity_logs.society_id', $request->society_id);
                }
            }

            // Apply Filters
            if ($request->filled('action')) {
                $query->where('activity_logs.action', $request->action);
            }

            if ($request->filled('from_date')) {
                $query->whereDate('activity_logs.created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('activity_logs.created_at', '<=', $request->to_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('operator', function ($row) {
                    if (! $row->user_id) {
                        return 'System / Guest';
                    }
                    $roleName = $row->user->role?->name ? ucfirst($row->user->role->name) : 'User';

                    return e($row->operator_name).' <span class="badge bg-secondary">'.e($roleName).'</span>';
                })
                ->addColumn('society_name', function ($row) {
                    return $row->society_name ?? 'Global / Super';
                })
                ->editColumn('action', function ($row) {
                    $badgeClass = match ($row->action) {
                        'create' => 'bg-success',
                        'update' => 'bg-warning text-dark',
                        'delete' => 'bg-danger',
                        'login' => 'bg-info text-dark',
                        'logout' => 'bg-dark',
                        'impersonate_start', 'impersonate_stop' => 'bg-primary',
                        default => 'bg-secondary',
                    };

                    return '<span class="badge '.$badgeClass.'">'.e(ucfirst(str_replace('_', ' ', $row->action))).'</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y, h:i A');
                })
                ->addColumn('actions', function ($row) {
                    if (empty($row->properties)) {
                        return '-';
                    }

                    return '
                        <button type="button" 
                                class="btn btn-sm btn-info text-white view-properties-btn" 
                                data-properties="'.e(json_encode($row->properties)).'" 
                                data-id="'.$row->id.'">
                            <i class="bi bi-info-circle"></i> Details
                        </button>
                    ';
                })
                ->rawColumns(['operator', 'action', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('ActivityLog DataTable error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load activity logs.',
            ], 500);
        }
    }
}
