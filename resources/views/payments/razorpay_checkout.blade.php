<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment...</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-loader {
            text-align: center;
            max-width: 400px;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

    <div class="payment-loader">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="fw-bold text-dark mb-1">Opening Razorpay Checkout</h5>
        <p class="text-muted small">Please do not refresh, close this window, or press back while we launch the secure payment screen.</p>
        
        <form action="{{ route('payments.callback') }}" method="POST" id="razorpayForm" style="display: none;">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        </form>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "{{ $key }}",
            "amount": "{{ $amount }}",
            "currency": "INR",
            "name": "Society Management System",
            "description": "{{ ucfirst($payment->type) }} Payment - Flat {{ $payment->resident->flat->flat_number }}",
            "image": "https://cdn-icons-png.flaticon.com/512/10149/10149454.png",
            "order_id": "{{ $order_id }}",
            "handler": function (response){

                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                
                document.getElementById('razorpayForm').submit();
            },
            "prefill": {
                "name": "{{ $user->name }}",
                "email": "{{ $user->email }}",
                "contact": "{{ $user->phone ?? '' }}"
            },
            "theme": {
                "color": "#0d6efd" 
            },
            "modal": {
                "ondismiss": function(){
                    window.location.href = "{{ route('payments.form') }}?message=Payment+cancelled+by+user&status=error";
                }
            }
        };

            var rzp = new Razorpay(options);

            window.onload = function() {
                rzp.open();
            };
    </script>
</body>
</html>
