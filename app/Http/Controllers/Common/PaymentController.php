<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function showForm()
    {
        $user = auth()->user();
        $resident = $user->resident;

        if (! $resident) {
            return redirect()->back()->with('message', 'Only registered residents can access the payment portal.')->with('status', 'error');
        }

        $flat = $resident->flat;
        if (! $flat) {
            return redirect()->back()->with('message', 'Resident is not assigned to a flat.')->with('status', 'error');
        }

        $ownerResident = $flat->residents()
            ->where('resident_type', 'owner')
            ->first();

        $owner = $ownerResident ? $ownerResident->user : null;

        return view('payments.form', compact('resident', 'flat', 'owner'));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:rent,maintenance',
            'receiver_id' => 'nullable|exists:users,id',
        ]);

        $user = auth()->user();
        $resident = $user->resident;

        if (! $resident) {
            return redirect()->back()->with('message', 'Resident record not found.')->with('status', 'error');
        }

        $amount = $request->amount;
        $type = $request->type;
        $receiverId = $request->receiver_id;

        $payment = Payment::create([
            'resident_id' => $resident->id,
            'receiver_id' => $receiverId,
            'type' => $type,
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'pending',
        ]);
        try {
            $keyId = config('services.razorpay.key_id');
            $keySecret = config('services.razorpay.key_secret');

            if (empty($keyId) || empty($keySecret)) {
                throw new Exception('Razorpay credentials are not configured in your env file.');
            }

            $api = new Api($keyId, $keySecret);

            $orderData = [
                'receipt' => 'rcpt_'.$payment->id,
                'amount' => $amount * 100,
                'currency' => 'INR',
                'payment_capture' => 1,
            ];

            $razorpayOrder = $api->order->create($orderData);

            $payment->update([
                'gateway_order_id' => $razorpayOrder['id'],
            ]);

            return view('payments.razorpay_checkout', [
                'payment' => $payment,
                'order_id' => $razorpayOrder['id'],
                'amount' => $amount * 100,
                'key' => $keyId,
                'user' => $user,
            ]);

        } catch (Exception $e) {
            $payment->update(['status' => 'failed']);

            return redirect()->route('payments.form')
                ->with('message', 'Failed to initiate Razorpay Order: '.$e->getMessage())
                ->with('status', 'error');
        }
    }

    public function callback(Request $request)
    {
        if (
            empty($request->razorpay_order_id) ||
            empty($request->razorpay_payment_id) ||
            empty($request->razorpay_signature)
        ) {
            return redirect()->route('payments.form')
                ->with('message', 'Invalid Razorpay response.')
                ->with('status', 'error');
        }

        $payment = Payment::where(
            'gateway_order_id',
            $request->razorpay_order_id
        )->first();

        if (! $payment) {
            return redirect()->route('payments.form')
                ->with('message', 'Payment record not found.')
                ->with('status', 'error');
        }

        return redirect()->route('payments.form')
            ->with('message', 'Payment received. Waiting for confirmation.')
            ->with('status', 'success');
    }

    public function webhook(Request $request)
    {
        // dd("fgsdfg");
        Log::info('RAZORPAY WEBHOOK RECEIVED');

        // return response()->json([
        //     'status' => 'ok',
        // ]);
        $payload = $request->getContent();

        $signature = $request->header('X-Razorpay-Signature');

        try {

            $api = new Api(
                config('services.razorpay.key_id'),
                config('services.razorpay.key_secret')
            );

            $api->utility->verifyWebhookSignature(
                $payload,
                $signature,
                config('services.razorpay.webhook_secret')
            );

        } catch (Exception $e) {

            Log::error('Invalid Razorpay webhook', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Invalid signature',
            ], 400);
        }

        $event = $request->input('event');

        if ($event === 'payment.captured') {

            $paymentEntity = $request->input('payload.payment.entity');

            Payment::where(
                'gateway_order_id',
                $paymentEntity['order_id']
            )->update([
                'status' => 'completed',
                'gateway_payment_id' => $paymentEntity['id'],
            ]);
        }

        if ($event === 'payment.failed') {

            $paymentEntity = $request->input('payload.payment.entity');

            Payment::where(
                'gateway_order_id',
                $paymentEntity['order_id']
            )->update([
                'status' => 'failed',
            ]);
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
