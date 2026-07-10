@extends('layouts.app')

@section('title', 'Raise Complaint')

@section('content')

<div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold text-dark">Raise Complaint</h5>
    </div>

    <form id="complaintForm" method="POST" action="{{ route('complaints.store') }}">
        @csrf

        <div class="card-body p-4">

            <div class="row g-3">

                <div class="col-md-12">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category')==$category)>
                            {{ ucfirst($category) }}
                        </option>
                        @endforeach
                    </select>
                    @error('category')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="5"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Enter complaint details...">{{ old('description') }}</textarea>
                    @error('description')
                    <div class="text-danger pt-1 fs-7">{{ $message }}</div>
                    @enderror
                </div>

            </div>

        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
            <a href="{{ route('complaints.index') }}" class="btn btn-light">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Submit Complaint
            </button>
        </div>

    </form>

</div>

@endsection