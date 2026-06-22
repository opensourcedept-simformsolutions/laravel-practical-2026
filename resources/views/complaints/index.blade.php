@extends('layouts.app')

@section('title', 'Complaints')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <div>
            @if(auth()->user()->role->name === 'super_admin')
            <span class="fw-semibold">All Complaints</span>
            @elseif(auth()->user()->role->name === 'admin')
            <span class="fw-semibold">Society Complaints</span>
            @else
            <span class="fw-semibold">My Complaints</span>
            @endif
        </div>

        @if(in_array(auth()->user()->role->name, ['resident', 'gatekeeper']))
        <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-square me-1"></i>
            Create Complaint
        </a>
        @endif

    </div>

    <div class="card-body">

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <div class="border rounded p-3 mb-4 bg-light">

            <h6 class="fw-semibold mb-3">
                <i class="bi bi-funnel me-1"></i>
                Filter Complaints
            </h6>

            <form id="filterForm">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Category</label>

                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="security">Security</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="water">Water</option>
                            <option value="parking">Parking</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date</label>

                        <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                    </div>

                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>
                        Apply Filters
                    </button>

                    <button type="button" id="resetFilters" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Reset
                    </button>
                </div>

            </form>

        </div>

        <div class="table-responsive">

            <table id="complaintsTable" class="table table-hover table-striped align-middle w-100 app-datatable">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>

                        @if(auth()->user()->role->name === 'super_admin')
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

            data: function (d) {
                d.category = $('[name="category"]').val();
                d.status = $('[name="status"]').val();
                d.date = $('[name="date"]').val();
            }
        },

        columns: [

            {
                data: 'id',
                name: 'id'
            },

            @if(auth()->user()->role->name === 'super_admin')
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
        ]
    });

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        table.draw();
    });

    $('#resetFilters').on('click', function () {
        $('#filterForm')[0].reset();
        table.draw();
    });

});
</script>
@endpush
