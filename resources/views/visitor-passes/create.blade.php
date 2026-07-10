@extends('layouts.app')

@section('title', 'Create Visitor Pass')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold text-dark">Create Visitor Pass</h5>
    </div>

    <form id="visitorPassForm" method="POST" action="{{ route('passes.store') }}">
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

                <div class="col-md-6">
                    <label class="form-label">Visit Date</label>
                    <input type="date" name="visit_date" class="form-control @error('visit_date') is-invalid @enderror"
                        value="{{ old('visit_date') }}">
                    @error('visit_date')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Vehicle Number (Optional)</label>
                    <input type="text" name="vehicle_number" style="text-transform:uppercase"
                        class="form-control @error('vehicle_number') is-invalid @enderror"
                        value="{{ old('vehicle_number') }}">
                    @error('vehicle_number')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

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

        $('input[name="visit_date"]').attr(
            'min',
            new Date().toISOString().split('T')[0]
        );

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
            visit_date: {
                required: true,
                dateISO: true,
                minToday: true
            },
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
    });
</script>
@endpush