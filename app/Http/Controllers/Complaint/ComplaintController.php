<?php

namespace App\Http\Controllers\Complaint;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaint\StoreComplaintRequest;
use App\Http\Requests\Complaint\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Services\ActivityLogger;
use App\Traits\AppliesDataTableFilters;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ComplaintController extends Controller
{
    use AppliesDataTableFilters, AuthorizesRequests;

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

            $complaint = Complaint::create([
                'user_id' => auth()->id(),
                'category' => $request->category,
                'description' => $request->description,
                'status' => ComplaintStatus::OPEN,
            ]);

            ActivityLogger::log('create', $complaint, "Complaint raised under category '".ucfirst($complaint->category)."'.");

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
                ->with(['message' => 'Something went wrong while submitting the complaint. Please try again.', 'status' => 'error']);
        }
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Complaint::class);
        try {
            if ($request->ajax()) {
                $query = Complaint::query();

                if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {

                    switch ($request->get('filter', 'active')) {

                        case 'deleted':
                            $query->onlyTrashed();
                            break;

                        case 'all':
                            $query->withTrashed();
                            break;

                        case 'active':
                        default:
                            break;
                    }
                }

                $query->select([
                    'complaints.*',
                    'users.name as resident_name',
                    'societies.name as society_name',
                ])
                    ->leftJoin('users', 'users.id', '=', 'complaints.user_id')
                    ->leftJoin('societies', 'societies.id', '=', 'users.society_id');

                $user = auth()->user();

                if ($request->filled('category')) {
                    $query->where('complaints.category', $request->category);
                }

                if ($request->filled('complaint_status')) {
                    $query->where('complaints.status', $request->complaint_status);
                }

                if ($user->isSuperAdmin() && $request->filled('society_id')) {
                    $query->where('users.society_id', $request->society_id);
                }

                if ($user->isResident() || $user->isGatekeeper()) {

                    $query->where('complaints.user_id', $user->id);

                } elseif ($user->isAdmin()) {

                    $query->where('users.society_id', $user->society_id);
                }

                return DataTables::of($query)
                    ->addIndexColumn()

                    ->editColumn('resident_name', fn ($complaint) => $complaint->resident_name ?? '-')

                    ->editColumn('society', fn ($complaint) => $complaint->society_name ?? '-')

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
                            'open' => '<span class="badge bg-primary">Open</span>',
                            'in_progress' => '<span class="badge bg-warning">In Progress</span>',
                            'resolved' => '<span class="badge bg-success">Resolved</span>',
                            default => '<span class="badge bg-secondary">'.
                                ucwords(str_replace('_', ' ', $complaint->status)).
                                '</span>',
                        };
                    })

                    ->editColumn('created_at', function ($complaint) {
                        return $complaint->created_at->format('d M Y');
                    })

                    ->addColumn('action', function ($complaint) {
                        $buttons = '';
                        if ($complaint->trashed()) {
                            $buttons = '
                                <a href="'.route('complaints.show', $complaint).'"
                                class="btn btn-info text-white btn-sm" title="view">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            ';
                            if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
                                $buttons .= '
                                    <button
                                        type="button"
                                        class="btn btn-secondary text-white btn-sm btn-action"
                                        data-url="'.route('complaints.restore', $complaint->id).'"
                                        data-method="PATCH"
                                        data-title="Restore Complaint?"
                                        data-text="This complaint will be restored."
                                        data-confirm="Restore"
                                        data-success="Complaint restored successfully."
                                        data-color="#6c757d"
                                        title="Restore">

                                        <i class="bi bi-arrow-up-left-circle-fill"></i>
                                    </button>
                                ';
                            }
                        } else {
                            $buttons = '
                                <a href="'.route('complaints.show', $complaint).'"
                                class="btn btn-info text-white btn-sm" title="view">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            ';

                            if (auth()->user()->can('update', $complaint)) {
                                $buttons .= '
                                    <a href="'.route('complaints.edit', $complaint).'"
                                    class="btn btn-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                ';
                            }

                            if (auth()->user()->can('delete', $complaint)) {
                                $buttons .= '
                                <button
                                    type="button"
                                    class="btn btn-danger text-white btn-sm btn-action"
                                    data-url="'.route('complaints.destroy', $complaint).'"
                                    data-method="DELETE"
                                    data-title="Delete Complaint?"
                                    data-text="This action cannot be undone."
                                    data-confirm="Delete"
                                    data-success="Complaint deleted successfully."
                                    title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            ';
                            }
                        }

                        return '
                            <div class="d-flex flex-nowrap justify-content-center gap-1">
                                '.$buttons.'
                            </div>
                        ';
                    })
                    ->rawColumns(['action', 'status', 'description'])
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
                    'message' => 'Something went wrong while loading complaints. Please try again.',
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

            ActivityLogger::log('update', $complaint, 'Complaint updated (status: '.ucfirst($complaint->status).').');

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

            ActivityLogger::log(
                'delete',
                $complaint,
                'Complaint deleted.'
            );

            return response()->json([
                'success' => true,
                'message' => 'Complaint deleted successfully.',
            ]);

        } catch (Exception $e) {

            Log::error('Complaint Delete Error', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete complaint.',
            ], 500);
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
            ->editColumn('society', fn ($complaint) => $complaint->society ?? '-')
            ->editColumn('user_name', fn ($complaint) => $complaint->user_name ?? '-')
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
                                data-bs-title="'.$description.'">
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
        $query = $this->applyDataTableFilters(
            $query,
            $request,
            [
                'users.name',
                'societies.name',
                'complaints.category',
                'complaints.description',
                'complaints.admin_notes',
                'complaints.status',
            ]
        );

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
                    $complaint->user_name ?? '-',
                    $complaint->society ?? '-',
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
        $query = Complaint::query()
            ->select([
                'complaints.*',
                'users.name as user_name',
                'societies.name as society',
            ])
            ->leftJoin('users', 'users.id', '=', 'complaints.user_id')
            ->leftJoin('societies', 'societies.id', '=', 'users.society_id');

        $user = auth()->user();

        if ($user->isResident() || $user->isGatekeeper()) {
            $query->where('complaints.user_id', $user->id);
        } elseif ($user->isAdmin()) {
            $query->where('users.society_id', $user->society_id);
        }

        if ($request->filled('category')) {
            $query->where('complaints.category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('complaints.status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('complaints.created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('complaints.created_at', '<=', $request->to_date);
        }

        return $query;
    }

    public function restore(Complaint $complaint)
    {
        $this->authorize('restore', $complaint);
        try {
            $complaint->restore();
            ActivityLogger::log('restore', $complaint, 'Complaint restored.');

            return response()->json([
                'success' => true,
                'message' => 'Complaint restored successfully.',
            ]);

        } catch (Exception $e) {

            Log::error('Complaint Restore Error', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to restore complaint.',
            ], 500);
        }
    }
}
