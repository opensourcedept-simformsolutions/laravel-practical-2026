@extends('layouts.app')

@section('title', 'Resident Details')

@section('content')
    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Left: Resident Details Card -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-person-fill text-primary me-2"></i>Resident Profile
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 80px; height: 80px;">
                                <span class="fw-bold fs-2">{{ strtoupper(substr($resident->user->name ?? '-', 0, 1)) }}</span>
                            </div>
                            <h4 class="mt-3 fw-bold text-dark">{{ $resident->user->name ?? '-' }}</h4>
                            <p class="text-secondary small mb-2">Resident ID: #{{ $resident->id }}</p>
                            @if($resident->resident_type === 'owner')
                                <span class="badge bg-success px-3 py-1.5 rounded-pill fw-semibold">Owner</span>
                            @else
                                <span class="badge bg-info text-white px-3 py-1.5 rounded-pill fw-semibold">Tenant</span>
                            @endif
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-secondary small d-block">Email Address</span>
                                    <span class="text-dark fw-bold">{{ $resident->user->email ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-secondary small d-block">Phone Number</span>
                                    <span class="text-dark fw-bold">{{ $resident->user->phone ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-secondary small d-block">Society Name</span>
                                    <span class="text-dark fw-bold">{{ $resident->flat?->society?->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-secondary small d-block">Flat Location</span>
                                    <span class="text-dark fw-bold">
                                        {{ $resident->flat?->wingRelation?->name ?? $resident->flat?->wing ?? '-' }} Wing, 
                                        Floor {{ $resident->flat?->floor ?? '-' }}, 
                                        Flat {{ $resident->flat?->flat_number ?? '-' }}
                                    </span>
                                    <a href="{{ route('flats.show', $resident->flat_id) }}" class="btn btn-link btn-sm p-0 ms-2 d-inline-block">
                                        View Flat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Co-residents List Card -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-house-heart-fill text-primary me-2"></i>Co-Residents (Same Flat)
                        </h5>
                        <span class="badge bg-primary rounded-pill px-3 py-1.5">{{ $coResidents->count() }} Others</span>
                    </div>
                    <div class="card-body p-4">
                        @if($coResidents->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($coResidents as $co)
                                    <div class="list-group-item px-0 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                                <span class="fw-bold">{{ strtoupper(substr($co->user->name ?? '-', 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark">{{ $co->user->name ?? '-' }}</h6>
                                                <p class="mb-0 text-secondary small">
                                                    <i class="bi bi-envelope me-1"></i>{{ $co->user->email ?? '-' }}
                                                    <span class="mx-1 d-none d-sm-inline">&bull;</span>
                                                    <br class="d-inline d-sm-none" />
                                                    <i class="bi bi-telephone me-1"></i>{{ $co->user->phone ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-5 ms-sm-0">
                                            @if($co->resident_type === 'owner')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill small">Owner</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill small">Tenant</span>
                                            @endif
                                            <a href="{{ route('residents.show', $co->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                <i class="bi bi-eye me-1"></i>View Profile
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-house mb-2" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold mb-1">No Other Residents</h6>
                                <p class="small mb-0">This resident is the only registered individual for this flat.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-start">
            <a href="{{ route('residents.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Back to Residents
            </a>
        </div>
    </div>
@endsection
