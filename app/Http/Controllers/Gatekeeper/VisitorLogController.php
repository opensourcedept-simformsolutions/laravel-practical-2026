<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Events\VisitorEntered;
use App\Events\VisitorExited;
use App\Models\VisitorLog;
use App\Services\ActivityLogger;
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
            $visitorLogId = decrypt($request->qr_code);

            $visitorLog = VisitorLog::with([
                'visitor',
                'flat',
            ])->findOrFail($visitorLogId);
        } catch (Exception $e) {
            Log::error('VisitorLogController findPass decryption/notfound error: ' . $e->getMessage(), [
                'exception' => $e,
                'qr_code' => $request->qr_code
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired QR pass.',
            ], 422);
        }

        $this->authorize('view', $visitorLog);

        if ($visitorLog->status !== 'pending') {

            return response()->json([
                'success' => false,
                'message' => 'Visitor already entered or pass not valid.',
            ], 422);
        }

        return response()->json([
            'id' => $visitorLog->id,
            'visitor' => $visitorLog->visitor->name,
            'phone' => $visitorLog->visitor->phone,
            'purpose' => $visitorLog->purpose,
            'status' => $visitorLog->status,
            'flat' => $visitorLog->flat->wing.'-'.$visitorLog->flat->flat_number,
        ]);
    }

    public function pending(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            if ($request->ajax()) {

                $user = auth()->user();

                $query = VisitorLog::query()
                    ->whereIn('visitor_logs.status', ['pending', 'entered'])
                    ->with(['visitor', 'flat'])
                    ->select([
                        'visitor_logs.*',
                        'visitors.name as visitor_name_val',
                        'visitors.phone as visitor_phone_val',
                        'societies.name as society_name_val',
                    ])
                    ->leftJoin('visitors', 'visitor_logs.visitor_id', '=', 'visitors.id')
                    ->leftJoin('flats', 'visitor_logs.flat_id', '=', 'flats.id')
                    ->leftJoin('societies', 'flats.society_id', '=', 'societies.id')
                    ->orderByRaw("
                        CASE
                            WHEN visitor_logs.status = 'pending' THEN 1
                            WHEN visitor_logs.status = 'entered' THEN 2
                        END
                    ");

                if (!$user->isSuperAdmin()) {
                    $query->where('flats.society_id', $user->society_id);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('visitor_name', function ($log) {
                        return $log->visitor_name_val ?? 'N/A';
                    })
                    ->addColumn('society', function ($log) {
                        return $log->society_name_val ?? 'N/A';
                    })
                    ->addColumn('phone', function ($log) {
                        return $log->visitor_phone_val ?? 'N/A';
                    })

                    ->addColumn('flat_details', function ($log) {
                        return ($log->flat?->wing ?? '-') . ' - ' . ($log->flat?->flat_number ?? '-');
                    })

                    ->editColumn('status', function ($log) {
                        return match ($log->status) {
                            'pending' => '<span class="badge bg-warning">Pending</span>',
                            'entered' => '<span class="badge bg-success">Entered</span>',
                            'exited' => '<span class="badge bg-secondary">Exited</span>',
                            default => ucfirst($log->status),
                        };
                    })

                    ->addColumn('action', function ($log) {

                        $buttons = '';

                        if ($log->status === 'pending') {

                            if (auth()->user()->can('markEntry', $log)) {
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

                            if (auth()->user()->can('update', $log)) {
                                $buttons .= '
                                    <a href="'.route('passes.edit', $log).'"
                                    class="btn btn-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                ';
                            }

                            if (auth()->user()->can('delete', $log)) {
                                $buttons .= '
                                    <form action="'.route('passes.destroy', $log).'"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm(\'Delete this pass?\');">

                                        '.csrf_field().'
                                        '.method_field('DELETE').'

                                        <button type="submit"
                                                class="btn btn-danger btn-sm" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                ';
                            }

                            return '
                                <div class="d-flex gap-1">
                                    '.$buttons.'
                                </div>
                            ';
                        }

                        if ($log->status === 'entered') {

                            return '
                                <form action="'.route('gatekeeper.visitor-logs.mark-exit', $log).'"
                                    method="POST">

                                    '.csrf_field().'
                                    '.method_field('PATCH').'

                                    <button type="submit"
                                            class="btn btn-danger btn-sm" title="Mark Exit">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </form>
                            ';
                        }

                        return '<span class="badge bg-secondary">Exited</span>';
                    })
                    ->rawColumns([
                        'status',
                        'action',
                    ])
                    ->make(true);
            }

            return view('visitor-passes.pending');
        } catch (Exception $e) {

            Log::error('Visitor Log Pending Error: ' . $e->getMessage(), [
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
                'photo' => ['required','image','mimes:jpg,jpeg,png','max:2048'],
            ]);

            if ($visitorLog->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Pending Passes Can Be Entered!',
                ], 422);
            }

            $visitorLog->entry_time = now();
            $visitorLog->gatekeeper_id = auth()->id();
            $visitorLog->status = 'entered';
            $visitorLog->photo_path = $request->file('photo')->store('visitor_photos', 'public');
            $visitorLog->save();

            ActivityLogger::log('mark_entry', $visitorLog, "Visitor {$visitorLog->visitor->name} entered flat " . ($visitorLog->flat?->wing ?? '-') . "-" . ($visitorLog->flat?->flat_number ?? '-') . ".");

            Log::info('VisitorEntered event dispatching', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            event(
                new VisitorEntered($visitorLog)
            );

            Log::info('VisitorEntered event dispatched', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor Entry Marked Successfully!',
            ]);
        } catch (Exception $e) {

            Log::error('Visitor Entry Error: ' . $e->getMessage(), [
                'visitor_log_id' => $visitorLog->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something Went Wrong While Mark Entry',
            ], 500);
        }
    }

    public function markExit(VisitorLog $visitorLog)
    {
        $this->authorize('markExit', $visitorLog);
        try {
            if ($visitorLog->status !== 'entered') {
                return redirect()->back()->with([
                    'message' => 'Only Entered Visitors Can Exit!',
                    'status' => 'error',
                ]);
            }

            $visitorLog->exit_time = now();
            $visitorLog->status = 'exited';
            $visitorLog->save();

            ActivityLogger::log('mark_exit', $visitorLog, "Visitor {$visitorLog->visitor->name} exited flat " . ($visitorLog->flat?->wing ?? '-') . "-" . ($visitorLog->flat?->flat_number ?? '-') . ".");

            Log::info('VisitorExited event dispatching', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            event(
                new VisitorExited($visitorLog)
            );

            Log::info('VisitorExited event dispatched', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            return redirect()->back()->with([
                'message' => 'Visitor Exit Marked Successfully!',
                'status' => 'success',
            ]);
        } catch (Exception $e) {

            Log::error('Visitor Exit Error: ' . $e->getMessage(), [
                'visitor_log_id' => $visitorLog->id,
                'exception' => $e,
            ]);

            return redirect()->back()->with([
                'message' => 'Something Went Wrong While Mark Exit',
                'status' => 'error',
            ]);
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
                        'visitors.name as visitor_name_val',
                        'visitors.phone as visitor_phone_val',
                    ])
                    ->leftJoin('visitors', 'visitor_logs.visitor_id', '=', 'visitors.id')
                    ->leftJoin('flats', 'visitor_logs.flat_id', '=', 'flats.id');

                if (!$user->isSuperAdmin()) {
                    $query->where('flats.society_id', $user->society_id);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('visitor_name', fn($row) => $row->visitor_name_val ?? 'N/A')
                    ->addColumn('phone', fn($row) => $row->visitor_phone_val ?? 'N/A')
                    ->addColumn(
                        'flat_details',
                        fn($row) => ($row->flat?->wing ?? '-') . '-' .
                            ($row->flat?->floor ?? '-') . '-' .
                            ($row->flat?->flat_number ?? '-')
                    )
                    ->addColumn(
                        'entry_date',
                        fn($row) => $row->entry_time?->format('d M Y') ?? '-'
                    )
                    ->addColumn(
                        'entry_time',
                        fn($row) => $row->entry_time?->format('h:i A') ?? '-'
                    )
                    ->addColumn(
                        'exit_date',
                        fn($row) => $row->exit_time?->format('d M Y') ?? '-'
                    )
                    ->addColumn(
                        'exit_time',
                        fn($row) => $row->exit_time?->format('h:i A') ?? '-'
                    )
                    ->addColumn('photo', function ($row) {
                        if(!$row->photo_path){
                            return '-';
                        }
                        return '
                            <a href="'.asset('storage/'.$row->photo_path).'" target="_blank" class="btn btn-info btn-sm" title="View Photo">
                                <i class="bi bi-eye"></i>
                            </a>
                        ';
                    })
                    ->rawColumns(['photo'])
                    ->make(true);
            }

            return view('visitor-passes.exited');
        } catch (Exception $e) {
            Log::error('Visitor log exited page datatable error: ' . $e->getMessage(), ['exception' => $e]);
            
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
}
