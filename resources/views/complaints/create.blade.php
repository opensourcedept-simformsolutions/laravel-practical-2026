@extends('layouts.app')

@section('title', 'Raise Complaint')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">

                    <div class="card shadow">

                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="mb-0">Raise Complaint</h5>
                        </div>

                        <form method="POST" action="{{ route('complaints.store') }}">
                            @csrf

                            <div class="card-body">

                                <p class="text-secondary mb-3 text-decoration-underline">
                                    Complaint Details
                                </p>

                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">
                                                Category
                                            </label>

                                            <select id="category" name="category" class="form-control">
                                                <option value="">
                                                    Select Category
                                                </option>

                                                @foreach ($categories as $category)
                                                <option value="{{ $category }}" {{ old('category')==$category
                                                    ? 'selected' : '' }}>
                                                    {{ ucfirst($category) }}
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
                                            <label for="description" class="form-label">
                                                Description
                                            </label>

                                            <textarea id="description" name="description" rows="5" class="form-control"
                                                placeholder="Enter complaint details...">{{ old('description') }}</textarea>

                                            @error('description')
                                            <div class="text-danger pt-1">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="card-footer bg-white d-flex justify-content-end border-top-0">

                                <a href="{{ route('complaints.index') }}" class="btn btn-light me-2">
                                    My Complaints
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    Submit Complaint
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
