@extends('layouts.app')

@section('title', 'Edit Visitor Pass')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold text-dark">Edit Visitor Pass</h5>
    </div>

    <form method="POST" action="{{ route('passes.update', $visitorLog->id) }}">
        @csrf
        @method('PUT')

        <div class="card-body p-4">

            <div class="row g-3">

                @if (!auth()->user()->isResident())
                <div class="col-md-6">
                    <label class="form-label">Flat</label>
                    <select id="flat_input" name="flat_id" class="form-select @error('flat_id') is-invalid @enderror">
                        <option value="">Select Flat</option>
                        @foreach ($flats as $id => $label)
                        <option value="{{ $id }}" @selected(old('flat_id', $visitorLog->flat_id) == $id)>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @error('flat_id')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label">Visitor Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $visitorLog->visitor->name) }}">
                    @error('name')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone', $visitorLog->visitor->phone) }}">
                    @error('phone')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror"
                        value="{{ old('purpose', $visitorLog->purpose) }}">
                    @error('purpose')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Visit Date</label>
                    <input type="date" name="visit_date" class="form-control @error('visit_date') is-invalid @enderror"
                        value="{{ old('visit_date', \Carbon\Carbon::parse($visitorLog->visit_date)->format('Y-m-d')) }}">
                    @error('visit_date')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Vehicle Number (Optional)</label>
                    <input type="text" name="vehicle_number"
                        class="form-control @error('vehicle_number') is-invalid @enderror"
                        value="{{ old('vehicle_number', $visitorLog->visitor->vehicle_number) }}">
                    @error('vehicle_number')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

            </div>

        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
            @if (auth()->user()->isGatekeeper())
            <a href="{{ route('gatekeeper.visitor-logs.pending') }}" class="btn btn-light">
                Cancel
            </a>
            @else
            <a href="{{ route('passes.index') }}" class="btn btn-light">
                Cancel
            </a>
            @endif
            <button type="submit" class="btn btn-primary">
                Update Pass
            </button>
        </div>

    </form>

</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
            $('#flat_input').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Flat',
                allowClear: true,
                width: '100%'
            });
        });
</script>
@endpush
