@extends('layouts.app')

@section('title', 'Edit Flat')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">Edit Flat</h5>
        </div>

        <form method="POST" action="{{ route('flats.update', $flat->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body p-4">

                <div class="row g-3">

                    @if (auth()->user()->isSuperAdmin())
                        <div class="col-md-6">
                            <label class="form-label">Society</label>
                            <select name="society_id" class="form-select @error('society_id') is-invalid @enderror">
                                <option value="">Select Society</option>
                                @foreach ($societies as $society)
                                    <option value="{{ $society->id }}" @selected(old('society_id', $flat->society_id) == $society->id)>
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
                        <input type="text" name="wing" class="form-control @error('wing') is-invalid @enderror" value="{{ old('wing', $flat->wing) }}">
                        @error('wing')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Floor</label>
                        <input type="number" name="floor" class="form-control @error('floor') is-invalid @enderror" value="{{ old('floor', $flat->floor) }}">
                        @error('floor')
                            <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Flat Number</label>
                        <input type="text" name="flat_number" class="form-control @error('flat_number') is-invalid @enderror" value="{{ old('flat_number', $flat->flat_number) }}">
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
                    Update Flat
                </button>
            </div>

        </form>

    </div>

@endsection
