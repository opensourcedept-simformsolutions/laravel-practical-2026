<?php

namespace App\Http\Controllers\VisitorPass;

use App\Enums\VisitorStatus;
use App\Events\VisitorApprovalRecalled;
use App\Events\VisitorApprovalRequested;
use App\Events\VisitorApprovalStatusUpdated;
use App\Events\VisitorEntered;
use App\Http\Controllers\Controller;
use App\Http\Requests\VisitorPass\StoreVisitorPassRequest;
use App\Http\Requests\VisitorPass\UpdateVisitorPassRequest;
use App\Models\Flat;
use App\Models\Notification;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Notifications\VisitorStatusNotification;
use App\Services\ActivityLogger;
use App\Traits\AppliesDataTableFilters;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class VisitorPassController extends Controller
{
    use AppliesDataTableFilters;

    public function index()
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            return view('visitor-passes.index');
        } catch (Exception $e) {
            Log::error('Visitor pass index page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function data(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            $user = auth()->user();

            $query = VisitorLog::query()
                ->select([
                    'visitor_logs.*',
                    'visitors.name as visitor_name',
                    'visitors.phone as visitor_phone',
                    'wings.name as flat_wing',
                    'flats.flat_number as flat_number',
                    'creators.name as creator_name',
                    'gatekeepers.name as gatekeeper_name',
                ])
                ->leftJoin('visitors', 'visitors.id', '=', 'visitor_logs.visitor_id')
                ->leftJoin('flats', 'flats.id', '=', 'visitor_logs.flat_id')
                ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id')
                ->leftJoin('users as creators', 'creators.id', '=', 'visitor_logs.created_by')
                ->leftJoin('users as gatekeepers', 'gatekeepers.id', '=', 'visitor_logs.gatekeeper_id');

            if (! $user->isSuperAdmin()) {
                if ($user->isResident()) {
                    $query->where('visitor_logs.flat_id', $user->resident->flat_id);
                } else {
                    $query->whereHas('flat', function ($q) use ($user) {
                        $q->where('society_id', $user->society_id);
                    });
                }
            }

            match ($request->get('filter', 'active')) {
                'deleted' => $query->onlyTrashed(),
                'all' => $query->withTrashed(),
                default => null,
            };

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('visitor', fn ($row) => $row->visitor_name ?? '-')
                ->editColumn('phone', fn ($row) => $row->visitor_phone ?? '-')
                ->editColumn(
                    'flat',
                    fn ($row) => $row->flat_wing && $row->flat_number
                        ? $row->flat_wing.'-'.$row->flat_number
                        : '-'
                )
                ->editColumn('creator', fn ($row) => $row->creator_name ?? '-')
                ->editColumn('gatekeeper', fn ($row) => $row->gatekeeper_name ?? '-')
                ->editColumn('purpose', fn ($row) => $row->purpose ?? '-')
                ->editColumn('status', fn ($row) => ucfirst($row->status))
                ->editColumn(
                    'entry_time',
                    fn ($row) => $row->entry_time
                        ? format_date($row->entry_time)
                        : '-'
                )
                ->editColumn(
                    'exit_time',
                    fn ($row) => $row->exit_time
                        ? format_date($row->exit_time)
                        : '-'
                )
                ->editColumn(
                    'visit_date',
                    fn ($row) => $row->visit_date
                        ? format_date($row->visit_date, 'd M Y')
                        : '-'
                )
                ->addColumn('actions', function ($row) {
                    $user = auth()->user();
                    $actions = '<div class="d-flex justify-content-center gap-2">';

                    if ($user->can('view', $row)) {
                        $actions .= '
                            <a href="'.route('passes.show', $row->id).'" class="btn btn-info text-white btn-sm" title="View Pass">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        ';
                    }

                    if ($row->trashed()) {
                        if ($user->can('restore', $row)) {
                            $actions .= '
                                <button
                                    class="btn btn-secondary btn-action btn-sm text-white"
                                    data-url="'.route('passes.restore', $row->id).'"
                                    data-method="PATCH"
                                    data-title="Restore Visitor Pass?"
                                    data-text="This visitor pass will be restored."
                                    data-confirm="Yes, Restore"
                                    data-success="Visitor pass restored successfully"
                                    title="Restore Pass">
                                    <i class="bi bi-arrow-up-left-circle-fill"></i>
                                </button>
                            ';
                        }
                    } else {
                        if ($row->status === VisitorStatus::PENDING_APPROVAL->value && $user->isResident() && $user->resident && (int) $row->flat_id === (int) $user->resident->flat_id) {
                            $actions .= '
                                <button
                                    type="button"
                                    class="btn btn-success btn-action btn-sm text-white"
                                    data-url="'.route('passes.approve', $row->id).'"
                                    data-method="PATCH"
                                    data-title="Approve Visitor?"
                                    data-text="Confirm you want to allow this visitor entry."
                                    data-confirm="Approve"
                                    data-success="Visitor approved successfully"
                                    data-color="#198754"
                                    title="Approve">
                                    <i class="bi bi-check-circle-fill"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-danger btn-action btn-sm text-white"
                                    data-url="'.route('passes.reject', $row->id).'"
                                    data-method="PATCH"
                                    data-title="Reject Visitor?"
                                    data-text="Confirm you want to deny this visitor entry."
                                    data-confirm="Reject"
                                    data-success="Visitor rejected successfully"
                                    data-color="#dc3545"
                                    title="Reject">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            ';
                        }

                        if ($user->can('update', $row)) {
                            $actions .= '
                                <a href="'.route('passes.edit', $row->id).'" class="btn btn-primary btn-sm" title="Edit Pass">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                            ';
                        }

                        if ($user->can('cancel', $row)) {
                            $actions .= '
                                <button
                                    class="btn btn-warning btn-action btn-sm text-white"
                                    data-url="'.route('passes.cancel', $row->id).'"
                                    data-method="PATCH"
                                    data-title="Cancel Visitor Pass?"
                                    data-text="This action cannot be undone."
                                    data-confirm="Yes, Cancel"
                                    data-success="Visitor pass cancelled successfully"
                                    title="Cancel Pass">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            ';
                        }

                        if ($user->can('delete', $row)) {
                            $actions .= '
                                <button
                                    class="btn btn-danger btn-action btn-sm text-white"
                                    data-url="'.route('passes.destroy', $row->id).'"
                                    data-method="DELETE"
                                    data-title="Delete Visitor Pass?"
                                    data-text="This action cannot be undone."
                                    data-confirm="Yes, Delete"
                                    data-success="Visitor pass deleted successfully"
                                    title="Delete Pass">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            ';
                        }
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Visitor pass datatable loading error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load visitor pass data.',
            ], 500);
        }
    }

    public function create()
    {
        $this->authorize('create', VisitorLog::class);

        try {
            return view('visitor-passes.create', ! auth()->user()->isResident() ? ['flats' => $this->getFlatOptions()] : []);
        } catch (Exception $e) {
            Log::error('Visitor pass create page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function store(StoreVisitorPassRequest $request)
    {
        $this->authorize('create', VisitorLog::class);

        try {

            $validated = $request->validated();
            $user = auth()->user();

            $visitorLog = null;
            DB::transaction(function () use ($validated, $user, $request, &$visitorLog) {

                $visitor = Visitor::updateOrCreate(
                    [
                        'phone' => $validated['phone'],
                    ],
                    [
                        'name' => $validated['name'],
                        'vehicle_number' => $validated['vehicle_number'] ?? null,
                    ]
                );

                if (! $user->isResident()) {

                    $flat = Flat::where('id', $validated['flat_id'])
                        ->where('society_id', $user->society_id)
                        ->firstOrFail();

                    $flatId = $flat->id;
                } else {

                    $flatId = $user->resident->flat_id;
                }

                $visitorLog = new VisitorLog;

                $visitorLog->visitor_id = $visitor->id;
                $visitorLog->flat_id = $flatId;
                $visitorLog->created_by = $user->id;
                $visitorLog->purpose = $validated['purpose'];
                $visitorLog->status = $user->isGatekeeper() ? VisitorStatus::PENDING_APPROVAL->value : VisitorStatus::PENDING->value;
                $visitorLog->visit_date = $user->isGatekeeper() ? today()->toDateString() : $validated['visit_date'];

                if ($user->isGatekeeper()) {
                    if ($request->hasFile('uploaded_photo')) {
                        $path = $request->file('uploaded_photo')->store('visitor_photos', 'public');
                        $visitorLog->photo_path = $path;
                    } elseif ($request->filled('captured_photo')) {
                        $img = $request->input('captured_photo');
                        $img = str_replace('data:image/jpeg;base64,', '', $img);
                        $img = str_replace('data:image/png;base64,', '', $img);
                        $img = str_replace(' ', '+', $img);
                        $data = base64_decode($img);

                        if (!\Storage::disk('public')->exists('visitor_photos')) {
                            \Storage::disk('public')->makeDirectory('visitor_photos');
                        }

                        $filename = 'visitor_photos/' . uniqid() . '.jpg';
                        \Storage::disk('public')->put($filename, $data);
                        $visitorLog->photo_path = $filename;
                    }
                }

                $visitorLog->save();
            });

            if ($visitorLog) {
                ActivityLogger::log('create', $visitorLog, "Visitor Pass for {$visitorLog->visitor->name} was created.");
                if ($visitorLog->status === VisitorStatus::PENDING_APPROVAL->value) {
                    $residents = User::whereHas('resident', function ($q) use ($visitorLog) {
                        $q->where('flat_id', $visitorLog->flat_id);
                    })->get();

                    foreach ($residents as $residentUser) {
                        try {
                            $residentUser->notify(new VisitorStatusNotification($visitorLog));
                        } catch (Exception $e) {
                            Log::error('Failed to notify flat member of pending approval: '.$e->getMessage());
                        }
                    }
                    try {
                        event(new VisitorApprovalRequested($visitorLog));
                    } catch (Exception $e) {
                        Log::error('Failed to broadcast VisitorApprovalRequested on create: '.$e->getMessage());
                    }
                }
            }

            Session::flash('message', 'Visitor Pass Created Successfully.');
            Session::flash('status', 'success');

            if ($user->isGatekeeper()) {
                return redirect()->route('gatekeeper.visitor-logs.pending');
            }

            return redirect()->route('passes.index');
        } catch (Exception $e) {
            Log::error('Visitor pass store error: '.$e->getMessage(), ['exception' => $e]);

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function show(VisitorLog $visitorLog)
    {
        $this->authorize('view', $visitorLog);

        try {
            $visitorLog->load([
                'visitor',
                'flat',
                'gatekeeper',
            ]);

            return view('visitor-passes.show', compact('visitorLog'));
        } catch (Exception $e) {
            Log::error('Visitor pass show page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function edit(VisitorLog $visitorLog)
    {
        $this->authorize('update', $visitorLog);

        try {
            return view('visitor-passes.edit', ['visitorLog' => $visitorLog, 'flats' => auth()->user()->isResident() ? collect() : $this->getFlatOptions()]);
        } catch (Exception $e) {
            Log::error('Visitor pass edit page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function update(UpdateVisitorPassRequest $request, VisitorLog $visitorLog)
    {
        $this->authorize('update', $visitorLog);

        try {
            $validated = $request->validated();
            $user = auth()->user();
            $oldFlatId = (int) $visitorLog->flat_id;

            DB::transaction(function () use ($validated, $visitorLog, $user) {
                $visitor = \App\Models\Visitor::updateOrCreate(
                    [
                        'phone' => $validated['phone'],
                    ],
                    [
                        'name' => $validated['name'],
                        'vehicle_number' => $validated['vehicle_number'] ?? null,
                    ]
                );

                $visitorLog->visitor_id = $visitor->id;
                $visitorLog->updated_by = $user->id;

                if (! $user->isResident()) {
                    if (! empty($validated['flat_id'])) {
                        $visitorLog->flat_id = $validated['flat_id'];
                    }
                }

                $visitorLog->purpose = $validated['purpose'];
                $visitorLog->visit_date = $user->isGatekeeper() ? today()->toDateString() : $validated['visit_date'];
                $visitorLog->save();
            });

            Session::flash('message', 'Visitor Pass updated successfully.');
            Session::flash('status', 'success');

            ActivityLogger::log('update', $visitorLog, "Visitor Pass for {$visitorLog->visitor->name} was updated.");

            if ($oldFlatId !== (int) $visitorLog->flat_id && in_array($visitorLog->status, [VisitorStatus::PENDING_APPROVAL->value, VisitorStatus::APPROVED->value, VisitorStatus::REJECTED->value])) {
                // Delete notifications for the old flat
                Notification::whereJsonContains('data->visitor_log_id', $visitorLog->id)->delete();

                // Reset the status to pending_approval and clear approver (since it was wrong flat)
                $visitorLog->update([
                    'status' => VisitorStatus::PENDING_APPROVAL->value,
                    'approved_by' => null,
                ]);

                // Broadcast recall to old flat
                try {
                    event(new VisitorApprovalRecalled($visitorLog, $oldFlatId));
                } catch (Exception $e) {
                    Log::error('Failed to broadcast VisitorApprovalRecalled on update: '.$e->getMessage());
                }

                // Log the correction in ActivityLog
                ActivityLogger::log('update', $visitorLog, "Visitor Pass flat corrected from Flat ID {$oldFlatId} to Flat ID {$visitorLog->flat_id}. Status reset to pending_approval.");

                // Notify all members of the new flat
                $residents = User::whereHas('resident', function ($q) use ($visitorLog) {
                    $q->where('flat_id', $visitorLog->flat_id);
                })->get();

                foreach ($residents as $residentUser) {
                    try {
                        $residentUser->notify(new VisitorStatusNotification($visitorLog));
                    } catch (Exception $e) {
                        Log::error('Failed to notify flat member of pending approval: '.$e->getMessage());
                    }
                }

                // Broadcast request to new flat
                try {
                    event(new VisitorApprovalRequested($visitorLog));
                } catch (Exception $e) {
                    Log::error('Failed to broadcast VisitorApprovalRequested on update: '.$e->getMessage());
                }
            }

            if ($user->isGatekeeper()) {
                return redirect()->route('gatekeeper.visitor-logs.pending');
            }

            return redirect()->route('passes.index');
        } catch (Exception $e) {
            Log::error('Visitor pass update error: '.$e->getMessage(), ['exception' => $e]);

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function cancel(VisitorLog $visitorLog)
    {
        $this->authorize('cancel', $visitorLog);

        try {
            if (! in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value])) {

                return response()->json([
                    'success' => false,
                    'message' => 'This visitor pass cannot be cancelled.',
                ], 422);
            }

            $visitorLog->update([
                'status' => VisitorStatus::CANCELLED->value,
                'updated_by' => auth()->id(),
            ]);

            ActivityLogger::log('cancel', $visitorLog, "Visitor Pass for {$visitorLog->visitor->name} was cancelled.");

            return response()->json([
                'success' => true,
                'message' => 'Visitor pass cancelled successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Visitor pass cancel error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function report(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            $flats = Flat::orderBy('wing')
                ->orderBy('floor')
                ->orderBy('flat_number')
                ->get();

            return view('visitor-passes.report', compact('flats'));
        } catch (Exception $e) {
            Log::error('Visitor pass report page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    public function reportData(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            $query = $this->getReportQuery($request);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('flat', function ($row) {
                    return "{$row->flat_wing}-{$row->flat_number}";
                })
                ->addColumn(
                    'entry_time',
                    fn ($row) => $row->entry_time ? format_date($row->entry_time) : '-'
                )
                ->addColumn(
                    'exit_time',
                    fn ($row) => $row->exit_time ? format_date($row->exit_time) : '-'
                )
                ->addColumn(
                    'visit_date',
                    fn ($row) => $row->visit_date ? format_date($row->visit_date, 'd M Y') : '-'
                )
                ->editColumn('society', fn ($row) => $row->society ?? '-')
                ->editColumn('visitor', fn ($row) => $row->visitor_name ?? '-')
                ->editColumn('phone', fn ($row) => $row->visitor_phone ?? '-')
                ->editColumn('status', fn ($row) => ucfirst($row->status))
                ->editColumn('gatekeeper', fn ($row) => $row->gatekeeper_name ?? '-')
                ->orderColumn('flat', function ($query, $order) {
                    $query->orderBy('wings.name', $order)
                        ->orderBy('flats.flat_number', $order);
                })
                ->rawColumns([])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Visitor pass report data loading error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load report data.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', VisitorLog::class);

        try {
            $query = $this->getReportQuery($request);
            $query = $this->applyDataTableFilters(
                $query,
                $request,
                [
                    'visitors.name',
                    'visitors.phone',
                    'societies.name',
                    'visitor_logs.purpose',
                    'visitor_logs.status',
                    'gatekeepers.name',
                ],
                [
                    'flat' => function ($query, $direction) {
                        $query->orderBy('wings.name', $direction)
                            ->orderBy('flats.floor', $direction)
                            ->orderBy('flats.flat_number', $direction);
                    },
                ]
            );

            return response()->streamDownload(function () use ($query) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Society',
                    'Visitor',
                    'Phone',
                    'Flat',
                    'Purpose',
                    'Status',
                    'Entry Time',
                    'Exit Time',
                    'Visit Date',
                    'Gatekeeper',
                ]);

                foreach ($query->get() as $visitorLog) {
                    fputcsv($handle, [
                        $visitorLog->id,
                        $visitorLog->society ?? '-',
                        $visitorLog->visitor_name ?? '-',
                        $visitorLog->visitor_phone ?? '-',
                        $visitorLog->flat_wing && $visitorLog->flat_number ? $visitorLog->flat_wing.'-'.$visitorLog->flat_number : '-',
                        $visitorLog->purpose ?? '-',
                        ucfirst($visitorLog->status),
                        $visitorLog->entry_time ? date('Y-m-d H:i:s', strtotime($visitorLog->entry_time)) : '-',
                        $visitorLog->exit_time ? date('Y-m-d H:i:s', strtotime($visitorLog->exit_time)) : '-',
                        $visitorLog->visit_date ? date('Y-m-d', strtotime($visitorLog->visit_date)) : '-',
                        $visitorLog->gatekeeper_name ?? '-',
                    ]);
                }

                fclose($handle);
            }, 'visitor-report.csv');
        } catch (Exception $e) {
            Log::error('Visitor pass report export error: '.$e->getMessage(), ['exception' => $e]);

            return back()->with([
                'message' => 'Something went wrong.',
                'status' => 'error',
            ]);
        }
    }

    private function getReportQuery(Request $request)
    {
        $user = auth()->user();

        $query = VisitorLog::query()
            ->select([
                'visitor_logs.*',
                'visitors.name as visitor_name',
                'visitors.phone as visitor_phone',
                'wings.name as flat_wing',
                'flats.floor as flat_floor',
                'flats.flat_number',
                'creators.name as creator_name',
                'gatekeepers.name as gatekeeper_name',
                'societies.name as society',
            ])
            ->leftJoin('visitors', 'visitors.id', '=', 'visitor_logs.visitor_id')
            ->leftJoin('flats', 'flats.id', '=', 'visitor_logs.flat_id')
            ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id')
            ->leftJoin('users as creators', 'creators.id', '=', 'visitor_logs.created_by')
            ->leftJoin('users as gatekeepers', 'gatekeepers.id', '=', 'visitor_logs.gatekeeper_id')
            ->leftJoin('societies', 'societies.id', '=', 'flats.society_id');

        if (! $user->isSuperAdmin()) {
            if ($user->isResident()) {
                $query->where('visitor_logs.flat_id', $user->resident->flat_id);
            } else {
                $query->where('flats.society_id', $user->society_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('visitor_logs.status', $request->status);
        }

        if ($request->filled('flat_id')) {
            $query->where('visitor_logs.flat_id', $request->flat_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('visitor_logs.visit_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('visitor_logs.visit_date', '<=', $request->to_date);
        }

        return $query;
    }

    public function destroy(VisitorLog $visitorLog)
    {
        $this->authorize('delete', $visitorLog);

        try {

            if (! in_array($visitorLog->status, [VisitorStatus::PENDING->value, VisitorStatus::PENDING_APPROVAL->value, VisitorStatus::REJECTED->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This pass cannot be deleted.',
                ], 422);
            }

            ActivityLogger::log(
                'delete',
                $visitorLog,
                "Visitor Pass for {$visitorLog->visitor->name} was deleted."
            );

            $visitorLog->update([
                'updated_by' => auth()->id(),
            ]);
            $visitorLog->delete();

            return response()->json([
                'success' => true,
                'message' => 'Visitor pass deleted successfully.',
            ]);
        } catch (Exception $e) {

            Log::error('Visitor pass delete error: '.$e->getMessage(), [
                'exception' => $e,
                'visitor_log_id' => $visitorLog->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting the visitor pass.',
            ], 500);
        }
    }

    public function restore(VisitorLog $visitorLog)
    {
        $this->authorize('restore', $visitorLog);

        try {
            $visitorLog->restore();

            ActivityLogger::log('restore', $visitorLog, "Visitor Pass for {$visitorLog->visitor->name} was restored.");

            return response()->json([
                'success' => true,
                'message' => 'Visitor pass restored successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Visitor pass restore error: '.$e->getMessage(), [
                'exception' => $e,
                'visitor_log_id' => $visitorLog->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while restoring the visitor pass.',
            ], 500);
        }
    }

    public function approve(VisitorLog $visitorLog)
    {
        $user = auth()->user();
        if (! $user->isResident() || ! $user->resident || (int) $visitorLog->flat_id !== (int) $user->resident->flat_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($visitorLog->status !== VisitorStatus::PENDING_APPROVAL->value) {
            $msg = 'This visitor pass cannot be approved.';
            if (in_array($visitorLog->status, [VisitorStatus::APPROVED->value, VisitorStatus::ENTERED->value])) {
                $msg = 'This visitor pass has already been approved.';
            } elseif ($visitorLog->status === VisitorStatus::REJECTED->value) {
                $msg = 'This visitor pass has already been rejected.';
            }

            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 422);
        }

        $visitorLog->update([
            'status' => VisitorStatus::APPROVED->value,
            'approved_by' => $user->id,
        ]);

        ActivityLogger::log('approve', $visitorLog, "Visitor Pass for {$visitorLog->visitor->name} was approved.");

        // Delete existing notifications for this visitor log
        Notification::whereJsonContains('data->visitor_log_id', $visitorLog->id)->delete();

        // Send notifications to all members of the flat
        $residents = User::whereHas('resident', function ($q) use ($visitorLog) {
            $q->where('flat_id', $visitorLog->flat_id);
        })->get();

        foreach ($residents as $residentUser) {
            try {
                $residentUser->notify(new VisitorStatusNotification($visitorLog));
            } catch (Exception $e) {
                Log::error('Failed to notify flat member: '.$e->getMessage());
            }
        }

        try {
            event(new VisitorApprovalStatusUpdated($visitorLog));
        } catch (Exception $e) {
            Log::error('Failed to broadcast VisitorApprovalStatusUpdated on approve: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Visitor request approved successfully.',
        ]);
    }

    public function reject(VisitorLog $visitorLog)
    {
        $user = auth()->user();
        if (! $user->isResident() || ! $user->resident || (int) $visitorLog->flat_id !== (int) $user->resident->flat_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($visitorLog->status !== VisitorStatus::PENDING_APPROVAL->value) {
            $msg = 'This visitor pass cannot be rejected.';
            if (in_array($visitorLog->status, [VisitorStatus::APPROVED->value, VisitorStatus::ENTERED->value])) {
                $msg = 'This visitor pass has already been approved.';
            } elseif ($visitorLog->status === VisitorStatus::REJECTED->value) {
                $msg = 'This visitor pass has already been rejected.';
            }

            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 422);
        }

        $visitorLog->update([
            'status' => VisitorStatus::REJECTED->value,
            'approved_by' => $user->id,
        ]);

        ActivityLogger::log('reject', $visitorLog, "Visitor Pass for {$visitorLog->visitor->name} was rejected.");

        // Delete existing notifications for this visitor log
        Notification::whereJsonContains('data->visitor_log_id', $visitorLog->id)->delete();

        // Send notifications to all members of the flat
        $residents = User::whereHas('resident', function ($q) use ($visitorLog) {
            $q->where('flat_id', $visitorLog->flat_id);
        })->get();

        foreach ($residents as $residentUser) {
            try {
                $residentUser->notify(new VisitorStatusNotification($visitorLog));
            } catch (Exception $e) {
                Log::error('Failed to notify flat member: '.$e->getMessage());
            }
        }

        try {
            event(new VisitorApprovalStatusUpdated($visitorLog));
        } catch (Exception $e) {
            Log::error('Failed to broadcast VisitorApprovalStatusUpdated on reject: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Visitor request rejected successfully.',
        ]);
    }

    private function getFlatOptions()
    {
        return Flat::where('society_id', auth()->user()->society_id)
            ->whereHas('residents')
            ->orderBy('wing')
            ->orderBy('flat_number')
            ->select('id', 'flat_number', 'wing')
            ->get()
            ->mapWithKeys(function ($flat) {
                return [
                    $flat->id => $flat->wing.'-'.$flat->flat_number,
                ];
            });
    }
}
