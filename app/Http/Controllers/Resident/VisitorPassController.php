<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorPassRequest;
use App\Http\Requests\UpdateVisitorPassRequest;
use App\Models\Flat;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class VisitorPassController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', VisitorLog::class);

        return view('passes.index');
    }

    public function data1(Request $request)
    {
        $user = auth()->user();

        $query = VisitorLog::query()
            ->select([
                'visitor_logs.*',
                'visitors.name as visitor_name',
                'visitors.phone as visitor_phone',
                'flats.wing as flat_wing',
                'flats.flat_number as flat_number',
                'creators.name as creator_name',
                'gatekeepers.name as gatekeeper_name',
            ])
            ->leftJoin('visitors', 'visitors.id', '=', 'visitor_logs.visitor_id')
            ->leftJoin('flats', 'flats.id', '=', 'visitor_logs.flat_id')
            ->leftJoin('users as creators', 'creators.id', '=', 'visitor_logs.created_by')
            ->leftJoin('users as gatekeepers', 'gatekeepers.id', '=', 'visitor_logs.gatekeeper_id');

        if ($user->isResident()) {
            $query->where('visitor_logs.flat_id', $user->resident->flat_id);
        }

        $total = VisitorLog::count();

        if ($search = $request->input('search.value')) {

            $query->where(function ($q) use ($search, $request) {

                foreach ($request->columns as $column) {

                    if (
                        ($column['searchable'] ?? 'false') === 'true'
                        && !empty($column['name'])
                    ) {
                        $q->orWhere(
                            $column['name'],
                            'like',
                            "%{$search}%"
                        );
                    }
                }
            });
        }

        // if ($request->search['value'] ?? null) {
        //     $search = $request->search['value'];

        //     $query->where(function ($q) use ($search) {
        //         $q->where('visitors.name', 'like', "%$search%")
        //             ->orWhere('visitors.phone', 'like', "%$search%")
        //             ->orWhere('visitor_logs.purpose', 'like', "%$search%")
        //             ->orWhere('visitor_logs.status', 'like', "%$search%")
        //             ->orWhere('visitor_logs.id', 'like', "%{$search}%");
        //     });
        // }

        $filtered = $query->count();

        $orderIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc');

        $orderColumn = $request->input("columns.$orderIndex.name");

        if ($orderColumn) {
            $query->orderBy($orderColumn, $orderDir);
        }

        $data = $query
            ->orderBy('visitor_logs.id', 'desc')
            ->skip($request->start)
            ->take($request->length)
            ->get();

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $total,
            "recordsFiltered" => $filtered,
            "data" => $data
        ]);
    }

    public function data(Request $request)
    {
        $user = auth()->user();

        $query = VisitorLog::query()
            ->select([
                'visitor_logs.*',
                'visitors.name as visitor_name',
                'visitors.phone as visitor_phone',
                'flats.wing as flat_wing',
                'flats.flat_number as flat_number',
                'creators.name as creator_name',
                'gatekeepers.name as gatekeeper_name',
            ])
            ->leftJoin('visitors', 'visitors.id', '=', 'visitor_logs.visitor_id')
            ->leftJoin('flats', 'flats.id', '=', 'visitor_logs.flat_id')
            ->leftJoin('users as creators', 'creators.id', '=', 'visitor_logs.created_by')
            ->leftJoin('users as gatekeepers', 'gatekeepers.id', '=', 'visitor_logs.gatekeeper_id');

        if ($user->isResident()) {
            $query->where('visitor_logs.flat_id', $user->resident->flat_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('visitor', fn($row) => $row->visitor_name ?? '-')
            ->editColumn('phone', fn($row) => $row->visitor_phone ?? '-')

            ->editColumn(
                'flat',
                fn($row) =>
                $row->flat_wing && $row->flat_number
                    ? $row->flat_wing . '-' . $row->flat_number
                    : '-'
            )

            ->editColumn('creator', fn($row) => $row->creator_name ?? '-')
            ->editColumn('gatekeeper', fn($row) => $row->gatekeeper_name ?? '-')

            ->editColumn('purpose', fn($row) => $row->purpose ?? '-')
            ->editColumn('status', fn($row) => ucfirst($row->status))

            ->editColumn(
                'entry_time',
                fn($row) =>
                $row->entry_time
                    ? format_date($row->entry_time)
                    : '-'
            )

            ->editColumn(
                'exit_time',
                fn($row) =>
                $row->exit_time
                    ? format_date($row->exit_time)
                    : '-'
            )

            ->editColumn(
                'visit_date',
                fn($row) =>
                $row->visit_date
                    ? format_date($row->visit_date, 'd M Y')
                    : '-'
            )

            ->addColumn('actions', function ($row) {

                $actions = '<div class="d-flex justify-content-center gap-2">';

                $actions .= '
                    <a href="' . route('passes.show', $row->id) . '" class="btn btn-info text-white">
                        <i class="bi bi-eye"></i>
                    </a>
                ';

                if ($row->status === 'pending') {
                    $actions .= '
                        <a href="' . route('passes.edit', $row->id) . '" class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    ';
                }

                if ($row->status === 'pending') {
                    $actions .= '
                        <button
                            class="btn btn-danger btn-action"
                            data-url="' . route('passes.cancel', $row->id) . '"
                            data-method="PATCH"
                            data-title="Cancel Visitor Pass?"
                            data-text="This action cannot be undone."
                            data-confirm="Yes, Cancel"
                            data-success="Visitor pass cancelled successfully">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    ';
                }

                $actions .= '</div>';

                return $actions;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('create', VisitorLog::class);

        $flats = [];

        if (auth()->user()->isGatekeeper()) {
            $flats = Flat::orderBy('flat_number')->get();
        }

        return view('passes.create', compact('flats'));
    }

    public function store(StoreVisitorPassRequest $request)
    {
        $this->authorize('create', VisitorLog::class);

        try {

            $validated = $request->validated();
            $user = auth()->user();

            DB::transaction(function () use ($validated, $user) {

                $visitor = Visitor::updateOrCreate(
                    [
                        'phone' => $validated['phone'],
                    ],
                    [
                        'name' => $validated['name'],
                        'vehicle_number' => $validated['vehicle_number'] ?? null,
                    ]
                );

                $visitorLog = new VisitorLog();

                $flatId = $user->isGatekeeper() ? $validated['flat_id'] : $user->resident->flat_id;

                $visitorLog->visitor_id = $visitor->id;
                $visitorLog->flat_id = $flatId;
                $visitorLog->created_by = $user->id;
                $visitorLog->purpose = $validated['purpose'];
                $visitorLog->status = 'pending';
                $visitorLog->visit_date = $validated['visit_date'];

                $visitorLog->save();
            });

            Session::flash('message', 'Visitor Pass Created Successfully.');
            Session::flash('status', 'success');

            if ($user->isGatekeeper()) {
                return redirect()->route('gatekeeper.visitor-logs.pending');
            }
                return redirect()->route('passes.index');
        } catch (Exception $e) {
            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function show(VisitorLog $visitorLog)
    {
        $this->authorize('view', $visitorLog);

        $visitorLog->load([
            'visitor',
            'flat',
            'gatekeeper',
        ]);

        return view('passes.show', compact('visitorLog'));
    }

    public function edit(VisitorLog $visitorLog)
    {
        $this->authorize('update', $visitorLog);

        $flats = [];

        if (auth()->user()->isGatekeeper()) {
            $flats = Flat::orderBy('flat_number')->get();
        }

        return view('passes.edit', compact(
            'visitorLog',
            'flats'
        ));
    }

    public function update(UpdateVisitorPassRequest $request, VisitorLog $visitorLog)
    {
        $this->authorize('update', $visitorLog);

        $validated = $request->validated();

        try {
            $user = auth()->user();

            DB::transaction(function () use ($validated, $visitorLog, $user) {

                $visitorLog->visitor->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'vehicle_number' => $validated['vehicle_number'] ?? null,
                ]);

            $visitorLog->created_by = $user->id;

            if ( $user->isGatekeeper() && ! empty($validated['flat_id'])) {
                $visitorLog->flat_id = $validated['flat_id'];
            }

            $visitorLog->purpose = $validated['purpose'];
            $visitorLog->visit_date = $validated['visit_date'];

            $visitorLog->save();
            });

            Session::flash('message', 'Visitor Pass updated successfully.');
            Session::flash('status', 'success');

            if ($user->isGatekeeper()) {
                return redirect()->route('gatekeeper.visitor-logs.pending');
            }
            
            return redirect()->route('passes.index');
        } catch (Exception $e) {
            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');
            return redirect()->back()->withInput();
        }
    }

    public function cancel(VisitorLog $visitorLog)
    {
        $this->authorize('cancel', $visitorLog);

        if ($visitorLog->status !== 'pending') {

            return response()->json([
                'success' => false,
                'message' => 'Only pending visitor passes can be cancelled.'
            ], 422);
        }

        $visitorLog->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor pass cancelled successfully.',
        ]);
    }

    public function report1(Request $request)
    {
        $query = VisitorLog::query()
            ->with(['visitor', 'flat', 'gatekeeper']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('flat_id')) {
            $query->where('flat_id', $request->flat_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('visit_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('visit_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('visitor', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                })
                    ->orWhere('purpose', 'like', "%$search%");
            });
        }

        $visitorLogs = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $flats = Flat::select('id', 'wing', 'flat_number')->get();

        return view('passes.report', compact('visitorLogs', 'flats'));
    }

    public function report(Request $request)
    {

        if ($request->ajax()) {

            $query = VisitorLog::query()
                ->select([
                    'visitor_logs.*',
                    'visitors.name as visitor_name',
                    'visitors.phone as visitor_phone',
                    'flats.wing as flat_wing',
                    'flats.flat_number as flat_number',
                    'users.name as gatekeeper_name',
                ])
                ->leftJoin('visitors', 'visitors.id', '=', 'visitor_logs.visitor_id')
                ->leftJoin('flats', 'flats.id', '=', 'visitor_logs.flat_id')
                ->leftJoin('users', 'users.id', '=', 'visitor_logs.gatekeeper_id')
                ->latest();

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

            return DataTables::of($query)

                ->addColumn('visitor', fn ($row) => $row->visitor_name ?? '-')
                ->addColumn('phone', fn ($row) => $row->visitor_phone ?? '-')

                ->addColumn('flat', function ($row) {
                    return $row->flat_wing && $row->flat_number
                        ? $row->flat_wing.'-'.$row->flat_number
                        : '-';
                })

                ->addColumn('status', fn ($row) => ucfirst($row->status))

                ->addColumn(
                    'entry_time',
                    fn ($row) => $row->entry_time ? format_date($row->entry_time) : '-'
                )

                ->addColumn(
                    'exit_time',
                    fn($row) =>
                    $row->exit_time ? format_date($row->exit_time) : '-'
                )

                ->addColumn(
                    'visit_date',
                    fn($row) =>
                    $row->visit_date ? format_date($row->visit_date, 'd M Y') : '-'
                )

                ->addColumn('gatekeeper', fn($row) => $row->gatekeeper_name ?? '-')

                ->rawColumns([])

                ->make(true);
        }

        return view('passes.report');
    }

    public function destroy(VisitorLog $visitorLog)
    {
        $this->authorize('delete', $visitorLog);

        if ($visitorLog->status !== 'pending') {
            return redirect()->back()->with([
                'message' => 'Only pending passes can be deleted.',
                'status' => 'error',
            ]);
        }

        $visitorLog->delete();

        return redirect()->back()->with([
            'message' => 'Visitor pass deleted successfully.',
            'status' => 'success',
        ]);
    }
}
