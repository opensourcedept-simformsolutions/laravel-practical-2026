<?php

namespace App\Http\Controllers;

use App\Events\VisitorEntered;
use App\Events\VisitorExited;
use App\Models\VisitorLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;


class VisitorLogController extends Controller
{
    public function pending(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            if ($request->ajax()) {

            $user = auth()->user();

            $query = VisitorLog::query()
                ->whereIn('status', ['pending', 'entered'])
                ->with([
                    'visitor',
                    'flat',
                ])
                ->select('visitor_logs.*')
                ->orderByRaw("
                    CASE
                        WHEN status = 'pending' THEN 1
                        WHEN status = 'entered' THEN 2
                    END
                ")->latest();


            if (!$user->isSuperAdmin()) {

                $query->whereHas('flat', function ($q) use ($user) {
                    $q->where('society_id', $user->society_id);
                });
            }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('visitor_name', function ($log) {
                        return $log->visitor?->name ?? 'N/A';
                    })
                    ->addColumn('socity',function($log){
                        return $log->flat->society->name ?? 'N/A';
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

        if ($request->ajax()) {

        $user = auth()->user();

        $query = VisitorLog::with([
            'visitor',
            'flat',
        ])->where('status', 'exited');

        if (!$user->isSuperAdmin()) {

            $query->whereHas('flat', function ($q) use ($user) {
                $q->where('society_id', $user->society_id);
            });
        }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('visitor_name', fn($row) => $row->visitor?->name ?? 'N/A')
                ->addColumn('phone', fn($row) => $row->visitor?->phone ?? 'N/A')
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

        return view('visitor-logs.exited');
    }
}
