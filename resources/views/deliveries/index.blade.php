@extends('layouts.app')
 
@section('title', 'Deliveries')
@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Delivery List</h5>
 
            <div class="d-flex gap-2 align-items-center">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isGatekeeper())
                <!-- Filters Dropdown Container -->
                <div class="position-relative">
                    <button type="button" id="filters-toggle-btn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> <span id="filters-btn-text">Filters</span> <i class="bi bi-chevron-down ms-1 collapse-icon"></i>
                    </button>

                    <div id="filters-dropdown-panel" class="card shadow-lg border position-absolute end-0 mt-2 p-3 d-none" style="width: 560px; max-width: 90vw; z-index: 1050;">
                        <div class="row g-3">
                            @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Soft-Delete Status</label>
                                    <select id="status-filter" class="form-select form-select-sm">
                                        <option value="active">Active Deliveries</option>
                                        <option value="deleted">Deleted Deliveries</option>
                                        <option value="all">All Deliveries</option>
                                    </select>
                                </div>
                            @endif

                            @if (auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Society</label>
                                    <select id="society-filter" class="form-select form-select-sm">
                                        <option value="">All Societies</option>
                                        @foreach (\App\Models\Society::orderBy('name')->get() as $soc)
                                            <option value="{{ $soc->id }}">{{ $soc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Delivery Status</label>
                                <select id="delivery-status-filter" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="received">Received</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Vendor</label>
                                <input type="text" id="vendor-filter" class="form-control form-control-sm" placeholder="e.g. Amazon">
                            </div>

                            @if (!auth()->user()->isResident())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Flat</label>
                                    <select id="flat-filter" class="form-select form-select-sm select2-flat">
                                        <option value="">All Flats</option>
                                        @if (isset($flats))
                                            @foreach ($flats as $flat)
                                                <option value="{{ $flat->id }}">{{ $flat->wing }}-{{ $flat->flat_number }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3 border-top pt-3">
                            <button type="button" id="btnResetFilters" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </button>
                            <button type="button" id="apply-filters" class="btn btn-primary btn-sm">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                @canany(['is-gatekeeper', 'is-admin'])
                    <a href="{{ route('deliveries.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-square me-1"></i>
                        Create Delivery
                    </a>
                @endcanany
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="deliveries-table" class='table table-hover table-striped align-middle mb-0'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            @if (Auth()->user()->isSuperAdmin())
                                <th>Society</th>
                            @endif
                            <th>Flat</th>
                            <th>Resident</th>
                            <th>Vendor</th>
                            <th>Package Details</th>
                            <th>Status</th>
                            <th>Received At</th>
                            <th>Delivered At</th>
                            <th class="text-center">Actions</th>
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
            table = $('#deliveries-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
 
                ajax: {
                    url: '{{ route('deliveries.data') }}',
                    data: function(d) {
                        d.filter = $('#status-filter').val();
                        d.delivery_status = $('#delivery-status-filter').val();
                        d.vendor = $('#vendor-filter').val();
                        d.flat_id = $('#flat-filter').val();
                        d.society_id = $('#society-filter').val();
                    }
                },
 
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    @if (Auth()->user()->isSuperAdmin())
                        {
                            data: 'society',
                            name: 'societies.name'
                        },
                    @endif {
                        data: 'flat',
                        name: 'flat'
                    },
                    {
                        data: 'resident',
                        name: 'users.name'
                    },
                    {
                        data: 'vendor',
                        name: 'deliveries.vendor'
                    },
                    {
                        data: 'package_details',
                        name: 'deliveries.package_details'
                    },
                    {
                        data: 'status',
                        name: 'deliveries.status'
                    },
                    {
                        data: 'received_at',
                        name: 'deliveries.received_at'
                    },
                    {
                        data: 'delivered_at',
                        name: 'deliveries.delivered_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [{{ auth()->user()->isSuperAdmin() ? 7 : 6 }}, 'desc']
                ]
            });

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
                rd();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $(document).on('change', '#society-filter', function() {
                let societyId = $(this).val();
                if (!societyId) {
                    $('#flat-filter').html('<option value="">All Flats</option>').trigger('change');
                    return;
                }

                $.ajax({
                    url: "/societies/" + societyId + "/flats",
                    type: 'GET',
                    success: function(response) {
                        if (!response.success) return;
                        let options = '<option value="">All Flats</option>';
                        response.data.forEach(function(flat) {
                            options += `<option value="${flat.id}">${flat.wing}-${flat.flat_number}</option>`;
                        });
                        $('#flat-filter').html(options).trigger('change');
                    },
                    error: function() {
                        $('#flat-filter').html('<option value="">Error loading flats</option>').trigger('change');
                    }
                });
            });

            $('#btnResetFilters').click(function() {
                $('#society-filter, #delivery-status-filter, #flat-filter').val('').trigger('change');
                $('#status-filter').val('active').trigger('change');
                $('#vendor-filter').val('');
                rd();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });
        });
    </script>
@endpush
 
 