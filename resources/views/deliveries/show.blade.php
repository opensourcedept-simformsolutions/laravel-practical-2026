@extends('layouts.app')

@section('title', 'Delivery Details')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Delivery #{{ $delivery->id }} Details</h5>
            <span class="badge {{ $delivery->status === 'delivered' ? 'bg-success' : 'bg-primary' }}">
                {{ ucfirst($delivery->status) }}
            </span>
        </div>

        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Flat</div>
                        <div class="fw-semibold">{{ $delivery->flat->wing }}-{{ $delivery->flat->flat_number }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Resident</div>
                        <div class="fw-semibold">{{ $delivery->resident->user->name }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Vendor</div>
                        <div class="fw-semibold">{{ $delivery->vendor }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Status</div>
                        <div class="fw-semibold">{{ ucfirst($delivery->status) }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Received At</div>
                        <div class="fw-semibold">{{ $delivery->received_at?->format('d M Y h:i A') }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Delivered At</div>
                        <div class="fw-semibold">
                            {{ $delivery->delivered_at?->format('d M Y h:i A') ?? 'Not Delivered Yet' }}</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Package Details</div>
                        <div class="fw-semibold" style="white-space: pre-line;">{{ $delivery->package_details }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
            <a href="{{ route('deliveries.index') }}" class="btn btn-light">
                Back
            </a>
            @if ($delivery->status !== 'delivered')
                @canany(['is-admin', 'is-gatekeeper'])
                    <a href="{{ route('deliveries.edit', $delivery) }}" class="btn btn-warning">
                        Edit Delivery
                    </a>
                @endcanany
            @endif
        </div>
    </div>
@endsection
