<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorPassRequest;
use App\Models\Flat;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class VisitorPassController extends Controller
{
    public function index()
    {
        try {
            return view('passes.index');
        } catch (Exception $e) {
            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
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
                        && ! empty($column['name'])
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
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
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
                return '<div class="d-flex gap-2">
                    <a href="/passes/'.$row->id.'" class="btn btn-info">
                        <i class="bi bi-eye me-1"></i>
                    </a>

                    <a href="'.route('passes.edit', $row->id).'" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-1"></i>
                    </a>

                    <button data-id="'.$row->id.'" data-url="'.route('passes.cancel', $row->id).'" class="btn-delete btn btn-danger shadow-none">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>';
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $flats = [];

        if (auth()->user()->isGatekeeper()) {
            $flats = Flat::orderBy('flat_number')->get();
        }

        return view('passes.create', compact('flats'));
    }

    public function store(StoreVisitorPassRequest $request)
    {
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

                $flatId = $user->isGatekeeper()
                    ? $validated['flat_id']
                    : $user->resident->flat_id;

                $visitorLog = new VisitorLog;

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

    public function edit($id)
    {
        $visitorLog = VisitorLog::with('visitor')->findOrFail($id);

        return view('passes.edit', compact('visitorLog'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required',
                'phone' => 'required',
                'purpose' => 'required',
                'visit_date' => 'required',
                'vehicle_number' => 'nullable',
                'status' => 'required',
            ]);

            $visitorLog = VisitorLog::findOrFail($id);
            $user = auth()->user();

            DB::transaction(function () use ($validated, $visitorLog, $user) {

                $visitorLog->visitor->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'vehicle_number' => $validated['vehicle_number'] ?? null,
                ]);

                $visitorLog->created_by = $user->id;
                $visitorLog->purpose = $validated['purpose'];
                $visitorLog->visit_date = $validated['visit_date'];
                $visitorLog->status = $validated['status'];
                $visitorLog->save();
            });

            Session::flash('message', 'Visitor Pass updated successfully.');
            Session::flash('status', 'success');

            return redirect()->route('passes.index');
        } catch (Exception $e) {
            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function cancel(VisitorLog $visitorLog)
    {
        if (! $visitorLog) {
            return redirect()
                ->back()
                ->with('error', 'Visitor pass not found.');
        }

        if ($visitorLog->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Only pending visitor passes can be cancelled.');
        }

        $visitorLog->update([
            'status' => 'cancelled',
        ]);

        return redirect()->back()
            ->with('success', 'Visitor pass cancelled successfully.');
    }

    public function report(Request $request)
    {
        $query = VisitorLog::with([
            'visitor',
            'flat',
            'gatekeeper',
        ]);

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('flat_id')) {
            $query->where(
                'flat_id',
                $request->flat_id
            );
        }

        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        $visitorLogs = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('passes.report', compact('visitorLogs'));
    }
}
