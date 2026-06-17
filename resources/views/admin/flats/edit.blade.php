@extends('layouts.app')

@section('content')

<h3>Edit Flat</h3>

<form method="POST" action="{{ route('flats.update', $flat->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-2">
        <label>Wing</label>
        <input type="text" name="wing" value="{{ $flat->wing }}" class="form-control">
    </div>

    <div class="mb-2">
        <label>Floor</label>
        <input type="number" name="floor" value="{{ $flat->floor }}" class="form-control">
    </div>

    <div class="mb-2">
        <label>Flat Number</label>
        <input type="text" name="flat_number" value="{{ $flat->flat_number }}" class="form-control">
    </div>

    <button class="btn btn-primary">Update</button>
</form>
@endsection