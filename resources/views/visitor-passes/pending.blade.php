@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">Visitor Pass List</h5>

        <div class="d-flex gap-2">
            <a href="{{ route('gatekeeper.visitor-logs.exited') }}" class="btn btn-info btn-sm">
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

    <div class="card-body">

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

            table = $('#visitorLogsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('gatekeeper.visitor-logs.pending') }}",

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
                $('#entryForm button[type="submit"]').prop('disabled', true);

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
                $('#entryForm button[type="submit"]').prop('disabled', true);

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
                    .prop('disabled', true);
            });

            $('#entryForm').on('submit', function(e) {
                e.preventDefault();

                if (!capturedFile) {

                    Toast.fire({
                        icon: 'warning',
                        title: 'Please capture a photo first'
                    });

                    return;
                }

                const formData = new FormData();
                formData.append('_token', $('input[name="_token"]').val());
                formData.append('_method', 'PATCH');
                formData.append('photo', capturedFile);

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
