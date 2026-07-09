
@extends('layouts.app')

@section('title', 'Wings')

@section('content')

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Wing List</h5>
            <div class="d-flex gap-2 align-items-center">
                @if (auth()->user()->isSuperAdmin())
                <!-- Filters Dropdown Container -->
                <div class="position-relative">
                    <button type="button" id="filters-toggle-btn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> <span id="filters-btn-text">Filters</span> <i class="bi bi-chevron-down ms-1 collapse-icon"></i>
                    </button>

                    <div id="filters-dropdown-panel" class="card shadow-lg border position-absolute end-0 mt-2 p-3 d-none" style="width: 320px; max-width: 90vw; z-index: 1050;">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-secondary small">Society</label>
                                <select id="societyFilter" class="form-select form-select-sm">
                                    <option value="">All Societies</option>
                                    @foreach ($societies as $society)
                                        <option value="{{ $society->id }}">{{ $society->name }}</option>
                                    @endforeach
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
                @endif

                @can('is-super-admin')
                    <a href="{{ route('wings.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-square me-1"></i>
                        Create Wing
                    </a>
                @endcan
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
                table.draw();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $('#btnResetFilters').click(function() {
                $('#societyFilter').val('').trigger('change.select2');
                table.draw();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });
        });
    </script>
@endpush
