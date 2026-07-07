
@extends('layouts.app')

@section('title', 'Edit Wing')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">Edit Wing</h5>
        </div>

        <form method="POST" action="{{ route('wings.update', $wing) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Wing Name</label>
                        <input name="name" value="{{ old('name', $wing->name) }}" class="form-control @error('name') is-invalid @enderror" />
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total Floors</label>
                        <input name="total_floors" value="{{ old('total_floors', $wing->total_floors) }}" type="number" class="form-control @error('total_floors') is-invalid @enderror" />
                        @error('total_floors')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Flats per Floor</label>
                        <input name="flats_per_floor" value="{{ old('flats_per_floor', $wing->flats_per_floor) }}" type="number" class="form-control @error('flats_per_floor') is-invalid @enderror" />
                        @error('flats_per_floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Society</label>
                        @if(auth()->user()->isSuperAdmin())
                            <select name="society_id" class="form-select @error('society_id') is-invalid @enderror">
                                <option value="">Select Society</option>
                                @foreach ($societies as $society)
                                    <option value="{{ $society->id }}" {{ old('society_id', $wing->society_id) == $society->id ? 'selected' : '' }}>{{ $society->name }}</option>
                                @endforeach
                            </select>
                        @else
                            @php $adminSociety = $societies->first(); @endphp
                            <select class="form-select" disabled>
                                <option>{{ $adminSociety->name ?? 'N/A' }}</option>
                            </select>
                            <input type="hidden" name="society_id" value="{{ $adminSociety->id ?? '' }}" />
                        @endif
                        @error('society_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('wings.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Wing</button>
            </div>
        </form>
    </div>

@endsection
