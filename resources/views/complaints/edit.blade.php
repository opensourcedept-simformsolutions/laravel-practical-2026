@extends('layouts.app')

@section('title', 'Edit Complaint')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">Update Complaint</h5>
        </div>

        <form method="POST" action="{{ route('complaints.update', $complaint) }}">
            @csrf
            @method('PATCH')

            <div class="card-body p-4">

                {{-- ADMIN FORM --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <p class="text-secondary mb-3 text-decoration-underline fw-semibold">
                        Complaint Status
                    </p>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="open" @selected(old('status', $complaint->status) === 'open')>
                                    Open
                                </option>
                                <option value="in_progress" @selected(old('status', $complaint->status) === 'in_progress')>
                                    In Progress
                                </option>
                                <option value="resolved" @selected(old('status', $complaint->status) === 'resolved')>
                                    Resolved
                                </option>
                            </select>
                            @error('status')
                                <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" rows="5" class="form-control @error('admin_notes') is-invalid @enderror">{{ old('admin_notes', $complaint->admin_notes) }}</textarea>
                            @error('admin_notes')
                                <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                @else

                    {{-- RESIDENT / GATEKEEPER FORM --}}
                    <p class="text-secondary mb-3 text-decoration-underline fw-semibold">
                        Complaint Details
                    </p>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror">
                                @foreach(\App\Enums\ComplaintCategory::cases() as $category)
                                    <option value="{{ $category->value }}" @selected(old('category', $complaint->category) === $category->value)>
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
                            <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $complaint->description) }}</textarea>
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
