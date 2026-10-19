<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * Initiate Razorpay payment for a pending order.
     * GET /payment/{orderNumber}/pay
     */
    public function initiate(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return redirect()->route('order.confirmation', $order->order_number)
                ->with('info', 'This order has already been paid for.');
        }

        try {
            $rzpPayload = $this->paymentService->createRazorpayOrder($order);
        } catch (\Throwable $e) {
            Log::error('Failed to create Razorpay order', [
                'order_number' => $order->order_number,
                'error'        => $e->getMessage(),
            ]);

            return redirect()->route('order.show', $order->order_number)
                ->with('error', 'Unable to initiate online payment: ' . $e->getMessage());
        }

        return view('payment.razorpay', compact('order', 'rzpPayload'));
    }

    /**
     * Handle Razorpay callback after payment success on the frontend modal.
     * POST /payment/callback
     */
    public function callback(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
            'order_number'        => 'required|string',
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $verified = $this->paymentService->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (! $verified) {
            $order->update(['payment_status' => 'failed']);

            return redirect()->route('order.show', $order->order_number)
                ->with('error', 'Payment verification failed. Please try again or contact support.');
        }

        $this->paymentService->markOrderPaid($order, $validated['razorpay_payment_id']);

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', 'Payment successful! Your order has been placed and confirmed.');
    }

    /**
     * Razorpay webhook endpoint.
     * POST /payment/webhook
     */
    public function webhook(Request $request)
    {
        $signature  = $request->header('X-Razorpay-Signature', '');
        $payload    = $request->json()->all();
        $rawContent = $request->getContent();

        $this->paymentService->handleWebhook($payload, $signature, $rawContent);

        return response()->json(['status' => 'ok']);
    }
}
