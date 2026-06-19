@extends('layouts.app')

@section('title', 'Visitor Report')

@section('content')

  <div class="container-fluid py-3">

    <div class="card mb-3">
      <div class="card-body">

        <form id="filterForm" class="row g-3">

          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">All</option>
              <option value="pending">Pending</option>
              <option value="entered">Entered</option>
              <option value="exited">Exited</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Flat</label>
            <input type="number" name="flat_id" class="form-control">
          </div>

          <div class="col-md-3">
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" class="form-control">
          </div>

          <div class="col-md-3">
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" class="form-control">
          </div>

          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary btn-sm">Filter</button>
            <button type="button" id="reset" class="btn btn-secondary btn-sm">Reset</button>
          </div>

        </form>

      </div>
    </div>

    <div class="card">
      <div class="card-body">

        <div class="table-responsive">
          <table id="reportTable" class="table table-striped table-hover w-100">

            <thead>
              <tr>
                <th>ID</th>
                <th>Visitor</th>
                <th>Phone</th>
                <th>Flat</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Entry</th>
                <th>Exit</th>
                <th>Date</th>
                <th>Gatekeeper</th>
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
    $(document).ready(function() {

      let table = $('#reportTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "{{ route('passes.report.data') }}",
          data: function(d) {
            d.status = $('select[name=status]').val();
            d.flat_id = $('input[name=flat_id]').val();
            d.from_date = $('input[name=from_date]').val();
            d.to_date = $('input[name=to_date]').val();
          }
        },

        columns: [{
            data: 'id',
            name: 'visitor_logs.id'
          },

          {
            data: 'visitor',
            name: 'visitors.name'
          },
          {
            data: 'phone',
            name: 'visitors.phone'
          },

          {
            data: null,
            name: 'flats.flat_number',
            render: function(row) {
              return row.flat_wing && row.flat_number ?
                row.flat_wing + '-' + row.flat_number :
                '-';
            }
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
            name: 'visitor_logs.entry_time'
          },
          {
            data: 'exit_time',
            name: 'visitor_logs.exit_time'
          },
          {
            data: 'visit_date',
            name: 'visitor_logs.visit_date'
          },

          {
            data: 'gatekeeper',
            name: 'users.name'
          }
        ]
      });

      $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.draw();
      });

      $('#reset').on('click', function() {
        $('#filterForm')[0].reset();
        table.draw();
      });

    });
  </script>
@endpush
