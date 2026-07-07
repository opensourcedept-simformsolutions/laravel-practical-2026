@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Left Column: User Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden text-center h-100">
                    <!-- Top header background -->
                    <div style="height: 100px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);"></div>
                    
                    <div class="card-body px-4 pb-4 pt-0">
                        <!-- Avatar (pulls up into the header) -->
                        <div class="d-flex justify-content-center" style="margin-top: -50px;">
                            <div class="bg-white p-1 rounded-circle shadow" style="width: 100px; height: 100px;">
                                <div class="bg-primary-subtle text-primary rounded-circle w-100 h-100 d-flex align-items-center justify-content-center fw-bold" style="font-size: 2.2rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </div>
                        </div>

                        <!-- User Identity -->
                        <h5 class="fw-bold text-dark mt-3 mb-1">{{ $user->name }}</h5>
                        <p class="text-secondary small mb-3">{{ $user->email }}</p>

                        <!-- Badges -->
                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <span class="badge bg-primary px-3 py-1.5 rounded-pill small">
                                <i class="bi bi-shield-check me-1"></i>{{ ucfirst($user->role->name) }}
                            </span>
                            @if ($user->hasVerifiedEmail())
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill small">
                                    <i class="bi bi-patch-check-fill me-1"></i>Verified
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill small">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Unverified
                                </span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column gap-2 mt-2">
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100">
                                <i class="bi bi-pencil-square me-1"></i>Edit Profile
                            </a>
                            <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-key-fill me-1"></i>Change Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Details Card -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i>Account Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Personal Info Group -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-person-fill me-1"></i>Personal Details
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded-3 border h-100">
                                    <span class="text-secondary small d-block mb-1">Full Name</span>
                                    <span class="text-dark fw-bold">{{ $user->name }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded-3 border h-100">
                                    <span class="text-secondary small d-block mb-1">Email Address</span>
                                    <span class="text-dark fw-bold">{{ $user->email }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded-3 border h-100">
                                    <span class="text-secondary small d-block mb-1">Phone Number</span>
                                    <span class="text-dark fw-bold">{{ $user->phone }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded-3 border h-100">
                                    <span class="text-secondary small d-block mb-1">Member Since</span>
                                    <span class="text-dark fw-bold">{{ $user->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Residence/System Info Group -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4">
                            <i class="bi bi-house-door-fill me-1"></i>Residence Details
                        </h6>
                        @if ($user->can('is-resident'))
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded-3 border h-100">
                                        <span class="text-secondary small d-block mb-1">Resident Type</span>
                                        <span class="text-dark fw-bold">{{ ucfirst($user->resident->resident_type ?? '-') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded-3 border h-100">
                                        <span class="text-secondary small d-block mb-1">Wing</span>
                                        <span class="text-dark fw-bold">{{ $user->resident?->flat?->wing ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded-3 border h-100">
                                        <span class="text-secondary small d-block mb-1">Floor Level</span>
                                        <span class="text-dark fw-bold">{{ $user->resident?->flat?->floor ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded-3 border h-100">
                                        <span class="text-secondary small d-block mb-1">Flat Number</span>
                                        <span class="text-dark fw-bold">{{ $user->resident?->flat?->flat_number ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-light p-4 rounded-3 border text-center">
                                <i class="bi bi-shield-lock text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="fw-bold text-dark mb-1">Administrative Profile</h6>
                                <p class="text-muted small mb-0">Residence information is only applicable for resident accounts.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
