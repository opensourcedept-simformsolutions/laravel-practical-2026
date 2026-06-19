<?php

namespace App\Http\Controllers;

use App\Enum\ComplaintCategory;
use App\Enum\ComplaintStatus;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\Complaint;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        try {

            Complaint::create([
                'user_id' => auth()->id(),
                'category' => $request->category,
                'description' => $request->description,
                'status' => ComplaintStatus::OPEN,
            ]);

            return redirect()
                ->route('complaints.index')
                ->with(['message' => 'Complaint submitted successfully', 'status' => 'success']);

        } catch (Exception $e) {

            Log::error('Complaint Create Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with(['message' => 'Something went wrong while submitting the complaint', 'status' => 'error']);
        }
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Complaint::class);

        try {

            if ($request->ajax()) {

                $query = Complaint::with('user.society');

                $user = auth()->user();

                if ($user->isResident() || $user->isGatekeeper()) {
                    $query->where('user_id', $user->id);

                } elseif ($user->isAdmin()) {
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
                        return $complaint->user->society?->name ?? 'N/A';
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
                ->with(['message' => 'Something went wrong while loading complaints.', 'status' => 'error']);
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
                ->with(['message' => 'Unable to load complaint details.', 'status' => 'error']);
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
                ->with(['message' => 'Unable to load complaint for editing.', 'status' => 'error']);
        }
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        try {

            $user = auth()->user();

            if ($user->isAdmin()) {

                $complaint->update([
                    'status' => $request->status,
                    'admin_notes' => $request->admin_notes,
                ]);

            } else {
                $complaint->update([
                    'category' => $request->category,
                    'description' => $request->description,
                ]);
            }

            return redirect()
                ->route('complaints.show', $complaint)
                ->with([
                    'message' => 'Complaint updated successfully.',
                    'status' => 'success',
                ]);

        } catch (Exception $e) {

            Log::error('Complaint Update Error', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with([
                    'message' => 'Failed to update complaint.',
                    'status' => 'error',
                ]);
        }
    }

    public function destroy(Complaint $complaint)
    {
        $this->authorize('delete', $complaint);

        try {

            $complaint->delete();

            return redirect()
                ->route('complaints.index')
                ->with([
                    'message' => 'Complaint deleted successfully.',
                    'status' => 'success',
                ]);

        } catch (Exception $e) {

            Log::error('Complaint Delete Error', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with([
                'message' => 'Unable to delete complaint.',
                'status' => 'error',
            ]);
        }
    }
}
