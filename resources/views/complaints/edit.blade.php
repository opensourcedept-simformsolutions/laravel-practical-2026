@extends('layouts.app')

@section('title', 'Edit Complaint')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold text-dark">Update Complaint</h5>
    </div>

    <form id="complaintForm" method="POST" action="{{ route('complaints.update', $complaint) }}">
        @csrf
        @method('PATCH')

        <div class="card-body p-4">

            @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <p class="text-secondary mb-3 text-decoration-underline fw-semibold">
                Complaint Status
            </p>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    @php
                    $currentStatus = old('status', $complaint->status);
                    @endphp

                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">

                        @if($currentStatus === 'open')
                        <option value="open" selected>Open</option>
                        <option value="in_progress">In Progress</option>

                        @elseif($currentStatus === 'in_progress')
                        <option value="in_progress" selected>In Progress</option>
                        <option value="resolved">Resolved</option>

                        @elseif($currentStatus === 'resolved')
                        <option value="resolved" selected>Resolved</option>
                        @endif

                    </select>
                    @error('status')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label id="admin_notes" class="form-label">Admin Notes</label>
                    <textarea name="admin_notes" rows="5"
                        class="form-control @error('admin_notes') is-invalid @enderror">{{ old('admin_notes', $complaint->admin_notes) }}</textarea>
                    @error('admin_notes')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

            </div>
            @else
            <p class="text-secondary mb-3 text-decoration-underline fw-semibold">
                Complaint Details
            </p>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
                        @foreach (\App\Enums\ComplaintCategory::cases() as $category)
                        <option value="{{ $category->value }}" @selected(old('category', $complaint->category) ===
                            $category->value)>
                            {{ ucfirst($category->value) }}
                        </option>
                        @endforeach
                    </select>
                    @error('category')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea id="description" name="description" rows="5"
                        class="form-control @error('description') is-invalid @enderror">{{ old('description', $complaint->description) }}</textarea>
                    @error('description')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            @endif

        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
            <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-light">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Update Complaint
            </button>
        </div>

    </form>

</div>

@endsection