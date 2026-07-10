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
      <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">

        <div class="modal-header bg-dark text-white border-0 py-3" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
          <h5 class="modal-title fw-bold d-flex align-items-center">
            <span class="badge bg-danger-subtle text-danger p-2 me-2" style="border-radius: 8px;">
              <i class="bi bi-exclamation-triangle-fill fs-6"></i>
            </span>
            Delete Society
          </h5>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
          </button>
        </div>

        <div class="modal-body p-4 bg-white">
          <div id="deletePreview">
            <div class="text-center py-5">
              <div class="spinner-border text-dark mb-3"></div>
              <div class="text-muted small">Generating deletion impact assessment...</div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light border-0 py-3" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
          <button class="btn btn-link text-secondary text-decoration-none fw-semibold px-4" data-bs-dismiss="modal">
            Cancel
          </button>

          <button class="btn btn-dark px-4 fw-semibold" id="softDeleteBtn">
            <i class="bi bi-trash me-1"></i>
            Soft Delete
          </button>

          <button class="btn btn-danger px-4 fw-semibold" id="forceDeleteBtn">
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
                <div class="spinner-border text-dark mb-3"></div>
                <div class="text-muted small">Generating deletion impact assessment...</div>
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
            <div class="row g-4">
                <!-- Left Column: Total Impact Summary Card -->
                <div class="col-lg-4">
                    <div class="card border-0 text-white h-100 position-relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; min-height: 320px;">
                        <div class="position-absolute" style="width: 250px; height: 250px; background: rgba(255,255,255,0.02); border-radius: 50%; top: -60px; right: -60px;"></div>
                        <div class="card-body p-4 d-flex flex-column justify-content-between position-relative" style="z-index: 1;">
                            <div>
                                <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill mb-3 fw-bold small text-uppercase">Impact Report</span>
                                <h4 class="fw-bold text-white mb-2">Cascade Assessment</h4>
                                <p class="text-light opacity-75 small">Deleting this society will recursively delete all elements connected to its infrastructure.</p>
                            </div>
                            
                            <div class="my-4 text-center">
                                <div class="display-3 fw-bold text-white mb-1">${data.total_records}</div>
                                <div class="text-uppercase text-light opacity-50 tracking-wider small fw-bold">Total Records Affected</div>
                            </div>

                            <div class="mt-auto">
                                <div class="d-flex align-items-start gap-2 bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 small">
                                    <i class="bi bi-shield-exclamation text-warning fs-5 mt-0.5"></i>
                                    <div class="text-light">
                                        <strong>Caution:</strong> Permanent deletes cannot be undone. Soft deletes can be restored by the super admin.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Breakdown Cards Grid -->
                <div class="col-lg-8">
                    <div class="row g-3">
                        <!-- Society Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-dark">
                                    <i class="bi bi-building fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Societies</div>
                                    <div class="fs-4 fw-bold text-dark">${data.society}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Admins Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-primary">
                                    <i class="bi bi-person-workspace fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Admins</div>
                                    <div class="fs-4 fw-bold text-dark">${data.admins}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Gatekeepers Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-info">
                                    <i class="bi bi-shield-shaded fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Gatekeepers</div>
                                    <div class="fs-4 fw-bold text-dark">${data.gatekeepers}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Wings Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-success">
                                    <i class="bi bi-grid-3x3-gap-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Wings</div>
                                    <div class="fs-4 fw-bold text-dark">${data.wings ?? 0}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Flats Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-success">
                                    <i class="bi bi-door-closed-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Flats</div>
                                    <div class="fs-4 fw-bold text-dark">${data.flats}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Residents Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-primary">
                                    <i class="bi bi-people-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Residents</div>
                                    <div class="fs-4 fw-bold text-dark">${data.residents}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Complaints Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-danger">
                                    <i class="bi bi-exclamation-octagon fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Complaints</div>
                                    <div class="fs-4 fw-bold text-dark">${data.complaints}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Deliveries Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-warning">
                                    <i class="bi bi-box-seam-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Deliveries</div>
                                    <div class="fs-4 fw-bold text-dark">${data.deliveries}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Visitor Logs Card -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-0 bg-light rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                                <div class="p-2.5 bg-white rounded-3 border text-dark">
                                    <i class="bi bi-journal-text fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Visitor Logs</div>
                                    <div class="fs-4 fw-bold text-dark">${data.visitor_logs}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light rounded-3 p-3 mt-3">
                        <h6 class="fw-bold mb-2 small text-dark"><i class="bi bi-person-badge-fill me-1"></i> Visitors & Audit Logs Detail:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1.5 px-3 rounded-pill fw-semibold">
                                <i class="bi bi-trash-fill me-1"></i> ${data.visitors_to_delete} Visitors to delete
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1.5 px-3 rounded-pill fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> ${data.visitors_to_keep} Visitors to keep
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle py-1.5 px-3 rounded-pill fw-semibold">
                                <i class="bi bi-file-earmark-text-fill me-1"></i> ${data.activity_logs_kept} Activity Logs retained
                            </span>
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
