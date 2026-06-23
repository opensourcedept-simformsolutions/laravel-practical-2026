@extends('layouts.app')

@section('title', 'Scan Visitor Pass')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="card shadow">

                    <div class="card-header">
                        <h5 class="mb-0">
                            Scan Visitor Pass
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-lg-6">

                                <div id="reader"></div>

                                <div class="text-center mt-3">
                                    <button id="scan-again-btn" class="btn btn-primary d-none">
                                        Scan Again
                                    </button>
                                </div>

                            </div>

                            <div class="col-lg-6">

                                <div id="visitor-data">

                                    <div class="alert alert-info">
                                        Point the camera at a visitor QR code.
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
@push('scripts')

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>

let scanner;

function startScanner() {

    scanner = new Html5Qrcode("reader");

    scanner.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: 500
        },
        function(decodedText) {

            scanner.stop();

            fetch("{{ route('gatekeeper.find-pass') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    qr_code: decodedText
                })
            })
            .then(res => res.json())
            .then(res => {

                $('#visitor-data').html(`
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5>${res.visitor}</h5>
                            <p>${res.phone}</p>
                            <p>${res.flat}</p>
                            <p>${res.purpose}</p>

                            <button class="btn btn-success"
                                onclick="markEntry(${res.id})">
                                Mark Entry
                            </button>
                        </div>
                    </div>
                `);

            })
            .catch(() => {
                $('#visitor-data').html(`
                    <div class="alert alert-danger">
                        Invalid QR Code
                    </div>
                `);
            });

        }
    ).catch(err => {
        console.error("Camera error:", err);
    });
}

function markEntry(id)
{
    $.ajax({
        url: '/gatekeeper/mark-entry/' + id,
        method: 'POST',
        success: function(res) {
            alert(res.message);
            $('#visitor-data').html('');
            startScanner();
        },
        error: function(xhr) {
            alert(xhr.responseJSON.message);
        }
    });
}

$(document).ready(function () {
    startScanner();
});

</script>

@endpush
