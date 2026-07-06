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

  <div class="modal fade" id="deleteSocietyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content border-0 shadow">

        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Delete Society
          </h5>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
          </button>
        </div>

        <div class="modal-body p-4">

          <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>

            <div>
              Review the records below before deleting this society.
              <strong>Permanent delete cannot be undone.</strong>
            </div>
          </div>

          <div id="deletePreview">
            <div class="text-center py-5">
              <div class="spinner-border text-primary"></div>

              <div class="mt-3 text-muted">
                Loading deletion summary...
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer bg-light">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancel
          </button>

          <button class="btn btn-warning" id="softDeleteBtn">
            <i class="bi bi-trash me-1"></i>
            Soft Delete
          </button>

          <button class="btn btn-danger" id="forceDeleteBtn">
            <i class="bi bi-trash-fill me-1"></i>
            Permanent Delete
          </button>
        </div>

      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    const societyBaseUrl = "{{ url('societies') }}";
    let currentSocietyId = null;

    $(document).on('click', '.btn-delete-society', function(e) {
      e.preventDefault();

      currentSocietyId = $(this).data('id');
      $('#deleteSocietyModal').modal('show');
      loadDeletePreview(currentSocietyId);
    });

    function loadDeletePreview(id) {
      $('#deletePreview').html(`
            <div class="text-center py-5">
                <div class="spinner-border"></div>
            </div>
        `);

      $.get(`${societyBaseUrl}/${id}/delete-preview`, function(response) {
        renderDeletePreview(response.data);
      }).fail(function() {
        $('#deletePreview').html(
          `<div class="alert alert-danger">Unable to load deletion summary. Please refresh and try again.</div>`
        );
      });
    }

    function renderDeletePreview(data) {
      $('#deletePreview').html(`
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-danger h-100">
                        <div class="card-body text-center">
                            <h2 class="text-danger mb-0">${data.total_records}</h2>
                            <small class="text-muted">Total Records Deleted</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th>Society</th>
                                        <td>${data.society}</td>
                                    </tr>

                                    <tr>
                                        <th>Admins</th>
                                        <td><span class="badge bg-primary rounded-pill">${data.admins}</span></td>
                                    </tr>

                                    <tr>
                                        <th>Gatekeepers</th>
                                        <td>${data.gatekeepers}</td>
                                    </tr>

                                    <tr>
                                        <th>Wings</th>
                                        <td>${data.wings ?? 0}</td>
                                    </tr>

                                    <tr>
                                        <th>Flats</th>
                                        <td><span class="badge bg-success rounded-pill">${data.flats}</span></td>
                                    </tr>

                                    <tr>
                                        <th>Residents</th>
                                        <td>${data.residents}</td>
                                    </tr>

                                    <tr>
                                        <th>Resident Users</th>
                                        <td>${data.resident_users}</td>
                                    </tr>

                                    <tr>
                                        <th>Complaints</th>
                                        <td>${data.complaints}</td>
                                    </tr>

                                    <tr>
                                        <th>Deliveries</th>
                                        <td>${data.deliveries}</td>
                                    </tr>

                                    <tr>
                                        <th>Visitor Logs</th>
                                        <td>${data.visitor_logs}</td>
                                    </tr>

                                    <tr class="table-danger">
                                        <th>Visitors Deleted</th>
                                        <td><span class="badge bg-danger rounded-pill">${data.visitors_to_delete}</span></td>
                                    </tr>

                                    <tr class="table-warning">
                                        <th>Visitors Retained</th>
                                        <td><span class="badge bg-warning rounded-pill">${data.visitors_to_keep}</span></td>
                                    </tr>

                                    <tr class="table-info">
                                        <th>Activity Logs Retained</th>
                                        <td>${data.activity_logs_kept}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    function handleDelete(mode) {
      if (!currentSocietyId) {
        return;
      }

      const button = mode === 'force' ? $('#forceDeleteBtn') : $('#softDeleteBtn');
      const otherButton = mode === 'force' ? $('#softDeleteBtn') : $('#forceDeleteBtn');

      button.prop('disabled', true).text('Processing...');
      otherButton.prop('disabled', true);

      $.ajax({
        url: `${societyBaseUrl}/${currentSocietyId}`,
        method: 'DELETE',
        data: { mode },
        success(response) {
          $('#deleteSocietyModal').modal('hide');
          table.ajax.reload(null, false);

          if (typeof Toast !== 'undefined') {
            Toast.fire({
              icon: 'success',
              title: response.message || 'Society deleted successfully',
            });
          }
        },
        error() {
          if (typeof Toast !== 'undefined') {
            Toast.fire({
              icon: 'error',
              title: 'Unable to delete society. Please try again.',
            });
          }
        },
        complete() {
          button.prop('disabled', false).html(mode === 'force' ? '<i class="bi bi-trash-fill me-1"></i> Permanent Delete' : '<i class="bi bi-trash me-1"></i> Soft Delete');
          otherButton.prop('disabled', false);
        }
      });
    }

    $('#softDeleteBtn').on('click', function() {
      handleDelete('soft');
    });

    $('#forceDeleteBtn').on('click', function() {
      handleDelete('force');
    });

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
