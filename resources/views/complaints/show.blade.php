@extends('layouts.app')

@section('title', 'Complaint Details')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">

                <h4 class="mb-0">
                    Complaint #{{ $complaint->id }}
                </h4>

                <a href="{{ route('complaints.index') }}" class="btn btn-light btn-sm">
                    Back
                </a>

            </div>

            {{-- Main Card --}}
            <div class="card shadow-sm border-0">

                <div class="card-body">

                    {{-- Top Summary --}}
                    <div class="d-flex justify-content-between align-items-start mb-4">

                        <div>
                            <h5 class="mb-1">
                                {{ ucfirst($complaint->category) }}
                            </h5>

                            <div class="text-muted">
                                Complaint Category
                            </div>
                        </div>

                        <div class="text-end">

                            @php
                            $badgeClass = match($complaint->status) {
                                'resolved' => 'bg-success',
                                'in_progress' => 'bg-warning',
                                default => 'bg-danger',
                            };
                            @endphp

                            <span class="badge {{ $badgeClass }}">
                                {{ ucwords(str_replace('_', ' ', $complaint->status)) }}
                            </span>

                            <div class="small text-muted mt-1">
                                Created:
                                <strong>
                                    {{ $complaint->created_at->format('d M Y') }}
                                </strong>
                            </div>

                        </div>

                    </div>

                    <hr>

                    {{-- Details --}}
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <div class="text-muted small">
                                    Category
                                </div>

                                <div class="fw-semibold">
                                    {{ ucfirst($complaint->category) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <div class="text-muted small">
                                    Status
                                </div>

                                <div class="fw-semibold">
                                    {{ ucwords(str_replace('_', ' ', $complaint->status)) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="p-3 bg-light rounded">
                                <div class="text-muted small">
                                    Description
                                </div>

                                <div class="fw-semibold">
                                    {{ $complaint->description }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="p-3 bg-light rounded">
                                <div class="text-muted small">
                                    Admin Notes
                                </div>

                                <div class="fw-semibold">
                                    {{ $complaint->admin_notes ?: 'No notes added yet.' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <div class="text-muted small">
                                    Created At
                                </div>

                                <div class="fw-semibold">
                                    {{ $complaint->created_at->format('d M Y h:i A') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <div class="text-muted small">
                                    Last Updated
                                </div>

                                <div class="fw-semibold">
                                    {{ $complaint->updated_at->format('d M Y h:i A') }}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                {{-- Footer Actions --}}
                <div class="card-footer bg-white border-top-0 text-end">

                    @can('update', $complaint)
                    <a href="{{ route('complaints.edit', $complaint) }}" class="btn btn-warning">
                        Edit Complaint
                    </a>
                    @endcan

                    @can('delete', $complaint)
                    <form action="{{ route('complaints.destroy', $complaint) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this complaint?');">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">
                            Delete Complaint
                        </button>
                    </form>
                    @endcan

                </div>

            </div>

        </div>
    </div>
</div>

@endsection
