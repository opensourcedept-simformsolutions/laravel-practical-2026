@extends('layouts.app')

@section('title', 'Edit Visitor Pass')

@section('content')
  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-12">

            <div class="card shadow">

              <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">Edit Visitor Pass</h5>
              </div>

              <form method="POST" action="{{ route('passes.update', $visitorLog->id) }}">
                @csrf
                @method('PUT')

                <div class="card-body">

                  <p class="text-secondary mb-3" style="text-decoration:underline">
                    Visitor Details
                  </p>

                  <div class="row">

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Visitor Name</label>
                        <input type="text" name="name" class="form-control"
                          value="{{ old('name', $visitorLog->visitor->name) }}">

                        @error('name')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                          value="{{ old('phone', $visitorLog->visitor->phone) }}">

                        @error('phone')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" name="purpose" class="form-control"
                          value="{{ old('purpose', $visitorLog->purpose) }}">

                        @error('purpose')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Visit Date</label>
                        <input type="date" name="visit_date" class="form-control"
                          value="{{ old('visit_date', \Carbon\Carbon::parse($visitorLog->visit_date)->format('Y-m-d')) }}">

                        @error('visit_date')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Vehicle Number (Optional)</label>
                        <input type="text" name="vehicle_number" class="form-control"
                          value="{{ old('vehicle_number', $visitorLog->visitor->vehicle_number) }}">

                        @error('vehicle_number')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                          <option value="pending"
                            {{ old('status', $visitorLog->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                          <option value="entered"
                            {{ old('status', $visitorLog->status) == 'entered' ? 'selected' : '' }}>Entered</option>
                          <option value="exited" {{ old('status', $visitorLog->status) == 'exited' ? 'selected' : '' }}>
                            Exited</option>
                        </select>

                        @error('status')
                          <div class="text-danger pt-1">
                            {{ $message }}
                          </div>
                        @enderror
                      </div>
                    </div>

                  </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-end border-top-0">

                  <a href="{{ route('passes.index') }}" class="btn btn-light me-2">
                    Cancel
                  </a>

                  <button type="submit" class="btn btn-primary">
                    Update Pass
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
