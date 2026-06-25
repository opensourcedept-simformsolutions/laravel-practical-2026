@extends('layouts.app')

@section('title', 'Delivery Details')

@section('content')
    <div class="mx-auto w-100" style="max-width: 1000px;">
        <x-form.form-section title="Delivery #{{ $delivery->id }} Details">

            <div class="detail-grid">

                <div class="detail-item">
                    <span class="detail-label">Flat</span>
                    <span class="detail-value">
                        {{ $delivery->flat->flat_number }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Resident</span>
                    <span class="detail-value">
                        {{ $delivery->resident->user->name }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Vendor</span>
                    <span class="detail-value">
                        {{ $delivery->vendor }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>

                    <span
                        class="badge rounded-pill {{ $delivery->status === 'delivered' ? 'text-bg-success' : 'text-bg-primary' }}">
                        {{ ucfirst($delivery->status) }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Received At</span>
                    <span class="detail-value">
                        {{ $delivery->received_at?->format('d M Y h:i A') }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Delivered At</span>
                    <span class="detail-value">
                        {{ $delivery->delivered_at?->format('d M Y h:i A') ?? 'Not Delivered Yet' }}
                    </span>
                </div>

                <div class="detail-item detail-item-full">
                    <span class="detail-label">Package Details</span>
                    <span class="detail-value">
                        {{ $delivery->package_details }}
                    </span>
                </div>

            </div>

            <div class="section-actions mt-3">
                <a href="{{ route('deliveries.index') }}" class="btn btn-secondary py-2">
                    <i class="bi bi-arrow-left"></i>
                    Back
                </a>

                @if ($delivery->status !== 'received')
                    @canany(['is-admin', 'is-gatekeeper'])
                        <a href="{{ route('deliveries.edit', $delivery) }}" class="btn btn-warning py-2 mx-2">
                            <i class="bi bi-pencil"></i>
                            Edit Delivery
                        </a>
                    @endcanany
                @endif
            </div>

        </x-form.form-section>

    </div>
@endsection
