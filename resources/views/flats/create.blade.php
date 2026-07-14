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
                        <input type="text" name="wing" class="form-control @error('wing') is-invalid @enderror"
                            value="{{ old('wing') }}">
                        @error('wing')
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

                    wing: {
                        required: true,
                        maxlength: 20,
                        pattern: /^[A-Za-z0-9]+$/
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

                    wing: {
                        required: "Wing is required.",
                        maxlength: "Wing may not exceed 20 characters.",
                        pattern: "Wing must be alphanumeric only."
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

        });
    </script>
@endpush
