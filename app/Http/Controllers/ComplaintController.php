<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\Complaint;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('resident_name', function ($complaint) {
                        return $complaint->user?->name ?? 'N/A';
                    })

                    ->addColumn('society', function ($complaint) {
                        return $complaint->user->society?->name ?? 'N/A';
                    })

                    ->editColumn('category', function ($complaint) {
                        return ucfirst($complaint->category);
                    })

                    ->editColumn('description', function ($complaint) {

                        $description = e($complaint->description);

                        if (strlen($complaint->description) > 40) {
                            return '
                                '.Str::limit($description, 40).'
                                <button type="button"
                                        class="btn btn-link btn-sm p-0 ms-1"
                                        data-bs-toggle="tooltip"
                                        title="'.$description.'">
                                    <i class="bi bi-eye"></i>
                                </button>
                            ';
                        }

                        return $description;
                    })

                    ->editColumn('status', function ($complaint) {
                        return match ($complaint->status) {
                            'open'        => '<span class="badge bg-primary">Open</span>',
                            'in_progress' => '<span class="badge bg-warning">In Progress</span>',
                            'resolved'    => '<span class="badge bg-success">Resolved</span>',
                            default       => '<span class="badge bg-secondary">' .
                                ucwords(str_replace('_', ' ', $complaint->status)) .
                                '</span>',
                        };
                    })

                    ->editColumn('created_at', function ($complaint) {
                        return $complaint->created_at->format('d M Y');
                    })

                    ->addColumn('action', function ($complaint) {

                        $buttons = '
                        <a href="'.route('complaints.show', $complaint).'"
                           class="btn btn-info btn-sm" title="view">
                            <i class="bi bi-eye"></i>
                        </a>
                    ';

                        if (auth()->user()->can('update', $complaint)) {
                            $buttons .= '
                            <a href="'.route('complaints.edit', $complaint).'"
                               class="btn btn-primary btn-sm" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        ';
                        }

                        if (auth()->user()->can('delete', $complaint)) {
                            $buttons .= '
                            <form action="'.route('complaints.destroy', $complaint).'"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm(\'Are you sure you want to delete this complaint?\');">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        ';
                        }

                        return '
                            <div class="d-flex flex-nowrap justify-content-center gap-1">
                                '.$buttons.'
                            </div>
                        ';
                    })

                    ->rawColumns(['action','status','description'])
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
                ->with([
                    'message' => 'Something went wrong while loading complaints.',
                    'status' => 'error',
                ]);
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

    public function report()
    {
        return view('complaints.report');
    }

    public function reportData(Request $request)
    {
        $query = $this->getReportQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('society', function ($complaint) {
                return $complaint->user->society?->name ?? 'N/A';
            })
            ->addColumn('user_name', function ($complaint) {
                return $complaint->user?->name ?? '-';
            })
            ->editColumn('category', function ($complaint) {
                return ucfirst($complaint->category);
            })
            ->editColumn('description', function ($complaint) {
                $description = e($complaint->description);
                if (strlen($complaint->description) > 40) {
                    return '
                        '.Str::limit($description, 40).'
                        <button type="button"
                                class="btn btn-link btn-sm p-0 ms-1"
                                data-bs-toggle="tooltip"
                                title="'.$description.'">
                            <i class="bi bi-eye"></i>
                        </button>
                    ';
                }
                    return $description;
            })
            ->editColumn('status', function ($complaint) {
                return ucwords(str_replace('_', ' ', $complaint->status));
            })
            ->editColumn('created_at', function ($complaint) {
                return $complaint->created_at->format('d M Y');
            })
            ->rawColumns(['description'])
            ->make(true);

    }

    public function export(Request $request)
    {
        $query = $this->getReportQuery($request);

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'User',
                'Society',
                'Category',
                'Description',
                'Admin Notes',
                'Status',
                'Created At',
            ]);

            foreach ($query->get() as $complaint) {
                fputcsv($handle, [
                    $complaint->id,
                    $complaint->user?->name ?? '-',
                    $complaint->user?->society?->name ?? '-',
                    ucfirst($complaint->category),
                    $complaint->description,
                    $complaint->admin_notes ?? '-',
                    ucwords(str_replace('_', ' ', $complaint->status)),
                    $complaint->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);

        }, 'complaints-report.csv');
    }

    private function getReportQuery(Request $request)
    {
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

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query;
    }
}
