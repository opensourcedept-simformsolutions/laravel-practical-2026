@extends('layouts.app')

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
    
    <h3>Add Flat</h3>

    <form method="POST" action="{{ route('flats.store') }}">
        @csrf

        <div class="mb-2">
            <label>Wing</label>
            <input type="text" name="wing" class="form-control">
        </div>

        <div class="mb-2">
            <label>Floor</label>
            <input type="number" name="floor" class="form-control">
        </div>

        <div class="mb-2">
            <label>Flat Number</label>
            <input type="text" name="flat_number" class="form-control">
        </div>

        <button class="btn btn-success">Save</button>
    </form>

@endsection
