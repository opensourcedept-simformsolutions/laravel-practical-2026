@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Visitor Pass List</h5>

            <div class="d-flex gap-2 align-items-center">
                <!-- Filters Dropdown Container -->
                <div class="position-relative">
                    <button type="button" id="filters-toggle-btn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> <span id="filters-btn-text">Filters</span> <i class="bi bi-chevron-down ms-1 collapse-icon"></i>
                    </button>

                    <div id="filters-dropdown-panel" class="card shadow-lg border position-absolute end-0 mt-2 p-3 d-none" style="width: 560px; max-width: 90vw; z-index: 1050;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Soft-Delete Status</label>
                                <select id="status-filter" class="form-select form-select-sm">
                                    <option value="active">Active Pass</option>
                                    <option value="deleted">Deleted Pass</option>
                                    <option value="all">All Pass</option>
                                </select>
                            </div>

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
                                <label class="form-label fw-semibold text-secondary small">Visit Status</label>
                                <select id="visit-status-filter" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="pending_approval">Pending Approval</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="entered">Entered</option>
                                    <option value="exited">Exited</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Visit Date</label>
                                <input type="date" id="visit-date-filter" class="form-control form-control-sm">
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

                <a href="{{ route('passes.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i> Create Pass
                </a>
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
                <table id="passTable" class="table table-hover table-striped align-middle w-100 app-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Visitor</th>
                            <th>Phone</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Entry Time</th>
                            <th>Exit Time</th>
                            <th>Visit Date</th>
                            <th>Flat</th>
                            <th>Created By</th>
                            <th>Gatekeeper</th>
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
        $(document).ready(function() {
            table = $('#passTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('passes.data') }}",
                    data: function(d) {
                        d.filter = $('#status-filter').val();
                        d.visit_status = $('#visit-status-filter').val();
                        d.visit_date = $('#visit-date-filter').val();
                        d.flat_id = $('#flat-filter').val();
                        d.society_id = $('#society-filter').val();
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'visitor_name',
                        name: 'visitors.name'
                    },
                    {
                        data: 'visitor_phone',
                        name: 'visitors.phone'
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
                        name: 'visitor_logs.entry_time',
                    },
                    {
                        data: 'exit_time',
                        name: 'visitor_logs.exit_time',
                    },
                    {
                        data: 'visit_date',
                        name: 'visitor_logs.visit_date',
                    },
                    {
                        data: 'flat',
                        name: 'visitor_logs.flat_id',
                    },
                    {
                        data: 'creator_name',
                        name: 'creators.name'
                    },
                    {
                        data: 'gatekeeper_name',
                        name: 'gatekeepers.name'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
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
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $(document).on('change', '#society-filter', function() {
                let societyId = $(this).val();
                if (!societyId) {
                    $('#flat-filter').html('<option value="">All Flats</option>').trigger('change');
                    updateFilterBadges();
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
                        updateFilterBadges();
                    },
                    error: function() {
                        $('#flat-filter').html('<option value="">Error loading flats</option>').trigger('change');
                        updateFilterBadges();
                    }
                });
            });

            $('#btnResetFilters').click(function() {
                $('#society-filter, #visit-status-filter, #flat-filter').val('').trigger('change');
                $('#status-filter').val('active').trigger('change');
                $('#visit-date-filter').val('');
                rd();
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            function updateFilterBadges() {
                let list = $('#active-filters-list');
                list.empty();
                let count = 0;

                let softDeleteVal = $('#status-filter').val();
                if (softDeleteVal) {
                    let softDeleteText = $('#status-filter option:selected').text().trim();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Filter: ${softDeleteText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="soft_delete">&times;</span>
                        </span>
                    `);
                    count++;
                }

                @if (auth()->user()->isSuperAdmin())
                    let societyVal = $('#society-filter').val();
                    if (societyVal) {
                        let societyText = $('#society-filter option:selected').text().trim();
                        list.append(`
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                                Society: ${societyText}
                                <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="society">&times;</span>
                            </span>
                        `);
                        count++;
                    }
                @endif

                let visitStatusVal = $('#visit-status-filter').val();
                if (visitStatusVal) {
                    let visitStatusText = $('#visit-status-filter option:selected').text().trim();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Visit Status: ${visitStatusText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="visit_status">&times;</span>
                        </span>
                    `);
                    count++;
                }

                let visitDateVal = $('#visit-date-filter').val();
                if (visitDateVal) {
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Visit Date: ${visitDateVal}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="visit_date">&times;</span>
                        </span>
                    `);
                    count++;
                }

                @if (!auth()->user()->isResident())
                    let flatVal = $('#flat-filter').val();
                    if (flatVal) {
                        let flatText = $('#flat-filter option:selected').text().trim();
                        list.append(`
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                                Flat: ${flatText}
                                <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="flat">&times;</span>
                            </span>
                        `);
                        count++;
                    }
                @endif

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
                if (filterType === 'soft_delete') {
                    $('#status-filter').val('all').trigger('change');
                } else if (filterType === 'society') {
                    $('#society-filter').val('').trigger('change');
                } else if (filterType === 'visit_status') {
                    $('#visit-status-filter').val('').trigger('change');
                } else if (filterType === 'visit_date') {
                    $('#visit-date-filter').val('');
                } else if (filterType === 'flat') {
                    $('#flat-filter').val('').trigger('change');
                }
                rd();
                updateFilterBadges();
            });

            $('#clear-all-filters').on('click', function() {
                $('#society-filter, #visit-status-filter, #flat-filter').val('').trigger('change');
                $('#status-filter').val('active').trigger('change');
                $('#visit-date-filter').val('');
                rd();
                updateFilterBadges();
            });

            // Run initial update for badges
            updateFilterBadges();
        });
    </script>
@endpush
