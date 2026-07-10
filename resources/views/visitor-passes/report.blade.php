@extends('layouts.app')

@section('title', 'Visitor Report')

@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Visitor Report</h5>

            <a id="export-btn" class="btn btn-success btn-sm">
                <i class="bi bi-download"></i>
                Export CSV
            </a>
        </div>

        <div class="card-body border-bottom">
            <h5 class="fw-semibold mb-3">
                <i class="bi bi-funnel me-2"></i>
                Report Filters
            </h5>

            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select id="status-filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="entered">Entered</option>
                        <option value="exited">Exited</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Flat</label>
                    <select id="flat-filter" class="form-select">
                        <option value="">All Flats</option>

                        @foreach ($flats as $flat)
                            <option value="{{ $flat->id }}">
                                {{ $flat->wing }}-{{ $flat->flat_number }}
                            </option>
                        @endforeach
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
            <div class="table-responsive">
                <table id="reportTable" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Society</th>
                            <th>Visitor</th>
                            <th>Phone</th>
                            <th>Flat</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Entry</th>
                            <th>Exit</th>
                            <th>Visit Date</th>
                            <th>Gatekeeper</th>
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
                $(this).val(fromDate + ' - ' + toDate);

                table.draw();
            });

            $('#date-range').on('cancel.daterangepicker', function() {
                fromDate = '';
                toDate = '';
                $(this).val('');

                table.draw();
            });

            table = $('#reportTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                ajax: {
                    url: "{{ route('reports.passes.data') }}",
                    data: function(d) {
                        d.status = $('#status-filter').val();
                        d.flat_id = $('#flat-filter').val();
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
                    {
                        data: 'society',
                        name: 'societies.name'
                    },
                    {
                        data: 'visitor',
                        name: 'visitors.name'
                    },
                    {
                        data: 'phone',
                        name: 'visitors.phone'
                    },
                    {
                        data: 'flat',
                        name: 'flat'
                    },
                    {
                        data: 'purpose',
                        name: 'visitor_logs.purpose'
                    },
                    {
                        data: 'status',
                        name: 'visitor_logs.status'
                    },
                    {
                        data: 'entry_time',
                        name: 'visitor_logs.entry_time'
                    },
                    {
                        data: 'exit_time',
                        name: 'visitor_logs.exit_time'
                    },
                    {
                        data: 'visit_date',
                        name: 'visitor_logs.visit_date'
                    },
                    {
                        data: 'gatekeeper',
                        name: 'gatekeepers.name'
                    }
                ]
            });

            $('#status-filter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Status',
                allowClear: true,
                width: '100%'
            });

            $('#flat-filter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Flat',
                allowClear: true,
                width: '100%'
            });

            $('#status-filter, #flat-filter').on('change', function() {
                table.draw();
            });

            $('#reset-filters').on('click', function() {
                $('#status-filter').val(null).trigger('change');
                $('#flat-filter').val(null).trigger('change');

                $('#date-range').val('');
                fromDate = '';
                toDate = '';

                table.draw();
            });

            $('#export-btn').on('click', function() {
                let params = table.ajax.params();

                params.status = $('#status-filter').val();
                params.flat_id = $('#flat-filter').val();
                params.from_date = fromDate;
                params.to_date = toDate;

                window.location =
                    "{{ route('reports.passes.export') }}?" + $.param(params);
            });

        });
    </script>
@endpush
