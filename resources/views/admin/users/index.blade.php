@extends('layouts.app')

@section('title', 'User Management')

@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">User Management</h5>

            <div class="d-flex gap-2 align-items-center">
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
                                    <select id="societyFilter" class="form-select form-select-sm">
                                        <option value="">
                                            All Societies
                                        </option>
                                        @foreach ($societies as $society)
                                            <option value="{{ $society->id }}">
                                                {{ $society->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Role</label>
                                <select id="roleFilter" class="form-select form-select-sm">
                                    <option value="">
                                        All Roles
                                    </option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}">
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Status</label>
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="active">Active Users</option>
                                    <option value="deleted">Deleted Users</option>
                                    <option value="all">All Users</option>
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

                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i>
                    Create User
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
                <table id="usersTable" class="table table-hover table-striped align-middle w-100 app-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            @if (auth()->user()->isSuperAdmin())
                                <th>Society</th>
                            @endif
                            <th>Role</th>
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
            let columns = [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },

                {
                    data: 'email',
                    name: 'email'
                },

                {
                    data: 'phone',
                    name: 'phone'
                },
            ];
            @if (auth()->user()->isSuperAdmin())
                columns.push({
                    data: 'society',
                    name: 'societies.name'
                });
            @endif

            columns.push({
                data: 'role',
                name: 'roles.name'
            });

            columns.push({
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            });

            table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                ajax: {
                    url: "{{ route('admin.users.index') }}",

                    data: function(d) {

                        d.role = $('#roleFilter').val();
                        d.filter = $('#statusFilter').val();

                        @if (auth()->user()->isSuperAdmin())
                            d.society_id = $('#societyFilter').val();
                        @endif
                    }
                },

                layout: {
                    topStart: {
                        buttons: [
                            'csv',
                            'excel'
                        ],
                        pageLength: true
                    },
                    topEnd: {
                        search: true
                    }
                },
                columns: columns
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
                $('#roleFilter, #societyFilter').val('').trigger('change');
                $('#statusFilter').val('active').trigger('change');
                table.ajax.reload();
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            function updateFilterBadges() {
                let list = $('#active-filters-list');
                list.empty();
                let count = 0;

                @if (auth()->user()->isSuperAdmin())
                    let societyVal = $('#societyFilter').val();
                    if (societyVal) {
                        let societyText = $('#societyFilter option:selected').text().trim();
                        list.append(`
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                                Society: ${societyText}
                                <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="society">&times;</span>
                            </span>
                        `);
                        count++;
                    }
                @endif

                let roleVal = $('#roleFilter').val();
                if (roleVal) {
                    let roleText = $('#roleFilter option:selected').text().trim();
                    list.append(`
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                            Role: ${roleText}
                            <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="role">&times;</span>
                        </span>
                    `);
                    count++;
                }

                let statusVal = $('#statusFilter').val();
                if (statusVal) {
                    let statusText = $('#statusFilter option:selected').text().trim();
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
                if (filterType === 'society') {
                    $('#societyFilter').val('').trigger('change');
                } else if (filterType === 'role') {
                    $('#roleFilter').val('').trigger('change');
                } else if (filterType === 'status') {
                    $('#statusFilter').val('all').trigger('change');
                }
                table.ajax.reload();
                updateFilterBadges();
            });

            $('#clear-all-filters').on('click', function() {
                $('#roleFilter, #societyFilter').val('').trigger('change');
                $('#statusFilter').val('active').trigger('change');
                table.ajax.reload();
                updateFilterBadges();
            });

            // Run initial update for badges
            updateFilterBadges();

        });
    </script>
@endpush
