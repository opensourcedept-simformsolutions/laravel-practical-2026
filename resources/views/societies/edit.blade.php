@extends('layouts.app')

@section('title', 'Edit Society')

@section('content')
  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-12">

            <div class="card shadow">

              <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">Edit Society</h5>
              </div>

              <form method="POST" action="{{ route('societies.update', $society) }}">
                @csrf
                @method('PUT')

                <div class="card-body">

                  <div class="row">

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Society Name</label>

                        <input type="text" name="name" class="form-control"
                          value="{{ old('name', $society->name) }}">

                        @error('name')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Pincode</label>

                        <input type="text" name="pincode" class="form-control"
                          value="{{ old('pincode', $society->pincode) }}">

                        @error('pincode')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-12">
                      <div class="mb-3">
                        <label class="form-label">Address</label>

                        <textarea name="address" rows="3" class="form-control">{{ old('address', $society->address) }}</textarea>

                        @error('address')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">City</label>

                        <input type="text" name="city" class="form-control"
                          value="{{ old('city', $society->city) }}">

                        @error('city')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">State</label>

                        <input type="text" name="state" class="form-control"
                          value="{{ old('state', $society->state) }}">

                        @error('state')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                  </div>

                </div>

                <div class="card-footer bg-white d-flex justify-content-end">

                  <a href="{{ route('societies.index') }}" class="btn btn-light me-2">
                    Cancel
                  </a>

                  <button type="submit" class="btn btn-primary">
                    Update Society
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
