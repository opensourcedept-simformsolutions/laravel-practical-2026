@extends('layouts.app')

@section('title', 'Complaints')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3">
        <div class="row align-items-center">

            <div class="col">
                @if (auth()->user()->isSuperAdmin())
                <h5 class="mb-0 fw-bold text-dark">All Complaints</h5>
                @elseif(auth()->user()->isAdmin())
                <h5 class="mb-0 fw-bold text-dark">Society Complaints</h5>
                @else
                <h5 class="mb-0 fw-bold text-dark">My Complaints</h5>
                @endif
            </div>

            <div class="col-auto">
                <div class="d-flex align-items-center gap-2">

                    @can('is-admin')
                    <select id="complaintFilter" class="form-select form-select-sm w-auto">
                        <option value="active" selected>Active Complaints</option>
                        <option value="deleted">Deleted Complaints</option>
                        <option value="all">All Complaints</option>
                    </select>
                    @endcan

                    @canany(['is-gatekeeper', 'is-admin'])
                    <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-square me-1"></i>
                        Create Complaint
                    </a>
                    @endcanany

                </div>
            </div>

        </div>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table id="complaintsTable" class="table table-hover table-striped align-middle w-100 app-datatable">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>

                        @can('is-admin')
                        <th>Resident</th>
                        @endcan

                        @if (auth()->user()->isSuperAdmin())
                        <th>Society</th>
                        @endif

                        <th>Category</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created At</th>
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

            let currentFilter = 'active';

            table = $('#complaintsTable').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('complaints.index') }}",
                    data: function (d) {
                        d.filter = currentFilter;
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },
                    @can('is-admin')
                        {
                            data: 'resident_name',
                            name: 'users.name'
                        },
                    @endcan
                    @if (auth()->user()->isSuperAdmin())
                        {
                            data: 'society',
                            name: 'societies.name'
                        },
                    @endif {
                        data: 'category',
                        name: 'complaints.category'
                    },
                    {
                        data: 'description',
                        name: 'complaints.description'
                    },
                    {
                        data: 'status',
                        name: 'complaints.status'
                    },
                    {
                        data: 'created_at',
                        name: 'complaints.created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').each(function() {
                        new bootstrap.Tooltip(this);
                    });
                }
            });
            $('#complaintFilter').on('change', function () {
                currentFilter = $(this).val();
                table.ajax.reload();
            });
        });
</script>
@endpush
