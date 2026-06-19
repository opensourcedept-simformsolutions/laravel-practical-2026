<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class VisitorLogController extends Controller
{
    public function pending(Request $request)
    {
        try {
            if ($request->ajax()) {

                $query = VisitorLog::query()
                    ->select('visitor_logs.*')
                    ->with(['visitor', 'flat']);

                return DataTables::of($query)

                    ->addColumn('visitor_name', function ($log) {
                        return $log->visitor?->name ?? 'N/A';
                    })

                    ->addColumn('phone', function ($log) {
                        return $log->visitor?->phone ?? 'N/A';
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

                        if ($log->status === 'pending') {

                            return '
                                <form action="' . route('gatekeeper.visitor-logs.mark-entry', $log) . '" method="POST">
                                    ' . csrf_field() . '
                                    <input type="hidden" name="_method" value="PATCH">
                                    <button class="btn btn-success btn-sm">
                                        Mark Entry
                                    </button>
                                </form>
                            ';
                        }

                        if ($log->status === 'entered') {

                            return '
                                <form action="' . route('gatekeeper.visitor-logs.mark-exit', $log) . '" method="POST">
                                    ' . csrf_field() . '
                                    <input type="hidden" name="_method" value="PATCH">
                                    <button class="btn btn-danger btn-sm">
                                        Mark Exit
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

            return view('visitor-logs.pending');

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

            return redirect()->back()->with(['message'=>'Something went wrong. Please try again.', 'status' => 'error']);
        }
    }

    public function markEntry(VisitorLog $visitorLog)
    {
        try {

            if ($visitorLog->status !== 'pending') {
                return redirect()->back()->with(['message' => 'Only Pending Passes Can Be Entered!', 'status' => 'error']);
            }

            $visitorLog->update([
                'entry_time' => now(),
                'gatekeeper_id' => auth()->id(),
                'status' => 'entered',
            ]);

            return redirect()->back()->with(['message' => 'Visitor Entry Marked Successfully!', 'status' => 'success']);

        } catch (Exception $e) {

            Log::error('Visitor Entry Error: ' . $e->getMessage(), [
                'visitor_log_id' => $visitorLog->id,
                'exception' => $e,
            ]);

            return redirect()->back()->with([
                'message' => 'Something went wrong while marking visitor entry.',
                'status' => 'error'
            ]);
        }
    }

    public function markExit(VisitorLog $visitorLog)
    {
        try {

            if ($visitorLog->status !== 'entered') {
                return redirect()->back()->with('error', 'Only Entered Visitors Can Exit!');
            }

            $visitorLog->update([
                'exit_time' => now(),
                'status' => 'exited',
            ]);

            return redirect()->back()->with(['message' => 'Visitor Exit Marked Successfully!', 'status' => 'sucess']);

        } catch (Exception $e) {

            Log::error('Visitor Exit Error: ' . $e->getMessage(), [
                'visitor_log_id' => $visitorLog->id,
                'exception' => $e,
            ]);

            return redirect()->back()->with(
                'error',
                'Something went wrong while marking visitor exit.'
            );
        }
    }
}
