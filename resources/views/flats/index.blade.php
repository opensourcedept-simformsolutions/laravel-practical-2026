@extends('layouts.app')

@section('title', 'Flats')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-dark">Flat List</h5>
                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        @can('is-admin')
                            <select id="status-filter" class="form-select form-select-sm w-auto">
                                <option value="active">Active Flats</option>
                                <option value="deleted">Deleted Flats</option>
                                <option value="all">All Flats</option>
                            </select>
                        @endcan

                        @canany(['is-gatekeeper', 'is-admin'])
                            <a href="{{ route('flats.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-square me-1"></i>
                                Create Flat
                            </a>
                        @endcanany
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if (auth()->user()->isSuperAdmin())
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Society</label>
                        <select id="society_filter" name="society_id" class="form-select">
                            <option value="">Select Society</option>

                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}"
                                    {{ old('society_id') == $society->id ? 'selected' : '' }}>
                                    {{ $society->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('society_id')
                            <div class="text-danger pt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table id="flatsTable" class="table table-hover table-striped align-middle w-100 app-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
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
        table = $('#flatsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('flats.index') }}",
                data: function(d) {
                    d.society_id = $('#society_filter').val();
                    d.filter = $('#status-filter').val();
                }
            },
            layout: {
                topStart: {
                    buttons: [{
                        text: 'CSV',
                        action: function (e, dt) {

                            let search = dt.search();
                            let society = $('#society_filter').val();

                            let url = "{{ route('flats.export') }}";

                            url += '?search=' + encodeURIComponent(search);

                            if (society) {
                                url += '&society_id=' + society;
                            }

                            window.location = url;
                        }
                    }]
                },
                topEnd: {
                    search: true,
                    pageLength: true
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'society',
                    name: 'societies.name',
                    visible: @json(auth()->user()->isSuperAdmin())
                },
                {
                    data: 'wing',
                    name: 'wing',
                    searchable: true
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

        $(document).on('change', '#status-filter, #society_filter', function() {
            rd();
        });
    </script>
@endpush
