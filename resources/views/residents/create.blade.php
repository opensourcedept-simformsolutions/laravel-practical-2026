@extends('layouts.app')

@section('title', 'Create Resident')

@section('content')
  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-12">

            <div class="card shadow">

              <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">Create Resident</h5>
              </div>

              <form method="POST" action="{{ route('residents.store') }}">
                @csrf

                <div class="card-body">

                  <p class="text-secondary mb-3" style="text-decoration:underline">
                    Resident Details
                  </p>

                  <div class="row">

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">

                        @error('name')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">

                        @error('email')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">

                        @error('phone')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Flat</label>
                        <select name="flat_id" class="form-select">
                          <option value="">Select Flat</option>
                          @foreach($flats as $flat)
                            <option value="{{ $flat->id }}"
                              {{ old('flat_id') == $flat->id ? 'selected' : '' }}>
                              {{ $flat->flat_number }} ({{ $flat->wing }})
                            </option>
                          @endforeach
                        </select>

                        @error('flat_id')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Resident Type</label>
                        <select name="resident_type" class="form-select">
                          <option value="owner" {{ old('resident_type') == 'owner' ? 'selected' : '' }}>Owner</option>
                          <option value="tenant" {{ old('resident_type') == 'tenant' ? 'selected' : '' }}>Tenant</option>
                        </select>

                        @error('resident_type')
                          <div class="text-danger pt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                  </div>

                </div>

                <div class="card-footer bg-white d-flex justify-content-end border-top-0">

                  <a href="{{ route('residents.index') }}" class="btn btn-light me-2">
                    Cancel
                  </a>

                  <button type="submit" class="btn btn-primary">
                    Create Resident
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