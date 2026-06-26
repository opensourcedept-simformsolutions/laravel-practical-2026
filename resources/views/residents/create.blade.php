@extends('layouts.app')

@section('title', 'Create Resident')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">Create Resident</h5>
        </div>

        <form method="POST" action="{{ route('residents.store') }}">
            @csrf

            <div class="card-body p-4">

                <div class="row g-3">

                    @if (auth()->user()->isSuperAdmin())
                        <div class="col-md-6">
                            <label class="form-label">Society</label>
                            <select name="society_id" id="society_id" class="form-select @error('society_id') is-invalid @enderror">
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
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                        @error('name')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Flat</label>
                        <select id="flat_id" name="flat_id" class="form-select @error('flat_id') is-invalid @enderror">
                            <option value="">Select Flat</option>
                            @foreach ($flats as $flat)
                                <option value="{{ $flat->id }}" @selected(old('flat_id') == $flat->id)>
                                    {{ $flat->flat_number }} ({{ $flat->wing }})
                                </option>
                            @endforeach
                        </select>
                        @error('flat_id')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Resident Type</label>
                        <select name="resident_type" class="form-select @error('resident_type') is-invalid @enderror">
                            <option value="owner" @selected(old('resident_type') == 'owner')>Owner</option>
                            <option value="tenant" @selected(old('resident_type') == 'tenant')>Tenant</option>
                        </select>
                        @error('resident_type')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('residents.index') }}" class="btn btn-light">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Create Resident
                </button>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $("#flat_id").select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Flat',
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
                            options += `
                                <option value="${flat.id}">
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
