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

                        {{-- QR CODE --}}
                        <div class="text-center mb-4">

                            <h6 class="mb-3">Visitor Pass QR</h6>

                            <div class="d-inline-block p-3 bg-white border rounded">
                                {!! QrCode::size(220)->generate($visitorLog->qr_code_data) !!}
                            </div>

                            <div class="mt-2 text-muted">
                                Pass ID: #{{ $visitorLog->id }}
                            </div>

                        </div>
                        <div class="mt-3 text-center">

                            <a href="https://wa.me/?text={{ urlencode(route('passes.show', $visitorLog->id)) }}"
                                target="_blank" class="btn btn-success btn-sm me-2">

                                <i class="bi bi-whatsapp"></i> Share WhatsApp
                            </a>

                            <button onclick="copyLink()" class="btn btn-primary btn-sm">

                                <i class="bi bi-link-45deg"></i> Copy Link
                            </button>

                        </div>

                        <br>

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


<script>
    function copyLink() {
        const link = "{{ route('passes.show', $visitorLog->id) }}";

        navigator.clipboard.writeText(link).then(() => {

            Toast.fire({
                icon: 'success',
                title: 'Link copied successfully'
            });

        });
    }
</script>
