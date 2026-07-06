@extends('layouts.app')

@section('title', 'Residents')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-dark">Resident List</h5>
                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        @can('is-admin')
                            <select id="status-filter" class="form-select form-select-sm w-auto me-1">
                                <option value="active">Active Resident</option>
                                <option value="deleted">Deleted Resident</option>
                                <option value="all">All Resident</option>
                            </select>
                            <select id="flat-filter" class="form-select form-select-sm w-auto select2-flat">
                                <option value="">All Flats</option>
                                @foreach ($flats as $flat)
                                    <option value="{{ $flat->id }}">{{ $flat->wing }}-{{ $flat->flat_number }}</option>
                                @endforeach
                            </select>
                        @endcan

                        @canany(['is-gatekeeper', 'is-admin'])
                            <a href="{{ route('residents.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-square me-1"></i>
                                Create Resident
                            </a>
                        @endcanany
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">

            @if (auth()->user()->isSuperAdmin())
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Society</label>
                        <select id="society_filter" class="form-select form-select-sm">
                            <option value="">All Societies</option>
                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}">{{ $society->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Flat</label>
                        <select id="flat-filter" class="form-select form-select-sm">
                            <option value="">All Flats</option>
                        </select>
                    </div>
                </div>
            @endif

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
        table = $('#residentsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,

            ajax: {
                url: "{{ route('residents.index') }}",
                data: function(d) {
                    d.society_id = $('#society_filter').val();
                    d.filter = $('#status-filter').val();
                    d.flat_id = $('#flat-filter').val();
                }
            },

            layout: {
                topStart: {
                    buttons: ['csv', 'excel']
                },
                topEnd: {
                    search: true,
                    pageLength: true
                }
            },

            columns: [{
                    data: 'id',
                    name: 'users.id'
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
                    name: 'wings.name'
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

        $(document).on('change', '#status-filter, #flat-filter', function() {
            rd();
        });

        $(document).on('change', '#society_filter', function() {
            let societyId = $(this).val();
            if (!societyId) {
                $('#flat-filter').html('<option value="">All Flats</option>');
                rd();
                return;
            }

            $.ajax({
                url: "/societies/" + societyId + "/flats",
                type: 'GET',
                success: function(response) {
                    if (!response.success) return;
                    let options = '<option value="">All Flats</option>';
                    response.data.forEach(function(flat) {
                        options += `<option value="${flat.id}">${flat.wing}-${flat.flat_number}</option>`;
                    });
                    $('#flat-filter').html(options);
                    rd();
                },
                error: function() {
                    $('#flat-filter').html('<option value="">Error loading flats</option>');
                    rd();
                }
            });
        });
    </script>
@endpush