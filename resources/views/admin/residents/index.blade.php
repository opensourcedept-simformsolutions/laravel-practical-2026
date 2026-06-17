@extends('layouts.app')

@section('title', 'Residents')

@section('content')

<div class="d-flex justify-content-between mb-3">
    <h3>Residents</h3>

    <a href="{{ route('residents.create') }}" class="btn btn-primary">
        + Add Resident
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<x-data-table id="residentsTable">

    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Flat</th>
            <th>Wing</th>
            <th>Type</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @foreach($residents as $resident)
            <tr>
                <td>{{ $resident->id }}</td>

                <td>{{ $resident->user->name }}</td>
                <td>{{ $resident->user->email }}</td>
                <td>{{ $resident->user->phone }}</td>

                <td>{{ $resident->flat->flat_number }}</td>
                <td>{{ $resident->flat->wing }}</td>

                <td>
                    @if($resident->resident_type === 'owner')
                        <span class="badge bg-success">Owner</span>
                    @else
                        <span class="badge bg-info">Tenant</span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('residents.edit', $resident->id) }}"
                       class="btn btn-warning btn-sm">
                        Edit
                    </a>

                    <form action="{{ route('residents.destroy', $resident->id) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Delete this resident?')">

                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm">
                            Delete
                        </button>

                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>

</x-data-table>

@endsection