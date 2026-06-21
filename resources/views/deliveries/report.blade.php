@extends('layouts.app')

@section('title', 'Deliveries')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-bold mb-0">
                Delivery Report
            </h2>
            <a id="export-btn" class="btn btn-success">
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
                    <label class="form-label">Flat</label>
                    <select id="flat-filter" class="form-control">
                        <option value="">All Flats</option>

                        @foreach ($flats as $flat)
                            <option value="{{ $flat->id }}">
                                {{ $flat->wing }} - Floor {{ $flat->floor }} - {{ $flat->flat_number }}
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
                    <label class="form-label">From Date</label>
                    <input type="date" id="from-date" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" id="to-date" class="form-control">
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

            let table = $('#deliveries-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                ajax: {
                    url: '{{ route('reports.deliveries.data') }}',
                    data: function(d) {
                        d.flat_id = $('#flat-filter').val();
                        d.vendor = $('#vendor-filter').val();
                        d.from_date = $('#from-date').val();
                        d.to_date = $('#to-date').val();
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
                        name: 'society'
                    },
                    {
                        data: 'flat',
                        name: 'flat'
                    },
                    {
                        data: 'resident',
                        name: 'resident'
                    },
                    {
                        data: 'vendor',
                        name: 'vendor'
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
            $('#vendor-filter').on('change', function() {
                table.draw();
            });

            $('#from-date, #to-date').change(function() {
                table.draw();
            });

            $('#export-btn').click(function() {

                let params = $.param({
                    flat_id: $('#flat-filter').val(),
                    vendor: $('#vendor-filter').val(),
                    from_date: $('#from-date').val(),
                    to_date: $('#to-date').val()
                });

                window.location =
                    "{{ route('reports.deliveries.export') }}?" + params;
            });

            $('#reset-filters').click(function() {
                $('#flat-filter').val(null).trigger('change');
                $('#vendor-filter').val(null).trigger('change');
                $('#from-date').val('');
                $('#to-date').val('');

                table.draw();
            });
        });
    </script>
@endpush
