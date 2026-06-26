@extends('layouts.app')

@section('title', 'Deliveries')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Delivery Report</h5>
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

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select id="status-filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="received">Received</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Flat</label>
                    <select id="flat-filter" class="form-control">
                        <option value="">All Flats</option>

                        @foreach ($flats as $flat)
                            <option value="{{ $flat->id }}">
                                {{ $flat->wing }}-{{ $flat->flat_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Vendor</label>

                    <select id="vendor-filter" class="form-select">
                        <option value="">All Vendors</option>

                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor }}">
                                {{ $vendor }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
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
                <table id="deliveries-table" class='table table-hover align-middle mb-0'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Society</th>
                            <th>Flat</th>
                            <th>Resident</th>
                            <th>Vendor</th>
                            <th>Package Details</th>
                            <th>Status</th>
                            <th>Received At</th>
                            <th>Delivered At</th>
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

            table = $('#deliveries-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                ajax: {
                    url: '{{ route('reports.deliveries.data') }}',
                    data: function(d) {
                        d.status = $('#status-filter').val();
                        d.flat_id = $('#flat-filter').val();
                        d.vendor = $('#vendor-filter').val();
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
                        data: 'flat',
                        name: 'flat'
                    },
                    {
                        data: 'resident',
                        name: 'users.name'
                    },
                    {
                        data: 'vendor',
                        name: 'vendor'
                    },
                    {
                        data: 'package_details',
                        name: 'package_details'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'received_at',
                        name: 'received_at'
                    },
                    {
                        data: 'delivered_at',
                        name: 'delivered_at'
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

            $('#vendor-filter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Vendor',
                allowClear: true,
                width: '100%'
            });

            $('#status-filter, #vendor-filter, #flat-filter').on('change', function() {
                table.draw();
            });

            $('#export-btn').click(function() {
                let params = $.param({
                    status: $('#status-filter').val(),
                    flat_id: $('#flat-filter').val(),
                    vendor: $('#vendor-filter').val(),
                    from_date: fromDate,
                    to_date: toDate
                });

                window.location = "{{ route('reports.deliveries.export') }}?" + params;
            });

            $('#reset-filters').click(function() {
                $('#status-filter').val(null).trigger('change');
                $('#flat-filter').val(null).trigger('change');
                $('#vendor-filter').val(null).trigger('change');
                $('#date-range').val('');
                fromDate = '';
                toDate = '';

                table.draw();
            });
        });
    </script>
@endpush
