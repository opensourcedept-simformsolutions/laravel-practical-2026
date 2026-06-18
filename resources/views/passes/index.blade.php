@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')

  <div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-semibold">Visitor Pass List</span>

      <a href="{{ route('passes.create') }}" type="button" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-square me-1"></i> Create Pass
      </a>
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
        ajax: "{{ route('passes.data') }}",

        columns: [{
            data: 'id',
            name: 'visitor_logs.id'
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

    });
  </script>
@endpush
