<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wing\StoreWingRequest;
use App\Http\Requests\Wing\UpdateWingRequest;
use App\Models\Flat;
use App\Models\Society;
use App\Models\Wing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class WingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Wing::class);

        try {
            if ($request->ajax()) {
                $query = Wing::query()->select(['wings.*', 'societies.name as society_name'])
                    ->leftJoin('societies', 'societies.id', '=', 'wings.society_id');

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('name', fn ($row) => $row->name)
                    ->addColumn('society', fn ($row) => $row->society_name ?? '-')
                    ->addColumn('actions', function ($row) {
                        $edit = route('wings.edit', $row->id);
                        $del = route('wings.destroy', $row->id);
                        $html = '<div class="text-center">';
                        $html .= '<a href="'.$edit.'" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i></a> ';
                        $html .= '<button class="btn btn-danger btn-action" data-url="'.$del.'" data-method="DELETE">';
                        $html .= '<i class="bi bi-trash"></i></button>';
                        $html .= '</div>';

                        return $html;
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            }

            $societies = Society::orderBy('name')->get();

            return view('wings.index', compact('societies'));
        } catch (\Throwable $e) {
            Log::error('Wing listing error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with(['message' => 'Something went wrong.', 'status' => 'error']);
        }
    }

    public function create()
    {
        $this->authorize('create', Wing::class);

        $societies = auth()->user()->isSuperAdmin()
            ? Society::orderBy('name')->get()
            : Society::where('id', auth()->user()->society_id)->get();

        return view('wings.create', compact('societies'));
    }

    public function store(StoreWingRequest $request)
    {
        $this->authorize('create', Wing::class);

        DB::beginTransaction();
        try {
            $data = $request->validated();

            $societyId = auth()->user()->isSuperAdmin() ? $data['society_id'] : auth()->user()->society_id;

            $wing = Wing::create([
                'society_id' => $societyId,
                'name' => $data['name'],
                'total_floors' => $data['total_floors'],
                'flats_per_floor' => $data['flats_per_floor'],
            ]);

            $inserts = [];
            for ($f = 1; $f <= $wing->total_floors; $f++) {
                for ($n = 1; $n <= $wing->flats_per_floor; $n++) {
                    if (! Flat::where('wing_id', $wing->id)->where('floor', $f)->where('flat_number', $n)->exists()) {
                        $inserts[] = [
                            'wing_id' => $wing->id,
                            'floor' => $f,
                            'flat_number' => $n,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            if (! empty($inserts)) {
                Flat::insert($inserts);
            }

            DB::commit();

            Session::flash('message', 'Wing created and flats generated.');
            Session::flash('status', 'success');

            return redirect()->route('wings.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Wing store error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withInput()->with(['message' => 'Something went wrong.', 'status' => 'error']);
        }
    }

    public function edit(Wing $wing)
    {
        $this->authorize('update', $wing);
        $societies = auth()->user()->isSuperAdmin()
            ? Society::orderBy('name')->get()
            : Society::where('id', auth()->user()->society_id)->get();

        return view('wings.edit', compact('wing', 'societies'));
    }

    public function update(UpdateWingRequest $request, Wing $wing)
    {
        $this->authorize('update', $wing);

        DB::beginTransaction();
        try {
            $data = $request->validated();

            $wing->update($data);

            // synchronize flats: create missing, prevent deletion if related records exist
            $desired = [];
            for ($f = 1; $f <= $wing->total_floors; $f++) {
                for ($n = 1; $n <= $wing->flats_per_floor; $n++) {
                    $desired[] = ['floor' => $f, 'flat_number' => $n];
                    if (! Flat::where('wing_id', $wing->id)->where('floor', $f)->where('flat_number', $n)->exists()) {
                        Flat::create(['wing_id' => $wing->id, 'floor' => $f, 'flat_number' => $n]);
                    }
                }
            }

            // find existing flats that are outside desired range
            $toDelete = Flat::where('wing_id', $wing->id)
                ->where(function ($q) use ($wing) {
                    $q->where('floor', '>', $wing->total_floors)
                        ->orWhere('flat_number', '>', $wing->flats_per_floor);
                })->get();

            foreach ($toDelete as $flat) {
                // prevent deletion if residents or deliveries exist
                if ($flat->residents()->exists() || $flat->deliveries()->exists() || $flat->visitorLogs()->exists()) {
                    DB::rollBack();

                    return redirect()->back()->withInput()->with(['message' => "Cannot remove flat {$flat->floor}-{$flat->flat_number} because it has related records.", 'status' => 'error']);
                }

                $flat->delete();
            }

            DB::commit();

            Session::flash('message', 'Wing updated and flats synchronized.');
            Session::flash('status', 'success');

            return redirect()->route('wings.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Wing update error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withInput()->with(['message' => 'Something went wrong.', 'status' => 'error']);
        }
    }

    public function destroy(Wing $wing)
    {
        $this->authorize('delete', $wing);

        try {
            // prevent deletion if any flats have related records
            $problem = $wing->flats()->whereHas('residents')->orWhereHas('deliveries')->orWhereHas('visitorLogs')->exists();
            if ($problem) {
                return response()->json(['success' => false, 'message' => 'Cannot delete wing with occupied flats.'], 422);
            }

            $wing->delete();

            return response()->json(['success' => true, 'message' => 'Wing deleted']);
        } catch (\Throwable $e) {
            Log::error('Wing delete error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'Failed to delete wing.'], 500);
        }
    }

    public function bySociety(Society $society)
    {
        $this->authorize('viewAny', Wing::class);

        return Wing::where('society_id', $society->id)
            ->orderBy('name')
            ->get(['id', 'name', 'total_floors', 'flats_per_floor']);
    }
}
