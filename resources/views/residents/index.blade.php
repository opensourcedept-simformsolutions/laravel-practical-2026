@extends('layouts.app')

@section('title', 'Residents')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Resident List</h5>

            <a href="{{ route('residents.create') }}" type="button" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-square me-1"></i> Add Resident
            </a>
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
        $(function() {

            $('#residentsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive:true,
                ajax: "{{ route('residents.index') }}",
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

                columns: [{
                        data: 'id',
                        name: 'id'
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
                        name: 'flats.wing'
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

        });
    </script>
@endpush
