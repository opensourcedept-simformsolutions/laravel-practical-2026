@extends('layouts.app')

@section('title', 'User Management')

@section('content')


    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <span class="fw-semibold">
                User Management
            </span>

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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
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

            $('#usersTable').DataTable({

                processing: true,

                serverSide: true,

                responsive: true,

                ajax: "{{ route('admin.users.index') }}",

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

                columns: [

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

                    {
                        data: 'role',
                        name: 'role.name'
                    },

                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }

                ]

            });

        });
    </script>
@endpush
