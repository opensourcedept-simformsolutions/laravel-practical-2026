<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function data()
    {
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
            ->addColumn('flat', fn ($delivery) => $delivery->flat->flat_number)
            ->addColumn('resident', fn ($delivery) => $delivery->resident->user->name)
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
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Delivery::class);

        return view('deliveries.index');
    }

    public function create()
    {
        $this->authorize('create', Delivery::class);

        return view('deliveries.create', [
            'flatOptions' => $this->getFlatOptions(),
            'residentOptions' => $this->getResidentOptions(),
        ]);
    }

    public function store(StoreDeliveryRequest $request)
    {
        $this->authorize('create', Delivery::class);

        $validatedData = $request->validated();

        $delivery = Delivery::create([
            'flat_id' => $validatedData['flat_id'],
            'resident_id' => $validatedData['resident_id'],
            'vendor' => $validatedData['vendor'],
            'package_details' => $validatedData['package_details'],
            'status' => DeliveryStatus::RECEIVED->value,
            'received_at' => now(),
            'delivered_at' => null,
        ]);

        Session::flash('message', 'Delivery recorded successfully.');
        Session::flash('status', 'success');

        return redirect()->route('deliveries.index');
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

        $delivery->update([
            'status' => DeliveryStatus::DELIVERED->value,
            'delivered_at' => now()]);

        Session::flash('message', 'Delivery marked as delivered.');
        Session::flash('status', 'success');

        return redirect()->back();
    }

    public function edit(Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        return view('deliveries.edit', [
            'delivery' => $delivery,
            'flatOptions' => $this->getFlatOptions(),
            'residentOptions' => $this->getResidentOptions(),
        ]);
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        $delivery->update($request->validated());

        Session::flash('message', 'Delivery updated successfully.');
        Session::flash('status', 'success');

        return redirect()->route('deliveries.index');
    }

    public function destroy(Delivery $delivery)
    {
        $this->authorize('delete', $delivery);

        $delivery->delete();

        Session::flash('message', 'Delivery Deleted successfully.');
        Session::flash('status', 'success');

        return redirect()->route('deliveries.index');
    }

    private function getFlatOptions()
    {
        $user = auth()->user();

        return Flat::query()
            ->when(! $user->isSuperAdmin(), function ($query) use ($user) {
                $query->where('society_id', $user->society_id);
            })
            ->get()
            ->mapWithKeys(function ($flat) {
                return [
                    $flat->id => $flat->wing.'-'.$flat->flat_number,
                ];
            });
    }

    private function getResidentOptions()
    {
        $user = auth()->user();

        return Resident::with('user')
            ->when(! $user->isSuperAdmin(), function ($query) use ($user) {
                $query->whereHas('flat', function ($q) use ($user) {
                    $q->where('society_id', $user->society_id);
                });
            })
            ->get()
            ->mapWithKeys(function ($resident) {
                return [
                    $resident->id => $resident->user->name,
                ];
            });
    }
}
