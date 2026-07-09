@extends('layouts.app')

@section('title', 'Visitor Pass #' . $visitorLog->id)

@section('content')
    <div class="container-fluid py-4">
        <!-- Ticket Container -->
        <div class="card border-0 shadow rounded-4 overflow-hidden mx-auto" style="max-width: 850px;">
            <!-- Ticket Header -->
            <div class="text-white p-4 text-center position-relative" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <div class="position-absolute top-50 start-0 translate-middle-y ms-3 d-none d-sm-block">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
                        PASS #{{ $visitorLog->id }}
                    </span>
                </div>
                <h4 class="mb-1 fw-bold tracking-wide">VISITOR ENTRY PASS</h4>
                <p class="mb-0 text-white-50 small">
                    <i class="bi bi-building me-1"></i>{{ $visitorLog->flat?->society?->name ?? 'Society Management System' }}
                </p>
                <div class="position-absolute top-50 end-0 translate-middle-y me-3 ticket-header-badge">
                    @php
                        $statusColors = [
                            'pending' => 'bg-warning text-dark',
                            'pending_approval' => 'bg-warning text-dark',
                            'approved' => 'bg-success text-white',
                            'entered' => 'bg-info text-white',
                            'exited' => 'bg-secondary text-white',
                            'cancelled' => 'bg-danger text-white',
                            'rejected' => 'bg-danger text-white',
                            'expired' => 'bg-danger text-white',
                        ];
                        $badgeClass = $statusColors[strtolower($visitorLog->status)] ?? 'bg-secondary text-white';
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill fw-bold text-uppercase small">
                        {{ str_replace('_', ' ', $visitorLog->status) }}
                    </span>
                </div>
            </div>

            <!-- Ticket Body -->
            <div class="card-body p-4 p-md-5 bg-white">
                <div class="row g-4">
                    <!-- Left: Details Column -->
                    <div class="col-md-7 border-end-md pe-md-4">
                        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                            <i class="bi bi-person-badge-fill text-primary me-2"></i>Visitor Information
                        </h5>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="text-secondary small d-block">Visitor Name</span>
                                <span class="text-dark fw-bold fs-5">{{ $visitorLog->visitor->name ?? '-' }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-secondary small d-block">Phone Number</span>
                                <span class="text-dark fw-semibold">{{ $visitorLog->visitor->phone ?? '-' }}</span>
                            </div>

                            <div class="col-sm-6">
                                <span class="text-secondary small d-block"><i class="bi bi-car-front-fill me-1 text-muted"></i>Vehicle Number</span>
                                <span class="text-dark fw-semibold">{{ $visitorLog->visitor->vehicle_number ?? 'No Vehicle' }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-secondary small d-block"><i class="bi bi-chat-left-text-fill me-1 text-muted"></i>Purpose of Visit</span>
                                <span class="text-dark fw-semibold">{{ $visitorLog->purpose ?? '-' }}</span>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-clock-history text-primary me-2"></i>Timing & Logs
                                </h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="p-2.5 bg-light rounded border border-light">
                                            <span class="text-secondary small d-block">Expected Visit Date</span>
                                            <span class="text-dark fw-semibold">{{ $visitorLog->visit_date ? format_date($visitorLog->visit_date, 'd M Y') : '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-2.5 bg-light rounded border border-light">
                                            <span class="text-secondary small d-block">Destination Flat</span>
                                            <span class="text-dark fw-semibold">
                                                {{ $visitorLog->flat?->wingRelation?->name ?? $visitorLog->flat?->wing ?? '-' }}-{{ $visitorLog->flat?->flat_number ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-2.5 bg-light rounded border border-light">
                                            <span class="text-secondary small d-block">Entry Time</span>
                                            <span class="text-dark fw-semibold">{{ $visitorLog->entry_time ? format_date($visitorLog->entry_time) : 'Not Entered' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-2.5 bg-light rounded border border-light">
                                            <span class="text-secondary small d-block">Exit Time</span>
                                            <span class="text-dark fw-semibold">{{ $visitorLog->exit_time ? format_date($visitorLog->exit_time) : 'Not Exited' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-3 text-muted small">
                                <span>Pass created by: <strong>{{ $visitorLog->creator->name ?? 'System' }}</strong></span>
                                @if($visitorLog->gatekeeper)
                                    <span class="ms-3">Verified by: <strong>{{ $visitorLog->gatekeeper->name }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right: Photo/QR Column -->
                    <div class="col-md-5 ps-md-4 text-center d-flex flex-column align-items-center justify-content-center">
                        <!-- Visitor Image/Avatar -->
                        <div class="mb-4">
                            @if ($visitorLog->photo_path)
                                <img src="{{ asset('storage/' . $visitorLog->photo_path) }}" class="rounded-3 shadow-sm border p-1" style="width: 140px; height: 140px; object-fit: cover;" alt="Visitor Photo">
                            @else
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 100px; height: 100px;">
                                    <i class="bi bi-person-fill" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>

                        <!-- QR Code Container -->
                        <div id="qrContainer" class="p-3 bg-white border rounded-3 shadow-sm mb-3">
                            {!! QrCode::size(160)->generate($visitorLog->qr_code_data) !!}
                            <div class="text-muted small mt-2 fw-semibold">Scan to Verify</div>
                        </div>

                        <!-- Control Buttons -->
                        <div class="d-flex flex-column gap-2 w-100 px-3">
                            <button onclick="downloadQR()" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-download me-1"></i>Download Pass QR
                            </button>
                            <button onclick="copyLink()" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-share me-1"></i>Share Pass Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Footer -->
            <div class="card-footer bg-light border-top p-4 d-flex justify-content-between align-items-center">
                <a href="{{ route('passes.index') }}" class="btn btn-light border">
                    <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Back to Passes
                </a>
                <span class="text-muted small">Generated on {{ $visitorLog->created_at->format('d M Y, h:i A') }}</span>
            </div>
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
                    title: 'Pass link copied to clipboard'
                });
            });
        }

        function downloadQR() {
            const svg = document.querySelector('#qrContainer svg');

            if (!svg) {
                Toast.fire({
                    icon: 'error',
                    title: 'QR Code not found.'
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
