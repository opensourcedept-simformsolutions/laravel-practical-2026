@extends('layouts.app')

@section('title', 'Payment Portal')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            @if (session('message') || request()->query('message'))
                @php
                    $status = session('status') ?? request()->query('status');
                    $message = session('message') ?? request()->query('message');
                @endphp
                <div class="alert alert-{{ $status == 'success' ? 'success' : 'danger' }} alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-{{ $status == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-2"></i>
                    {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-dark text-white p-4">
                    <h4 class="fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>Resident Payment Portal</h4>
                    <span class="text-white-50 fs-7">Pay maintenance fees or rent to flat owner</span>
                </div>
                
                <div class="card-body p-4">
                    <div class="bg-light p-3 rounded-3 mb-4 border border-1">
                        <div class="row text-muted fs-7 mb-2">
                            <div class="col-6">Resident</div>
                            <div class="col-6 text-end text-dark fw-semibold">{{ auth()->user()->name }}</div>
                        </div>
                        <div class="row text-muted fs-7 mb-2">
                            <div class="col-6">Flat Assignment</div>
                            <div class="col-6 text-end text-dark fw-semibold">
                                Wing {{ $flat->wing }}, Floor {{ $flat->floor }}, Flat {{ $flat->flat_number }}
                            </div>
                        </div>
                        <div class="row text-muted fs-7 mb-0">
                            <div class="col-6">Type</div>
                            <div class="col-6 text-end text-dark fw-semibold">{{ ucfirst($resident->resident_type) }}</div>
                        </div>
                    </div>

                    <form action="{{ route('payments.pay') }}" method="POST" id="paymentForm">
                        @csrf
                        
                        {{-- Payment Type Selector --}}
                        <div class="mb-3">
                            <label for="type" class="form-label fw-semibold text-dark">Payment Type</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required onchange="toggleReceiver()">
                                <option value="maintenance">Society Maintenance Fee</option>
                                @if($resident->resident_type === 'tenant' && $owner)
                                    <option value="rent">Rent Payment (To Owner: {{ $owner->name }})</option>
                                @endif
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" name="receiver_id" id="receiver_id" value="">

                        <div class="mb-4">
                            <label for="amount" class="form-label fw-semibold text-dark">Amount (INR)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-semibold text-dark">₹</span>
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       class="form-control border-start-0 ps-1 @error('amount') is-invalid @enderror" 
                                       placeholder="Enter amount" 
                                       min="1" 
                                       required>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimum payment is ₹1.00.</div>
                        </div>

                        <div class="d-flex align-items-center bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3 p-3 mb-4">
                            <i class="bi bi-shield-lock-fill text-info fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark fs-7">Secure Payment by Razorpay</h6>
                                <p class="mb-0 text-muted fs-8">Cards, UPI, Netbanking, and Wallet payment options will open on next screen.</p>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2.5 fw-semibold rounded-3 shadow-sm">
                                Proceed to Pay <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleReceiver() {
        const typeSelect = document.getElementById('type');
        const receiverInput = document.getElementById('receiver_id');
        const ownerId = "{{ $owner ? $owner->id : '' }}";

        if (typeSelect.value === 'rent') {
            receiverInput.value = ownerId;
        } else {
            receiverInput.value = ''; 
        }
    }

    window.onload = function() {
        toggleReceiver();
    };
</script>
@endsection
