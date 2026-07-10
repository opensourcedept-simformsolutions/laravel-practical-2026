@extends('layouts.app')

@section('title', 'Visitor Report')

@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Visitor Report</h5>
            <div class="d-flex gap-2 align-items-center">
                <!-- Filters Dropdown Container -->
                <div class="position-relative">
                    <button type="button" id="filters-toggle-btn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> <span id="filters-btn-text">Filters</span> <i class="bi bi-chevron-down ms-1 collapse-icon"></i>
                    </button>

                    <div id="filters-dropdown-panel" class="card shadow-lg border position-absolute end-0 mt-2 p-3 d-none" style="width: 560px; max-width: 90vw; z-index: 1050;">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Status</label>
                                <select id="status-filter" class="form-select">
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

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Flat</label>
                                <select id="flat-filter" class="form-select">
                                    <option value="">All Flats</option>
                                    @foreach ($flats as $flat)
                                        <option value="{{ $flat->id }}">
                                            {{ $flat->wing }}-{{ $flat->flat_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Date Range</label>
                                <input type="text" id="date-range" class="form-control" placeholder="Select Date Range" readonly>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3 border-top pt-3">
                            <button type="button" id="reset-filters" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </button>
                            <button type="button" id="apply-filters" class="btn btn-primary btn-sm">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export CSV Button -->
                <a id="export-btn" class="btn btn-success btn-sm">
                    <i class="bi bi-download"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <div class="card-body border-bottom p-0" style="border: none !important;">
            <!-- Active Filters Badges -->
            <div id="active-filters-container" class="align-items-center flex-wrap gap-2 m-3 p-2 bg-light rounded-3" style="display: none !important;">
                <span class="text-muted small fw-semibold ms-1">Active Filters:</span>
                <div id="active-filters-list" class="d-flex flex-wrap gap-2 align-items-center"></div>
                <button type="button" id="clear-all-filters" class="btn btn-link btn-sm text-decoration-none p-0 ms-2 fw-semibold text-danger">
                    Clear All
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
                $(this).val(fromDate + ' - ' + toDate).trigger('change');
            });

            $('#date-range').on('cancel.daterangepicker', function() {
                fromDate = '';
                toDate = '';
                $(this).val('').trigger('change');
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

            $('#apply-filters').click(function() {
                table.draw();
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $('#reset-filters').on('click', function() {
                $('#status-filter').val(null).trigger('change');
                $('#flat-filter').val(null).trigger('change');
                $('#date-range').val('').trigger('change');
                fromDate = '';
                toDate = '';

                table.draw();
                updateFilterBadges();
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

            function updateFilterBadges() {
                let list = $('#active-filters-list');
                list.empty();
                let count = 0;

                let statusVal = $('#status-filter').val();
                if (statusVal) {
                    let statusText = $('#status-filter option:selected').text();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Status: ${statusText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="status">&times;</span>
                        </span>
                    `);
                    count++;
                }

                let flatVal = $('#flat-filter').val();
                if (flatVal) {
                    let flatText = $('#flat-filter option:selected').text();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Flat: ${flatText.trim()}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="flat">&times;</span>
                        </span>
                    `);
                    count++;
                }

                let dateVal = $('#date-range').val();
                if (dateVal) {
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Date: ${dateVal}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="date">&times;</span>
                        </span>
                    `);
                    count++;
                }

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
                if (filterType === 'status') {
                    $('#status-filter').val(null).trigger('change');
                } else if (filterType === 'flat') {
                    $('#flat-filter').val(null).trigger('change');
                } else if (filterType === 'date') {
                    $('#date-range').val('').trigger('change');
                    fromDate = '';
                    toDate = '';
                }
                table.draw();
                updateFilterBadges();
            });

            $('#clear-all-filters').on('click', function() {
                $('#status-filter').val(null).trigger('change');
                $('#flat-filter').val(null).trigger('change');
                $('#date-range').val('').trigger('change');
                fromDate = '';
                toDate = '';
                table.draw();
                updateFilterBadges();
            });

            // Run initial update for badges
            updateFilterBadges();
        });
    </script>
@endpush

@push('styles')
    <style>
        .collapse-icon {
            transition: transform 0.2s ease-in-out;
        }
        [aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }
        .select2-container {
            width: 100% !important;
        }
        #filters-dropdown-panel {
            visibility: visible !important;
        }
        .remove-filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            transition: background-color 0.15s ease;
        }
        .remove-filter-btn:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }
    </style>
@endpush
