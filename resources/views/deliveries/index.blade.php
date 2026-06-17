@extends('layouts.app')

@section('title', 'Deliveries')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-bold mb-0">
                Deliveries
            </h2>
            <a href="{{ route('deliveries.create') }}" type="button" class="btn btn-primary">
                <i class="bi bi-plus-square me-1"></i> Create Delivery
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="deliveries-table" class='table table-hover align-middle mb-0'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Flat</th>
                            <th>Resident</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th>Received At</th>
                            <th>Actions</th>
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
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'flat',
                        name: 'flat'
                    },
                    {
                        data: 'resident',
                        name: 'resident'
                    },
                    {
                        data: 'vendor',
                        name: 'vendor'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'received_at',
                        name: 'received_at'
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
    </script>
@endpush
