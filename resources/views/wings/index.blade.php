
@extends('layouts.app')

@section('title', 'Wings')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-dark">Wing List</h5>
                </div>

                <div class="col-auto">
                    @can('is-super-admin')
                        <a href="{{ route('wings.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-square me-1"></i>
                            Create Wing
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="wingsTable" class="table table-hover table-striped align-middle w-100 app-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Society</th>
                            <th>Wing</th>
                            <th>Floors</th>
                            <th>Flats / Floor</th>
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
        $('#wingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('wings.index') }}'
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'society', name: 'societies.name'},
                {data: 'name', name: 'name'},
                {data: 'total_floors', name: 'total_floors'},
                {data: 'flats_per_floor', name: 'flats_per_floor'},
                {data: 'actions', orderable: false, searchable: false}
            ]
        });
    </script>
@endpush
