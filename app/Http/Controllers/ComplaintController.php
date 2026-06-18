<?php

namespace App\Http\Controllers;

use App\Enum\ComplaintCategory;
use App\Enum\ComplaintStatus;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\Complaint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ComplaintController extends Controller
{
    use AuthorizesRequests;

    public function create()
    {
        $this->authorize('create', Complaint::class);

        $categories = array_column(ComplaintCategory::cases(), 'value');

        return view('complaints.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreComplaintRequest $request)
    {
        $this->authorize('create', Complaint::class);

        Complaint::create([
            'user_id' => auth()->id(),
            'category' => $request->category,
            'description' => $request->description,
            'status' => ComplaintStatus::OPEN,
        ]);

        return redirect()
            ->route('complaints.create')
            ->with('success', 'Complaint submitted successfully.');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Complaint::class);

        if ($request->ajax()) {

            $query = Complaint::with('user.society');

            $user = auth()->user();

            if (in_array($user->role->name, ['resident', 'gatekeeper'])) {

                $query->where('user_id', $user->id);

            } elseif ($user->role->name === 'admin') {

                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('society_id', $user->society_id);
                });

            }
            // super_admin sees everything

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            return DataTables::of($query)
                ->addColumn('society', function ($complaint) {
                    return $complaint->user?->society?->name ?? 'N/A';
                })

                ->editColumn('category', function ($complaint) {
                    return ucfirst($complaint->category);
                })

                ->editColumn('status', function ($complaint) {
                    return ucwords(str_replace('_', ' ', $complaint->status));
                })

                ->editColumn('created_at', function ($complaint) {
                    return $complaint->created_at->format('d M Y');
                })

                ->addColumn('action', function ($complaint) {
                    return '<a href="' .
                        route('complaints.show', $complaint) .
                        '" class="btn btn-primary btn-sm">View</a>';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('complaints.index');
    }

    public function show(Complaint $complaint)
    {
        $this->authorize('view', $complaint);

        return view('complaints.show', compact('complaint'));
    }

    public function edit(Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        return view('complaints.edit', compact('complaint'));
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        $complaint->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }
}
