@extends('layouts.app')

@section('title', 'Societies')

@section('content')

  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold text-dark">Society List</h5>

      <a href="{{ route('societies.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-square me-1"></i> Add Society
      </a>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table id="societyTable" class="table table-hover table-striped align-middle w-100 app-datatable">

          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Address</th>
              <th>City</th>
              <th>State</th>
              <th>Pincode</th>
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

      table = $('#societyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('societies.data') }}",

        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'name',
            name: 'name'
          },
          {
            data: 'address',
            name: 'address'
          },
          {
            data: 'city',
            name: 'city'
          },
          {
            data: 'state',
            name: 'state'
          },
          {
            data: 'pincode',
            name: 'pincode'
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
            data: 'actions',
            orderable: false,
            searchable: false
          }
        ]
      });

    });
  </script>
@endpush
