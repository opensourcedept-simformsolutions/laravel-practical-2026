@extends('layouts.app')

@section('title', 'Edit Resident')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">

                        <div class="card shadow">

                            <div class="card-header bg-white border-bottom-0">
                                <h5 class="mb-0">Edit Resident</h5>
                            </div>

                            <form method="POST" action="{{ route('residents.update', $resident->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="card-body">

                                    <p class="text-secondary mb-3" style="text-decoration:underline">
                                        Resident Details
                                    </p>

                                    <div class="row">
                                        @if (auth()->user()->isSuperAdmin())
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Society</label>

                                                    <select name="society_id" id="society_id" class="form-select">
                                                        <option value="">Select Society</option>
                                                        @foreach ($societies as $society)
                                                            <option value="{{ $society->id }}"
                                                                  {{ old('society_id', $resident->flat->society_id) == $society->id ? 'selected' : '' }}>
                                                                   {{ $society->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('society_id')
                                                        <div class="text-danger pt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control"
                                                    value="{{ old('name', $resident->user->name) }}">

                                                @error('name')
                                                    <div class="text-danger pt-1">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control"
                                                    value="{{ old('email', $resident->user->email) }}">

                                                @error('email')
                                                    <div class="text-danger pt-1">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" name="phone" class="form-control"
                                                    value="{{ old('phone', $resident->user->phone) }}">

                                                @error('phone')
                                                    <div class="text-danger pt-1">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Flat</label>
                                                <select id="flat_id" name="flat_id" class="form-select">
                                                    <option value="">Select Flat</option>
                                                    @foreach ($flats as $flat)
                                                        <option value="{{ $flat->id }}"
                                                            {{ old('flat_id', $resident->flat_id) == $flat->id ? 'selected' : '' }}>
                                                            {{ $flat->wing }}-{{ $flat->flat_number }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                @error('flat_id')
                                                    <div class="text-danger pt-1">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Resident Type</label>

                                                <select name="resident_type" class="form-select">
                                                    <option value="owner"
                                                        {{ old('resident_type', $resident->resident_type) == 'owner' ? 'selected' : '' }}>
                                                        Owner
                                                    </option>

                                                    <option value="tenant"
                                                        {{ old('resident_type', $resident->resident_type) == 'tenant' ? 'selected' : '' }}>
                                                        Tenant
                                                    </option>
                                                </select>

                                                @error('resident_type')
                                                    <div class="text-danger pt-1">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="card-footer bg-white d-flex justify-content-end border-top-0">

                                    <a href="{{ route('residents.index') }}" class="btn btn-light me-2">
                                        Cancel
                                    </a>

                                    <button type="submit" class="btn btn-primary">
                                        Update Resident
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const selectedFlat = {{ old('flat_id', $resident->flat_id) }};

            $("#flat_id").select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Status',
                allowClear: true,
                width: '100%'
            });

            $('#society_id').on('change', function() {

                let societyId = $(this).val();

                if (!societyId) {
                    $('#flat_id').html('<option value="">Select Flat</option>');
                    return;
                }

                $.ajax({
                    url: "/societies/" + societyId + "/flats",
                    type: 'GET',
                    success: function(response) {

                        if (!response.success) {
                            return;
                        }

                        let options = '<option value="">Select Flat</option>';

                        response.data.forEach(function(flat) {

                            const selected = selectedFlat == flat.id
                                ? 'selected'
                                : '';

                            options += `
                                <option value="${flat.id}" ${selected}>
                                    ${flat.wing}-${flat.flat_number}
                                </option>
                            `;
                        });

                        $('#flat_id').html(options).trigger('change');
                    },
                    error: function(xhr) {
                        $('#flat_id').html('<option value="">Error loading flats</option>');

                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message ?? 'Failed to load flats.'
                        });
                    }
                });

            });

        });
    </script>
@endpush
