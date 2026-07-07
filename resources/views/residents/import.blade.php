@extends('layouts.app')

@section('title', 'Bulk Upload Residents')

@section('content')
<style>
    .import-container {
        animation: fadeIn 0.4s ease-out;
    }
    .import-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 3.5rem 2rem;
        background: #f8fafc;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .import-dropzone:hover, .import-dropzone.dragover {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.03);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
    }
    .dropzone-icon-wrapper {
        width: 80px;
        height: 80px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        transition: all 0.3s ease;
    }
    .import-dropzone:hover .dropzone-icon-wrapper {
        transform: scale(1.1);
        background: #3b82f6;
        color: #ffffff;
    }
    .import-dropzone:hover .dropzone-icon-wrapper i {
        color: #ffffff !important;
        transform: translateY(-2px);
    }
    .dropzone-icon-wrapper i {
        font-size: 2.25rem;
        color: #3b82f6;
        transition: all 0.3s ease;
    }
    .step-card {
        border-left: 4px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .step-card:hover {
        border-left-color: #3b82f6;
        background-color: #f8fafc;
    }
    .step-number {
        width: 28px;
        height: 28px;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .step-card:hover .step-number {
        background: #3b82f6;
        color: #ffffff;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="import-container container-fluid px-0">
    <div class="row g-4">
        <!-- Upload Form Column -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <span class="p-2 bg-primary-subtle text-primary rounded-3 me-2">
                            <i class="bi bi-file-earmark-arrow-up-fill fs-5"></i>
                        </span>
                        Import Residents CSV
                    </h5>
                </div>

                <form method="POST" action="{{ route('residents.import.upload') }}" enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div class="card-body p-4">
                        <div class="import-dropzone text-center" id="dropzone" onclick="document.getElementById('fileInput').click()">
                            <input type="file" name="csv_file" id="fileInput" class="d-none @error('csv_file') is-invalid @enderror" accept=".csv" required />
                            
                            <div class="dropzone-icon-wrapper">
                                <i class="bi bi-cloud-arrow-up-fill"></i>
                            </div>
                            
                            <h5 class="fw-bold text-dark mb-2" id="dropzoneTitle">Drag & Drop CSV File</h5>
                            <p class="text-secondary small mb-3">Or click here to browse files on your computer</p>
                            
                            <div class="d-inline-flex align-items-center px-3 py-1.5 bg-white border rounded-pill shadow-sm small text-secondary" id="fileDetails">
                                <i class="bi bi-info-circle text-primary me-2"></i>Only CSV files up to 5MB are accepted
                            </div>

                            @error('csv_file')
                                <div class="text-danger small mt-3"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-warning">
                            <div class="d-flex">
                                <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-3"></i>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Important Note</h6>
                                    <p class="mb-0 text-secondary small">Make sure your CSV data matches the layout template. Any duplicate emails or non-existent flats will flag errors during verification.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                        <a href="{{ route('residents.index') }}" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 d-flex align-items-center" id="btnSubmit">
                            <i class="bi bi-arrow-right-circle me-2"></i>Upload & Map
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions Column -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-journal-text me-2 text-primary"></i>How it works
                    </h5>
                </div>

                <div class="card-body p-4">
                    <!-- Download Template Section -->
                    <div class="p-3 bg-primary-subtle bg-opacity-10 border border-primary-subtle rounded-3 mb-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Standard template</h6>
                            <p class="text-secondary mb-0 small">Download pre-formatted sample CSV layout</p>
                        </div>
                        <a href="{{ route('residents.import.sample') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center">
                            <i class="bi bi-download me-1.5"></i>Template
                        </a>
                    </div>

                    <!-- Steps Timeline -->
                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 rounded-3 step-card">
                            <div class="d-flex align-items-center mb-2">
                                <span class="step-number me-2">1</span>
                                <h6 class="fw-bold text-dark mb-0">Download & Fill</h6>
                            </div>
                            <p class="text-secondary small mb-0">Download the standard CSV template. Add resident columns: Name, Email, Phone, Wing Name, and Flat Number.</p>
                        </div>

                        <div class="p-3 rounded-3 step-card">
                            <div class="d-flex align-items-center mb-2">
                                <span class="step-number me-2">2</span>
                                <h6 class="fw-bold text-dark mb-0">Map Fields</h6>
                            </div>
                            <p class="text-secondary small mb-0">Upload your file, then select which CSV column corresponds to each system parameter. Setup default resident type & missing details action plans.</p>
                        </div>

                        <div class="p-3 rounded-3 step-card">
                            <div class="d-flex align-items-center mb-2">
                                <span class="step-number me-2">3</span>
                                <h6 class="fw-bold text-dark mb-0">Preview & Confirm</h6>
                            </div>
                            <p class="text-secondary small mb-0">Review validation checks for all rows. Once clean, submit to import them into the database safely wrapped in SQL transactions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const fileDetails = document.getElementById('fileDetails');
        const dropzoneTitle = document.getElementById('dropzoneTitle');

        // Drag events
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        // Drop file
        dropzone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length) {
                fileInput.files = files;
                updateFileDetails(files[0]);
            }
        }, false);

        // File browser selection change
        fileInput.addEventListener('change', function() {
            if (this.files.length) {
                updateFileDetails(this.files[0]);
            }
        });

        function updateFileDetails(file) {
            if (file) {
                dropzoneTitle.textContent = "File Selected";
                fileDetails.innerHTML = `<i class="bi bi-filetype-csv text-success me-2 fs-6"></i><strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
                fileDetails.className = "d-inline-flex align-items-center px-3 py-1.5 bg-success-subtle text-success border border-success-subtle rounded-pill small";
            }
        }
    });
</script>
@endsection
