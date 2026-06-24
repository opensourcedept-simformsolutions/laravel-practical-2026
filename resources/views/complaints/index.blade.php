@extends('layouts.app')

@section('title', 'Complaints')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <div>
            @if(auth()->user()->isSuperAdmin())
                <span class="fw-semibold">All Complaints</span>
            @elseif(auth()->user()->isAdmin())
                <span class="fw-semibold">Society Complaints</span>
            @else
                <span class="fw-semibold">My Complaints</span>
            @endif
        </div>

        @canany(['is-gatekeeper','is-resident'])
            <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-square me-1"></i>
                Create Complaint
            </a>
        @endcan

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

                    @if(auth()->user()->isSuperAdmin())
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
    $(document).ready(function () {

    let table = $('#complaintsTable').DataTable({

        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('complaints.index') }}",
        },

        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                searchable: false,
                orderable: false
            },

            @can('is-admin')
            {
                data: 'resident_name',
                name: 'user.name',
                orderable: false,
                searchable: false
            },
            @endcan

            @if(auth()->user()->isSuperAdmin())
            {
                data: 'society',
                name: 'society',
                orderable: false,
                searchable: false
            },
            @endif

            {
                data: 'category',
                name: 'category'
            },

            {
                data: 'description',
                name: 'description'
            },

            {
                data: 'status',
                name: 'status'
            },

            {
                data: 'created_at',
                name: 'created_at'
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],

        drawCallback: function () {
            $('[data-bs-toggle="tooltip"]').each(function () {
                new bootstrap.Tooltip(this);
            });
        }
    });
});
</script>
@endpush
