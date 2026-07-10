@extends('layouts.app')

@section('title', 'Flat Details')

@section('content')
    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Left: Flat Details Card -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-house-door-fill text-primary me-2"></i>Flat Profile
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 80px; height: 80px;">
                                <i class="bi bi-house-fill" style="font-size: 2.5rem;"></i>
                            </div>
                            <h4 class="mt-3 fw-bold text-dark">
                                {{ $flat->wingRelation?->name ?? $flat->wing ?? '-' }}-{{ $flat->flat_number }}
                            </h4>
                            <p class="text-secondary small mb-0">Flat ID: #{{ $flat->id }}</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-secondary small d-block">Wing</span>
                                    <span class="text-dark fw-bold">{{ $flat->wingRelation?->name ?? $flat->wing ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-secondary small d-block">Floor</span>
                                    <span class="text-dark fw-bold">{{ $flat->floor }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-secondary small d-block">Flat Number</span>
                                    <span class="text-dark fw-bold">{{ $flat->flat_number }}</span>
                                </div>
                            </div>

                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded border">
                                        <span class="text-secondary small d-block">Society Name</span>
                                        <span class="text-dark fw-bold">{{ $flat->society->name ?? '-' }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Residents List Card -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-people-fill text-primary me-2"></i>Flat Residents
                        </h5>
                        <span class="badge bg-primary rounded-pill px-3 py-1.5">{{ $flat->residents->count() }} Registered</span>
                    </div>
                    <div class="card-body p-4">
                        @if($flat->residents->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($flat->residents as $resident)
                                    <div class="list-group-item px-0 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                                <span class="fw-bold">{{ strtoupper(substr($resident->user->name ?? '-', 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark">{{ $resident->user->name ?? '-' }}</h6>
                                                <p class="mb-0 text-secondary small">
                                                    <i class="bi bi-envelope me-1"></i>{{ $resident->user->email ?? '-' }}
                                                    <span class="mx-1 d-none d-sm-inline">&bull;</span>
                                                    <br class="d-inline d-sm-none" />
                                                    <i class="bi bi-telephone me-1"></i>{{ $resident->user->phone ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-5 ms-sm-0">
                                            @if($resident->resident_type === 'owner')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill small">Owner</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill small">Tenant</span>
                                            @endif
                                            <a href="{{ route('residents.show', $resident->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                <i class="bi bi-eye me-1"></i>View Profile
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-people mb-2" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold mb-1">No Residents Registered</h6>
                                <p class="small mb-0">There are no resident accounts currently linked to this flat.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-start">
            <a href="{{ route('flats.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Back to Flats
            </a>
        </div>
    </div>
@endsection
