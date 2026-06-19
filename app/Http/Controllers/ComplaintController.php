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
use Illuminate\Support\Facades\Log;
use Exception;

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

        try {

            Complaint::create([
                'user_id' => auth()->id(),
                'category' => $request->category,
                'description' => $request->description,
                'status' => ComplaintStatus::OPEN,
            ]);

            return redirect()
                ->route('complaints.create')
                ->with('success', 'Complaint submitted successfully.');

        } catch (Exception $e) {

            Log::error('Complaint Create Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while submitting the complaint.');
        }
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Complaint::class);

        try {

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
                        return '<a href="'.
                            route('complaints.show', $complaint).
                            '" class="btn btn-primary btn-sm">View</a>';
                    })

                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('complaints.index');

        } catch (Exception $e) {

            Log::error('Complaint Listing Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load complaints.',
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while loading complaints.');
        }
    }

    public function show(Complaint $complaint)
    {
        $this->authorize('view', $complaint);
        try {

            return view('complaints.show', compact('complaint'));

        } catch (Exception $e) {

            Log::error('Complaint View Error', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return redirect()
                ->route('complaints.index')
                ->with('error', 'Unable to load complaint details.');
        }
    }

    public function edit(Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        try {

            return view('complaints.edit', compact('complaint'));

        } catch (Exception $e) {

            Log::error('Complaint Edit Error', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return redirect()
                ->route('complaints.index')
                ->with('error', 'Unable to load complaint for editing.');
        }
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        try {

            $complaint->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
            ]);

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Complaint updated successfully.');

        } catch (Exception $e) {

            Log::error('Complaint Update Error', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the complaint.');
        }
    }
}
