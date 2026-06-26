@extends('layouts.app')

@section('title', 'Deliveries')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Delivery List</h5>

            @canany(['is-gatekeeper', 'is-admin'])
                <a href="{{ route('deliveries.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i>
                    Create Delivery
                </a>
            @endcanany

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="deliveries-table" class='table table-hover table-striped align-middle mb-0'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Flat</th>
                            <th>Resident</th>
                            <th>Vendor</th>
                            <th>Package Details</th>
                            <th>Status</th>
                            <th>Received At</th>
                            <th>Delivered At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#deliveries-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                ajax: '{{ route('deliveries.data') }}',

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'flat',
                        name: 'flat'
                    },
                    {
                        data: 'resident',
                        name: 'users.name'
                    },
                    {
                        data: 'vendor',
                        name: 'deliveries.vendor'
                    },
                    {
                        data: 'package_details',
                        name: 'deliveries.package_details'
                    },
                    {
                        data: 'status',
                        name: 'deliveries.status'
                    },
                    {
                        data: 'received_at',
                        name: 'deliveries.received_at'
                    },
                    {
                        data: 'delivered_at',
                        name: 'deliveries.delivered_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
        $(document).on('submit', '.delete-form', function(e) {
            e.preventDefault();

            const form = this;

            Swal.fire({
                title: 'Delete Delivery?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@endpush
