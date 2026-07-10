@extends('layouts.app')

@section('title', 'Complaints')

@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            @if (auth()->user()->isSuperAdmin())
            <h5 class="mb-0 fw-bold text-dark">All Complaints</h5>
            @elseif(auth()->user()->isAdmin())
            <h5 class="mb-0 fw-bold text-dark">Society Complaints</h5>
            @else
            <h5 class="mb-0 fw-bold text-dark">My Complaints</h5>
            @endif

            <div class="d-flex gap-2 align-items-center">
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
                                    <select id="complaintFilter" class="form-select form-select-sm">
                                        <option value="active" selected>Active Complaints</option>
                                        <option value="deleted">Deleted Complaints</option>
                                        <option value="all">All Complaints</option>
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
                                <label class="form-label fw-semibold text-secondary small">Category</label>
                                <select id="category-filter" class="form-select form-select-sm">
                                    <option value="">All Categories</option>
                                    @foreach (\App\Enums\ComplaintCategory::cases() as $cat)
                                        <option value="{{ $cat->value }}">{{ ucfirst($cat->value) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Complaint Status</label>
                                <select id="complaint-status-filter" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </div>
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

                @canany(['is-gatekeeper', 'is-admin'])
                <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i>
                    Create Complaint
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

            <table id="complaintsTable" class="table table-hover table-striped align-middle w-100 app-datatable">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>

                        @can('is-admin')
                        <th>Resident</th>
                        @endcan

                        @if (auth()->user()->isSuperAdmin())
                        <th>Society</th>
                        @endif

                        <th>Category</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody></tbody>

            </table>

        </div>

    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

            table = $('#complaintsTable').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('complaints.index') }}",
                    data: function (d) {
                        d.filter = $('#complaintFilter').val() || 'active';
                        d.category = $('#category-filter').val();
                        d.complaint_status = $('#complaint-status-filter').val();
                        d.society_id = $('#society-filter').val();
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },
                    @can('is-admin')
                        {
                            data: 'resident_name',
                            name: 'users.name'
                        },
                    @endcan
                    @if (auth()->user()->isSuperAdmin())
                        {
                            data: 'society',
                            name: 'societies.name'
                        },
                    @endif {
                        data: 'category',
                        name: 'complaints.category'
                    },
                    {
                        data: 'description',
                        name: 'complaints.description'
                    },
                    {
                        data: 'status',
                        name: 'complaints.status'
                    },
                    {
                        data: 'created_at',
                        name: 'complaints.created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').each(function() {
                        new bootstrap.Tooltip(this);
                    });
                }
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
                table.ajax.reload();
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $('#btnResetFilters').click(function() {
                $('#category-filter, #complaint-status-filter, #society-filter').val('').trigger('change');
                $('#complaintFilter').val('active').trigger('change');
                table.ajax.reload();
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            function updateFilterBadges() {
                let list = $('#active-filters-list');
                list.empty();
                let count = 0;

                @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    let softDeleteVal = $('#complaintFilter').val();
                    if (softDeleteVal) {
                        let softDeleteText = $('#complaintFilter option:selected').text().trim();
                        list.append(`
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                                Filter: ${softDeleteText}
                                <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="soft_delete">&times;</span>
                            </span>
                        `);
                        count++;
                    }
                @endif

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

                let categoryVal = $('#category-filter').val();
                if (categoryVal) {
                    let categoryText = $('#category-filter option:selected').text().trim();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Category: ${categoryText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="category">&times;</span>
                        </span>
                    `);
                    count++;
                }

                let statusVal = $('#complaint-status-filter').val();
                if (statusVal) {
                    let statusText = $('#complaint-status-filter option:selected').text().trim();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Status: ${statusText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="status">&times;</span>
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
                if (filterType === 'soft_delete') {
                    $('#complaintFilter').val('all').trigger('change');
                } else if (filterType === 'society') {
                    $('#society-filter').val('').trigger('change');
                } else if (filterType === 'category') {
                    $('#category-filter').val('').trigger('change');
                } else if (filterType === 'status') {
                    $('#complaint-status-filter').val('').trigger('change');
                }
                table.ajax.reload();
                updateFilterBadges();
            });

            $('#clear-all-filters').on('click', function() {
                $('#category-filter, #complaint-status-filter, #society-filter').val('').trigger('change');
                $('#complaintFilter').val('active').trigger('change');
                table.ajax.reload();
                updateFilterBadges();
            });

            // Run initial update for badges
            updateFilterBadges();
        });
</script>
@endpush
