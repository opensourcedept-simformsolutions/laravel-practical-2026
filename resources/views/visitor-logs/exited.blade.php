@extends('layouts.app')

@section('title', 'Exited Visitors')

@section('content')

  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-semibold">Exited Visitor List</span>

      <a href="{{ route('gatekeeper.visitor-logs.pending') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        Back
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">

        <table id="exitedVisitorsTable" class="table table-bordered table-striped w-100">
          <thead>
            <tr>
              <th>ID</th>
              <th>Visitor</th>
              <th>Phone</th>
              <th>Flat</th>
              <th>Purpose</th>
              <th>Entry Date</th>
              <th>Entry Time</th>
              <th>Exit Date</th>
              <th>Exit Time</th>
              <th>Status</th>
              <th>Photo</th>
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

      $('#exitedVisitorsTable').DataTable({
        processing: true,
        serverSide: true,

        ajax: "{{ route('gatekeeper.visitor-logs.exited') }}",

        order: [
          [6, 'desc']
        ],

        columns: [{
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
            data: 'entry_date',
            name: 'entry_time'
          },
          {
            data: 'entry_time',
            name: 'entry_time'
          },
          {
            data: 'exit_date',
            name: 'exit_time'
          },
          {
            data: 'exit_time',
            name: 'exit_time'
          },
          {
            data: 'status',
            name: 'status'
          },
          {
            data: 'photo',
            name: 'photo',
            searchable: false,
            orderable: false
          }
        ]
      });

    });
  </script>
@endpush
