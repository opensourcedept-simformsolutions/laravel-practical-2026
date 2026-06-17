@extends('layouts.app')

@section('title', 'Complaints')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        @if(auth()->user()->role->name === 'super_admin')
        <h2>All Complaints</h2>
        @elseif(auth()->user()->role->name === 'admin')
        <h2>Society Complaints</h2>
        @else
        <h2>My Complaints</h2>
        @endif
    </div>

    @if(in_array(auth()->user()->role->name, ['resident', 'gatekeeper']))
    <a href="{{ route('complaints.create') }}" class="btn btn-primary">
        + Create Complaint
    </a>
    @endif

</div>

@if(session('success')) <div class="alert alert-success">
    {{ session('success') }} </div>
@endif

<x-form.form method="GET">
    <x-form.form-section title="Filter Complaints">

        <div class="row">

            <div class="col-md-4">
                <x-form.field name="category" label="Category">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="security" {{ request('category')=='security' ? 'selected' : '' }}>
                            Security
                        </option>
                        <option value="cleaning" {{ request('category')=='cleaning' ? 'selected' : '' }}>
                            Cleaning
                        </option>
                        <option value="water" {{ request('category')=='water' ? 'selected' : '' }}>
                            Water
                        </option>
                        <option value="parking" {{ request('category')=='parking' ? 'selected' : '' }}>
                            Parking
                        </option>
                    </select>
                </x-form.field>
            </div>

            <div class="col-md-4">
                <x-form.field name="status" label="Status">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status')=='open' ? 'selected' : '' }}>
                            Open
                        </option>
                        <option value="in_progress" {{ request('status')=='in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="resolved" {{ request('status')=='resolved' ? 'selected' : '' }}>
                            Resolved
                        </option>
                    </select>
                </x-form.field>
            </div>

            <div class="col-md-4">
                <x-form.field name="date" label="Date">
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </x-form.field>
            </div>

        </div>

        <div class="mt-3">
            <x-form.submit-button>
                Filter
            </x-form.submit-button>
        </div>

    </x-form.form-section>

</x-form.form>

@if($complaints->count())

<x-table>

    <thead>
        <tr>
            <th>#</th>

            @if(auth()->user()->role->name === 'super_admin')
            <th>Society</th>
            @endif

            <th>Category</th>
            <th>Description</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

        @foreach($complaints as $complaint)

        <tr>

            <td>{{ $complaint->id }}</td>

            @if(auth()->user()->role->name === 'super_admin')
            <td>{{ $complaint->user->society->name ?? 'N/A' }}</td>
            @endif

            <td>{{ ucfirst($complaint->category) }}</td>

            <td>
                {{ Str::limit($complaint->description, 50) }}
            </td>

            <td>
                {{ str_replace('_', ' ', ucfirst($complaint->status)) }}
            </td>

            <td>
                {{ $complaint->created_at->format('d M Y') }}
            </td>

            <td>

                @if(in_array(auth()->user()->role->name, ['admin', 'super_admin']))
                <a href="{{ route('admin.complaints.show', $complaint) }}">
                    View
                </a>
                @else
                <a href="{{ route('complaints.show', $complaint) }}">
                    View
                </a>
                @endif

            </td>

        </tr>

        @endforeach

    </tbody>

</x-table>

@else

<div class="alert alert-info">
    No complaints found.
</div>

@endif

@endsection
