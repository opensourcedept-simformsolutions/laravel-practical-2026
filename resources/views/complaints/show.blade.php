@extends('layouts.app')

@section('title', 'Complaint Details')

@section('content')

<div class="mb-3">
    @if(in_array(auth()->user()->role->name, ['admin', 'super_admin']))
    <a href="{{ route('admin.complaints.index') }}">
        ← Back to Complaints
    </a>
    @else
    <a href="{{ route('complaints.index') }}">
        ← Back to Complaints
    </a>
    @endif

</div>

@if(session('success')) <div class="alert alert-success">
    {{ session('success') }} </div>
@endif

<x-form.form-section title="Complaint #{{ $complaint->id }}">

    <x-form.fieldset legend="Complaint Information">

        <div class="mb-3">
            <strong>Category:</strong>
            {{ ucfirst($complaint->category) }}
        </div>

        <div class="mb-3">
            <strong>Description:</strong>
            <br>
            {{ $complaint->description }}
        </div>

        <div class="mb-3">
            <strong>Status:</strong>

            @if($complaint->status == 'open')
            Open
            @elseif($complaint->status == 'in_progress')
            In Progress
            @else
            Resolved
            @endif
        </div>

        <div class="mb-3">
            <strong>Created At:</strong>
            {{ $complaint->created_at->format('d M Y h:i A') }}
        </div>

        <div class="mb-3">
            <strong>Admin Notes:</strong>
            <div>
                {{ $complaint->admin_notes ?? 'No notes added yet.' }}
            </div>
        </div>

    </x-form.fieldset>

</x-form.form-section>

@if(in_array(auth()->user()->role->name, ['admin', 'super_admin']))

<x-form.form :action="route('admin.complaints.update', $complaint)" method="POST" class="form-contained">

    @method('PATCH')

    <x-form.form-section title="Update Complaint">

        <x-form.fieldset legend="Complaint Status">

            <x-form.field name="status" label="Status" required>

                <select name="status" id="status" class="form-control">

                    <option value="open" {{ $complaint->status == 'open' ? 'selected' : '' }}>
                        Open
                    </option>

                    <option value="in_progress" {{ $complaint->status == 'in_progress' ? 'selected' : '' }}>
                        In Progress
                    </option>

                    <option value="resolved" {{ $complaint->status == 'resolved' ? 'selected' : '' }}>
                        Resolved
                    </option>

                </select>

            </x-form.field>

            <x-form.field name="admin_notes" label="Admin Notes">

                <textarea name="admin_notes" id="admin_notes" rows="5"
                    class="form-control">{{ old('admin_notes', $complaint->admin_notes) }}</textarea>

            </x-form.field>

            <div class="d-flex justify-content-end">

                <x-form.submit-button>
                    Update Complaint
                </x-form.submit-button>

            </div>

        </x-form.fieldset>

    </x-form.form-section>

</x-form.form>

@endif

@endsection
