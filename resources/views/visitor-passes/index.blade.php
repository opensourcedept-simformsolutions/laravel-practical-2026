@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')

  <div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold text-dark">Visitor Pass List</h5>

      <div class="d-flex align-items-center gap-2">
        <select id="status-filter" class="form-select form-select-sm w-auto">
            <option value="active">Active Pass</option>
            <option value="deleted">Deleted Pass</option>
            <option value="all">All Pass</option>
        </select>

        <a href="{{ route('passes.create') }}" type="button" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-square me-1"></i> Create Pass
        </a>
      </div>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table id="passTable" class="table table-hover table-striped align-middle w-100 app-datatable">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Visitor</th>
              <th>Phone</th>
              <th>Purpose</th>
              <th>Status</th>
              <th>Entry Time</th>
              <th>Exit Time</th>
              <th>Visit Date</th>
              <th>Flat</th>
              <th>Created By</th>
              <th>Gatekeeper</th>
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

      // Data: "Read visitor_name from the JSON response and display it in this column."

      table = $('#passTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('passes.data') }}",
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
          {
            data: 'visitor_name',
            name: 'visitors.name'
          },
          {
            data: 'visitor_phone',
            name: 'visitors.phone'
          },
          {
            data: 'purpose',
            name: 'visitor_logs.purpose'
          },
          {
            data: 'status',
            name: 'visitor_logs.status'
          },
          {
            data: 'entry_time',
            name: 'visitor_logs.entry_time',
          },
          {
            data: 'exit_time',
            name: 'visitor_logs.exit_time',
          },
          {
            data: 'visit_date',
            name: 'visitor_logs.visit_date',
          },
          {
            data: 'flat',
            name: 'visitor_logs.flat_id',
          },
          {
            data: 'creator_name',
            name: 'creators.name'
          },
          {
            data: 'gatekeeper_name',
            name: 'gatekeepers.name'
          },
          {
            data: 'actions',
            orderable: false,
            searchable: false,
          }
        ]
      });

      $(document).on('change', '#status-filter', function() {
          rd();
      });

    });
  </script>
@endpush
