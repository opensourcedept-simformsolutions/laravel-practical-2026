@extends('layouts.app')

@section('title', 'Complaints')

@section('content')
    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Complaints Report</h5>

            <div class="d-flex gap-2">

                <a id="export-btn" class="btn btn-success btn-sm">
                    <i class="bi bi-download"></i>
                    Export CSV
                </a>

            </div>
        </div>

        <div class="card-body border-bottom">
            <h5 class="fw-semibold mb-3">
                <i class="bi bi-funnel me-2"></i>
                Report Filters
            </h5>

            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Category</label>

                    <select id="category-filter" class="form-select">
                        <option value="">All Categories</option>
                        <option value="security">Security</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="water">Water</option>
                        <option value="parking">Parking</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>

                    <select id="status-filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date Range</label>

                    <input type="text" id="date-range" class="form-control" placeholder="Select Date Range" readonly>
                </div>

            </div>

            <div class="col-md-12 pt-3 text-end">
                <button id="reset-filters" class="btn btn-secondary px-4">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    Reset Filters
                </button>
            </div>
        </div>

        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table id="complaintsTable" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            @if (auth()->user()->isSuperAdmin())
                                <th>Society</th>
                            @endif
                            <th>User</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Admin Notes</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            let fromDate = '';
            let toDate = '';

            $('#date-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                fromDate = picker.startDate.format('YYYY-MM-DD');
                toDate = picker.endDate.format('YYYY-MM-DD');
                $(this).val(
                    fromDate + ' - ' + toDate
                );

                table.draw();
            });

            $('#date-range').on('cancel.daterangepicker', function() {
                fromDate = '';
                toDate = '';
                $(this).val('');

                table.draw();
            });

            table = $('#complaintsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                ajax: {
                    url: "{{ route('reports.complaints.data') }}",
                    data: function(d) {
                        d.category = $('#category-filter').val();
                        d.status = $('#status-filter').val();
                        d.from_date = fromDate;
                        d.to_date = toDate;
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    @if (auth()->user()->isSuperAdmin())
                        {
                            data: 'society',
                            name: 'society',
                            orderable: false,
                            searchable: false
                        },
                    @endif {
                        data: 'user_name',
                        name: 'user.name'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'admin_notes',
                        name: 'admin_notes'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }
                ],

                drawCallback: function () {
                    $('[data-bs-toggle="tooltip"]').each(function () {
                        new bootstrap.Tooltip(this);
                    });
                }

            });

            $('#category-filter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Category',
                allowClear: true,
                width: '100%'
            });

            $('#status-filter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Status',
                allowClear: true,
                width: '100%'
            });

            $('#category-filter, #status-filter').on('change', function() {
                table.draw();
            });

            $('#reset-filters').on('click', function() {
                $('#category-filter').val(null).trigger('change');
                $('#status-filter').val(null).trigger('change');
                $('#date-range').val('');
                fromDate = '';
                toDate = '';

                table.draw();
            });

            $('#export-btn').click(function() {

                let params = $.param({
                    category: $('#category-filter').val(),
                    status: $('#status-filter').val(),
                    from_date: fromDate,
                    to_date: toDate
                });

                window.location =
                    "{{ route('reports.complaints.export') }}?" + params;
            });

        });
    </script>
@endpush
