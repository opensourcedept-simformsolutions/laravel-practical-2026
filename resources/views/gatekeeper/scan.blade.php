@extends('layouts.app')

@section('title', 'Scan Visitor Pass')

@section('content')
  <div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-semibold">Scan Visitor Pass</span>
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

          <div id="camera-section" class="d-none mt-4">

            <video id="video" autoplay playsinline width="100%" class="border rounded">
            </video>

            <canvas id="canvas" style="display:none;">
            </canvas>

            <img id="preview" class="img-thumbnail mt-3 d-none" width="250">

            <div class="mt-3">

              <button type="button" class="btn btn-primary" id="captureBtn">
                Capture Photo
              </button>

              <button type="button" class="btn btn-warning d-none" id="recaptureBtn">
                Recapture
              </button>

              <button type="button" class="btn btn-success d-none" id="markEntryBtn">
                Mark Entry
              </button>

            </div>

          </div>
        </div>

      </div>

    </div>

  </div>

@endsection

@push('scripts')
  <script>
    
    let scanner;
    let stream = null;
    let capturedFile = null;
    let visitorLogId = null;

    function startScanner() {

      scanner = new Html5Qrcode("reader");

      scanner.start({
          facingMode: "environment"
        }, {
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

                           <button
                                class="btn btn-primary"
                                onclick="openCamera(${res.id})">
                                Capture Photo
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

    $('#markEntryBtn').on('click', function() {

      if (!capturedFile) {

        Toast.fire({
          icon: 'error',
          title: 'Please capture photo first'
        });

        return;
      }

      const formData = new FormData();

      formData.append(
        '_token',
        '{{ csrf_token() }}'
      );

      formData.append(
        'photo',
        capturedFile
      );

      $.ajax({
        url: '/gatekeeper/mark-entry/' +
          visitorLogId,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {

          Toast.fire({
            icon: 'success',
            title: res.message
          });

          $('#visitor-data').html(`
                <div class="alert alert-success">
                    Entry Marked Successfully
                </div>
            `);

          $('#camera-section')
            .addClass('d-none');

          startScanner();
        },
        error: function(xhr) {

          Toast.fire({
            icon: 'success',
            title: xhr.responseJSON.message
          });
        }
      });
    });

    async function openCamera(id) {
      visitorLogId = id;

      $('#camera-section').removeClass('d-none');

      $('#preview')
        .attr('src', '')
        .addClass('d-none');
      $('#video').removeClass('d-none');
      $('#captureBtn').removeClass('d-none');
      $('#recaptureBtn').addClass('d-none');
      $('#markEntryBtn').addClass('d-none');

      capturedFile = null;

      try {

        stream =
          await navigator.mediaDevices
          .getUserMedia({
            video: true
          });

        document
          .getElementById('video')
          .srcObject = stream;

      } catch (e) {
        Toast.fire({
          icon: 'error',
          title: 'Unable to access camera'
        });
      }
    }

    $('#captureBtn').on('click', function() {

      const video =
        document.getElementById('video');

      const canvas =
        document.getElementById('canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;

      const ctx =
        canvas.getContext('2d');

      ctx.drawImage(
        video,
        0,
        0,
        canvas.width,
        canvas.height
      );

      canvas.toBlob(function(blob) {

        capturedFile = new File(
          [blob],
          `visitor_${Date.now()}.jpg`, {
            type: 'image/jpeg'
          }
        );
        $('#preview')
          .attr(
            'src',
            URL.createObjectURL(blob)
          ).removeClass('d-none');
        $('#video').addClass('d-none');
        $('#captureBtn').addClass('d-none');
        $('#recaptureBtn').removeClass('d-none');
        $('#markEntryBtn').removeClass('d-none');
      }, 'image/jpeg', 0.9);

      if (stream) {
        stream
          .getTracks()
          .forEach(track => track.stop());

        stream = null;
      }
    });
    $('#recaptureBtn').on('click', async function() {

      capturedFile = null;

      $('#preview')
        .attr('src', '')
        .addClass('d-none');
      $('#video').removeClass('d-none');
      $('#captureBtn').removeClass('d-none');
      $('#recaptureBtn').addClass('d-none');
      $('#markEntryBtn').addClass('d-none');

      try {

        stream =
          await navigator.mediaDevices
          .getUserMedia({
            video: true
          });

        document
          .getElementById('video')
          .srcObject = stream;

      } catch (e) {
        Toast.fire({
          icon: 'error',
          title: 'Unable to access camera'
        });
      }
    });

    $(document).ready(function() {
      startScanner();
    });
  </script>
@endpush
