<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\StoreDeliveryRequest;
use App\Http\Requests\Delivery\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use App\Services\ActivityLogger;
use App\Services\DeliveryNotificationService;
use App\Traits\AppliesDataTableFilters;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    use AppliesDataTableFilters;

    public function __construct(private DeliveryNotificationService $notificationService) {}

    public function data(Request $request)
    {
        try {
            $query = Delivery::query()
                ->select([
                    'deliveries.*',
                    'users.name as resident_name',
                    'wings.name as flat_wing',
                    'flats.floor as flat_floor',
                    'flats.flat_number',
                    'flats.society_id',
                    'societies.name as society_name',
                ])
                ->leftJoin('residents', 'deliveries.resident_id', '=', 'residents.id')
                ->leftJoin('users', 'residents.user_id', '=', 'users.id')
                ->leftJoin('flats', 'deliveries.flat_id', '=', 'flats.id')
                ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id')
                ->leftJoin('societies', 'flats.society_id', '=', 'societies.id');

            $user = auth()->user();

            if ($user->isSuperAdmin() || $user->isAdmin()) {
                match ($request->get('filter', 'active')) {
                    'deleted' => $query->onlyTrashed(),
                    'all' => $query->withTrashed(),
                    default => null,
                };
            }

            if ($request->filled('delivery_status')) {
                $query->where('deliveries.status', $request->delivery_status);
            }

            if ($request->filled('vendor')) {
                $query->where('deliveries.vendor', $request->vendor);
            }

            if ($request->filled('flat_id')) {
                $query->where('deliveries.flat_id', $request->flat_id);
            }

            if ($user->isSuperAdmin() && $request->filled('society_id')) {
                $query->where('flats.society_id', $request->society_id);
            }

            if (! $user->isSuperAdmin()) {
                if ($user->isResident()) {
                    $query->where('deliveries.flat_id', $user->resident->flat_id);
                } else {
                    $query->where('flats.society_id', $user->society_id);
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('flat', function ($delivery) {
                    return "{$delivery->flat_wing}-{$delivery->flat_number}";
                })
                ->addColumn('society', function ($delivery) {
                    return $delivery->society_name;
                })
                ->addColumn('resident', function ($delivery) {
                    return $delivery->resident_name ?? '-';
                })
                ->addColumn('actions', function ($delivery) {
                    return view(
                        'deliveries.partials.actions',
                        compact('delivery')
                    )->render();
                })
                ->editColumn('package_details', function ($delivery) {
                    $short = Str::limit($delivery->package_details, 50);

                    return '<span title="'.e($delivery->package_details).'">'.e($short).'</span>';
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
                    return $delivery->received_at?->format('d M Y');
                })
                ->editColumn('delivered_at', function ($delivery) {
                    return $delivery->delivered_at?->format('d M Y') ?? '-';
                })
                ->orderColumn('society', function ($query, $order) {
                    $query->orderBy('societies.name', $order);
                })
                ->orderColumn('flat', function ($query, $order) {
                    $query->orderBy('wings.name', $order)
                        ->orderBy('flats.flat_number', $order);
                })
                ->rawColumns(['package_details', 'status', 'actions'])
                ->toJson();
        } catch (Exception $e) {
            $this->notificationService->failed(
                'load delivery datatable',
                $e
            );

            return response()->json([
                'message' => 'Failed to load deliveries.',
            ], 500);
        }
    }

    public function reportData(Request $request)
    {
        try {
            $query = $this->getReportQuery($request);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('society', function ($delivery) {
                    return $delivery->society_name;
                })
                ->addColumn('flat', function ($delivery) {
                    return "{$delivery->flat_wing}-{$delivery->flat_number}";
                })

                ->addColumn('resident', function ($delivery) {
                    return $delivery->resident_name ?? '-';
                })
                ->editColumn('package_details', function ($delivery) {
                    $short = Str::limit($delivery->package_details, 50);

                    return '<span title="'.e($delivery->package_details).'">'.e($short).'</span>';
                })
                ->editColumn('status', function ($delivery) {
                    return ucfirst($delivery->status);
                })
                ->editColumn('received_at', function ($delivery) {
                    return $delivery->received_at->format('d M Y');
                })
                ->editColumn('delivered_at', function ($delivery) {
                    return $delivery->delivered_at?->format('d M Y') ?? '-';
                })
                ->orderColumn('society', function ($query, $order) {
                    $query->orderBy('societies.name', $order);
                })
                ->orderColumn('flat', function ($query, $order) {
                    $query->orderBy('wings.name', $order)
                        ->orderBy('flats.floor', $order)
                        ->orderBy('flats.flat_number', $order);
                })
                ->rawColumns(['package_details'])
                ->toJson();
        } catch (Exception $e) {
            $this->notificationService->failed(
                'load delivery report data',
                $e
            );

            return response()->json([
                'message' => 'Failed to load deliveries.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $query = $this->getReportQuery($request);
            $query = $this->applyDataTableFilters(
                $query,
                $request,
                [
                    'deliveries.vendor',
                    'users.name',
                    'societies.name',
                    'deliveries.package_details',
                    'deliveries.status',
                ],
                [
                    'flat' => function ($query, $direction) {
                        $query->orderBy('wings.name', $direction)
                            ->orderBy('flats.floor', $direction)
                            ->orderBy('flats.flat_number', $direction);
                    },
                ]
            );

            return response()->streamDownload(function () use ($query) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Society ID',
                    'Society Name',
                    'Flat',
                    'Resident',
                    'Package Details',
                    'Vendor',
                    'Status',
                    'Received At',
                    'Delivered At',
                ]);

                foreach ($query->get() as $delivery) {
                    fputcsv($handle, [
                        $delivery->id,
                        $delivery->society_id,
                        $delivery->society_name,
                        $delivery->flat_wing.'-'.$delivery->flat_number,
                        $delivery->resident_name ?? '-',
                        $delivery->package_details,
                        $delivery->vendor,
                        ucfirst($delivery->status),
                        $delivery->received_at->format('Y-m-d H:i:s'),
                        $delivery->delivered_at?->format('Y-m-d H:i:s') ?? '-',
                    ]);
                }

                fclose($handle);
            }, 'delivery-report.csv');
        } catch (Exception $e) {
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
            ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id')
            ->when(! $user->isSuperAdmin(), function ($query) use ($user) {
                if ($user->isResident()) {
                    $query->where('flats.id', $user->resident->flat_id);
                } else {
                    $query->where('flats.society_id', $user->society_id);
                }
            })
            ->orderBy('wings.name')
            ->orderBy('flats.floor')
            ->orderBy('flats.flat_number')
            ->select('flats.*')
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

        $user = auth()->user();
        $flats = collect();

        if (! $user->isResident()) {
            if (! $user->isSuperAdmin()) {
                $flats = Flat::where('society_id', $user->society_id)
                    ->orderBy('wing')
                    ->orderBy('floor')
                    ->orderBy('flat_number')
                    ->get();
            }
        }

        return view('deliveries.index', compact('flats'));
    }

    public function create()
    {
        $this->authorize('create', Delivery::class);

        return view('deliveries.create', ['residentOptions' => $this->getResidentOptions()]);
    }

    public function store(StoreDeliveryRequest $request)
    {
        $this->authorize('create', Delivery::class);

        $validatedData = $request->validated();

        $resident = Resident::findOrFail(
            $validatedData['resident_id']
        );

        try {
            $delivery = Delivery::create([
                'flat_id' => $resident->flat_id,
                'resident_id' => $resident->id,
                'vendor' => $validatedData['vendor'],
                'package_details' => $validatedData['package_details'],
                'status' => DeliveryStatus::RECEIVED->value,
                'received_at' => now(),
                'delivered_at' => null,
            ]);

            ActivityLogger::log('create', $delivery, "Delivery from {$delivery->vendor} for flat ".($delivery->flat?->wing ?? '-').'-'.($delivery->flat?->flat_number ?? '-').' logged.');

            $this->notificationService->notify($delivery, 'Delivery received');

            return redirect()->route('deliveries.index')
                ->with(['status' => 'success', 'message' => 'Delivery recorded successfully.']);
        } catch (Exception $e) {
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

            ActivityLogger::log('deliver', $delivery, "Delivery from {$delivery->vendor} marked as delivered.");

            $this->notificationService->notify($delivery, 'Delivery delivered');

            return back()
                ->with(['status' => 'success', 'message' => 'Delivery marked as delivered.']);
        } catch (Exception $e) {
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
            $data = $request->validated();

            $oldValues = $delivery->only([
                'flat_id',
                'resident_id',
                'vendor',
                'package_details',
                'status',
            ]);

            if (isset($data['resident_id']) && $data['resident_id'] !== $delivery->resident_id) {
                $data['flat_id'] = Resident::findOrFail($data['resident_id'])->flat_id;
            }

            $delivery->fill($data);

            if (! $delivery->isDirty()) {
                return redirect()
                    ->route('deliveries.index')
                    ->with(['status' => 'info', 'message' => 'No changes detected.']);
            }

            $delivery->save();

            ActivityLogger::log('update', $delivery, 'Delivery details updated.');

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
        } catch (Exception $e) {
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
            ActivityLogger::log('delete', $delivery, 'Delivery record deleted.');
            $delivery->delete();
            $this->notificationService->notify($delivery, 'Delivery deleted');

            return response()->json([
                'success' => true,
                'message' => 'Delivery deleted successfully.',
            ]);
        } catch (Exception $e) {
            $this->notificationService->failed('delete delivery', $e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete delivery.',
            ]);
        }
    }

    public function restore(Delivery $delivery)
    {
        $this->authorize('restore', $delivery);

        try {
            $delivery->restore();

            ActivityLogger::log('restore', $delivery, 'Delivery restored.');

            $this->notificationService->notify($delivery, 'Delivery restored');

            return response()->json([
                'success' => true,
                'message' => 'Delivery restored successfully.',
            ]);
        } catch (Exception $e) {
            $this->notificationService->failed('restore delivery', $e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore delivery.',
            ]);
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
        $query = Delivery::query()
            ->select([
                'deliveries.*',
                'users.name as resident_name',
                'wings.name as flat_wing',
                'flats.floor as flat_floor',
                'flats.flat_number',
                'flats.society_id',
                'societies.name as society_name',
            ])
            ->leftJoin('residents', 'deliveries.resident_id', '=', 'residents.id')
            ->leftJoin('users', 'residents.user_id', '=', 'users.id')
            ->leftJoin('flats', 'deliveries.flat_id', '=', 'flats.id')
            ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id')
            ->leftJoin('societies', 'flats.society_id', '=', 'societies.id');

        $user = auth()->user();

        if (! $user->isSuperAdmin()) {
            if ($user->isResident()) {
                $query->where('deliveries.flat_id', $user->resident->flat_id);
            } else {
                $query->where('flats.society_id', $user->society_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('deliveries.status', $request->status);
        }

        if ($request->filled('flat_id')) {
            $query->where('deliveries.flat_id', $request->flat_id);
        }

        if ($request->filled('vendor')) {
            $query->where('deliveries.vendor', 'like', "%{$request->vendor}%");
        }

        if ($request->filled('from_date')) {
            $query->whereDate('deliveries.received_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('deliveries.received_at', '<=', $request->to_date);
        }

        return $query;
    }
}
