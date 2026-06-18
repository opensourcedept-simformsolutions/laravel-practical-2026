@extends('layouts.app')

@section('title', 'Create Flat')

@section('content')

  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-12">

            <div class="card shadow">

              <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">Create Flat</h5>
              </div>

              <form method="POST" action="{{ route('flats.store') }}">
                @csrf

                <div class="card-body">

                  <p class="text-secondary mb-3" style="text-decoration:underline">
                    Flat Details
                  </p>

                  <div class="row">

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Wing</label>
                        <input type="text" name="wing" class="form-control" value="{{ old('wing') }}">

                        @error('wing')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Floor</label>
                        <input type="number" name="floor" class="form-control" value="{{ old('floor') }}">

                        @error('floor')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Flat Number</label>
                        <input type="text" name="flat_number" class="form-control" value="{{ old('flat_number') }}">

                        @error('flat_number')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                  </div>

                </div>

                <div class="card-footer bg-white d-flex justify-content-end border-top-0">

                  <a href="{{ route('flats.index') }}" class="btn btn-light me-2">
                    Cancel
                  </a>

                  <button type="submit" class="btn btn-primary">
                    Create Flat
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