@extends('layouts.app')

@section('title', 'Validate Import Data')

@section('content')
<style>
    .preview-container {
        animation: fadeIn 0.4s ease-out;
    }
    .preview-card {
        border-radius: 16px;
    }
    .table-input {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        width: 100%;
        transition: all 0.2s ease;
    }
    .table-input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .table-input.is-invalid {
        border-color: #ef4444;
        background-color: #fef2f2;
    }
    .sticky-actions {
        position: sticky;
        bottom: 0;
        background: #ffffff;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
        border-top: 1px solid #e2e8f0;
        z-index: 10;
        margin: 0 -1.5rem -1.5rem;
        padding: 1.25rem 1.5rem;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="preview-container container-fluid px-0">
    <!-- Validation Summary Card -->
    <div class="card shadow-sm border-0 rounded-3 mb-4 preview-card">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <span class="p-2 bg-primary-subtle text-primary rounded-3 me-2">
                        <i class="bi bi-shield-check-fill fs-5"></i>
                    </span>
                    Validation Results & Editor
                </h5>
                <small class="text-secondary">File uploaded: <strong>{{ $bulkImport->original_filename }}</strong></small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                Step 3 of 3: Verification
            </span>
        </div>

        <form method="POST" action="{{ route('residents.import.process') }}" id="importProcessForm">
            @csrf
            <!-- Preservation settings -->
            <input type="hidden" name="import_id" value="{{ $bulkImport->id }}" />
            <input type="hidden" name="society_id" value="{{ $societyId }}" />
            <input type="hidden" name="default_resident_type" value="{{ request('default_resident_type', $bulkImport->default_resident_type ?? 'tenant') }}" />
            <input type="hidden" name="fallback_email_action" value="{{ request('fallback_email_action', $bulkImport->fallback_email_action ?? 'generate') }}" />
            <input type="hidden" name="fallback_phone_action" value="{{ request('fallback_phone_action', $bulkImport->fallback_phone_action ?? 'generate') }}" />

            <div class="card-body p-4">
                <!-- Summary Stats Card -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 text-center bg-light">
                            <div class="text-secondary small fw-bold uppercase">Total Rows</div>
                            <h3 class="fw-bold mb-0 text-dark">{{ $totalRows }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border border-success rounded-3 text-center bg-success bg-opacity-10">
                            <div class="text-success small fw-bold uppercase">Valid Rows</div>
                            <h3 class="fw-bold mb-0 text-success">{{ $validCount }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border border-danger rounded-3 text-center bg-danger bg-opacity-10">
                            <div class="text-danger small fw-bold uppercase">Invalid Rows</div>
                            <h3 class="fw-bold mb-0 text-danger">{{ $invalidCount }}</h3>
                        </div>
                    </div>
                </div>

                @if ($isValid)
                    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">All Records Validated!</h6>
                            <p class="mb-0 small text-secondary">All rows are valid and ready. Click <strong>Confirm Import</strong> below to register these residents.</p>
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                        <div class="w-100 d-flex justify-content-between align-items-center flex-wrap">
                            <div class="me-3">
                                <h6 class="alert-heading fw-bold mb-1">Validation Conflicts Found ({{ $invalidCount }} rows)</h6>
                                @if ($showEditor)
                                    <p class="mb-0 small text-secondary">You can edit the invalid cells directly inside the table below, then click <strong>Re-validate</strong> to recheck.</p>
                                @else
                                    <p class="mb-0 small text-secondary">The file contains more than 10 records with validation errors. Inline editing is disabled for this volume. Please download the error report below, fix them in your CSV, and re-upload.</p>
                                @endif
                            </div>
                            @if (!$showEditor)
                                <a href="{{ route('residents.import.errors', $bulkImport->id) }}" class="btn btn-danger btn-sm px-3 mt-2 mt-sm-0">
                                    <i class="bi bi-download me-1.5"></i>Download Error Report CSV
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <h6 class="fw-bold text-secondary mb-3 d-flex align-items-center">
                    <i class="bi bi-table me-2"></i>{{ $showEditor ? 'Interactive Data Sheet' : 'Data Preview (Read-Only)' }}
                </h6>
                
                <div class="table-responsive border rounded-3 mb-5" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top shadow-sm" style="z-index: 1;">
                            <tr>
                                <th style="width: 70px;" class="text-center">Row</th>
                                <th style="width: 110px;">Status</th>
                                <th style="width: 220px;">Name <span class="text-danger">*</span></th>
                                <th style="width: 280px;">Email <span class="text-danger">*</span></th>
                                <th style="width: 180px;">Phone <span class="text-danger">*</span></th>
                                <th style="width: 100px;">Wing <span class="text-danger">*</span></th>
                                <th style="width: 100px;">Flat <span class="text-danger">*</span></th>
                                <th style="width: 140px;">Type</th>
                                <th style="min-width: 240px;">Validation Error Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($validatedRows as $index => $row)
                                <tr class="{{ $row['valid'] ? '' : 'table-danger bg-opacity-10' }}">
                                    <!-- Row Number -->
                                    <td class="fw-bold text-secondary text-center">
                                        {{ $row['row_number'] }}
                                        <input type="hidden" name="rows[{{ $index }}][row_number]" value="{{ $row['row_number'] }}">
                                        <input type="hidden" name="rows[{{ $index }}][flat_id]" value="{{ $row['flat_id'] }}">
                                    </td>
                                    
                                    <!-- Status Badge -->
                                    <td>
                                        @if ($row['valid'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill small">
                                                <i class="bi bi-check-circle-fill me-1"></i>Valid
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill small">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>Error
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Name Field -->
                                    <td>
                                        @if ($showEditor)
                                            <input type="text" name="rows[{{ $index }}][name]" value="{{ old('rows.'.$index.'.name', $row['name']) }}" 
                                                class="table-input @if(!$row['valid'] && collect($row['errors'])->contains(fn($e) => str_contains(strtolower($e), 'name'))) is-invalid @endif" required />
                                        @else
                                            <span class="fw-semibold text-dark">{{ $row['name'] }}</span>
                                            <input type="hidden" name="rows[{{ $index }}][name]" value="{{ $row['name'] }}">
                                        @endif
                                    </td>

                                    <!-- Email Field -->
                                    <td>
                                        @if ($showEditor)
                                            <input type="email" name="rows[{{ $index }}][email]" value="{{ old('rows.'.$index.'.email', $row['email']) }}" 
                                                class="table-input @if(!$row['valid'] && collect($row['errors'])->contains(fn($e) => str_contains(strtolower($e), 'email'))) is-invalid @endif" required />
                                        @else
                                            <span>{{ $row['email'] }}</span>
                                            <input type="hidden" name="rows[{{ $index }}][email]" value="{{ $row['email'] }}">
                                        @endif
                                        @if (str_contains($row['email'], '@dummy.societyms.test'))
                                            <div class="text-muted" style="font-size: 0.65rem; padding-left: 0.5rem;"><i class="bi bi-magic me-0.5"></i>Auto-generated fallback</div>
                                        @endif
                                    </td>

                                    <!-- Phone Field -->
                                    <td>
                                        @if ($showEditor)
                                            <input type="text" name="rows[{{ $index }}][phone]" value="{{ old('rows.'.$index.'.phone', $row['phone']) }}" 
                                                class="table-input @if(!$row['valid'] && collect($row['errors'])->contains(fn($e) => str_contains(strtolower($e), 'phone'))) is-invalid @endif" required />
                                        @else
                                            <span>{{ $row['phone'] }}</span>
                                            <input type="hidden" name="rows[{{ $index }}][phone]" value="{{ $row['phone'] }}">
                                        @endif
                                        @if (str_starts_with($row['phone'], '99') && strlen($row['phone']) === 10)
                                            <div class="text-muted" style="font-size: 0.65rem; padding-left: 0.5rem;"><i class="bi bi-magic me-0.5"></i>Auto-generated fallback</div>
                                        @endif
                                    </td>

                                    <!-- Wing Field -->
                                    <td>
                                        @if ($showEditor)
                                            <input type="text" name="rows[{{ $index }}][wing]" value="{{ old('rows.'.$index.'.wing', $row['wing']) }}" 
                                                class="table-input text-center @if(!$row['valid'] && collect($row['errors'])->contains(fn($e) => str_contains(strtolower($e), 'wing'))) is-invalid @endif" required />
                                        @else
                                            <span class="badge bg-light text-dark border">{{ $row['wing'] }}</span>
                                            <input type="hidden" name="rows[{{ $index }}][wing]" value="{{ $row['wing'] }}">
                                        @endif
                                    </td>

                                    <!-- Flat Number Field -->
                                    <td>
                                        @if ($showEditor)
                                            <input type="text" name="rows[{{ $index }}][flat_number]" value="{{ old('rows.'.$index.'.flat_number', $row['flat_number']) }}" 
                                                class="table-input text-center @if(!$row['valid'] && collect($row['errors'])->contains(fn($e) => str_contains(strtolower($e), 'flat'))) is-invalid @endif" required />
                                        @else
                                            <span class="badge bg-light text-dark border">{{ $row['flat_number'] }}</span>
                                            <input type="hidden" name="rows[{{ $index }}][flat_number]" value="{{ $row['flat_number'] }}">
                                        @endif
                                    </td>

                                    <!-- Resident Type Field -->
                                    <td>
                                        @if ($showEditor)
                                            <select name="rows[{{ $index }}][resident_type]" class="form-select form-select-sm table-input">
                                                <option value="tenant" @selected($row['resident_type'] === 'tenant')>Tenant</option>
                                                <option value="owner" @selected($row['resident_type'] === 'owner')>Owner</option>
                                            </select>
                                        @else
                                            <span class="badge {{ $row['resident_type'] === 'owner' ? 'bg-success' : 'bg-info' }}">{{ ucfirst($row['resident_type']) }}</span>
                                            <input type="hidden" name="rows[{{ $index }}][resident_type]" value="{{ $row['resident_type'] }}">
                                        @endif
                                    </td>

                                    <!-- Error details column -->
                                    <td>
                                        @if ($row['valid'])
                                            <span class="text-success small d-flex align-items-center">
                                                <i class="bi bi-check-all fs-5 me-1"></i>Ready for import
                                            </span>
                                        @else
                                            <div class="text-danger small">
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($row['errors'] as $error)
                                                        <li><strong>{{ $error }}</strong></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Standard Card Footer -->
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('residents.import.form') }}" class="btn btn-light px-4">
                    <i class="bi bi-arrow-left me-2"></i>Upload different file
                </a>
                
                <div class="d-flex gap-2">
                    @if ($showEditor)
                        <!-- Re-validate Button (Overrides Form Action) -->
                        <button type="submit" formaction="{{ route('residents.import.map') }}" class="btn btn-outline-primary px-4">
                            <i class="bi bi-arrow-clockwise me-2"></i>Re-validate edited rows
                        </button>
                    @else
                        <!-- Download Error Report Button -->
                        <a href="{{ route('residents.import.errors', $bulkImport->id) }}" class="btn btn-outline-danger px-4">
                            <i class="bi bi-download me-2"></i>Download Error Report
                        </a>
                    @endif
                    
                    <!-- Process Import Button (Submits Form to processImport) -->
                    <button type="submit" class="btn btn-success px-4" @disabled(!$isValid)>
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Confirm Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
