@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Activity Logs</h5>
        </div>

        <div class="card-body">
            <!-- Filter Section -->
            <div class="row g-3 mb-4">
                @if (auth()->user()->isSuperAdmin())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Society</label>
                        <select id="society_filter" class="form-select">
                            <option value="">All Societies</option>
                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}">{{ $society->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-3">
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

                <div class="col-md-2">
                    <label class="form-label fw-semibold text-secondary small">From Date</label>
                    <input type="date" id="from_date_filter" class="form-select">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold text-secondary small">To Date</label>
                    <input type="date" id="to_date_filter" class="form-select">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="reset_filters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Table Section -->
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

    <!-- Properties Details Modal -->
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

            const columns = [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' }
            ];

            if (isSuperAdmin) {
                columns.push({ data: 'society_name', name: 'society.name', orderable: false });
            }

            columns.push(
                { data: 'operator', name: 'user.name' },
                { data: 'action', name: 'action' },
                { data: 'description', name: 'description' },
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
                        d.from_date = $('#from_date_filter').val();
                        d.to_date = $('#to_date_filter').val();
                    }
                },
                columns: columns,
                order: [[1, 'desc']], // Order by Timestamp desc by default
                layout: {
                    topStart: null,
                    topEnd: {
                        search: true,
                        pageLength: true
                    }
                }
            });

            // Filter triggers
            $('#society_filter, #action_filter, #from_date_filter, #to_date_filter').on('change', function() {
                table.draw();
            });

            // Reset filters
            $('#reset_filters').on('click', function() {
                $('#society_filter').val('');
                $('#action_filter').val('');
                $('#from_date_filter').val('');
                $('#to_date_filter').val('');
                table.draw();
            });

            // Show Details Modal Handler
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
