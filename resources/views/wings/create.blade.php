
@extends('layouts.app')

@section('title', 'Create Wing')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-dark">Create Wing</h5>
                </div>
                <div class="col-auto">
                    <a href="{{ route('wings.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('wings.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Wing Name</label>
                        <input name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" />
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total Floors</label>
                        <input name="total_floors" value="{{ old('total_floors', 1) }}" type="number" class="form-control @error('total_floors') is-invalid @enderror" />
                        @error('total_floors')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Flats per Floor</label>
                        <input name="flats_per_floor" value="{{ old('flats_per_floor', 1) }}" type="number" class="form-control @error('flats_per_floor') is-invalid @enderror" />
                        @error('flats_per_floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Society</label>
                        @if(auth()->user()->isSuperAdmin())
                            <select name="society_id" class="form-select @error('society_id') is-invalid @enderror">
                                <option value="">Select Society</option>
                                @foreach ($societies as $s)
                                    <option value="{{ $s->id }}" @selected(old('society_id') == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        @else
                            {{-- show user's society but send value via hidden input --}}
                            @php $s = $societies->first(); @endphp
                            <select class="form-select" disabled>
                                <option>{{ $s->name ?? 'N/A' }}</option>
                            </select>
                            <input type="hidden" name="society_id" value="{{ $s->id ?? '' }}" />
                        @endif
                        @error('society_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">Create Wing</button>
                </div>
            </form>
        </div>
    </div>

@endsection
