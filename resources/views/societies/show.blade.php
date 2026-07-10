@extends('layouts.app')

@section('title', 'Society Details')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Society Details</h5>
            @if ($society->trashed())
                <span class="badge bg-danger">Deleted</span>
            @else
                <span class="badge bg-success">Active</span>
            @endif
        </div>

        <div class="card-body p-4">

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">ID</div>
                <div class="col-md-8 text-dark">{{ $society->id }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">Society Name</div>
                <div class="col-md-8 text-dark">{{ $society->name }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">Address</div>
                <div class="col-md-8 text-dark">{{ $society->address }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">City</div>
                <div class="col-md-8 text-dark">{{ $society->city }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">State</div>
                <div class="col-md-8 text-dark">{{ $society->state }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">Pincode</div>
                <div class="col-md-8 text-dark">{{ $society->pincode }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">Created At</div>
                <div class="col-md-8 text-dark">{{ $society->created_at->format('d M Y h:i A') }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold text-secondary">Updated At</div>
                <div class="col-md-8 text-dark">{{ $society->updated_at->format('d M Y h:i A') }}</div>
            </div>

            @if ($society->deleted_at)
                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold text-danger">Deleted At</div>
                    <div class="col-md-8 text-danger">{{ $society->deleted_at->format('d M Y h:i A') }}</div>
                </div>
            @endif

        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
            <a href="{{ route('societies.index') }}" class="btn btn-light">
                Back
            </a>
            @if (!$society->trashed())
                <a href="{{ route('societies.edit', $society->id) }}" class="btn btn-primary">
                    Edit
                </a>
            @endif
        </div>

    </div>

@endsection
