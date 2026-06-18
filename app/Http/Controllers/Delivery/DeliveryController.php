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
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function data()
    {
        $query = Delivery::with([
            'flat',
            'resident.user',
        ]);

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
        return view('deliveries.index');
    }

    public function create()
    {
        return view('deliveries.create', [
            'flatOptions' => $this->getFlatOptions(),
            'residentOptions' => $this->getResidentOptions(),
        ]);
    }

    public function store(StoreDeliveryRequest $request)
    {
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

        return redirect()
            ->route('deliveries.index')
            ->with('Delivery recorded successfully.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['flat', 'resident']);

        return view('deliveries.show', ['delivery' => $delivery]);
    }

    public function markDelivered(Delivery $delivery)
    {
        $delivery->update([
            'status' => DeliveryStatus::DELIVERED->value,
            'delivered_at' => now()]);

        return redirect()
            ->back()
            ->with('Delivery marked as delivered.');
    }

    public function edit(Delivery $delivery)
    {
        return view('deliveries.edit', [
            'delivery' => $delivery,
            'flatOptions' => $this->getFlatOptions(),
            'residentOptions' => $this->getResidentOptions(),
        ]);
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        dd($request->validated());
        $delivery->update($request->validated());

        return redirect()
            ->route('deliveries.index')
            ->with('success', 'Delivery updated successfully.');
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();

        return redirect()
            ->route('deliveries.index')
            ->with('success', 'Delivery Deleted successfully.');
    }

    private function getFlatOptions()
    {
        return Flat::all()->mapWithKeys(function ($flat) {
            return [
                $flat->id => $flat->wing.'-'.$flat->flat_number,
            ];
        });
    }

    private function getResidentOptions()
    {
        return Resident::with('user')->get()->mapWithKeys(function ($resident) {
            return [
                $resident->id => $resident->user->name,
            ];
        });
    }
}
