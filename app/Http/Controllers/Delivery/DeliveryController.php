<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use App\Services\DeliveryNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

/**
 * Manage delivery operations including CRUD actions,
 * reporting, exports, and delivery status updates.
 */
class DeliveryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private DeliveryNotificationService $notificationService) {}

    /**
     * Return delivery data for DataTables listing.
     *
     * Applies role-based filtering:
     * - Super Admin: all deliveries
     * - Society Admin/Gatekeeper: deliveries within their society
     * - Resident: deliveries for their flat only
     *
     * @return JsonResponse
     */
    public function data()
    {
        try {
            $query = Delivery::with([
                'flat' => fn ($q) => $q->withTrashed(),
                'resident' => fn ($q) => $q->withTrashed(),
                'resident.user' => fn ($q) => $q->withTrashed(),
            ]);

            $user = auth()->user();

            if (! $user->isSuperAdmin()) {
                if ($user->isResident()) {
                    $query->where('flat_id', $user->resident->flat_id);
                } else {
                    $query->whereHas('flat', function ($query) use ($user) {
                        $query->where('society_id', $user->society_id);
                    });
                }
            }

            return DataTables::eloquent($query)
                ->addColumn('flat', function ($delivery) {
                    return $delivery->flat->wing.' - Floor '.$delivery->flat->floor.' - '.$delivery->flat->flat_number;
                })
                ->addColumn('resident', function ($delivery) {
                    return $delivery->resident->user->name;
                })
                ->editColumn('status', function ($delivery) {
                    $class = $delivery->status === 'delivered'
                        ? 'text-bg-success'
                        : 'text-bg-primary';

                    return '<span class="badge rounded-pill '.$class.'">'
                        .ucfirst($delivery->status)
                        .'</span>';
                })
                ->editColumn('received_at', function ($delivery) {
                    return $delivery->received_at?->format('d M Y H:i');
                })
                ->addColumn('actions', function ($delivery) {
                    return view(
                        'deliveries.partials.actions',
                        compact('delivery')
                    )->render();
                })
                ->rawColumns(['status', 'actions'])
                ->toJson();
        } catch (\Throwable $e) {

            $this->notificationService->failed(
                'load delivery datatable',
                $e
            );

            abort(500);
        }
    }

    /**
     * Return filtered delivery report data for DataTables.
     *
     * Supports filtering by status, flat, vendor, and date range.
     *
     * @return JsonResponse
     */
    public function reportData(Request $request)
    {
        try {
            $query = $this->getReportQuery($request);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('society', function ($delivery) {
                    return $delivery->flat->society->name;
                })
                ->addColumn('flat', function ($delivery) {
                    return $delivery->flat->wing.' - Floor '.$delivery->flat->floor.' - '.$delivery->flat->flat_number;
                })
                ->addColumn('resident', function ($delivery) {
                    return $delivery->resident->user->name;
                })
                ->editColumn('status', function ($delivery) {
                    return ucfirst($delivery->status);
                })
                ->editColumn('received_at', function ($delivery) {
                    return $delivery->received_at->format('d M Y H:i');
                })
                ->editColumn('delivered_at', function ($delivery) {
                    return $delivery->delivered_at?->format('d M Y H:i') ?? '-';
                })
                ->toJson();
        } catch (\Throwable $e) {

            $this->notificationService->failed(
                'load delivery datatable',
                $e
            );

            abort(500);
        }
    }

    /**
     * Export filtered delivery report as CSV.
     *
     * @return StreamedResponse
     */
    public function export(Request $request)
    {
        try {
            $query = $this->getReportQuery($request);

            return response()->streamDownload(function () use ($query) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Society ID',
                    'Society Name',
                    'Flat',
                    'Resident',
                    'Vendor',
                    'Status',
                    'Received At',
                    'Delivered At',
                ]);

                foreach ($query->get() as $delivery) {
                    fputcsv($handle, [
                        $delivery->id,
                        $delivery->flat->society_id,
                        $delivery->flat->society->name,
                        $delivery->flat->wing.' - Floor '.$delivery->flat->floor.' - '.$delivery->flat->flat_number,
                        $delivery->resident->user->name,
                        $delivery->vendor,
                        ucfirst($delivery->status),
                        $delivery->received_at->format('Y-m-d H:i:s'),
                        $delivery->delivered_at?->format('Y-m-d H:i:s') ?? '-',
                    ]);
                }

                fclose($handle);
            }, 'delivery-report.csv');

        } catch (\Throwable $e) {
            $this->notificationService
                ->failed('export delivery report', $e);

            return back()->with([
                'status' => 'error',
                'message' => 'Failed to export delivery report.',
            ]);
        }
    }

    /**
     * Display delivery reporting filters and report page.
     *
     * @return View
     */
    public function report()
    {
        $user = auth()->user();

        $flats = Flat::query()
            ->when(! $user->isSuperAdmin(), function ($query) use ($user) {
                if ($user->isResident()) {
                    $query->where('id', $user->resident->flat_id);
                } else {
                    $query->where('society_id', $user->society_id);
                }
            })
            ->orderBy('wing')
            ->orderBy('floor')
            ->orderBy('flat_number')
            ->get();

        $vendors = Delivery::query()
            ->when(! $user->isSuperAdmin(), function ($query) use ($user) {
                if ($user->isResident()) {
                    $query->where('flat_id', $user->resident->flat_id);
                } else {
                    $query->whereHas('flat', function ($q) use ($user) {
                        $q->where('society_id', $user->society_id);
                    });
                }
            })
            ->whereNotNull('vendor')
            ->distinct()
            ->orderBy('vendor')
            ->pluck('vendor');

        return view('deliveries.report', compact(
            'flats',
            'vendors'
        ));
    }

    /**
     * Display the delivery listing page.
     *
     * @return View
     */
    public function index()
    {
        $this->authorize('viewAny', Delivery::class);

        return view('deliveries.index');
    }

    /**
     * Show the delivery creation form.
     *
     * @return View
     */
    public function create()
    {
        $this->authorize('create', Delivery::class);

        return view('deliveries.create', ['residentOptions' => $this->getResidentOptions()]);
    }

    /**
     * Store a newly received delivery.
     *
     * Creates a delivery record with RECEIVED status and
     * notifies the delivery notification service.
     *
     * @return RedirectResponse
     */
    public function store(StoreDeliveryRequest $request)
    {
        $this->authorize('create', Delivery::class);

        try {
            $validatedData = $request->validated();

            $resident = Resident::findOrFail(
                $validatedData['resident_id']
            );

            $delivery = Delivery::create([
                'flat_id' => $resident->flat_id,
                'resident_id' => $resident->id,
                'vendor' => $validatedData['vendor'],
                'package_details' => $validatedData['package_details'],
                'status' => DeliveryStatus::RECEIVED->value,
                'received_at' => now(),
                'delivered_at' => null,
            ]);

            $this->notificationService->notify($delivery, 'Delivery received');

            return redirect()->route('deliveries.index')
                ->with(['status' => 'success', 'message' => 'Delivery recorded successfully.']);
        } catch (\Throwable $e) {
            $this->notificationService->failed('create delivery', $e);

            return back()
                ->withInput()
                ->with(['status' => 'error', 'message' => 'Failed to create delivery.']);
        }
    }

    /**
     * Display a delivery record.
     *
     * @return View
     */
    public function show(Delivery $delivery)
    {
        $this->authorize('view', $delivery);

        $delivery->load(['flat', 'resident']);

        return view('deliveries.show', ['delivery' => $delivery]);
    }

    /**
     * Mark a delivery as delivered.
     *
     * Updates status and delivery timestamp.
     *
     * @return RedirectResponse
     */
    public function markDelivered(Delivery $delivery)
    {
        $this->authorize('markDelivered', $delivery);

        try {
            if ($delivery->status === DeliveryStatus::DELIVERED->value) {
                return back()->with([
                    'status' => 'warning',
                    'message' => 'Delivery is already marked as delivered.',
                ]);
            }
            $delivery->update([
                'status' => DeliveryStatus::DELIVERED->value,
                'delivered_at' => now(),
            ]);

            $this->notificationService->notify($delivery, 'Delivery delivered');

            return back()
                ->with(['status' => 'success', 'message' => 'Delivery marked as delivered.']);
        } catch (\Throwable $e) {
            $this->notificationService->failed('mark delivery as delivered', $e);

            return back()
                ->withInput()
                ->with(['status' => 'error', 'message' => 'Failed to mark delivery as delivered']);
        }
    }

    /**
     * Show the delivery edit form.
     *
     * @return View
     */
    public function edit(Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        return view('deliveries.edit', [
            'delivery' => $delivery,
            'residentOptions' => $this->getResidentOptions(),
        ]);
    }

    /**
     * Update an existing delivery record.
     *
     * @return RedirectResponse
     */
    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        try {
            $data = $request->validated();

            $delivery->fill($data);

            if (! $delivery->isDirty()) {
                return redirect()
                    ->route('deliveries.index')
                    ->with(['status' => 'info', 'message' => 'No changes detected.']);
            }

            $oldValues = $delivery->only([
                'flat_id',
                'resident_id',
                'vendor',
                'package_details',
                'status',
            ]);

            $delivery->save();

            $newValues = $delivery->only([
                'flat_id',
                'resident_id',
                'vendor',
                'package_details',
                'status',
            ]);

            $this->notificationService->notify($delivery, 'Delivery updated', [
                'before' => $oldValues,
                'after' => $newValues,
            ]);

            return redirect()
                ->route('deliveries.index')
                ->with(['status' => 'success', 'message' => 'Delivery updated successfully.']);
        } catch (\Throwable $e) {
            $this->notificationService->failed('update delivery', $e);

            return back()
                ->withInput()
                ->with(['status' => 'error', 'message' => 'Failed to update delivery.']);
        }
    }

    /**
     * Delete a delivery record.
     *
     * @return RedirectResponse
     */
    public function destroy(Delivery $delivery)
    {
        $this->authorize('delete', $delivery);

        try {
            $this->notificationService->notify($delivery, 'Delivery deleted');

            $delivery->delete();

            return redirect()
                ->route('deliveries.index')
                ->with(['status' => 'success', 'message' => 'Delivery deleted successfully.']);
        } catch (\Throwable $e) {
            $this->notificationService->failed('delete delivery', $e);

            return back()
                ->withInput()
                ->with(['status' => 'error', 'message' => 'Failed to delete delivery.']);
        }
    }

    /**
     * Get resident dropdown options available to the current user.
     *
     * All Role are restricted by society unless the current
     * user is a super administrator.
     *
     * @return Collection<int, string>
     */
    private function getResidentOptions()
    {
        $user = auth()->user();

        return Resident::with(['user', 'flat'])
            ->when(! $user->isSuperAdmin(), function ($query) use ($user) {
                $query->whereHas('flat', function ($q) use ($user) {
                    $q->where('society_id', $user->society_id);
                });
            })
            ->get()
            ->mapWithKeys(function ($resident) {
                return [
                    $resident->id => $resident->flat->wing.'-'.$resident->flat->flat_number
                        .' - '.$resident->user->name,
                ];
            });
    }

    /**
     * Build the base query used by report and export features.
     *
     * Applies role-based access restrictions and optional
     * report filters from the request.
     *
     * @return Builder
     */
    private function getReportQuery(Request $request)
    {
        $query = Delivery::with([
            'flat' => fn ($q) => $q->withTrashed(),
            'flat.society' => fn ($q) => $q->withTrashed(),
            'resident' => fn ($q) => $q->withTrashed(),
            'resident.user' => fn ($q) => $q->withTrashed(),
        ]);

        $user = auth()->user();

        if (! $user->isSuperAdmin()) {
            if ($user->isResident()) {
                $query->where('flat_id', $user->resident->flat_id);
            } else {
                $query->whereHas('flat', function ($query) use ($user) {
                    $query->where('society_id', $user->society_id);
                });
            }
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('flat_id')) {
            $query->where('flat_id', $request->flat_id);
        }

        if ($request->filled('vendor')) {
            $query->where('vendor', 'like', '%'.$request->vendor.'%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('received_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('received_at', '<=', $request->to_date);
        }

        return $query;
    }
}
