@extends('layouts.app')

@section('title', 'Create Resident')

@section('content')

<div class="d-flex justify-content-between mb-3">
    <h3>Create Resident</h3>

    <a href="{{ route('residents.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">    
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('residents.store') }}" class="card p-3">
    @csrf

    {{-- NAME --}}
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name') }}"
               >
    </div>

    {{-- EMAIL --}}
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email') }}"
               >
    </div>

    {{-- PHONE --}}
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text"
               name="phone"
               class="form-control"
               value="{{ old('phone') }}"
               >
    </div>

    {{-- FLAT --}}
    <div class="mb-3">
        <label class="form-label">Flat</label>
        <select name="flat_id" class="form-control" >
            <option value="">Select Flat</option>
            @foreach($flats as $flat)
                <option value="{{ $flat->id }}">
                    {{ $flat->wing }} - {{ $flat->flat_number }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- TYPE --}}
    <div class="mb-3">
        <label class="form-label">Resident Type</label>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="resident_type" value="owner" checked>
            <label class="form-check-label">Owner</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="resident_type" value="tenant">
            <label class="form-check-label">Tenant</label>
        </div>
    </div>

    <button class="btn btn-success">
        Create Resident
    </button>

</form>

@endsection