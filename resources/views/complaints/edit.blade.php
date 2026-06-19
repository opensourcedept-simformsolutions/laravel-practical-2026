@extends('layouts.app')

@section('title', 'Edit Complaint')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">

                    <div class="card shadow">

                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="mb-0">Update Complaint</h5>
                        </div>

                        <form method="POST" action="{{ route('complaints.update', $complaint) }}">
                            @csrf
                            @method('PATCH')

                            <div class="card-body">

                                {{-- ADMIN FORM --}}
                                @if(auth()->user()->isAdmin())

                                <p class="text-secondary mb-3 text-decoration-underline">
                                    Complaint Status
                                </p>

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">

                                            <label class="form-label">
                                                Status
                                            </label>

                                            <select name="status" class="form-select">

                                                <option value="open" {{ old('status', $complaint->status) === 'open' ?
                                                    'selected' : '' }}>
                                                    Open
                                                </option>

                                                <option value="in_progress" {{ old('status', $complaint->status) ===
                                                    'in_progress' ? 'selected' : '' }}>
                                                    In Progress
                                                </option>

                                                <option value="resolved" {{ old('status', $complaint->status) ===
                                                    'resolved' ? 'selected' : '' }}>
                                                    Resolved
                                                </option>

                                            </select>

                                            @error('status')
                                            <div class="text-danger pt-1">
                                                {{ $message }}
                                            </div>
                                            @enderror

                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">

                                            <label class="form-label">
                                                Admin Notes
                                            </label>

                                            <textarea name="admin_notes" rows="5"
                                                class="form-control">{{ old('admin_notes', $complaint->admin_notes) }}</textarea>

                                            @error('admin_notes')
                                            <div class="text-danger pt-1">
                                                {{ $message }}
                                            </div>
                                            @enderror

                                        </div>
                                    </div>

                                </div>

                                @else

                                {{-- RESIDENT / GATEKEEPER FORM --}}
                                <p class="text-secondary mb-3 text-decoration-underline">
                                    Complaint Details
                                </p>

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">

                                            <label class="form-label">
                                                Category
                                            </label>

                                            <select name="category" class="form-select">

                                                @foreach(\App\Enum\ComplaintCategory::cases() as $category)
                                                <option value="{{ $category->value }}" {{ old('category', $complaint->
                                                    category) === $category->value ? 'selected' : '' }}
                                                    >
                                                    {{ ucfirst($category->value) }}
                                                </option>
                                                @endforeach

                                            </select>

                                            @error('category')
                                            <div class="text-danger pt-1">
                                                {{ $message }}
                                            </div>
                                            @enderror

                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">

                                            <label class="form-label">
                                                Description
                                            </label>

                                            <textarea name="description" rows="5"
                                                class="form-control">{{ old('description', $complaint->description) }}</textarea>

                                            @error('description')
                                            <div class="text-danger pt-1">
                                                {{ $message }}
                                            </div>
                                            @enderror

                                        </div>
                                    </div>

                                </div>

                                @endif

                            </div>

                            <div class="card-footer bg-white d-flex justify-content-end border-top-0">

                                <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-light me-2">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    Update Complaint
                                </button>

                            </div>

                        </form>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
