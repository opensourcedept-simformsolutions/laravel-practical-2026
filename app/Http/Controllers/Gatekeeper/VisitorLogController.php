<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Events\VisitorEntered;
use App\Events\VisitorExited;
use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class VisitorLogController extends Controller
{
    public function scanPage()
    {
        return view('visitor-passes.scan');
    }
    
    public function findPass(Request $request)
    {
        try {
            $today = Carbon::today();

            $visitorLogId = decrypt($request->qr_code);

            $visitorLog = VisitorLog::with(['visitor', 'flat'])
                ->find($visitorLogId);

            if (! $visitorLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired QR pass.',
                ], 404);
            }

            if (Carbon::parse($visitorLog->visit_date)->toDateString() !== $today->toDateString()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired QR pass.',
                ], 422);
            }

            if ($visitorLog->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Visitor already entered or pass not valid.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'id' => $visitorLog->id,
                'visitor' => $visitorLog->visitor->name,
                'phone' => $visitorLog->visitor->phone,
                'purpose' => $visitorLog->purpose,
                'status' => $visitorLog->status,
                'flat' => $visitorLog->flat->wing.'-'.$visitorLog->flat->flat_number,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function pending(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            if ($request->ajax()) {
                $user = auth()->user();
                $query = VisitorLog::query();
                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    switch ($request->get('filter', 'active')) {
                        case 'deleted':
                            $query->onlyTrashed();
                            break;
                        case 'all':
                            $query->withTrashed();
                            break;
                        case 'active':
                        default:
                            break;
                    }
                }
                $query->whereIn('visitor_logs.status', ['pending', 'entered']);
                $query->select([
                    'visitor_logs.*',
                    'visitors.name as visitor_name',
                    'visitors.phone as visitor_phone',
                    'societies.name as society_name',
                    'flats.wing as flat_wing',
                    'flats.floor as flat_floor',
                    'flats.flat_number',
                ])
                    ->leftJoin('visitors', 'visitor_logs.visitor_id', '=', 'visitors.id')
                    ->leftJoin('flats', 'visitor_logs.flat_id', '=', 'flats.id')
                    ->leftJoin('societies', 'flats.society_id', '=', 'societies.id')
                    ->whereDate('visitor_logs.visit_date', today());

                if (! $user->isSuperAdmin()) {
                    $query->where('flats.society_id', $user->society_id);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('flat_details', function ($log) {
                        return "{$log->flat_wing}-{$log->flat_number}";
                    })
                    ->addColumn('action', function ($log) {
                        $buttons = '';

                        if ($log->trashed()) {
                            if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
                                $buttons .= '
                                    <button
                                        type="button"
                                        class="btn btn-warning btn-sm btn-action"
                                        data-url="'.route('gatekeeper.visitor-logs.restore', $log->id).'"
                                        data-method="PATCH"
                                        data-title="Restore Visitor Pass?"
                                        data-text="This visitor pass will be restored."
                                        data-confirm="Restore"
                                        data-color="#198754"
                                        title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                ';
                            }
                        } else {
                            if (auth()->user()->can('markEntry', $log) && $log->status !== 'entered') {
                                $buttons .= '
                                <button
                                    type="button"
                                    class="btn btn-success btn-sm entry-btn"
                                    data-id="'.$log->id.'"
                                    title="Mark Entry">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                            ';
                            }

                            if (auth()->user()->can('markExit', $log) && $log->status !== 'pending') {
                                $buttons .= '
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm btn-action"
                                    data-url="'.route('gatekeeper.visitor-logs.mark-exit', $log).'"
                                    data-method="PATCH"
                                    data-title="Mark Exit?"
                                    data-text="Confirm that the visitor has exited."
                                    data-confirm="Mark Exit"
                                    data-success="Visitor marked as exited."
                                    title="Mark Exit">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                            ';
                            }

                            if (auth()->user()->can('update', $log)) {
                                $buttons .= '
                                <a href="'.route('passes.edit', $log).'"
                                class="btn btn-primary btn-sm"
                                title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            ';
                            }

                            if (auth()->user()->can('delete', $log)) {
                                $buttons .= '
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm btn-action"
                                    data-url="'.route('passes.destroy', $log).'"
                                    data-method="DELETE"
                                    data-title="Delete Visitor Pass?"
                                    data-text="This action cannot be undone."
                                    data-confirm="Delete"
                                    data-success="Visitor pass deleted successfully."
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            ';
                            }
                        }

                        return '
                            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
                                '.$buttons.'
                            </div>
                        ';
                    })
                    ->editColumn('visitor_name', fn ($log) => $log->visitor_name ?? '-')
                    ->editColumn('society', fn ($log) => $log->society_name ?? '-')
                    ->editColumn('phone', fn ($log) => $log->visitor_phone ?? '-')
                    ->editColumn('status', function ($log) {
                        if ($log->trashed()) {
                            return '<span class="badge bg-none text-danger">Deleted</span>';
                        }

                        return match ($log->status) {
                            'pending' => '<span class="badge bg-none text-secondary">Pending</span>',
                            'entered' => '<span class="badge bg-none text-success">Entered</span>',
                            default => ucfirst($log->status),
                        };
                    })
                    ->orderColumn('society', function ($query, $order) {
                        $query->orderBy('societies.name', $order);
                    })
                    ->orderColumn('flat_details', function ($query, $order) {
                        $query->orderBy('flats.wing', $order)
                            ->orderBy('flats.flat_number', $order);
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            return view('visitor-passes.pending');
        } catch (Exception $e) {

            Log::error('Visitor Log Pending Error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong while loading visitor logs.',
                ], 500);
            }

            return redirect()->back()->with([
                'message' => 'Something went wrong. Please try again.',
                'status' => 'error',
            ]);
        }
    }

    public function markEntry(Request $request, VisitorLog $visitorLog)
    {
        $this->authorize('markEntry', $visitorLog);

        try {
            $request->validate([
                'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ]);

            if ($visitorLog->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Pending Passes Can Be Entered!',
                ], 422);
            }

            $uploaded = Cloudinary::uploadApi()->upload(
                $request->file('photo')->getRealPath(),
                [
                    'folder' => 'visitor_photos',
                ]
            );

            $visitorLog->photo_path = $uploaded['secure_url'];
            $visitorLog->entry_time = now();
            $visitorLog->gatekeeper_id = auth()->id();
            $visitorLog->status = 'entered';
            $visitorLog->save();

            ActivityLogger::log('mark_entry', $visitorLog, "Visitor {$visitorLog->visitor->name} entered flat ".($visitorLog->flat?->wing ?? '-').'-'.($visitorLog->flat?->flat_number ?? '-').'.');

            event(new VisitorEntered($visitorLog));

            return response()->json([
                'success' => true,
                'message' => 'Visitor Entry Marked Successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Visitor Entry Error: '.$e->getMessage(), [
                'visitor_log_id' => $visitorLog->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something Went Wrong While Marking Entry: '.$e->getMessage(),
            ], 500);
        }
    }

    public function markExit(VisitorLog $visitorLog)
    {
        $this->authorize('markExit', $visitorLog);

        try {

            if ($visitorLog->status !== 'entered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Entered Visitors Can Exit!',
                ], 422);
            }

            $visitorLog->exit_time = now();
            $visitorLog->status = 'exited';
            $visitorLog->save();

            ActivityLogger::log('mark_exit', $visitorLog, "Visitor {$visitorLog->visitor->name} exited flat ".($visitorLog->flat?->wing ?? '-').'-'.($visitorLog->flat?->flat_number ?? '-').'.');

            Log::info('VisitorExited event dispatching', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            event(
                new VisitorExited($visitorLog)
            );

            Log::info('VisitorExited event dispatched', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor Exit Marked Successfully!',
            ]);
        } catch (Exception $e) {

            Log::error('Visitor Exit Error: '.$e->getMessage(), [
                'visitor_log_id' => $visitorLog->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something Went Wrong While Mark Exit',
            ], 500);
        }
    }

    public function exited(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            if ($request->ajax()) {

                $user = auth()->user();

                $query = VisitorLog::query()
                    ->where('visitor_logs.status', 'exited')
                    ->select([
                        'visitor_logs.*',
                        'visitors.name as visitor_name',
                        'visitors.phone as visitor_phone',
                        'flats.wing as flat_wing',
                        'flats.floor as flat_floor',
                        'flats.flat_number',
                    ])
                    ->leftJoin('visitors', 'visitor_logs.visitor_id', '=', 'visitors.id')
                    ->leftJoin('flats', 'visitor_logs.flat_id', '=', 'flats.id');

                if (! $user->isSuperAdmin()) {
                    $query->where('flats.society_id', $user->society_id);
                }

                return DataTables::of($query)
                    ->addIndexColumn()

                    ->addColumn('flat_details', fn ($row) => "{$row->flat_wing}-{$row->flat_number}")
                    ->editColumn(
                        'entry_date',
                        fn ($row) => $row->entry_time?->format('d M Y') ?? '-'
                    )
                    ->editColumn(
                        'entry_time',
                        fn ($row) => $row->entry_time?->format('h:i A') ?? '-'
                    )
                    ->editColumn(
                        'exit_date',
                        fn ($row) => $row->exit_time?->format('d M Y') ?? '-'
                    )
                    ->editColumn(
                        'exit_time',
                        fn ($row) => $row->exit_time?->format('h:i A') ?? '-'
                    )
                    ->editColumn('visitor_name', fn ($row) => $row->visitor_name ?? '-')
                    ->editColumn('phone', fn ($row) => $row->visitor_phone ?? '-')
                    ->editColumn('photo_path', function ($row) {
                        if (! $row->photo_path) {
                            return '-';
                        }

                        // Check if the path is a Cloudinary URL or local path
                        $photoUrl = str_starts_with($row->photo_path, 'http')
                            ? $row->photo_path
                            : asset('storage/'.$row->photo_path);

                        return '
-                            <a href="'.asset('storage/'.$row->photo_path).'"
+                            <a href="'.$photoUrl.'"
                            target="_blank"
                            class="btn btn-info btn-sm"
                            title="View Photo">
                                <i class="bi bi-eye"></i>
                            </a>
                        ';
                    })
                    ->orderColumn('flat_details', function ($query, $order) {
                        $query->orderBy('flats.wing', $order)
                            ->orderBy('flats.flat_number', $order);
                    })
                    ->rawColumns(['photo_path'])
                    ->make(true);
            }

            return view('visitor-passes.exited');
        } catch (Exception $e) {
            Log::error('Visitor log exited page datatable error: '.$e->getMessage(), ['exception' => $e]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong while loading visitor logs.',
                ], 500);
            }

            return redirect()->back()->with([
                'message' => 'Something went wrong. Please try again.',
                'status' => 'error',
            ]);
        }
    }

    public function restore(VisitorLog $visitorLog)
    {
        $this->authorize('restore', $visitorLog);
        try {
            $visitorLog->restore();
            ActivityLogger::log('restore', $visitorLog, 'Visitor pass restored.');

            return response()->json([
                'success' => true,
                'message' => 'Visitor pass restored successfully.',
            ]);
        } catch (Exception $e) {

            Log::error('Visitor Pass Restore Error', ['visitor_log_id' => $visitorLog->id, 'user_id' => auth()->id(), 'error' => $e->getMessage(), 'exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to restore visitor pass.',
            ], 500);
        }
    }
}
