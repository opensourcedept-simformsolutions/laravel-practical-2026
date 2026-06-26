@extends('layouts.app')

@section('title', 'Edit Delivery')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">Edit Delivery</h5>
        </div>

        <form action="{{ route('deliveries.update', $delivery) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body p-4">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="resident_id" class="form-label">Resident</label>
                        <select name="resident_id" id="resident_id" class="form-select @error('resident_id') is-invalid @enderror">
                            <option value="">Select Resident</option>
                            @foreach ($residentOptions as $id => $label)
                                <option value="{{ $id }}" @selected(old('resident_id', $delivery->resident_id) == $id)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('resident_id')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="">Select Status</option>
                                <option value="{{ \App\Enums\DeliveryStatus::RECEIVED->value }}" @selected(old('status', $delivery->status) == \App\Enums\DeliveryStatus::RECEIVED->value)>Received</option>
                                <option value="{{ \App\Enums\DeliveryStatus::DELIVERED->value }}" @selected(old('status', $delivery->status) == \App\Enums\DeliveryStatus::DELIVERED->value)>Delivered</option>
                            </select>
                            @error('status')
                                <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label for="vendor" class="form-label">Vendor</label>
                        <input type="text" name="vendor" id="vendor" class="form-control @error('vendor') is-invalid @enderror" value="{{ old('vendor', $delivery->vendor) }}">
                        @error('vendor')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="package_details" class="form-label">Package Details</label>
                        <textarea name="package_details" id="package_details" rows="4" class="form-control @error('package_details') is-invalid @enderror">{{ old('package_details', $delivery->package_details) }}</textarea>
                        @error('package_details')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('deliveries.index') }}" class="btn btn-light">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Update Delivery
                </button>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
    <script>
        $(function () {
            $('#resident_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search Resident',
                width: '100%'
            });
        });

        $(function () {
            $('#status').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search status',
                width: '100%'
            });
        });
    </script>
@endpush
