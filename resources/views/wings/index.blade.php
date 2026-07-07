
@extends('layouts.app')

@section('title', 'Wings')

@section('content')

    @if (auth()->user()->isSuperAdmin())
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
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Society</label>
                    <select id="societyFilter" class="form-select form-select-sm">
                        <option value="">All Societies</option>
                        @foreach ($societies as $society)
                            <option value="{{ $society->id }}">{{ $society->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Wing List</h5>
            @can('is-super-admin')
                <a href="{{ route('wings.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i>
                    Create Wing
                </a>
            @endcan
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
        $(document).ready(function() {
            table = $('#wingsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('wings.index') }}',
                    data: function(d) {
                        if ($('#societyFilter').length) {
                            d.society_id = $('#societyFilter').val();
                        }
                    }
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

            $('#societyFilter').change(function() {
                table.draw();
            });

            $('#btnResetFilters').click(function() {
                $('#societyFilter').val('').trigger('change.select2');
            });
        });
    </script>
@endpush
