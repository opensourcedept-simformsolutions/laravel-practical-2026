@extends('layouts.app')

@section('title', 'Flats')

@section('content')

    <div class="d-flex justify-content-between mb-3">
        <h3>Flats</h3>

        <a href="{{ route('flats.create') }}" class="btn btn-primary">
            + Add Flat
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered" id="flatsTable">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Wing</th>
                <th>Floor</th>
                <th>Flat Number</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($flats as $flat)
                <tr>
                    <td>{{ $flat->id }}</td>
                    <td>{{ $flat->wing }}</td>
                    <td>{{ $flat->floor }}</td>
                    <td>{{ $flat->flat_number }}</td>
                    <td>

                        <a href="{{ route('flats.edit', $flat->id) }}" class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('flats.destroy', $flat->id) }}" class="d-inline">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this flat?')">
                                Delete
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#flatsTable').DataTable();
        });
    </script>
@endpush
