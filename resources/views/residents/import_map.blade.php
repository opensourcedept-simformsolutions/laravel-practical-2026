@extends('layouts.app')

@section('title', 'Map CSV Columns')

@section('content')
<style>
    .mapping-container {
        animation: fadeIn 0.4s ease-out;
    }
    .mapping-card {
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .mapping-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .panel-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="mapping-container container-fluid px-0">
    <div class="card shadow-sm border-0 rounded-3 mapping-card">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <span class="p-2 bg-primary-subtle text-primary rounded-3 me-2">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                    </span>
                    Configure Import Columns
                </h5>
                <small class="text-secondary">File uploaded: <strong>{{ $bulkImport->original_filename }}</strong></small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                Step 2 of 3: Column Mapping
            </span>
        </div>

        <form method="POST" action="{{ route('residents.import.map') }}">
            @csrf
            <input type="hidden" name="import_id" value="{{ $bulkImport->id }}" />

            <div class="card-body p-4">
                <!-- Society Selection for Super Admin -->
                @if(auth()->user()->isSuperAdmin())
                    <div class="p-4 bg-warning-subtle bg-opacity-25 border border-warning-subtle rounded-3 mb-4">
                        <div class="d-flex align-items-start">
                            <span class="p-2 bg-warning bg-opacity-10 text-warning rounded-3 me-3">
                                <i class="bi bi-buildings-fill fs-4"></i>
                            </span>
                            <div class="w-100">
                                <h6 class="fw-bold text-dark mb-2">Import Target Society</h6>
                                <p class="text-secondary small mb-3">Since you are logged in as a Super Admin, please choose the target society where these residents should be registered.</p>
                                <div class="col-md-6">
                                    <select name="society_id" class="form-select form-select-sm" required>
                                        <option value="">Select Target Society</option>
                                        @foreach ($societies as $soc)
                                            <option value="{{ $soc->id }}" @selected(old('society_id') == $soc->id)>{{ $soc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('society_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section 1: Resident Details Mapping -->
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="p-2 bg-info-subtle text-info rounded-3 me-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-badge-fill small"></i>
                        </span>
                        <h6 class="mb-0 fw-bold text-dark">1. Core Resident Details</h6>
                    </div>
                    
                    <div class="panel-section border">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="mapping-icon-wrapper bg-white border text-primary me-2 shadow-sm">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <div>
                                        <label class="form-label fw-bold mb-0" style="font-size: 0.9rem;">Resident Name <span class="text-danger">*</span></label>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Full name of the user</div>
                                    </div>
                                </div>
                                <select name="map_name" class="form-select" required>
                                    <option value="">-- Select CSV Column --</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}" @selected(strtolower($header) === 'name' || strtolower($header) === 'resident name' || strtolower($header) === 'resident_name' || strtolower($header) === 'fullname')>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Email -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="mapping-icon-wrapper bg-white border text-success me-2 shadow-sm">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <div>
                                        <label class="form-label fw-bold mb-0" style="font-size: 0.9rem;">Email Address <span class="text-danger">*</span></label>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Login email address</div>
                                    </div>
                                </div>
                                <select name="map_email" class="form-select" required>
                                    <option value="">-- Select CSV Column --</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}" @selected(strtolower($header) === 'email' || strtolower($header) === 'email address' || strtolower($header) === 'email_address')>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="mapping-icon-wrapper bg-white border text-warning me-2 shadow-sm">
                                        <i class="bi bi-telephone-fill"></i>
                                    </span>
                                    <div>
                                        <label class="form-label fw-bold mb-0" style="font-size: 0.9rem;">Phone Number <span class="text-danger">*</span></label>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Contact phone number</div>
                                    </div>
                                </div>
                                <select name="map_phone" class="form-select" required>
                                    <option value="">-- Select CSV Column --</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}" @selected(strtolower($header) === 'phone' || strtolower($header) === 'phone number' || strtolower($header) === 'mobile' || strtolower($header) === 'contact')>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: House Location Mapping -->
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="p-2 bg-success-subtle text-success rounded-3 me-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-house-door-fill small"></i>
                        </span>
                        <h6 class="mb-0 fw-bold text-dark">2. Physical Location</h6>
                    </div>

                    <div class="panel-section border">
                        <div class="row g-3">
                            <!-- Wing Name -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="mapping-icon-wrapper bg-white border text-info me-2 shadow-sm">
                                        <i class="bi bi-building"></i>
                                    </span>
                                    <div>
                                        <label class="form-label fw-bold mb-0" style="font-size: 0.9rem;">Wing Name <span class="text-danger">*</span></label>
                                        <div class="text-muted small" style="font-size: 0.75rem;">e.g., Wing A, Block B</div>
                                    </div>
                                </div>
                                <select name="map_wing" class="form-select" required>
                                    <option value="">-- Select CSV Column --</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}" @selected(strtolower($header) === 'wing' || strtolower($header) === 'wing name' || strtolower($header) === 'wing_name' || strtolower($header) === 'block')>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Flat Number -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="mapping-icon-wrapper bg-white border text-danger me-2 shadow-sm">
                                        <i class="bi bi-door-closed-fill"></i>
                                    </span>
                                    <div>
                                        <label class="form-label fw-bold mb-0" style="font-size: 0.9rem;">Flat Number <span class="text-danger">*</span></label>
                                        <div class="text-muted small" style="font-size: 0.75rem;">e.g., 101, 204, 303</div>
                                    </div>
                                </div>
                                <select name="map_flat_number" class="form-select" required>
                                    <option value="">-- Select CSV Column --</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}" @selected(strtolower($header) === 'flat' || strtolower($header) === 'flat number' || strtolower($header) === 'flat_number')>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Resident Type mapping -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="mapping-icon-wrapper bg-white border text-secondary me-2 shadow-sm">
                                        <i class="bi bi-tag-fill"></i>
                                    </span>
                                    <div>
                                        <label class="form-label fw-bold mb-0" style="font-size: 0.9rem;">Resident Type</label>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Owner or Tenant</div>
                                    </div>
                                </div>
                                <select name="map_resident_type" class="form-select">
                                    <option value="">-- Choose from CSV (if present) --</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}" @selected(strtolower($header) === 'type' || strtolower($header) === 'resident type' || strtolower($header) === 'resident_type')>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Fallback Strategy Configurations -->
                <div>
                    <div class="d-flex align-items-center mb-3">
                        <span class="p-2 bg-warning-subtle text-warning rounded-3 me-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-gear-wide-connected small"></i>
                        </span>
                        <h6 class="mb-0 fw-bold text-dark">3. Missing / Fallback Field Handling</h6>
                    </div>

                    <div class="panel-section border">
                        <div class="row g-3">
                            <!-- Default Resident Type -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark mb-1 small">Default Type</label>
                                <select name="default_resident_type" class="form-select" required>
                                    <option value="tenant" selected>Tenant</option>
                                    <option value="owner">Owner</option>
                                </select>
                                <div class="form-text text-muted" style="font-size: 0.75rem;">Used if resident type is empty or unmapped.</div>
                            </div>

                            <!-- If Email is Missing -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark mb-1 small">Fallback Email Strategy</label>
                                <select name="fallback_email_action" class="form-select" required>
                                    <option value="generate" selected>Auto-Generate Dummy Email</option>
                                    <option value="fail">Fail Validation (Reject Row)</option>
                                </select>
                                <div class="form-text text-muted" style="font-size: 0.75rem;">e.g., john-doe_301@dummy.societyms.test</div>
                            </div>

                            <!-- If Phone is Missing -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark mb-1 small">Fallback Phone Strategy</label>
                                <select name="fallback_phone_action" class="form-select" required>
                                    <option value="generate" selected>Auto-Generate Dummy Phone</option>
                                    <option value="fail">Fail Validation (Reject Row)</option>
                                </select>
                                <div class="form-text text-muted" style="font-size: 0.75rem;">Generates unique test phone starting with 99.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('residents.import.form') }}" class="btn btn-light px-4">Back</a>
                <button type="submit" class="btn btn-primary px-4 d-flex align-items-center">
                    <i class="bi bi-shield-check me-2"></i>Validate Records
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
