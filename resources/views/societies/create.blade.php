@extends('layouts.app')

@section('title', 'Create Society')

@section('content')
<style>
    /* Minimalist B&W Wizard Styles */
    .step-nav-label {
        font-weight: 500;
        color: #6c757d;
        cursor: default;
        transition: all 0.2s ease;
    }
    .step-nav-label.active {
        color: #212529;
        font-weight: 700;
        border-bottom: 2px solid #212529;
        padding-bottom: 2px;
    }
    
    /* Step Animation */
    .wizard-panel {
        display: none;
    }
    .wizard-panel.active {
        display: block;
    }

    /* Drag & Drop Upload Zone */
    .upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: #212529;
        background-color: rgba(33, 37, 41, 0.02);
    }
    .upload-zone i {
        color: #495057;
    }

    /* Wing Config Cards */
    .wing-config-card {
        border: 1px solid #dee2e6;
        border-radius: 12px;
        background-color: #ffffff;
        position: relative;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .wing-config-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: #adb5bd;
    }
    .border-dashed {
        border-style: dashed !important;
    }
    .border-dashed:hover {
        border-color: #212529 !important;
        background-color: rgba(33, 37, 41, 0.01);
    }
</style>

<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <!-- Minimal Step Indicator -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                    <div class="fw-bold text-dark fs-5">
                        Step <span id="current-step-display">1</span> of 4: <span id="current-step-title-display">Basic Details</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center text-muted small fw-semibold">
                        <span class="step-nav-label active" data-step="1">1. Details</span>
                        <span class="text-secondary">&middot;</span>
                        <span class="step-nav-label" data-step="2">2. Wings Setup</span>
                        <span class="text-secondary">&middot;</span>
                        <span class="step-nav-label" data-step="3">3. Residents</span>
                        <span class="text-secondary">&middot;</span>
                        <span class="step-nav-label" data-step="4">4. Summary</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wizard Form -->
        <form id="society-wizard-form" method="POST" action="{{ route('societies.store') }}" novalidate>
            @csrf

            <!-- STEP 1: Basic Details -->
            <div class="card shadow-sm border-0 rounded-3 wizard-panel active" id="step-1-panel">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">Society Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Society Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="Enter society name" required>
                            <div class="invalid-feedback">Society name must be between 2 and 100 characters.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pincode <span class="text-danger">*</span></label>
                            <input type="text" name="pincode" class="form-control form-control-lg" placeholder="e.g. 380001" required>
                            <div class="invalid-feedback">Pincode must be a valid 6-digit Indian pincode.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                            <textarea name="address" rows="3" class="form-control" placeholder="Enter society address" required></textarea>
                            <div class="invalid-feedback">Address is required (2 to 255 characters).</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control form-control-lg" placeholder="e.g. Ahmedabad" required>
                            <div class="invalid-feedback">City is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control form-control-lg" placeholder="e.g. Gujarat" required>
                            <div class="invalid-feedback">State is required.</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between">
                    <a href="{{ route('societies.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="button" class="btn btn-dark px-4 next-step-btn" data-current-step="1">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Wings Configuration -->
            <div class="card shadow-sm border-0 rounded-3 wizard-panel" id="step-2-panel">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">Wings & Flats Setup</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Add wings and configure total floors and flats per floor for each wing. Newly created flats will be automatically generated.</p>

                    <!-- Dynamic Wings Cards Grid -->
                    <div id="wings-config-grid" class="row g-4 mb-2">
                        
                        <!-- Add Wing Dashed Card -->
                        <div class="col-md-6" id="add-wing-card-wrapper">
                            <div class="card h-100 border-2 border-dashed d-flex align-items-center justify-content-center py-5" style="cursor: pointer;" id="btn-add-wing-card">
                                <div class="text-center text-muted">
                                    <i class="bi bi-plus-circle fs-3 mb-2"></i>
                                    <div class="fw-bold small">Add Wing</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-4 prev-step-btn" data-current-step="2">
                        <i class="bi bi-arrow-left me-1"></i> Previous
                    </button>
                    <button type="button" class="btn btn-dark px-4 next-step-btn" data-current-step="2">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Residents CSV Import -->
            <div class="card shadow-sm border-0 rounded-3 wizard-panel" id="step-3-panel">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">Bulk Import Residents (Optional)</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">You can optionally upload a CSV file containing resident data to automatically register and welcome them into their respective flats.</p>
                    
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="card bg-light border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3">CSV File Instructions</h6>
                                    <p class="small text-muted mb-2">The CSV file must contain the following columns exactly in the first row:</p>
                                    <ul class="small text-muted ps-3 mb-3">
                                        <li><code>name</code> (2-100 characters)</li>
                                        <li><code>email</code> (must be unique)</li>
                                        <li><code>phone</code> (valid 7-20 digit number)</li>
                                        <li><code>wing</code> (matches configured wing name, e.g. Wing A)</li>
                                        <li><code>flat_number</code> (matches flat layout, e.g. 101)</li>
                                        <li><code>resident_type</code> (must be <code>owner</code> or <code>tenant</code>)</li>
                                    </ul>
                                    <a href="#" class="btn btn-outline-secondary btn-sm w-100" id="btn-download-template">
                                        <i class="bi bi-download me-1"></i> Download CSV Template
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="upload-zone" id="csv-upload-zone">
                                <input type="file" id="csv-file-input" class="d-none" accept=".csv">
                                <i class="bi bi-cloud-arrow-up-fill fs-1 mb-3"></i>
                                <h6 class="fw-bold mb-2">Drag and drop your CSV here</h6>
                                <p class="text-muted small mb-3">or click to browse from files</p>
                                <span class="badge bg-secondary py-2 px-3" id="file-name-badge" style="display:none;"></span>
                            </div>

                            <div class="mt-3 text-end" id="clear-csv-container" style="display:none;">
                                <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none fw-semibold p-0" id="btn-clear-csv">
                                    <i class="bi bi-trash me-1"></i> Clear Uploaded CSV
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Resident Data Preview -->
                    <div id="csv-preview-container" class="mt-4" style="display: none;">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">Uploaded Residents Preview (<span id="csv-record-count">0</span> rows)</h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1.5 px-3 rounded-pill fw-semibold" id="csv-validation-badge">CSV Validated</span>
                        </div>
                        <div class="table-responsive border rounded-3" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-hover table-striped mb-0 align-middle small" id="csv-preview-table">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Wing</th>
                                        <th>Flat</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated dynamically by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-4 prev-step-btn" data-current-step="3">
                        <i class="bi bi-arrow-left me-1"></i> Previous
                    </button>
                    <button type="button" class="btn btn-dark px-4 next-step-btn" data-current-step="3">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Summary & Confirm -->
            <div class="card shadow-sm border-0 rounded-3 wizard-panel" id="step-4-panel">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">Summary & Confirmation</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Society Info -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light rounded-3 h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-dark">Society Details</h6>
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td class="text-muted fw-semibold" style="width: 120px;">Name:</td>
                                            <td class="fw-bold text-dark" id="summary-name">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold">Pincode:</td>
                                            <td id="summary-pincode">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold">Address:</td>
                                            <td id="summary-address">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold">City:</td>
                                            <td id="summary-city">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold">State:</td>
                                            <td id="summary-state">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Config Summary -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light rounded-3 h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-dark">Infrastructure Details</h6>
                                    <div class="d-flex gap-3 mb-4">
                                        <div class="p-3 bg-white rounded-3 border text-center flex-fill">
                                            <div class="text-muted small fw-semibold">Wings</div>
                                            <div class="fs-3 fw-bold text-dark" id="summary-wings-count">0</div>
                                        </div>
                                        <div class="p-3 bg-white rounded-3 border text-center flex-fill">
                                            <div class="text-muted small fw-semibold">Flats (Total)</div>
                                            <div class="fs-3 fw-bold text-dark" id="summary-flats-count">0</div>
                                        </div>
                                        <div class="p-3 bg-white rounded-3 border text-center flex-fill">
                                            <div class="text-muted small fw-semibold">Residents</div>
                                            <div class="fs-3 fw-bold text-dark" id="summary-residents-count">0</div>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold mb-2 small text-dark">Wings Breakdown:</h6>
                                    <div id="summary-wings-list" class="d-flex flex-wrap gap-2">
                                        <!-- Populated dynamically by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-4 prev-step-btn" data-current-step="4">
                        <i class="bi bi-arrow-left me-1"></i> Previous
                    </button>
                    <button type="submit" class="btn btn-dark px-5 fw-bold" id="submit-wizard-btn">
                        Create Society
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentStep = 1;
        const totalSteps = 4;
        let parsedResidents = [];

        const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        // Initialize with 1 empty wing card
        addWingCard();

        // Plus Card click to add a new Wing
        document.getElementById('btn-add-wing-card').addEventListener('click', () => {
            addWingCard();
        });

        function addWingCard() {
            const grid = document.getElementById('wings-config-grid');
            const wrapper = document.getElementById('add-wing-card-wrapper');

            // Find current number of wings to set a helpful placeholder name
            const currentWings = document.querySelectorAll('.wing-config-card').length;
            const letter = alphabet[currentWings] || `${currentWings + 1}`;
            const placeholderName = `Wing ${letter}`;

            const cardHtml = `
                <div class="col-md-6 wing-config-card-wrapper">
                    <div class="card wing-config-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark small">New Wing</h6>
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 border-0 btn-remove-wing" title="Remove Wing">
                                <i class="bi bi-trash3 fs-6"></i>
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 mb-1">
                                <label class="form-label small fw-semibold mb-1">Wing Name</label>
                                <input type="text" class="form-control form-control-sm wing-name-input" placeholder="e.g. ${placeholderName}" required>
                                <div class="invalid-feedback">Wing name is required.</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Total Floors</label>
                                <input type="number" class="form-control form-control-sm wing-floors-input" placeholder="e.g. 5" min="1" max="200" required>
                                <div class="invalid-feedback">Must be 1-200.</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Flats/Floor</label>
                                <input type="number" class="form-control form-control-sm wing-flats-input" placeholder="e.g. 4" min="1" max="50" required>
                                <div class="invalid-feedback">Must be 1-50.</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Insert new card before the dashed Add Wing card
            wrapper.insertAdjacentHTML('beforebegin', cardHtml);
        }

        // Handle wing removal click (delegated listener)
        document.getElementById('wings-config-grid').addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.btn-remove-wing');
            if (removeBtn) {
                // Ensure at least 1 wing card remains
                const totalCards = document.querySelectorAll('.wing-config-card').length;
                if (totalCards <= 1) {
                    Toast.fire({
                        icon: 'warning',
                        title: 'A society must have at least one wing.'
                    });
                    return;
                }
                const cardWrapper = removeBtn.closest('.wing-config-card-wrapper');
                if (cardWrapper) {
                    cardWrapper.remove();
                }
            }
        });

        // Drag and drop CSV upload
        const uploadZone = document.getElementById('csv-upload-zone');
        const fileInput = document.getElementById('csv-file-input');
        const fileBadge = document.getElementById('file-name-badge');
        const clearContainer = document.getElementById('clear-csv-container');

        uploadZone.addEventListener('click', () => fileInput.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleCSVFile(fileInput.files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                handleCSVFile(fileInput.files[0]);
            }
        });

        document.getElementById('btn-clear-csv').addEventListener('click', () => {
            clearCSVUpload();
        });

        function clearCSVUpload() {
            fileInput.value = '';
            parsedResidents = [];
            fileBadge.style.display = 'none';
            fileBadge.innerText = '';
            clearContainer.style.display = 'none';
            document.getElementById('csv-preview-container').style.display = 'none';
            document.getElementById('csv-preview-table').querySelector('tbody').innerHTML = '';
            Toast.fire({
                icon: 'info',
                title: 'CSV cleared.'
            });
        }

        function handleCSVFile(file) {
            if (!file.name.endsWith('.csv')) {
                Toast.fire({
                    icon: 'error',
                    title: 'Please upload a valid CSV file.'
                });
                return;
            }

            fileBadge.innerText = file.name;
            fileBadge.style.display = 'inline-block';
            clearContainer.style.display = 'block';

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    let text = e.target.result;
                    parsedResidents = parseCSV(text);
                    if (parsedResidents.length === 0) {
                        Toast.fire({
                            icon: 'warning',
                            title: 'The uploaded CSV file is empty.'
                        });
                        clearCSVUpload();
                        return;
                    }
                    
                    renderCSVPreview(parsedResidents);
                    Toast.fire({
                        icon: 'success',
                        title: `Successfully parsed ${parsedResidents.length} residents!`
                    });
                } catch (err) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error reading CSV file.'
                    });
                    clearCSVUpload();
                }
            };
            reader.readAsText(file);
        }

        function parseCSV(text) {
            let lines = text.split(/\r?\n/);
            let result = [];
            if (lines.length < 2) return result;
            
            // Extract & sanitize headers
            let headers = lines[0].split(',').map(h => h.trim().toLowerCase().replace(/[\x00-\x1F\x80-\xFF]/g, ''));
            
            for (let i = 1; i < lines.length; i++) {
                let line = lines[i].trim();
                if (!line) continue;
                
                let cells = [];
                let currentCell = '';
                let inQuotes = false;
                
                for (let j = 0; j < line.length; j++) {
                    let char = line[j];
                    if (char === '"') {
                        inQuotes = !inQuotes;
                    } else if (char === ',' && !inQuotes) {
                        cells.push(currentCell.trim());
                        currentCell = '';
                    } else {
                        currentCell += char;
                    }
                }
                cells.push(currentCell.trim());
                
                let obj = {};
                headers.forEach((h, index) => {
                    obj[h] = cells[index] || '';
                });
                result.push(obj);
            }
            return result;
        }

        function renderCSVPreview(rows) {
            const tbody = document.getElementById('csv-preview-table').querySelector('tbody');
            tbody.innerHTML = '';

            rows.slice(0, 50).forEach(row => {
                const tr = `
                    <tr>
                        <td class="fw-semibold text-dark">${escapeHtml(row.name)}</td>
                        <td>${escapeHtml(row.email)}</td>
                        <td>${escapeHtml(row.phone)}</td>
                        <td>${escapeHtml(row.wing)}</td>
                        <td>${escapeHtml(row.flat_number)}</td>
                        <td><span class="badge bg-light text-dark border">${escapeHtml(row.resident_type)}</span></td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', tr);
            });

            if (rows.length > 50) {
                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td colspan="6" class="text-center text-muted small py-2 bg-light">...and ${rows.length - 50} more records.</td>
                    </tr>
                `);
            }

            document.getElementById('csv-record-count').innerText = rows.length;
            document.getElementById('csv-preview-container').style.display = 'block';
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // CSV template downloader
        document.getElementById('btn-download-template').addEventListener('click', (e) => {
            e.preventDefault();
            let csvContent = "data:text/csv;charset=utf-8,name,email,phone,wing,flat_number,resident_type\nJohn Doe,john@example.com,9876543210,Wing A,101,owner\nJane Smith,jane@example.com,9988776655,Wing A,102,tenant\n";
            let encodedUri = encodeURI(csvContent);
            let link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "resident_bulk_import_template.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Navigation controls
        document.querySelectorAll('.next-step-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                let currentStepNum = parseInt(this.dataset.currentStep);
                
                if (validateStep(currentStepNum)) {
                    goToStep(currentStepNum + 1);
                }
            });
        });

        document.querySelectorAll('.prev-step-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                let currentStepNum = parseInt(this.dataset.currentStep);
                goToStep(currentStepNum - 1);
            });
        });

        function validateStep(step) {
            let isValid = true;
            
            if (step === 1) {
                // Validate Basic details
                const fields = ['name', 'pincode', 'address', 'city', 'state'];
                fields.forEach(field => {
                    const el = document.querySelector(`[name="${field}"]`);
                    if (el.value.trim() === '') {
                        el.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        el.classList.remove('is-invalid');
                    }
                });

                // Extra validation for pincode (6-digit Indian pincode)
                const pinEl = document.querySelector('[name="pincode"]');
                const pinPattern = /^[1-9][0-9]{5}$/;
                if (pinEl.value.trim() !== '' && !pinPattern.test(pinEl.value.trim())) {
                    pinEl.classList.add('is-invalid');
                    isValid = false;
                }
            } else if (step === 2) {
                // Validate Wings Setup
                const wingNames = [];
                document.querySelectorAll('.wing-config-card').forEach((card) => {
                    const nameEl = card.querySelector('.wing-name-input');
                    const floorsEl = card.querySelector('.wing-floors-input');
                    const flatsEl = card.querySelector('.wing-flats-input');

                    if (nameEl.value.trim() === '') {
                        nameEl.classList.add('is-invalid');
                        isValid = false;
                    } else if (wingNames.includes(nameEl.value.trim())) {
                        nameEl.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        nameEl.classList.remove('is-invalid');
                        wingNames.push(nameEl.value.trim());
                    }

                    const floorsVal = parseInt(floorsEl.value) || 0;
                    if (floorsVal < 1 || floorsVal > 200) {
                        floorsEl.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        floorsEl.classList.remove('is-invalid');
                    }

                    const flatsVal = parseInt(flatsEl.value) || 0;
                    if (flatsVal < 1 || flatsVal > 50) {
                        flatsEl.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        flatsEl.classList.remove('is-invalid');
                    }
                });
            } else if (step === 3) {
                isValid = true;
            }

            if (!isValid) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Please fix all validation errors before proceeding.'
                });
            }

            return isValid;
        }

        function goToStep(step) {
            // Hide current panel, show next
            document.querySelectorAll('.wizard-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(`step-${step}-panel`).classList.add('active');

            // Update step nav labels (simple black & white styles)
            document.querySelectorAll('.step-nav-label').forEach(label => {
                let lStep = parseInt(label.dataset.step);
                if (lStep === step) {
                    label.classList.add('active');
                } else {
                    label.classList.remove('active');
                }
            });

            // Update display values
            document.getElementById('current-step-display').innerText = step;
            
            let title = "Basic Details";
            if (step === 2) title = "Wings Setup";
            else if (step === 3) title = "Residents";
            else if (step === 4) title = "Summary";
            document.getElementById('current-step-title-display').innerText = title;

            currentStep = step;

            // If we are moving to step 4, generate the summary values
            if (step === 4) {
                updateSummary();
            }
        }

        function updateSummary() {
            document.getElementById('summary-name').innerText = document.querySelector('[name="name"]').value;
            document.getElementById('summary-pincode').innerText = document.querySelector('[name="pincode"]').value;
            document.getElementById('summary-address').innerText = document.querySelector('[name="address"]').value;
            document.getElementById('summary-city').innerText = document.querySelector('[name="city"]').value;
            document.getElementById('summary-state').innerText = document.querySelector('[name="state"]').value;

            // Wings breakdown
            const wingsListEl = document.getElementById('summary-wings-list');
            wingsListEl.innerHTML = '';

            let totalFlats = 0;
            let wingsCount = 0;

            document.querySelectorAll('.wing-config-card').forEach((card) => {
                const name = card.querySelector('.wing-name-input').value;
                const floors = parseInt(card.querySelector('.wing-floors-input').value) || 0;
                const flats = parseInt(card.querySelector('.wing-flats-input').value) || 0;
                
                const wingFlatsCount = floors * flats;
                totalFlats += wingFlatsCount;
                wingsCount++;

                const pill = `<span class="badge bg-light text-dark border rounded-3 py-1.5 px-3 mb-1 fw-semibold">${escapeHtml(name)} (${floors} floors, ${flats} flats/floor = ${wingFlatsCount} flats)</span>`;
                wingsListEl.insertAdjacentHTML('beforeend', pill);
            });

            document.getElementById('summary-wings-count').innerText = wingsCount;
            document.getElementById('summary-flats-count').innerText = totalFlats;
            document.getElementById('summary-residents-count').innerText = parsedResidents.length;
        }

        // Handle AJAX form submission
        document.getElementById('society-wizard-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Collect wings data
            let wings = [];
            document.querySelectorAll('.wing-config-card').forEach((card) => {
                wings.push({
                    name: card.querySelector('.wing-name-input').value,
                    total_floors: parseInt(card.querySelector('.wing-floors-input').value) || 0,
                    flats_per_floor: parseInt(card.querySelector('.wing-flats-input').value) || 0
                });
            });

            let formData = {
                _token: document.querySelector('input[name="_token"]').value,
                name: document.querySelector('[name="name"]').value,
                pincode: document.querySelector('[name="pincode"]').value,
                address: document.querySelector('[name="address"]').value,
                city: document.querySelector('[name="city"]').value,
                state: document.querySelector('[name="state"]').value,
                wings: wings,
                residents: parsedResidents
            };

            const submitBtn = document.getElementById('submit-wizard-btn');
            const originalBtnHtml = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating Society...`;

            // Clear any old validation styles
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback-server').forEach(el => el.remove());

            // Submit JSON payload
            fetch(this.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else if (response.status === 422) {
                    return response.json().then(errData => {
                        throw errData;
                    });
                } else {
                    throw new Error('Something went wrong on the server.');
                }
            })
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                }
            })
            .catch(errorsObj => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;

                if (errorsObj && errorsObj.errors) {
                    displayValidationErrors(errorsObj.errors);
                    Toast.fire({
                        icon: 'error',
                        title: 'Validation failed. Please check the highlighted steps.'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: errorsObj.message || 'An error occurred during submission.'
                    });
                }
            });
        });

        function displayValidationErrors(errors) {
            let firstErrorStep = null;

            for (const key in errors) {
                let errorStep = 1;
                
                if (key.startsWith('wings.')) {
                    errorStep = 2;
                    let parts = key.split('.');
                    let wingIndex = parseInt(parts[1]);
                    let fieldName = parts[2];

                    let cards = document.querySelectorAll('.wing-config-card');
                    if (cards[wingIndex]) {
                        let input;
                        if (fieldName === 'name') input = cards[wingIndex].querySelector('.wing-name-input');
                        else if (fieldName === 'total_floors') input = cards[wingIndex].querySelector('.wing-floors-input');
                        else if (fieldName === 'flats_per_floor') input = cards[wingIndex].querySelector('.wing-flats-input');

                        if (input) {
                            input.classList.add('is-invalid');
                            input.insertAdjacentHTML('afterend', `<div class="invalid-feedback invalid-feedback-server" style="display:block;">${errors[key][0]}</div>`);
                        }
                    }
                } else if (key.startsWith('residents.')) {
                    errorStep = 3;
                    Toast.fire({
                        icon: 'error',
                        title: `Resident validation error: ${errors[key][0]}`
                    });
                } else {
                    errorStep = 1;
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        input.insertAdjacentHTML('afterend', `<div class="invalid-feedback invalid-feedback-server" style="display:block;">${errors[key][0]}</div>`);
                    }
                }

                if (firstErrorStep === null || errorStep < firstErrorStep) {
                    firstErrorStep = errorStep;
                }
            }

            if (firstErrorStep !== null) {
                goToStep(firstErrorStep);
            }
        }
    });
</script>
@endsection
