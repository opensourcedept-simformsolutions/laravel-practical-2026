@extends('layouts.app')

@section('title', 'Visitor Passes')

@section('content')

<div class="container py-4">

    <h2 class="mb-4">
        Pending Visitor Passes
    </h2>

    <div class="card">
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Visitor Pass List</span>

            <div class="d-flex gap-2">

                <a href="{{ route('gatekeeper.visitor-logs.exited') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Exited Visitors
                </a>

                <a href="{{ route('passes.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-square me-1"></i>
                    Create Pass
                </a>

            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                <table id="visitorLogsTable" class="table table-bordered table-striped w-100">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Visitor</th>
                            <th>Phone</th>
                            <th>Flat</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="entryModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form id="entryForm" method="POST">

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

                    <video id="video" autoplay playsinline width="100%" class="border rounded">
                    </video>

                    <canvas id="canvas" style="display:none;">
                    </canvas>

                    <img id="preview" class="img-thumbnail mt-3 d-none" width="250">

                    <input type="hidden" name="photo" id="photo">

                    <div class="mt-3">

                        <button type="button" class="btn btn-primary" id="captureBtn">

                            Capture Photo

                        </button>

                    </div>

                </div>

                <div class="modal-footer">

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
    $(function () {

    let stream = null;

    $('#visitorLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('gatekeeper.visitor-logs.pending') }}",

        columns: [
            {
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

    $(document).on('click', '.entry-btn', async function () {

        const id = $(this).data('id');

        const routeTemplate =
            "{{ route('gatekeeper.visitor-logs.mark-entry', ['visitorLog' => '__ID__']) }}";

        $('#entryForm').attr(
            'action',
            routeTemplate.replace('__ID__', id)
        );

        $('#photo').val('');

        $('#preview')
            .attr('src', '')
            .addClass('d-none');

        $('#entryForm button[type="submit"]')
            .prop('disabled', true);

        const modal = new bootstrap.Modal(
            document.getElementById('entryModal')
        );

        modal.show();

        try {

            stream = await navigator.mediaDevices.getUserMedia({
                video: true
            });

            document.getElementById('video').srcObject = stream;

        } catch (e) {

            alert('Unable to access camera');

        }

    });

    $('#captureBtn').on('click', function () {

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const ctx = canvas.getContext('2d');

        ctx.drawImage(
            video,
            0,
            0,
            canvas.width,
            canvas.height
        );

        const image = canvas.toDataURL('image/jpeg');

        $('#photo').val(image);

        $('#preview')
            .attr('src', image)
            .removeClass('d-none');

        $('#entryForm button[type="submit"]')
            .prop('disabled', false);

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    });

    $('#entryModal').on('hidden.bs.modal', function () {

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        $('#photo').val('');

        $('#preview')
            .attr('src', '')
            .addClass('d-none');

        $('#entryForm button[type="submit"]')
            .prop('disabled', true);
    });

});
</script>
@endpush
