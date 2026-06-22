@extends('layouts.app')

@section('title', 'Deliveries')
@section('content')
    <x-table id="deliveries-table" title="Deliveries">
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
    </x-table>
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
