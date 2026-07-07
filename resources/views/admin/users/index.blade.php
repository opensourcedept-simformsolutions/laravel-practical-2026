@extends('layouts.app')

@section('title', 'User Management')

@section('content')

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

                <div class="col-md-3">
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

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Status</label>
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="active">Active Users</option>
                        <option value="deleted">Deleted Users</option>
                        <option value="all">All Users</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">User Management</h5>

            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-square me-1"></i>
                Create User
            </a>
        </div>

        <div class="card-body">
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
                        ]
                    },
                    topEnd: {
                        search: true,
                        pageLength: true
                    }
                },
                columns: columns
            });

            $('#roleFilter, #statusFilter').change(function() {
                table.ajax.reload();
            });

            @if (auth()->user()->isSuperAdmin())
                $('#societyFilter').change(function() {
                    table.ajax.reload();
                });
            @endif

            $('#btnResetFilters').click(function() {
                $('#roleFilter, #societyFilter').val('').trigger('change');
                $('#statusFilter').val('active').trigger('change');
                table.ajax.reload();
            });

        });
    </script>
@endpush
