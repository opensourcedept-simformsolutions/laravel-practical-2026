@extends('layouts.app')

@section('title', 'Create Flat')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">Create Flat</h5>
        </div>

        <form method="POST" id="flatForm" action="{{ route('flats.store') }}">
            @csrf

            <div class="card-body p-4">

                <div class="row g-3">

                    @if (auth()->user()->isSuperAdmin())
                        <div class="col-md-6">
                            <label class="form-label">Society</label>
                            <select name="society_id" class="form-select @error('society_id') is-invalid @enderror">
                                <option value="">Select Society</option>
                                @foreach ($societies as $society)
                                    <option value="{{ $society->id }}" @selected(old('society_id') == $society->id)>
                                        {{ $society->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('society_id')
                                <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">Wing</label>
                        <select name="wing_id" id="wing_id" class="form-select @error('wing_id') is-invalid @enderror">
                            <option value="">Select Wing</option>
                            @foreach ($wings as $wing)
                                <option value="{{ $wing->id }}" @selected(old('wing_id') == $wing->id)>{{ $wing->name }}</option>
                            @endforeach
                        </select>
                        @error('wing_id')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Floor</label>
                        <input type="number" name="floor" class="form-control @error('floor') is-invalid @enderror"
                            value="{{ old('floor') }}">
                        @error('floor')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Flat Number</label>
                        <input type="text" name="flat_number"
                            class="form-control @error('flat_number') is-invalid @enderror"
                            value="{{ old('flat_number') }}">
                        @error('flat_number')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('flats.index') }}" class="btn btn-light">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Create Flat
                </button>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $("#flatForm").validate({
                rules: {

                    wing_id: {
                        required: true
                    },

                    floor: {
                        required: true,
                        digits: true,
                        min: 0,
                        max: 50
                    },

                    flat_number: {
                        required: true,
                        digits: true,
                        min: 1,
                        max: 9999
                    },

                    society_id: {
                        required: {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }}
                    }
                },
                messages: {

                    wing_id: {
                        required: "Wing is required."
                    },

                    floor: {
                        required: "Floor is required.",
                        digits: "Floor must be a number.",
                        min: "Floor must be at least 0.",
                        max: "Floor must be less than or equal to 50."
                    },

                    flat_number: {
                        required: "Flat number is required.",
                        digits: "Flat number must be numeric.",
                        min: "Flat number must be at least 1.",
                        max: "Flat number must be less than or equal to 9999."
                    },

                    society_id: {
                        required: "Society is required."
                    }
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                }
            });

            const wingsUrlTemplate = "{{ url('/societies') }}/SOC/wings";

            function loadWingsForSociety(societyId, selectedWingId = null) {
                const $wing = $('#wing_id');
                $wing.empty().append($('<option>', { value: '', text: 'Select Wing' }));

                if (!societyId) {
                    return;
                }

                $.get(wingsUrlTemplate.replace('SOC', societyId))
                    .done(function(data) {
                        data.forEach(function(w) {
                            const option = $('<option>', {
                                value: w.id,
                                text: w.name,
                            });

                            if (selectedWingId && selectedWingId.toString() === w.id.toString()) {
                                option.prop('selected', true);
                            }

                            $wing.append(option);
                        });
                    })
                    .fail(function() {
                        alert('Failed to load wings for selected society.');
                    });
            }

            const initialSocietyId = $('select[name="society_id"]').val();
            const initialWingId = '{{ old('wing_id') }}';
            if (initialSocietyId) {
                loadWingsForSociety(initialSocietyId, initialWingId);
            }

            $('#flatForm').on('change', 'select[name="society_id"]', function() {
                const societyId = $(this).val();
                loadWingsForSociety(societyId);
            });
        });
    </script>
@endpush
