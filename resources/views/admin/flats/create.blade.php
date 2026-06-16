@extends('layouts.app')

@section('content')

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