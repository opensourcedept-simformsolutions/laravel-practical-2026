@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-funnel me-2 text-primary"></i>Filters
            </h6>
            <button type="button" id="btnResetFilters" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Soft-Delete Status</label>
                    <select id="status-filter" class="form-select form-select-sm">
                        <option value="active">Active Pass</option>
                        <option value="deleted">Deleted Pass</option>
                        <option value="all">All Pass</option>
                    </select>
                </div>

                @if (auth()->user()->isSuperAdmin())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Society</label>
                        <select id="society-filter" class="form-select form-select-sm">
                            <option value="">All Societies</option>
                            @foreach (\App\Models\Society::orderBy('name')->get() as $soc)
                                <option value="{{ $soc->id }}">{{ $soc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Visit Status</label>
                    <select id="visit-status-filter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="pending_approval">Pending Approval</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="entered">Entered</option>
                        <option value="exited">Exited</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Visit Date</label>
                    <input type="date" id="visit-date-filter" class="form-control form-control-sm">
                </div>

                @if (!auth()->user()->isResident())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Flat</label>
                        <select id="flat-filter" class="form-select form-select-sm select2-flat">
                            <option value="">All Flats</option>
                            @if (isset($flats))
                                @foreach ($flats as $flat)
                                    <option value="{{ $flat->id }}">{{ $flat->wing }}-{{ $flat->flat_number }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Visitor Pass List</h5>

            <a href="{{ route('passes.create') }}" class="btn btn-primary btn-sm">
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
        ajax: {
            url: "{{ route('passes.data') }}",
            data: function(d) {
                d.filter = $('#status-filter').val();
                d.visit_status = $('#visit-status-filter').val();
                d.visit_date = $('#visit-date-filter').val();
                d.flat_id = $('#flat-filter').val();
                d.society_id = $('#society-filter').val();
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

      $(document).on('change', '#status-filter, #visit-status-filter, #flat-filter, #society-filter', function() {
          rd();
      });

      $(document).on('change input', '#visit-date-filter', function() {
          rd();
      });

      $(document).on('change', '#society-filter', function() {
          let societyId = $(this).val();
          if (!societyId) {
              $('#flat-filter').html('<option value="">All Flats</option>');
              rd();
              return;
          }

          $.ajax({
              url: "/societies/" + societyId + "/flats",
              type: 'GET',
              success: function(response) {
                  if (!response.success) return;
                  let options = '<option value="">All Flats</option>';
                  response.data.forEach(function(flat) {
                      options += `<option value="${flat.id}">${flat.wing}-${flat.flat_number}</option>`;
                  });
                  $('#flat-filter').html(options);
                  rd();
              },
              error: function() {
                  $('#flat-filter').html('<option value="">Error loading flats</option>');
                  rd();
              }
          });
      });

      $('#btnResetFilters').click(function() {
          $('#society-filter, #visit-status-filter, #flat-filter').val('').trigger('change');
          $('#status-filter').val('active').trigger('change');
          $('#visit-date-filter').val('');
          rd();
      });

    });
  </script>
@endpush
