@extends('layouts.app')

@section('title', 'Visitor Details | ' . config('app.name'))

@section('content')

  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0">Visitor Details</h4>

          <a href="{{ route('passes.index') }}" class="btn btn-light btn-sm">
            ← Back
          </a>
        </div>

        {{-- Main Card --}}
        <div class="card shadow-sm border-0">

          <div class="card-body">

            {{-- TOP SUMMARY --}}
            <div class="d-flex justify-content-between align-items-start mb-4">

              <div>
                <h5 class="mb-1">
                  {{ $visitorLog->visitor->name ?? '-' }}
                </h5>

                <div class="text-muted">
                  {{ $visitorLog->visitor->phone ?? '-' }}
                </div>
              </div>

              <div class="text-end">
                <span class="badge bg-secondary">
                  {{ ucfirst($visitorLog->status) }}
                </span>

                <div class="small text-muted mt-1">
                  Visit Date:
                  <strong>
                    {{ $visitorLog->visit_date ? format_date($visitorLog->visit_date, 'd M Y') : '-' }}
                  </strong>
                </div>
              </div>

            </div>

            <hr>

            {{-- DETAILS GRID --}}
            <div class="row">

              <div class="col-md-6 mb-3">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Vehicle Number</div>
                  <div class="fw-semibold">
                    {{ $visitorLog->visitor->vehicle_number ?? '-' }}
                  </div>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Purpose</div>
                  <div class="fw-semibold">
                    {{ $visitorLog->purpose ?? '-' }}
                  </div>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Entry Time</div>
                  <div class="fw-semibold">
                    {{ $visitorLog->entry_time ? format_date($visitorLog->entry_time) : '-' }}
                  </div>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Exit Time</div>
                  <div class="fw-semibold">
                    {{ $visitorLog->exit_time ? format_date($visitorLog->exit_time) : '-' }}
                  </div>
                </div>
              </div>

              <div class="col-12 mb-3">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Gatekeeper</div>
                  <div class="fw-semibold">
                    {{ $visitorLog->gatekeeper->name ?? '-' }}
                  </div>
                </div>
              </div>

            </div>

          </div>

        </div>

      </div>
    </div>
  </div>

@endsection
