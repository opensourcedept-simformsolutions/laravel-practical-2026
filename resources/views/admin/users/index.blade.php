@extends('layouts.app')

@section('title', 'User Management')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">User Management</h5>

            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-square me-1"></i>
                Create User
            </a>

        </div>

        <div class="card-body">
            <div class="row mb-3">

                @if (auth()->user()->isSuperAdmin())
                    <div class="col-md-3">
                        <select id="societyFilter" class="form-select">
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
                    <select id="roleFilter" class="form-select">
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
            $('#roleFilter').change(function() {
                table.ajax.reload();
            });

            @if (auth()->user()->isSuperAdmin())

                $('#societyFilter').change(function() {
                    table.ajax.reload();
                });
            @endif

        });
    </script>
@endpush
