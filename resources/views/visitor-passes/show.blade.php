@extends('layouts.app')

@section('title', 'Visitor Details')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Visitor Details</h5>
            <span class="badge bg-secondary">{{ ucfirst($visitorLog->status) }}</span>
        </div>

        <div class="card-body p-4">

            {{-- TOP SUMMARY --}}
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="mb-1 text-primary fw-bold">
                        {{ $visitorLog->visitor->name ?? '-' }}
                    </h5>
                    <div class="text-muted">
                        {{ $visitorLog->visitor->phone ?? '-' }}
                    </div>
                </div>

                <div class="text-end">
                    <div class="small text-muted mt-1">
                        Visit Date:
                        <strong>
                            {{ $visitorLog->visit_date ? format_date($visitorLog->visit_date, 'd M Y') : '-' }}
                        </strong>
                    </div>
                </div>
            </div>

            {{-- QR CODE --}}
            <div class="text-center mb-4">
                <h6 class="mb-3 fw-semibold">Visitor Pass QR</h6>

                <div class="d-inline-block p-3 bg-white border rounded shadow-xs">
                    {!! QrCode::size(220)->generate($visitorLog->qr_code_data) !!}
                </div>

                <div class="mt-2 text-muted small">
                    Pass ID: #{{ $visitorLog->id }}
                </div>

                <div class="mt-3">
                    <button onclick="downloadQR()" class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Download QR Code
                    </button>
                </div>
            </div>

            {{-- DETAILS GRID --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Vehicle Number</div>
                        <div class="fw-semibold">{{ $visitorLog->visitor->vehicle_number ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Purpose</div>
                        <div class="fw-semibold">{{ $visitorLog->purpose ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Entry Time</div>
                        <div class="fw-semibold">{{ $visitorLog->entry_time ? format_date($visitorLog->entry_time) : '-' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Exit Time</div>
                        <div class="fw-semibold">{{ $visitorLog->exit_time ? format_date($visitorLog->exit_time) : '-' }}</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 bg-light rounded border border-light">
                        <div class="text-muted small">Gatekeeper</div>
                        <div class="fw-semibold">{{ $visitorLog->gatekeeper->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            <a href="{{ route('passes.index') }}" class="btn btn-light">
                Back
            </a>
        </div>

    </div>

@endsection

@push('scripts')
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
@endpush

@push('scripts')
<script>
    function downloadQR() {
        const svg = document.querySelector('.text-center svg');

        if (!svg) {
            Toast.fire({
                icon: 'error',
                title: 'QR Code not found'
            });
            return;
        }

        const serializer = new XMLSerializer();
        const source = serializer.serializeToString(svg);

        const blob = new Blob([source], {
            type: 'image/svg+xml;charset=utf-8'
        });

        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = 'visitor-pass-{{ $visitorLog->id }}.svg';

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        URL.revokeObjectURL(url);

        Toast.fire({
            icon: 'success',
            title: 'QR Code downloaded successfully'
        });
    }
</script>
@endpush