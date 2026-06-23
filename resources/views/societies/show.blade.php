@extends('layouts.app')

@section('title', 'Society Details')

@section('content')
  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-lg-8 mx-auto">

            <div class="card shadow">

              <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                  Society Details
                </h5>

                @if ($society->trashed())
                  <span class="badge bg-danger">
                    Deleted
                  </span>
                @else
                  <span class="badge bg-success">
                    Active
                  </span>
                @endif
              </div>

              <div class="card-body">

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    ID
                  </div>

                  <div class="col-md-8">
                    {{ $society->id }}
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    Society Name
                  </div>

                  <div class="col-md-8">
                    {{ $society->name }}
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    Address
                  </div>

                  <div class="col-md-8">
                    {{ $society->address }}
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    City
                  </div>

                  <div class="col-md-8">
                    {{ $society->city }}
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    State
                  </div>

                  <div class="col-md-8">
                    {{ $society->state }}
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    Pincode
                  </div>

                  <div class="col-md-8">
                    {{ $society->pincode }}
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    Created At
                  </div>

                  <div class="col-md-8">
                    {{ $society->created_at->format('d M Y h:i A') }}
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4 fw-semibold">
                    Updated At
                  </div>

                  <div class="col-md-8">
                    {{ $society->updated_at->format('d M Y h:i A') }}
                  </div>
                </div>

                @if ($society->deleted_at)
                  <div class="row mb-3">
                    <div class="col-md-4 fw-semibold text-danger">
                      Deleted At
                    </div>

                    <div class="col-md-8 text-danger">
                      {{ $society->deleted_at->format('d M Y h:i A') }}
                    </div>
                  </div>
                @endif

              </div>

              <div class="card-footer bg-white d-flex justify-content-end">

                <a href="{{ route('societies.index') }}" class="btn btn-light me-2">
                  Back
                </a>

                @if (!$society->trashed())
                  <a href="{{ route('societies.edit', $society->id) }}" class="btn btn-primary">
                    Edit
                  </a>
                @endif

              </div>

            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
@endsection
