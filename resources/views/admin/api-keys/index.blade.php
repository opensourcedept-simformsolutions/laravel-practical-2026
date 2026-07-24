@extends('layouts.app')

@section('title', 'API Key Management')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-cpu-fill text-primary"></i> API Key Management
            </h4>
            <p class="text-muted small mb-0">
                Manage, rotate, and monitor secure API Access Keys. Protect third-party integrations with scopes, rate limits, and IP whitelists.
            </p>
        </div>

        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createKeyModal">
                <i class="bi bi-plus-lg"></i> Generate New API Key
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Keys</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1" id="stat-total-keys">-</h3>
                    </div>
                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-key fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Active Keys</div>
                        <h3 class="fw-bold text-success mb-0 mt-1" id="stat-active-keys">-</h3>
                    </div>
                    <div class="avatar bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Suspended Keys</div>
                        <h3 class="fw-bold text-warning mb-0 mt-1" id="stat-suspended-keys">-</h3>
                    </div>
                    <div class="avatar bg-warning bg-opacity-10 text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-pause-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Revoked Keys</div>
                        <h3 class="fw-bold text-danger mb-0 mt-1" id="stat-revoked-keys">-</h3>
                    </div>
                    <div class="avatar bg-danger bg-opacity-10 text-danger rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-x fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- API Keys Table --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-list-task text-primary"></i> API Access Credentials
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="apiKeysTable">
                    <thead>
                        <tr>
                            <th>Key Name</th>
                            <th>API Key Token</th>
                            <th>Status</th>
                            <th>Scopes</th>
                            <th>IP Whitelist</th>
                            <th>Usage / Limit</th>
                            <th>Expires At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="apiKeysList">
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Loading access keys...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Create API Key --}}
<div class="modal fade" id="createKeyModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="createKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="createKeyModalLabel">Generate New API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createKeyForm">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="key-name" class="form-label fw-semibold text-dark">Key Name</label>
                        <input type="text" class="form-control" id="key-name" required placeholder="e.g. Production Mobile App">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark d-block">Scopes / Permissions</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="scope-wildcard" value="*" checked>
                            <label class="form-check-label" for="scope-wildcard">All Permitted (*)</label>
                        </div>
                        <div class="mt-2" id="scope-specific-wrapper" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input scope-checkbox" type="checkbox" id="scope-read" value="read">
                                <label class="form-check-label" for="scope-read">Read Access</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input scope-checkbox" type="checkbox" id="scope-write" value="write">
                                <label class="form-check-label" for="scope-write">Write Access</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="key-ip-whitelist" class="form-label fw-semibold text-dark">IP Whitelist (Optional)</label>
                        <input type="text" class="form-control" id="key-ip-whitelist" placeholder="Comma-separated e.g. 192.168.1.1, 10.0.0.1">
                        <div class="form-text text-muted">Leave empty to allow all IP addresses.</div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="key-rate-limit" class="form-label fw-semibold text-dark">Rate Limit (req/min)</label>
                            <input type="number" class="form-control" id="key-rate-limit" value="60" min="0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="key-quota-limit" class="form-label fw-semibold text-dark">Monthly Quota</label>
                            <input type="number" class="form-control" id="key-quota-limit" value="10000" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="key-expires" class="form-label fw-semibold text-dark">Expiration Period (Days)</label>
                        <select class="form-select" id="key-expires">
                            <option value="30" selected>30 Days</option>
                            <option value="90">90 Days</option>
                            <option value="365">1 Year</option>
                            <option value="">Never Expires</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status" id="create-spinner"></span>
                        Generate Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Display Generated Secret --}}
<div class="modal fade" id="displaySecretModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="displaySecretModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-success bg-opacity-10">
                <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2" id="displaySecretModalLabel">
                    <i class="bi bi-shield-check-fill"></i> API Key Generated Successfully
                </h5>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-warning border-opacity-50 text-dark d-flex gap-2 align-items-start shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                    <div>
                        <strong class="d-block mb-1">Save Secret Key Now!</strong>
                        For security reasons, this secret key will not be shown again. If you lose it, you will have to regenerate or rotate the credentials.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark small">API Key (X-API-KEY)</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light font-monospace" id="generated-key" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('generated-key')">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark small">API Secret (X-API-SECRET)</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light font-monospace text-danger fw-bold" id="generated-secret" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('generated-secret')">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-success w-100" data-bs-dismiss="modal">I Have Saved the Secret Key</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Rotate API Key --}}
<div class="modal fade" id="rotateKeyModal" tabindex="-1" aria-labelledby="rotateKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="rotateKeyModalLabel">Rotate API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rotateKeyForm">
                <input type="hidden" id="rotate-key-id">
                <div class="modal-body p-4">
                    <p class="text-muted small">
                        Rotating a key invalidates the existing secret and issues a new key-pair. 
                        You can configure a grace period during which the old key remains valid to allow seamless updates in your clients.
                    </p>

                    <div class="mb-3">
                        <label for="rotate-grace-period" class="form-label fw-semibold text-dark">Grace Period</label>
                        <select class="form-select" id="rotate-grace-period">
                            <option value="15">15 Minutes</option>
                            <option value="60" selected>1 Hour</option>
                            <option value="1440">24 Hours (1 Day)</option>
                        </select>
                        <div class="form-text text-muted">Both old and new keys will function during this grace window.</div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger d-flex align-items-center gap-1">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status" id="rotate-spinner"></span>
                        Rotate Key Credentials
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE_URL = '/api/api-keys';

    $(document).ready(function() {
        loadApiKeys();

        // Wildcard scope toggle logic
        $('#scope-wildcard').change(function() {
            if ($(this).is(':checked')) {
                $('#scope-specific-wrapper').slideUp();
                $('.scope-checkbox').prop('checked', false);
            } else {
                $('#scope-specific-wrapper').slideDown();
            }
        });

        // Store API Key Form Submit
        $('#createKeyForm').submit(function(e) {
            e.preventDefault();
            $('#create-spinner').removeClass('d-none');
            $('button[type="submit"]').prop('disabled', true);

            // Scopes extraction
            let scopes = [];
            if ($('#scope-wildcard').is(':checked')) {
                scopes.push('*');
            } else {
                $('.scope-checkbox:checked').each(function() {
                    scopes.push($(this).val());
                });
            }

            // IP Whitelist parsing
            let ipWhitelist = null;
            let ipRaw = $('#key-ip-whitelist').val().trim();
            if (ipRaw) {
                ipWhitelist = ipRaw.split(',').map(ip => ip.trim());
            }

            let payload = {
                name: $('#key-name').val().trim(),
                scopes: scopes,
                ip_whitelist: ipWhitelist,
                rate_limit_limit: $('#key-rate-limit').val(),
                quota_limit: $('#key-quota-limit').val(),
                expires_in_days: $('#key-expires').val()
            };

            $.ajax({
                url: API_BASE_URL,
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                success: function(response) {
                    $('#create-spinner').addClass('d-none');
                    $('button[type="submit"]').prop('disabled', false);
                    $('#createKeyModal').modal('hide');
                    $('#createKeyForm')[0].reset();
                    $('#scope-wildcard').prop('checked', true).trigger('change');

                    // Display the exposed secret keys
                    $('#generated-key').val(response.data.key);
                    $('#generated-secret').val(response.data.secret);
                    $('#displaySecretModal').modal('show');

                    loadApiKeys();
                },
                error: function(xhr) {
                    $('#create-spinner').addClass('d-none');
                    $('button[type="submit"]').prop('disabled', false);
                    let message = 'Failed to generate API Key.';
                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        });

        // Rotate API Key Form Submit
        $('#rotateKeyForm').submit(function(e) {
            e.preventDefault();
            $('#rotate-spinner').removeClass('d-none');
            $('#rotateKeyForm button[type="submit"]').prop('disabled', true);

            let keyId = $('#rotate-key-id').val();
            let payload = {
                grace_period_minutes: $('#rotate-grace-period').val()
            };

            $.ajax({
                url: `${API_BASE_URL}/${keyId}/rotate`,
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                success: function(response) {
                    $('#rotate-spinner').addClass('d-none');
                    $('#rotateKeyForm button[type="submit"]').prop('disabled', false);
                    $('#rotateKeyModal').modal('hide');

                    // Display new credentials
                    $('#generated-key').val(response.data.key);
                    $('#generated-secret').val(response.data.secret);
                    $('#displaySecretModal').modal('show');

                    loadApiKeys();
                },
                error: function(xhr) {
                    $('#rotate-spinner').addClass('d-none');
                    $('#rotateKeyForm button[type="submit"]').prop('disabled', false);
                    let message = 'Failed to rotate credentials.';
                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        });
    });

    function loadApiKeys() {
        $.ajax({
            url: API_BASE_URL,
            type: 'GET',
            success: function(response) {
                renderApiKeysList(response.data);
                updateStats(response.data);
            },
            error: function() {
                $('#apiKeysList').html(`
                    <tr>
                        <td colspan="8" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i> Failed to load API Keys.
                        </td>
                    </tr>
                `);
            }
        });
    }

    function renderApiKeysList(keys) {
        if (!keys.length) {
            $('#apiKeysList').html(`
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-cpu fs-2 d-block mb-2 opacity-50"></i>
                        No API Keys found. Generate a key to begin.
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        keys.forEach(key => {
            let statusBadge = '';
            if (key.status === 'active') {
                statusBadge = '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1">Active</span>';
            } else if (key.status === 'suspended') {
                statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2.5 py-1">Suspended</span>';
            } else {
                statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1">Revoked</span>';
            }

            let scopesBadges = '';
            if (key.scopes && key.scopes.includes('*')) {
                scopesBadges = '<span class="badge bg-primary bg-opacity-10 text-primary fs-9 rounded-pill">All (*)</span>';
            } else if (key.scopes) {
                scopesBadges = key.scopes.map(s => `<span class="badge bg-secondary bg-opacity-10 text-dark fs-9 rounded-pill me-1">${s}</span>`).join('');
            }

            let ipText = 'All IPs';
            if (key.ip_whitelist && key.ip_whitelist.length) {
                ipText = `<span class="small font-monospace" title="${key.ip_whitelist.join(', ')}">${key.ip_whitelist.length} Whitelisted</span>`;
            }

            let limitText = `<div class="small">Limits: ${key.rate_limit_limit}/min</div><div class="small text-muted">Quota: ${key.quota_used} / ${key.quota_limit || '∞'}</div>`;
            
            let expiryText = key.expires_at ? new Date(key.expires_at).toLocaleDateString() : 'Never';
            if (key.rotation_grace_expires_at) {
                expiryText = `<div class="text-danger small" title="Grace Period ends: ${key.rotation_grace_expires_at}">Expired (Grace Period)</div>`;
            }

            // Actions mapping
            let actionBtn = '';
            if (key.status === 'active') {
                actionBtn += `<button class="dropdown-item" onclick="suspendKey(${key.id})"><i class="bi bi-pause-circle me-2"></i> Suspend</button>`;
                actionBtn += `<button class="dropdown-item" onclick="triggerRotation(${key.id})"><i class="bi bi-arrow-repeat me-2"></i> Rotate Key</button>`;
            } else if (key.status === 'suspended') {
                actionBtn += `<button class="dropdown-item" onclick="activateKey(${key.id})"><i class="bi bi-play-circle me-2"></i> Activate</button>`;
            }

            if (key.status !== 'revoked') {
                actionBtn += `<button class="dropdown-item text-danger" onclick="revokeKey(${key.id})"><i class="bi bi-shield-x me-2"></i> Revoke</button>`;
            }

            actionBtn += `<div class="dropdown-divider"></div>`;
            actionBtn += `<button class="dropdown-item text-danger" onclick="deleteKey(${key.id})"><i class="bi bi-trash me-2"></i> Delete Permanently</button>`;

            html += `
                <tr>
                    <td class="fw-bold text-dark">${escapeHtml(key.name)}</td>
                    <td class="font-monospace text-muted small">${key.key.substring(0, 8)}...${key.key.substring(key.key.length - 8)}</td>
                    <td>${statusBadge}</td>
                    <td>${scopesBadges}</td>
                    <td>${ipText}</td>
                    <td>${limitText}</td>
                    <td>${expiryText}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm shadow-sm border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1">
                                ${actionBtn}
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
        });

        $('#apiKeysList').html(html);
    }

    function updateStats(keys) {
        let active = 0, suspended = 0, revoked = 0;
        keys.forEach(k => {
            if (k.status === 'active') active++;
            else if (k.status === 'suspended') suspended++;
            else if (k.status === 'revoked') revoked++;
        });

        $('#stat-total-keys').text(keys.length);
        $('#stat-active-keys').text(active);
        $('#stat-suspended-keys').text(suspended);
        $('#stat-revoked-keys').text(revoked);
    }

    // Key operations
    function activateKey(id) {
        performAction(id, 'activate', 'Key activated successfully.');
    }

    function suspendKey(id) {
        performAction(id, 'suspend', 'Key suspended successfully.');
    }

    function revokeKey(id) {
        Swal.fire({
            title: 'Revoke API Key?',
            text: 'Revoking this key disables access permanently. You cannot reactivate revoked keys!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Revoke Key',
            confirmButtonColor: '#dc3545'
        }).then(res => {
            if (res.isConfirmed) {
                performAction(id, 'revoke', 'Key credentials revoked.');
            }
        });
    }

    function triggerRotation(id) {
        $('#rotate-key-id').val(id);
        $('#rotateKeyModal').modal('show');
    }

    function deleteKey(id) {
        Swal.fire({
            title: 'Delete API Key?',
            text: 'Are you sure you want to permanently delete this key record? All usage logs will also be deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete Key',
            confirmButtonColor: '#dc3545'
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `${API_BASE_URL}/${id}`,
                    type: 'DELETE',
                    success: function(resp) {
                        Toast.fire({ icon: 'success', title: resp.message });
                        loadApiKeys();
                    },
                    error: function() {
                        Toast.fire({ icon: 'error', title: 'Failed to delete key.' });
                    }
                });
            }
        });
    }

    function performAction(id, action, successText) {
        $.ajax({
            url: `${API_BASE_URL}/${id}/${action}`,
            type: 'POST',
            success: function() {
                Toast.fire({ icon: 'success', title: successText });
                loadApiKeys();
            },
            error: function(xhr) {
                let message = `Failed to ${action} key.`;
                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                Toast.fire({ icon: 'error', title: message });
            }
        });
    }

    function copyToClipboard(elementId) {
        let input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);

        Toast.fire({
            icon: 'success',
            title: 'Copied to clipboard!'
        });
    }

    function escapeHtml(text) {
        return text ? $('<div>').text(text).html() : '';
    }
</script>
@endpush
@endsection
