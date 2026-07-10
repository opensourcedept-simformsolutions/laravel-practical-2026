@extends('layouts.app')

@section('title', 'Flats')

@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Flat List</h5>

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

                            <div class="col-md-6">
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

                            <div class="col-md-6">
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
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Status</label>
                                    <select id="status-filter" class="form-select form-select-sm">
                                        <option value="active">Active Flats</option>
                                        <option value="deleted">Deleted Flats</option>
                                        <option value="all">All Flats</option>
                                    </select>
                                </div>
                            @endcan
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
                    <a href="{{ route('flats.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-square me-1"></i>
                        Create Flat
                    </a>
                @endcanany
            </div>
        </div>

        <div class="card-body">
            <!-- Active Filters Badges -->
            <div id="active-filters-container" class="align-items-center flex-wrap gap-2 mb-3 p-2 bg-light rounded-3"
                style="display: none !important;">
                <span class="text-muted small fw-semibold ms-1">Active Filters:</span>
                <div id="active-filters-list" class="d-flex flex-wrap gap-2 align-items-center"></div>
                <button type="button" id="clear-all-filters"
                    class="btn btn-link btn-sm text-decoration-none p-0 ms-2 fw-semibold text-danger">
                    Clear All
                </button>
            </div>

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
                    buttons: [
                        {
                            text: 'CSV',
                            action: function (e, dt) {
                                let search = dt.search();
                                let society = $('#society_filter').val();
                                let url = "{{ route('flats.export') }}";
                                url += '?search=' + encodeURIComponent(search) + '&format=csv';
                                if (society) {
                                    url += '&society_id=' + society;
                                }
                                window.location = url;
                            }
                        },
                        {
                            text: 'Excel',
                            action: function (e, dt) {
                                let search = dt.search();
                                let society = $('#society_filter').val();
                                let url = "{{ route('flats.export') }}";
                                url += '?search=' + encodeURIComponent(search) + '&format=excel';
                                if (society) {
                                    url += '&society_id=' + society;
                                }
                                window.location = url;
                            }
                        }
                    ],
                    pageLength: true
                },
                topEnd: {
                    search: true
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
            updateFilterBadges();
            $('#filters-dropdown-panel').addClass('d-none');
            $('#filters-toggle-btn').attr('aria-expanded', 'false');
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
                    updateFilterBadges();
                });
            } else {
                $wingSelect.trigger('change.select2');
                updateFilterBadges();
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
            updateFilterBadges();
        });

        $('#floor_filter').change(function() {
            updateFilterBadges();
        });

        $('#status-filter').change(function() {
            updateFilterBadges();
        });

        $('#btnResetFilters').click(function() {
            $('#society_filter, #wing_filter, #floor_filter').val('').trigger('change.select2');
            $('#status-filter').val('active').trigger('change.select2');
            rd();
            updateFilterBadges();
            $('#filters-dropdown-panel').addClass('d-none');
            $('#filters-toggle-btn').attr('aria-expanded', 'false');
        });

        function updateFilterBadges() {
            let list = $('#active-filters-list');
            list.empty();
            let count = 0;

            @if (auth()->user()->isSuperAdmin())
                let societyVal = $('#society_filter').val();
                if (societyVal) {
                    let societyText = $('#society_filter option:selected').text().trim();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Society: ${societyText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="society">&times;</span>
                        </span>
                    `);
                    count++;
                }
            @endif

            let wingVal = $('#wing_filter').val();
            if (wingVal) {
                let wingText = $('#wing_filter option:selected').text().trim();
                list.append(`
                    <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                        Wing: ${wingText}
                        <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="wing">&times;</span>
                    </span>
                `);
                count++;
            }

            let floorVal = $('#floor_filter').val();
            if (floorVal) {
                let floorText = $('#floor_filter option:selected').text().trim();
                list.append(`
                    <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                        Floor: ${floorText}
                        <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="floor">&times;</span>
                    </span>
                `);
                count++;
            }

            @can('is-admin')
                let statusVal = $('#status-filter').val();
                if (statusVal) {
                    let statusText = $('#status-filter option:selected').text().trim();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Status: ${statusText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="status">&times;</span>
                        </span>
                    `);
                    count++;
                }
            @endcan

            if (count > 0) {
                $('#filters-btn-text').text(`Filters (${count})`);
                $('#active-filters-container').attr('style', 'display: flex !important;');
            } else {
                $('#filters-btn-text').text('Filters');
                $('#active-filters-container').attr('style', 'display: none !important;');
            }
        }

        $(document).on('click', '.remove-filter-btn', function() {
            let filterType = $(this).data('filter');
            if (filterType === 'society') {
                $('#society_filter').val('').trigger('change.select2');
            } else if (filterType === 'wing') {
                $('#wing_filter').val('').trigger('change.select2');
            } else if (filterType === 'floor') {
                $('#floor_filter').val('').trigger('change.select2');
            } else if (filterType === 'status') {
                $('#status-filter').val('all').trigger('change.select2');
            }
            rd();
            updateFilterBadges();
        });

        $('#clear-all-filters').on('click', function() {
            $('#society_filter, #wing_filter, #floor_filter').val('').trigger('change.select2');
            $('#status-filter').val('active').trigger('change.select2');
            rd();
            updateFilterBadges();
        });

        // Run initial update for badges
        updateFilterBadges();
    </script>
@endpush
