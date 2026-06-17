@extends('layouts.app')

@section('title', 'Complaint Details')

@section('content')

<div class="mb-3">
    @if(in_array(auth()->user()->role->name, ['admin', 'super_admin']))
    <a href="{{ route('admin.complaints.index') }}" class="btn btn-primary">
        ← Back to Complaints
    </a>
    @else
    <a href="{{ route('complaints.index') }}" class="btn btn-primary">
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

@canany(['is-admin', 'is-superadmin'])
    <div class="mt-3">
            <a href="{{ route('admin.complaints.edit', $complaint) }}" class="btn btn-warning">
                Edit Complaint
            </a>
        </div>
@endcanany

@endsection
