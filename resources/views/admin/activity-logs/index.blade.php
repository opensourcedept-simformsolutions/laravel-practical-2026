@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Activity Logs</h5>

            <div class="d-flex gap-2 align-items-center">
                <!-- Filters Dropdown Container -->
                <div class="position-relative">
                    <button type="button" id="filters-toggle-btn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> <span id="filters-btn-text">Filters</span> <i class="bi bi-chevron-down ms-1 collapse-icon"></i>
                    </button>

                    <div id="filters-dropdown-panel" class="card shadow-lg border position-absolute end-0 mt-2 p-3 d-none" style="width: 560px; max-width: 90vw; z-index: 1050;">
                        <div class="row g-3">
                            @if (auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Society</label>
                                    <select id="society_filter" class="form-select">
                                        <option value="">All Societies</option>
                                        @foreach ($societies as $society)
                                            <option value="{{ $society->id }}">{{ $society->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Action</label>
                                    <select id="action_filter" class="form-select">
                                        <option value="">All Actions</option>
                                        <option value="create">Create</option>
                                        <option value="update">Update</option>
                                        <option value="delete">Delete</option>
                                        <option value="login">Login</option>
                                        <option value="logout">Logout</option>
                                        <option value="impersonate_start">Impersonation Start</option>
                                        <option value="impersonate_stop">Impersonation Stop</option>
                                        <option value="mark_entry">Visitor Entry</option>
                                        <option value="mark_exit">Visitor Exit</option>
                                        <option value="deliver">Package Delivered</option>
                                        <option value="cancel">Pass Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Date Range</label>
                                    <input type="text" id="date_range" class="form-control bg-white" placeholder="Select Date Range" readonly style="cursor: pointer;">
                                </div>
                            @else
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Action</label>
                                    <select id="action_filter" class="form-select">
                                        <option value="">All Actions</option>
                                        <option value="create">Create</option>
                                        <option value="update">Update</option>
                                        <option value="delete">Delete</option>
                                        <option value="login">Login</option>
                                        <option value="logout">Logout</option>
                                        <option value="impersonate_start">Impersonation Start</option>
                                        <option value="impersonate_stop">Impersonation Stop</option>
                                        <option value="mark_entry">Visitor Entry</option>
                                        <option value="mark_exit">Visitor Exit</option>
                                        <option value="deliver">Package Delivered</option>
                                        <option value="cancel">Pass Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Date Range</label>
                                    <input type="text" id="date_range" class="form-control bg-white" placeholder="Select Date Range" readonly style="cursor: pointer;">
                                </div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3 border-top pt-3">
                            <button type="button" id="reset_filters" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </button>
                            <button type="button" id="apply-filters" class="btn btn-primary btn-sm">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="activityLogsTable" class="table table-hover table-striped align-middle w-100 app-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            @if(auth()->user()->isSuperAdmin())
                                <th>Society</th>
                            @endif
                            <th>Operator</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th class="text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark" id="detailsModalLabel">
                        <i class="bi bi-info-circle text-primary me-2"></i> Activity Properties
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">Below are the raw data changes and parameters captured for this activity:</p>
                    <div class="bg-dark text-light p-3 rounded-3 overflow-auto" style="max-height: 400px;">
                        <pre class="mb-0 text-success" id="propertiesContent" style="font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size: 13px;"></pre>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const isSuperAdmin = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};

            let fromDate = '';
            let toDate = '';

            const columns = [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'activity_logs.created_at' }
            ];

            if (isSuperAdmin) {
                columns.push({ data: 'society_name', name: 'societies.name', orderable: true });
            }

            columns.push(
                { data: 'operator', name: 'users.name' },
                { data: 'action', name: 'activity_logs.action' },
                { data: 'description', name: 'activity_logs.description' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, class: 'text-center' }
            );

            const table = $('#activityLogsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.activity-logs.data') }}",
                    data: function(d) {
                        d.society_id = $('#society_filter').val();
                        d.action = $('#action_filter').val();
                        d.from_date = fromDate;
                        d.to_date = toDate;
                    }
                },
                columns: columns,
                order: [[1, 'desc']],
                layout: {
                    topStart: null,
                    topEnd: {
                        search: true,
                        pageLength: true
                    }
                }
            });

            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                fromDate = picker.startDate.format('YYYY-MM-DD');
                toDate = picker.endDate.format('YYYY-MM-DD');
                $(this).val(fromDate + ' - ' + toDate);
            });

            $('#date_range').on('cancel.daterangepicker', function() {
                fromDate = '';
                toDate = '';
                $(this).val('');
            });

            $('#action_filter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Action',
                allowClear: true,
                width: '100%'
            });

            if (isSuperAdmin) {
                $('#society_filter').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select Society',
                    allowClear: true,
                    width: '100%'
                });
            }

            // Toggle custom floating filter panel
            $('#filters-toggle-btn').click(function(e) {
                e.stopPropagation();
                $('#filters-dropdown-panel').toggleClass('d-none');
                $(this).attr('aria-expanded', !$('#filters-dropdown-panel').hasClass('d-none'));
            });

            // Close floating filter panel when clicking outside, excluding Select2 and Daterangepicker elements
            $(document).click(function(e) {
                let panel = $('#filters-dropdown-panel');
                let toggleBtn = $('#filters-toggle-btn');

                if (!panel.is(e.target) && panel.has(e.target).length === 0 &&
                    !toggleBtn.is(e.target) && toggleBtn.has(e.target).length === 0 &&
                    $(e.target).closest('.select2-container').length === 0 &&
                    $(e.target).closest('.daterangepicker').length === 0) {
                    panel.addClass('d-none');
                    toggleBtn.attr('aria-expanded', 'false');
                }
            });

            $('#apply-filters').click(function() {
                table.draw();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $('#reset_filters').on('click', function() {
                if (isSuperAdmin) {
                    $('#society_filter').val(null).trigger('change');
                }
                $('#action_filter').val(null).trigger('change');
                $('#date_range').val('');
                fromDate = '';
                toDate = '';
                table.draw();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $(document).on('click', '.view-properties-btn', function() {
                const rawProps = $(this).attr('data-properties');
                try {
                    const parsedProps = JSON.parse(rawProps);
                    const formattedJson = JSON.stringify(parsedProps, null, 4);
                    $('#propertiesContent').text(formattedJson);

                    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    modal.show();
                } catch (e) {
                    $('#propertiesContent').text(rawProps);
                }
            });
        });
    </script>
@endpush
