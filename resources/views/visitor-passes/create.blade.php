@extends('layouts.app')

@section('title', 'Create Visitor Pass')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold text-dark">Create Visitor Pass</h5>
    </div>

    <form id="visitorPassForm" method="POST" action="{{ route('passes.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="card-body p-4">

            <div class="row g-3">

                @if (!auth()->user()->isResident())
                <div class="col-md-6">
                    <label class="form-label">Flat</label>
                    <select id="flat_input" name="flat_id" class="form-select @error('flat_id') is-invalid @enderror">
                        <option value="">Select Flat</option>
                        @foreach ($flats as $id => $label)
                        <option value="{{ $id }}" @selected(old('flat_id')==$id)>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @error('flat_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label">Visitor Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}">
                    @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}">
                    @error('phone')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror"
                        value="{{ old('purpose') }}">
                    @error('purpose')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if(!auth()->user()->isGatekeeper())
                <div class="col-md-6">
                    <label class="form-label">Visit Date</label>
                    <input type="date" name="visit_date" class="form-control @error('visit_date') is-invalid @enderror"
                        value="{{ old('visit_date', '') }}">
                    @error('visit_date')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label">Vehicle Number (Optional)</label>
                    <input type="text" name="vehicle_number" style="text-transform:uppercase"
                        class="form-control @error('vehicle_number') is-invalid @enderror"
                        value="{{ old('vehicle_number') }}">
                    @error('vehicle_number')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if (auth()->user()->isGatekeeper())
                <div class="col-12 mt-4">
                    <div class="card bg-light border-0 rounded-3">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-camera-fill text-primary"></i> Visitor Photograph
                            </h6>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Photo Source Option</label>
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="photo_source" id="source_upload" value="upload" checked>
                                            <label class="form-check-label" for="source_upload">Upload File</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="photo_source" id="source_camera" value="camera">
                                            <label class="form-check-label" for="source_camera">Use Webcam / Camera</label>
                                        </div>
                                    </div>

                                    <!-- Upload Container -->
                                    <div id="upload_container" class="photo-container">
                                        <div class="upload-zone border border-dashed rounded-3 p-4 text-center bg-white" id="dropzone" style="border-style: dashed !important; border-width: 2px !important; cursor: pointer;">
                                            <i class="bi bi-cloud-upload text-muted display-6"></i>
                                            <p class="mt-2 mb-1 text-secondary fw-medium">Drag & drop image here or click to upload</p>
                                            <span class="text-xs text-muted">Supports JPG, PNG (Max 5MB)</span>
                                            <input type="file" name="uploaded_photo" id="uploaded_photo_input" class="d-none" accept="image/*">
                                        </div>
                                    </div>

                                    <!-- Camera Container -->
                                    <div id="camera_container" class="photo-container d-none">
                                        <div class="camera-preview border rounded-3 overflow-hidden bg-black position-relative" style="aspect-ratio: 4/3; max-width: 400px; margin: 0 auto;">
                                            <video id="webcam_video" autoplay playsinline class="w-100 h-100" style="object-fit: cover;"></video>
                                            <canvas id="webcam_canvas" class="d-none" width="640" height="480"></canvas>
                                            <div class="position-absolute bottom-0 start-0 end-0 p-3 text-center" style="background: rgba(0,0,0,0.5); z-index: 10;">
                                                <button type="button" class="btn btn-primary btn-sm px-4" id="capture_btn">
                                                    <i class="bi bi-camera-fill me-1"></i> Capture Photo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex flex-column align-items-center justify-content-center">
                                    <label class="form-label fw-semibold align-self-start mb-3">Photo Preview</label>
                                    <div class="photo-preview-box border rounded-3 bg-white d-flex align-items-center justify-content-center overflow-hidden position-relative" style="width: 240px; height: 180px; aspect-ratio: 4/3;">
                                        <div id="preview_placeholder" class="text-center text-muted">
                                            <i class="bi bi-person-bounding-box display-4"></i>
                                            <p class="mb-0 mt-2 text-xs">No photograph selected</p>
                                        </div>
                                        <img id="image_preview" class="w-100 h-100 d-none" style="object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-2 end-2 d-none" id="clear_photo_btn" style="padding: 2px 6px;">
                                            <i class="bi bi-trash-fill fs-7"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="captured_photo" id="captured_photo_input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>

        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
            <a
                @if (auth()->user()->isGatekeeper())
                    href="{{ route('gatekeeper.visitor-logs.pending') }}"
                @else
                    href="{{ route('passes.index') }}" 
                @endif
                class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Pass
            </button>
        </div>

    </form>

</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($('#flat_input').length) {
            $('#flat_input').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Flat',
                allowClear: true,
                width: '100%'
            }).on('change', function() {
                $(this).valid();
            });
        }

        @if(auth()->user()->isGatekeeper())
            $('input[name="visit_date"]').attr('min', '{{ today()->format("Y-m-d") }}');
            $('input[name="visit_date"]').attr('max', '{{ today()->format("Y-m-d") }}');
        @else
            $('input[name="visit_date"]').attr(
                'min',
                new Date().toISOString().split('T')[0]
            );
        @endif

        $('input[name="vehicle_number"]').on('input', function () {
            this.value = this.value.toUpperCase();
        });

        $.validator.addMethod('minToday', function(value) {
            if (!value) {
                return true;
            }

            var inputDate = new Date(value);
            var today = new Date();
            today.setHours(0, 0, 0, 0);

            return inputDate >= today;
        }, "The visit date cannot be earlier than today.");

        var rules = {
            name: {
                required: true,
                minlength: 2,
                normalizer: function(value) {
                    return $.trim(value);
                },
                maxlength: 100,
                pattern: /^[\p{L}\s\.'\-]+$/u
            },
            phone: {
                required: true,
                pattern: /^[6-9][0-9]{9}$/
            },
            purpose: {
                required: true,
                minlength: 2,
                normalizer: function(value) {
                    return $.trim(value);
                },
                maxlength: 255
            },
            @if(!auth()->user()->isGatekeeper())
            visit_date: {
                required: true,
                dateISO: true,
                minToday: true
            },
            @endif
            vehicle_number: {
                maxlength: 20,
                normalizer: function(value) {
                    return $.trim(value);
                },
                pattern: /^([A-Z]{2}\s?\d{1,2}\s?[A-Z]{1,3}\s?\d{1,4}|\d{2}\s?BH\s?\d{4}\s?[A-Z]{1,2})$/i
            }
        };

        if ($('#flat_input').length) {
            rules.flat_id = { required: true };
        }

        $('#visitorPassForm').validate({
            rules: rules,
            messages: {
                flat_id: {
                    required: "Please select a flat."
                },
                name: {
                    required: "Please enter the visitor's name.",
                    minlength: "The visitor's name must be at least 2 characters.",
                    maxlength: "The visitor's name cannot exceed 100 characters.",
                    pattern: "The visitor's name may contain only letters, spaces, apostrophes ('), hyphens (-), and periods (.)."
                },
                phone: {
                    required: "Please enter the visitor's mobile number.",
                    pattern: "Please enter a valid 10-digit Indian mobile number."
                },
                purpose: {
                    required: "Please enter the purpose of the visit.",
                    minlength: "The purpose must be at least 2 characters.",
                    maxlength: "The purpose cannot exceed 255 characters."
                },
                visit_date: {
                    required: "Please select the visit date.",
                    dateISO: "Please enter a valid date.",
                    minToday: "The visit date cannot be earlier than today."
                },
                vehicle_number: {
                    maxlength: "The vehicle number cannot exceed 20 characters.",
                    pattern: "Please enter a valid Indian vehicle registration number."
                }
            },
            errorElement: 'div',
            errorClass: 'text-danger pt-1 fs-7',
            errorPlacement: function(error, element) {
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2-container'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            }
        });

        // Photo Capture & Upload handling
        let localStream = null;

        $('input[name="photo_source"]').on('change', function() {
            const source = $(this).val();
            if (source === 'upload') {
                $('#upload_container').removeClass('d-none');
                $('#camera_container').addClass('d-none');
                stopWebcam();
            } else {
                $('#camera_container').removeClass('d-none');
                $('#upload_container').addClass('d-none');
                startWebcam();
            }
            clearPhoto();
        });

        $('#dropzone').on('click', function() {
            $('#uploaded_photo_input').trigger('click');
        });

        $('#uploaded_photo_input').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    showPreview(event.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop events
        const dropzone = document.getElementById('dropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('bg-light');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('bg-light');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const file = dt.files[0];
                if (file) {
                    document.getElementById('uploaded_photo_input').files = dt.files;
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        showPreview(event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function startWebcam() {
            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(function(stream) {
                    localStream = stream;
                    const video = document.getElementById('webcam_video');
                    if (video) {
                        video.srcObject = stream;
                    }
                })
                .catch(function(err) {
                    console.error("Camera access failed: ", err);
                    alert("Could not access camera. Please make sure permissions are allowed.");
                    $('#source_upload').prop('checked', true).trigger('change');
                });
        }

        function stopWebcam() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
        }

        $('#capture_btn').on('click', function() {
            const video = document.getElementById('webcam_video');
            const canvas = document.getElementById('webcam_canvas');
            if (video && canvas) {
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataUrl = canvas.toDataURL('image/jpeg');
                $('#captured_photo_input').val(dataUrl);
                showPreview(dataUrl);
            }
        });

        $('#clear_photo_btn').on('click', function() {
            clearPhoto();
        });

        function showPreview(dataUrl) {
            $('#image_preview').attr('src', dataUrl).removeClass('d-none');
            $('#preview_placeholder').addClass('d-none');
            $('#clear_photo_btn').removeClass('d-none');
        }

        function clearPhoto() {
            $('#uploaded_photo_input').val('');
            $('#captured_photo_input').val('');
            $('#image_preview').addClass('d-none').attr('src', '');
            $('#preview_placeholder').removeClass('d-none');
            $('#clear_photo_btn').addClass('d-none');
        }

        // Stop camera if form is submitted or user leaves
        $('#visitorPassForm').on('submit', function() {
            stopWebcam();
        });
    });
</script>
@endpush