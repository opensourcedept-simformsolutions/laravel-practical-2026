@extends('layouts.app')

@section('title', 'Residents')

@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Resident List</h5>

            <div class="d-flex gap-2 align-items-center">
                @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
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
                                    <select id="society_filter" class="form-select form-select-sm">
                                        <option value="">All Societies</option>
                                        @foreach ($societies as $society)
                                            <option value="{{ $society->id }}">{{ $society->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Wing</label>
                                    <select id="wing_filter" class="form-select form-select-sm">
                                        <option value="">All Wings</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Flat</label>
                                    <select id="flat-filter" class="form-select form-select-sm">
                                        <option value="">All Flats</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Resident Type</label>
                                    <select id="type_filter" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="owner">Owner</option>
                                        <option value="tenant">Tenant</option>
                                    </select>
                                </div>
                            @elseif (auth()->user()->isAdmin())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Status</label>
                                    <select id="status-filter" class="form-select form-select-sm">
                                        <option value="active">Active Resident</option>
                                        <option value="deleted">Deleted Resident</option>
                                        <option value="all">All Resident</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Wing</label>
                                    <select id="wing_filter" class="form-select form-select-sm">
                                        <option value="">All Wings</option>
                                        @foreach ($wings as $wing)
                                            <option value="{{ $wing->id }}">{{ $wing->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Flat</label>
                                    <select id="flat-filter" class="form-select form-select-sm select2-flat">
                                        <option value="">All Flats</option>
                                        @foreach ($flats as $flat)
                                            <option value="{{ $flat->id }}">{{ $flat->wing }}-{{ $flat->flat_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Resident Type</label>
                                    <select id="type_filter" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="owner">Owner</option>
                                        <option value="tenant">Tenant</option>
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
                    <a href="{{ route('residents.import.form') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i>
                        Bulk Upload
                    </a>
                    <a href="{{ route('residents.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-square me-1"></i>
                        Create Resident
                    </a>
                @endcanany
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="residentsTable" class="table table-hover table-striped align-middle w-100">

                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Flat</th>
                            <th>Wing</th>
                            <th>Type</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                </table>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        table = $('#residentsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,

            ajax: {
                url: "{{ route('residents.index') }}",
                data: function(d) {
                    d.society_id = $('#society_filter').val();
                    d.filter = $('#status-filter').val();
                    d.flat_id = $('#flat-filter').val();
                    d.resident_type = $('#type_filter').val();
                    d.wing_id = $('#wing_filter').val();
                }
            },

            layout: {
                topStart: {
                    buttons: ['csv', 'excel'],
                    pageLength: true
                },
                topEnd: {
                    search: true
                }
            },

            columns: [{
                    data: 'id',
                    name: 'users.id'
                },
                {
                    data: 'name',
                    name: 'users.name'
                },
                {
                    data: 'email',
                    name: 'users.email'
                },
                {
                    data: 'phone',
                    name: 'users.phone'
                },
                {
                    data: 'flat',
                    name: 'flats.flat_number'
                },
                {
                    data: 'wing',
                    name: 'wings.name'
                },
                {
                    data: 'type',
                    name: 'resident_type',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
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

        function updateFlats(shouldDraw = false) {
            let wingId = $('#wing_filter').val();
            let societyId = $('#society_filter').length ? $('#society_filter').val() : '{{ auth()->user()->society_id }}';

            if (wingId) {
                $.ajax({
                    url: `/wings/${wingId}/flats`,
                    type: 'GET',
                    success: function(response) {
                        if (!response.success) return;
                        let options = '<option value="">All Flats</option>';
                        response.data.forEach(function(flat) {
                            options += `<option value="${flat.id}">${flat.wing}-${flat.flat_number}</option>`;
                        });
                        $('#flat-filter').html(options);
                        if (shouldDraw) rd();
                    },
                    error: function() {
                        $('#flat-filter').html('<option value="">All Flats</option>');
                        if (shouldDraw) rd();
                    }
                });
            } else if (societyId) {
                $.ajax({
                    url: `/societies/${societyId}/flats`,
                    type: 'GET',
                    success: function(response) {
                        if (!response.success) return;
                        let options = '<option value="">All Flats</option>';
                        response.data.forEach(function(flat) {
                            options += `<option value="${flat.id}">${flat.wing}-${flat.flat_number}</option>`;
                        });
                        $('#flat-filter').html(options);
                        if (shouldDraw) rd();
                    },
                    error: function() {
                        $('#flat-filter').html('<option value="">All Flats</option>');
                        if (shouldDraw) rd();
                    }
                });
            } else {
                $('#flat-filter').html('<option value="">All Flats</option>');
                if (shouldDraw) rd();
            }
        }

        $(document).on('change', '#wing_filter', function() {
            updateFlats(false);
        });

        $(document).on('change', '#society_filter', function() {
            let societyId = $(this).val();
            let $wingSelect = $('#wing_filter');
            $wingSelect.html('<option value="">All Wings</option>');
            
            if (!societyId) {
                $('#flat-filter').html('<option value="">All Flats</option>');
                return;
            }

            $.get(`/societies/${societyId}/wings`, function(wings) {
                wings.forEach(wing => {
                    $wingSelect.append(`<option value="${wing.id}">${wing.name}</option>`);
                });
            });

            updateFlats(false);
        });

        $('#btnResetFilters').click(function() {
            $('#society_filter, #wing_filter, #flat-filter, #type_filter').val('').trigger('change');
            $('#status-filter').val('active').trigger('change');
            rd();
            $('#filters-dropdown-panel').addClass('d-none');
            $('#filters-toggle-btn').attr('aria-expanded', 'false');
        });
    </script>
@endpush