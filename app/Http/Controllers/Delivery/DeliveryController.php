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
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryNotificationService $notificationService) {}

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

    public function index()
    {
        $this->authorize('viewAny', Delivery::class);

        return view('deliveries.index');
    }

    public function create()
    {
        $this->authorize('create', Delivery::class);

        return view('deliveries.create', ['residentOptions' => $this->getResidentOptions()]);
    }

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

    public function show(Delivery $delivery)
    {
        $this->authorize('view', $delivery);

        $delivery->load(['flat', 'resident']);

        return view('deliveries.show', ['delivery' => $delivery]);
    }

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

    public function edit(Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        return view('deliveries.edit', [
            'delivery' => $delivery,
            'residentOptions' => $this->getResidentOptions(),
        ]);
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        try {
            $oldValues = $delivery->only([
                'flat_id',
                'resident_id',
                'vendor',
                'package_details',
                'status',
            ]);

            $delivery->update($request->validated());

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
