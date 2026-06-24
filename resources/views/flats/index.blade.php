@extends('layouts.app')

@section('title', 'Flats')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Flats List</span>

            <a href="{{ route('flats.create') }}" type="button" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-square me-1"></i> Add Flat
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="flatsTable" class="table table-hover table-striped align-middle w-100 app-datatable">

                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
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
        $(document).ready(function() {

            $('#flatsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive:true,
                ajax: "{{ route('flats.index') }}",
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
                        data: 'wing',
                        name: 'wing'
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

        });
    </script>
@endpush
