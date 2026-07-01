@extends('layouts.app')

@section('title', 'Deliveries')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-dark">Delivery List</h5>
                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        @can('is-admin')
                            <select id="status-filter" class="form-select form-select-sm w-auto">
                                <option value="active">Active Deliveries</option>
                                <option value="deleted">Deleted Deliveries</option>
                                <option value="all">All Deliveries</option>
                            </select>
                        @endcan

                        @canany(['is-gatekeeper', 'is-admin'])
                            <a href="{{ route('deliveries.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-square me-1"></i>
                                Create Delivery
                            </a>
                        @endcanany
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="deliveries-table" class='table table-hover table-striped align-middle mb-0'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            @if (Auth()->user()->isSuperAdmin())
                                <th>Society</th>
                            @endif
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
            table = $('#deliveries-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                ajax: {
                    url: '{{ route('deliveries.data') }}',
                    data: function(d) {
                        d.filter = $('#status-filter').val();
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    @if (Auth()->user()->isSuperAdmin())
                        {
                            data: 'society',
                            name: 'societies.name'
                        },
                    @endif {
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
        $('#status-filter').on('change', function() {
            rd()
        });
    </script>
@endpush
