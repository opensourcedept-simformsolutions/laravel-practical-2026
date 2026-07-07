@extends('layouts.app')

@section('title', 'Flats')

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
                        <select id="society_filter" name="society_id" class="form-select form-select-sm">
                            <option value="">Select Society</option>
                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}"
                                    {{ old('society_id') == $society->id ? 'selected' : '' }}>
                                    {{ $society->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Wing</label>
                    <select id="wing_filter" class="form-select form-select-sm">
                        <option value="">All Wings</option>
                        @if (isset($wings))
                            @foreach ($wings as $wing)
                                <option value="{{ $wing->id }}" data-total-floors="{{ $wing->total_floors }}">{{ $wing->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Floor</label>
                    <select id="floor_filter" class="form-select form-select-sm">
                        <option value="">All Floors</option>
                        @if (isset($floors))
                            @foreach ($floors as $floor)
                                <option value="{{ $floor }}">{{ $floor }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                @can('is-admin')
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Status</label>
                        <select id="status-filter" class="form-select form-select-sm">
                            <option value="active">Active Flats</option>
                            <option value="deleted">Deleted Flats</option>
                            <option value="all">All Flats</option>
                        </select>
                    </div>
                @endcan
            </div>
        </div>
    </div>
    @endif

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Flat List</h5>

            @canany(['is-gatekeeper', 'is-admin'])
                <a href="{{ route('flats.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i>
                    Create Flat
                </a>
            @endcanany
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="flatsTable" class="table table-hover table-striped align-middle w-100 app-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Wing</th>
                            <th>Floor</th>
                            <th>Flat Number</th>
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
        table = $('#flatsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('flats.index') }}",
                data: function(d) {
                    d.society_id = $('#society_filter').val();
                    d.filter = $('#status-filter').val();
                    d.wing_id = $('#wing_filter').val();
                    d.floor = $('#floor_filter').val();
                }
            },
            layout: {
                topStart: {
                    buttons: [{
                        text: 'CSV',
                        action: function (e, dt) {

                            let search = dt.search();
                            let society = $('#society_filter').val();

                            let url = "{{ route('flats.export') }}";

                            url += '?search=' + encodeURIComponent(search);

                            if (society) {
                                url += '&society_id=' + society;
                            }

                            window.location = url;
                        }
                    }]
                },
                topEnd: {
                    search: true,
                    pageLength: true
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'society',
                    name: 'societies.name',
                    visible: @json(auth()->user()->isSuperAdmin())
                },
                {
                    data: 'wing',
                    name: 'wings.name',
                    searchable: true
                },
                {
                    data: 'floor',
                    name: 'floor'
                },
                {
                    data: 'flat_number',
                    name: 'flat_number'
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        const defaultFloors = @json($floors);

        $('#society_filter, #wing_filter, #floor_filter, #status-filter').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        $(document).on('change', '#status-filter, #society_filter, #wing_filter, #floor_filter', function() {
            rd();
        });

        $('#society_filter').change(function() {
            let societyId = $(this).val();
            let $wingSelect = $('#wing_filter');
            $wingSelect.html('<option value="">All Wings</option>');
            
            let $floorSelect = $('#floor_filter');
            $floorSelect.html('<option value="">All Floors</option>');
            defaultFloors.forEach(floor => {
                $floorSelect.append(`<option value="${floor}">${floor}</option>`);
            });
            $floorSelect.val('').trigger('change.select2');

            if (societyId) {
                $.get(`/societies/${societyId}/wings`, function(wings) {
                    wings.forEach(wing => {
                        $wingSelect.append(`<option value="${wing.id}" data-total-floors="${wing.total_floors}">${wing.name}</option>`);
                    });
                    $wingSelect.trigger('change.select2');
                });
            } else {
                $wingSelect.trigger('change.select2');
            }
        });

        $('#wing_filter').change(function() {
            let totalFloors = $('#wing_filter option:selected').data('total-floors');
            let $floorSelect = $('#floor_filter');
            $floorSelect.html('<option value="">All Floors</option>');
            
            if (totalFloors) {
                for (let i = 1; i <= totalFloors; i++) {
                    $floorSelect.append(`<option value="${i}">${i}</option>`);
                }
            } else {
                defaultFloors.forEach(floor => {
                    $floorSelect.append(`<option value="${floor}">${floor}</option>`);
                });
            }
            $floorSelect.val('').trigger('change.select2');
        });

        $('#btnResetFilters').click(function() {
            $('#society_filter, #wing_filter, #floor_filter').val('').trigger('change.select2');
            $('#status-filter').val('active').trigger('change.select2');
            rd();
        });
    </script>
@endpush
