@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')

<div class="container py-4">

    <h2 class="mb-4">
        Pending Visitor Passes
    </h2>

    <div class="card">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Visitor Pass List</span>

            <div class="d-flex gap-2">

                <a href="{{ route('gatekeeper.visitor-logs.exited') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Exited Visitors
                </a>

                <a href="{{ route('passes.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i>
                    Create Pass
                </a>

            </div>
        </div>

        <div class="card-body">

            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            <div class="table-responsive">

                <table id="visitorLogsTable" class="table table-bordered table-striped w-100">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Visitor</th>
                            <th>Phone</th>
                            <th>Flat</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    $(function () {

    $('#visitorLogsTable').DataTable({

        processing: true,
        serverSide: true,

        ajax: "{{ route('gatekeeper.visitor-logs.pending') }}",

        columns: [
            {
                data: 'id',
                name: 'id'
            },
            {
                data: 'visitor_name',
                name: 'visitor.name'
            },
            {
                data: 'phone',
                name: 'visitor.phone'
            },
            {
                data: 'flat_details',
                name: 'flat.flat_number',
                orderable: false
            },
            {
                data: 'purpose',
                name: 'purpose'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                searchable: false,
                orderable: false
            }
        ]
    });

});
</script>
@endpush
