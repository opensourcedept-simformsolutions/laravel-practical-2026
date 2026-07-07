@extends('layouts.app')

@section('title', 'Residents')

@section('content')

    @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <!-- Filter Card -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-funnel me-2 text-primary"></i>Filters
            </h6>
            <button type="button" id="btnResetFilters" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if (auth()->user()->isSuperAdmin())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Society</label>
                        <select id="society_filter" class="form-select form-select-sm">
                            <option value="">All Societies</option>
                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}">{{ $society->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Wing</label>
                        <select id="wing_filter" class="form-select form-select-sm">
                            <option value="">All Wings</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Flat</label>
                        <select id="flat-filter" class="form-select form-select-sm">
                            <option value="">All Flats</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Resident Type</label>
                        <select id="type_filter" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="owner">Owner</option>
                            <option value="tenant">Tenant</option>
                        </select>
                    </div>
                @elseif (auth()->user()->isAdmin())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Status</label>
                        <select id="status-filter" class="form-select form-select-sm">
                            <option value="active">Active Resident</option>
                            <option value="deleted">Deleted Resident</option>
                            <option value="all">All Resident</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Wing</label>
                        <select id="wing_filter" class="form-select form-select-sm">
                            <option value="">All Wings</option>
                            @foreach ($wings as $wing)
                                <option value="{{ $wing->id }}">{{ $wing->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Flat</label>
                        <select id="flat-filter" class="form-select form-select-sm select2-flat">
                            <option value="">All Flats</option>
                            @foreach ($flats as $flat)
                                <option value="{{ $flat->id }}">{{ $flat->wing }}-{{ $flat->flat_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Resident Type</label>
                        <select id="type_filter" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="owner">Owner</option>
                            <option value="tenant">Tenant</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Resident List</h5>

            @canany(['is-gatekeeper', 'is-admin'])
                <div class="d-flex gap-2">
                    <a href="{{ route('residents.import.form') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i>
                        Bulk Upload
                    </a>
                    <a href="{{ route('residents.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-square me-1"></i>
                        Create Resident
                    </a>
                </div>
            @endcanany
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
                    buttons: ['csv', 'excel']
                },
                topEnd: {
                    search: true,
                    pageLength: true
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

        function updateFlats() {
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
                        rd();
                    },
                    error: function() {
                        $('#flat-filter').html('<option value="">All Flats</option>');
                        rd();
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
                        rd();
                    },
                    error: function() {
                        $('#flat-filter').html('<option value="">All Flats</option>');
                        rd();
                    }
                });
            } else {
                $('#flat-filter').html('<option value="">All Flats</option>');
                rd();
            }
        }

        $(document).on('change', '#status-filter, #flat-filter, #type_filter', function() {
            rd();
        });

        $(document).on('change', '#wing_filter', function() {
            updateFlats();
        });

        $(document).on('change', '#society_filter', function() {
            let societyId = $(this).val();
            let $wingSelect = $('#wing_filter');
            $wingSelect.html('<option value="">All Wings</option>');
            
            if (!societyId) {
                $('#flat-filter').html('<option value="">All Flats</option>');
                rd();
                return;
            }

            $.get(`/societies/${societyId}/wings`, function(wings) {
                wings.forEach(wing => {
                    $wingSelect.append(`<option value="${wing.id}">${wing.name}</option>`);
                });
            });

            updateFlats();
        });

        $('#btnResetFilters').click(function() {
            $('#society_filter, #wing_filter, #flat-filter, #type_filter').val('').trigger('change');
            $('#status-filter').val('active').trigger('change');
            rd();
        });
    </script>
@endpush