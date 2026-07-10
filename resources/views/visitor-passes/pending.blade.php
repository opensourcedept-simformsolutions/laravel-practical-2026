@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white border-bottom py-3">
        <div class="row align-items-center">

            <div class="col">
                <h5 class="mb-0 fw-bold text-dark">Today's Visitor List</h5>
            </div>

            <div class="col-auto">
                <div class="d-flex align-items-center gap-2">

                    @can('is-admin')
                    <!-- Filters Dropdown Container -->
                    <div class="position-relative">
                        <button type="button" id="filters-toggle-btn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-funnel"></i> <span id="filters-btn-text">Filters</span> <i class="bi bi-chevron-down ms-1 collapse-icon"></i>
                        </button>

                        <div id="filters-dropdown-panel" class="card shadow-lg border position-absolute end-0 mt-2 p-3 d-none" style="width: 560px; max-width: 90vw; z-index: 1050;">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold text-secondary small">Status</label>
                                    <select id="status-filter" class="form-select form-select-sm">
                                        <option value="active" selected>Active Visitor Passes</option>
                                        <option value="deleted">Deleted Visitor Passes</option>
                                        <option value="all">All Visitor Passes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3 border-top pt-3">
                                <button type="button" id="btnResetFilters" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                </button>
                                <button type="button" id="apply-filters" class="btn btn-primary btn-sm">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    @endcan

                    <a href="{{ route('gatekeeper.visitor-logs.exited') }}" class="btn btn-info btn-sm text-nowrap">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Exited Visitors
                    </a>

                    @if(!(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                    <a href="{{ route('passes.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-square me-1"></i>
                        Create Pass
                    </a>
                    @endif

                </div>
            </div>

        </div>
    </div>

    <div class="card-body">
        <!-- Active Filters Badges -->
        <div id="active-filters-container" class="align-items-center flex-wrap gap-2 mb-3 p-2 bg-light rounded-3"
            style="display: none !important;">
            <span class="text-muted small fw-semibold ms-1">Active Filters:</span>
            <div id="active-filters-list" class="d-flex flex-wrap gap-2 align-items-center"></div>
            <button type="button" id="clear-all-filters"
                class="btn btn-link btn-sm text-decoration-none p-0 ms-2 fw-semibold text-danger">
                Clear All
            </button>
        </div>

        <div class="table-responsive">
            <table id="visitorLogsTable" class="table table-hover table-striped align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        @if (auth()->user()->isSuperAdmin())
                        <th>Society Name</th>
                        @endif
                        <th>Visitor</th>
                        <th>Phone</th>
                        <th>Flat</th>
                        <th>Purpose</th>
                        <th>Visit Date</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

<div class="modal fade" id="entryModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form id="entryForm" method="POST" enctype="multipart/form-data">

                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h5 class="modal-title">
                        Capture Visitor Photo
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body text-center">

                    <video id="video" autoplay playsinline width="100%" class="border rounded"></video>

                    <canvas id="canvas" style="display:none;"></canvas>

                    <img id="preview" class="img-thumbnail mt-3 d-none" width="250">

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" id="captureBtn">
                            Capture Photo
                        </button>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-warning d-none" id="recaptureBtn">
                        Recapture Photo
                    </button>
                    <button type="submit" class="btn btn-success">
                        Mark Entry
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    $(function() {

            let stream = null;
            let capturedFile = null;
            let currentFilter = 'active';

            table = $('#visitorLogsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('gatekeeper.visitor-logs.pending') }}",
                    data: function (d) {
                    d.filter = currentFilter;
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    @if (auth()->user()->isSuperAdmin())
                        {
                            data: 'society',
                            name: 'societies.name'
                        },
                    @endif {
                        data: 'visitor_name',
                        name: 'visitors.name'
                    },
                    {
                        data: 'phone',
                        name: 'visitors.phone'
                    },
                    {
                        data: 'flat_details',
                        name: 'flat_details'
                    },
                    {
                        data: 'purpose',
                        name: 'purpose'
                    },
                    {
                        data: 'visit_date',
                        name: 'visit_date'
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
            // Toggle custom floating filter panel
            $('#filters-toggle-btn').click(function(e) {
                e.stopPropagation();
                $('#filters-dropdown-panel').toggleClass('d-none');
                $(this).attr('aria-expanded', !$('#filters-dropdown-panel').hasClass('d-none'));
            });

            // Close floating filter panel when clicking outside
            $(document).click(function(e) {
                let panel = $('#filters-dropdown-panel');
                let toggleBtn = $('#filters-toggle-btn');

                if (!panel.is(e.target) && panel.has(e.target).length === 0 &&
                    !toggleBtn.is(e.target) && toggleBtn.has(e.target).length === 0) {
                    panel.addClass('d-none');
                    toggleBtn.attr('aria-expanded', 'false');
                }
            });

            $('#apply-filters').click(function() {
                currentFilter = $('#status-filter').val();
                rd();
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            $('#btnResetFilters').click(function() {
                $('#status-filter').val('active');
                currentFilter = 'active';
                rd();
                updateFilterBadges();
                $('#filters-dropdown-panel').addClass('d-none');
                $('#filters-toggle-btn').attr('aria-expanded', 'false');
            });

            function updateFilterBadges() {
                let list = $('#active-filters-list');
                list.empty();
                let count = 0;

                if ($('#status-filter').length) {
                    let softDeleteVal = $('#status-filter').val();
                    if (softDeleteVal) {
                        let softDeleteText = $('#status-filter option:selected').text().trim();
                        list.append(`
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1.5 px-3 rounded-pill">
                                Filter: ${softDeleteText}
                                <span class="ms-1 remove-filter-btn text-danger fw-bold" style="cursor: pointer; font-size: 1rem; line-height: 1;" data-filter="soft_delete">&times;</span>
                            </span>
                        `);
                        count++;
                    }
                }

                if (count > 0) {
                    $('#filters-btn-text').text(`Filters (${count})`);
                    $('#active-filters-container').attr('style', 'display: flex !important;');
                } else {
                    $('#filters-btn-text').text('Filters');
                    $('#active-filters-container').attr('style', 'display: none !important;');
                }
            }

            $(document).on('click', '.remove-filter-btn', function() {
                let filterType = $(this).data('filter');
                if (filterType === 'soft_delete') {
                    $('#status-filter').val('all');
                    currentFilter = 'all';
                }
                rd();
                updateFilterBadges();
            });

            $('#clear-all-filters').on('click', function() {
                $('#status-filter').val('active');
                currentFilter = 'active';
                rd();
                updateFilterBadges();
            });

            // Run initial update for badges
            updateFilterBadges();

            $(document).on('click', '.entry-btn', async function() {

                const id = $(this).data('id');

                const routeTemplate =
                    "{{ route('gatekeeper.visitor-logs.mark-entry', ['visitorLog' => '__ID__']) }}";

                $('#entryForm').attr('action', routeTemplate.replace('__ID__', id));
                capturedFile = null;
                $('#video').removeClass('d-none');
                $('#captureBtn').removeClass('d-none');
                $('#recaptureBtn').addClass('d-none');
                $('#preview').attr('src', '').addClass('d-none');
                $('#entryForm button[type="submit"]').prop('disabled', false);

                const modal = new bootstrap.Modal(document.getElementById('entryModal'));

                modal.show();

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true
                    });

                    document.getElementById('video').srcObject = stream;

                } catch (e) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Unable to access camera'
                    });
                }

            });

            $('#captureBtn').on('click', function() {

                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob(function(blob) {
                    capturedFile = new File([blob], `visitor_${Date.now()}.jpg`, {
                        type: 'image/jpeg'
                    });
                    $('#preview').attr('src', URL.createObjectURL(blob)).removeClass('d-none');
                    $('#video').addClass('d-none');
                    $('#captureBtn').addClass('d-none');
                    $('#recaptureBtn').removeClass('d-none');
                    $('#entryForm button[type="submit"]').prop('disabled', false);
                }, 'image/jpeg', 0.9);

                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
            });

            $('#recaptureBtn').on('click', async function() {

                capturedFile = null;
                $('#preview').attr('src', '').addClass('d-none');
                $('#video').removeClass('d-none');
                $('#captureBtn').removeClass('d-none');
                $('#recaptureBtn').addClass('d-none');
                $('#entryForm button[type="submit"]').prop('disabled', false);

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true
                    });
                    document.getElementById('video').srcObject = stream;
                } catch (e) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Unable to access camera'
                    });
                }
            });

            $('#entryModal').on('hidden.bs.modal', function() {
                capturedFile = null;
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }

                $('#preview')
                    .attr('src', '')
                    .addClass('d-none');

                $('#video').removeClass('d-none');
                $('#captureBtn').removeClass('d-none');
                $('#recaptureBtn').addClass('d-none');

                $('#entryForm button[type="submit"]')
                    .prop('disabled', false);
            });

            $('#entryForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData();
                formData.append('_token', $('input[name="_token"]').val());
                formData.append('_method', 'PATCH');
                if (capturedFile) {
                    formData.append('photo', capturedFile);
                }

                $('#entryForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        bootstrap.Modal.getInstance(document.getElementById('entryModal'))
                        .hide();
                        rd();
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    },
                    error: function(xhr) {
                        $('#entryForm button[type="submit"]').prop('disabled', false);
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message ?? 'Failed to mark entry'
                        });
                    }
                });
            });

        });
</script>
@endpush