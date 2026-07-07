@extends('layouts.app')

@section('title', 'Complaints')

@section('content')

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
                @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Soft-Delete Status</label>
                        <select id="complaintFilter" class="form-select form-select-sm">
                            <option value="active" selected>Active Complaints</option>
                            <option value="deleted">Deleted Complaints</option>
                            <option value="all">All Complaints</option>
                        </select>
                    </div>
                @endif

                @if (auth()->user()->isSuperAdmin())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Society</label>
                        <select id="society-filter" class="form-select form-select-sm">
                            <option value="">All Societies</option>
                            @foreach (\App\Models\Society::orderBy('name')->get() as $soc)
                                <option value="{{ $soc->id }}">{{ $soc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Category</label>
                    <select id="category-filter" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach (\App\Enums\ComplaintCategory::cases() as $cat)
                            <option value="{{ $cat->value }}">{{ ucfirst($cat->value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Complaint Status</label>
                    <select id="complaint-status-filter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            @if (auth()->user()->isSuperAdmin())
            <h5 class="mb-0 fw-bold text-dark">All Complaints</h5>
            @elseif(auth()->user()->isAdmin())
            <h5 class="mb-0 fw-bold text-dark">Society Complaints</h5>
            @else
            <h5 class="mb-0 fw-bold text-dark">My Complaints</h5>
            @endif

            @canany(['is-gatekeeper', 'is-admin'])
            <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-square me-1"></i>
                Create Complaint
            </a>
            @endcanany
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

            table = $('#complaintsTable').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('complaints.index') }}",
                    data: function (d) {
                        d.filter = $('#complaintFilter').val() || 'active';
                        d.category = $('#category-filter').val();
                        d.complaint_status = $('#complaint-status-filter').val();
                        d.society_id = $('#society-filter').val();
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
            $('#complaintFilter, #category-filter, #complaint-status-filter, #society-filter').on('change', function () {
                table.ajax.reload();
            });

            $('#btnResetFilters').click(function() {
                $('#category-filter, #complaint-status-filter, #society-filter').val('').trigger('change');
                $('#complaintFilter').val('active').trigger('change');
                table.ajax.reload();
            });
        });
</script>
@endpush
