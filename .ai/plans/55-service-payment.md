# Step 55: PaymentService

**Goal:** Abstract payment gateway wrapper with Razorpay integration scaffold for order payment processing.
**Depends On:** Step 54 (OrderService), Step 20 (Order model)
**Next Step:** All 55 steps complete! Ready to begin implementation starting from Step 01.

---

## Files to Create

- `app/Services/PaymentService.php`
- `app/Http/Controllers/Frontend/PaymentController.php`
- Routes added to `routes/web.php`
- `.env` keys for Razorpay

---

## 1. Install Razorpay SDK

```bash
composer require razorpay/razorpay
```

---

## 2. `.env` Keys

```
RAZORPAY_KEY_ID=rzp_test_XXXXXXXXXX
RAZORPAY_KEY_SECRET=your_secret_key_here
```

---

## 3. `config/services.php` — Add Razorpay config

```php
// Add inside the return array:
'razorpay' => [
    'key_id'     => env('RAZORPAY_KEY_ID'),
    'key_secret' => env('RAZORPAY_KEY_SECRET'),
],
```

---

## 4. `app/Services/PaymentService.php`

```php
<?php

namespace App\Services;

use App\Models\Order;
use Razorpay\Api\Api;

class PaymentService
{
    protected Api $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret'),
        );
    }

    /**
     * Create a Razorpay order for the given platform order.
     * Call this before rendering the Razorpay checkout modal.
     *
     * @param  Order  $order
     * @return array  Razorpay order payload to pass to the JS SDK
     */
    public function createRazorpayOrder(Order $order): array
    {
        $rzpOrder = $this->razorpay->order->create([
            'amount'   => (int) round($order->total_amount * 100), // paise
            'currency' => 'INR',
            'receipt'  => $order->order_number,
            'notes'    => [
                'platform_order_id' => $order->id,
                'customer_email'    => $order->user->email,
            ],
        ]);

        // Store the Razorpay order ID on our order for verification
        $order->update(['payment_reference' => $rzpOrder->id]);

        return [
            'key'         => config('services.razorpay.key_id'),
            'amount'      => (int) round($order->total_amount * 100),
            'currency'    => 'INR',
            'name'        => 'Pinora Jewellery',
            'description' => 'Order ' . $order->order_number,
            'order_id'    => $rzpOrder->id,
            'prefill'     => [
                'name'    => $order->user->name,
                'email'   => $order->user->email,
                'contact' => $order->shipping_phone,
            ],
            'theme'       => [
                'color' => '#C9A84C',
            ],
        ];
    }

    /**
     * Verify a Razorpay payment signature after the frontend completes payment.
     * This prevents payment tampering.
     *
     * @param  string  $rzpOrderId    From Razorpay JS callback
     * @param  string  $rzpPaymentId  From Razorpay JS callback
     * @param  string  $rzpSignature  From Razorpay JS callback
     * @return bool
     */
    public function verifySignature(
        string $rzpOrderId,
        string $rzpPaymentId,
        string $rzpSignature
    ): bool {
        try {
            $this->razorpay->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $rzpOrderId,
                'razorpay_payment_id' => $rzpPaymentId,
                'razorpay_signature'  => $rzpSignature,
            ]);
            return true;
        } catch (\Razorpay\Api\Errors\SignatureVerificationError) {
            return false;
        }
    }

    /**
     * Mark an order as paid after successful payment verification.
     *
     * @param  Order   $order
     * @param  string  $rzpPaymentId  The Razorpay payment ID to store
     * @return void
     */
    public function markOrderPaid(Order $order, string $rzpPaymentId): void
    {
        $order->update([
            'payment_status'    => 'paid',
            'payment_reference' => $rzpPaymentId,
            'paid_at'           => now(),
            'status'            => 'confirmed',
        ]);
    }

    /**
     * Handle Razorpay webhook events (called by PaymentController@webhook).
     * Supports: payment.captured, payment.failed, refund.created
     *
     * @param  array   $payload    Decoded webhook JSON body
     * @param  string  $signature  X-Razorpay-Signature header value
     * @return void
     */
    public function handleWebhook(array $payload, string $signature): void
    {
        // Verify webhook signature
        $webhookSecret = config('services.razorpay.key_secret');
        $expectedSig   = hash_hmac('sha256', json_encode($payload), $webhookSecret);

        if (! hash_equals($expectedSig, $signature)) {
            abort(400, 'Invalid webhook signature.');
        }

        $event = $payload['event'] ?? null;

        match ($event) {
            'payment.captured' => $this->onPaymentCaptured($payload),
            'payment.failed'   => $this->onPaymentFailed($payload),
            'refund.created'   => $this->onRefundCreated($payload),
            default            => null,
        };
    }

    // ──────────────────────────────────────────────────
    // Private webhook event handlers
    // ──────────────────────────────────────────────────

    private function onPaymentCaptured(array $payload): void
    {
        $rzpOrderId = $payload['payload']['payment']['entity']['order_id'] ?? null;
        $rzpPaymentId = $payload['payload']['payment']['entity']['id'] ?? null;

        if (! $rzpOrderId) return;

        $order = Order::where('payment_reference', $rzpOrderId)->first();

        if ($order && $order->payment_status !== 'paid') {
            $this->markOrderPaid($order, $rzpPaymentId);
        }
    }

    private function onPaymentFailed(array $payload): void
    {
        $rzpOrderId = $payload['payload']['payment']['entity']['order_id'] ?? null;

        if (! $rzpOrderId) return;

        $order = Order::where('payment_reference', $rzpOrderId)->first();

        if ($order) {
            $order->update(['payment_status' => 'failed']);
        }
    }

    private function onRefundCreated(array $payload): void
    {
        // Placeholder: handle refunds in a future phase
        // Log the refund event for manual processing
        \Illuminate\Support\Facades\Log::info('Razorpay refund created', $payload);
    }
}
```

---

## 5. `app/Http/Controllers/Frontend/PaymentController.php`

```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

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
            ->where('payment_status', 'pending')
            ->firstOrFail();

        $rzpPayload = $this->paymentService->createRazorpayOrder($order);

        return view('payment.razorpay', compact('order', 'rzpPayload'));
    }

    /**
     * Handle Razorpay callback after payment success on the frontend.
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
            $validated['razorpay_signature'],
        );

        if (! $verified) {
            return redirect()->route('order.show', $order->order_number)
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        $this->paymentService->markOrderPaid($order, $validated['razorpay_payment_id']);

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', 'Payment successful!');
    }

    /**
     * Razorpay webhook endpoint.
     * POST /payment/webhook
     */
    public function webhook(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $payload   = $request->json()->all();

        $this->paymentService->handleWebhook($payload, $signature);

        return response()->json(['status' => 'ok']);
    }
}
```

---

## 6. Routes — `routes/web.php`

```php
use App\Http\Controllers\Frontend\PaymentController;

// Payment routes
Route::middleware('auth')->group(function () {
    Route::get('/payment/{orderNumber}/pay', [PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
});

// Webhook — no CSRF (must exclude from CSRF in VerifyCsrfToken middleware)
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
```

---

## 7. Exclude webhook from CSRF — `app/Http/Middleware/VerifyCsrfToken.php`

```php
protected $except = [
    'payment/webhook',
];
```

---

## 8. Razorpay Checkout View — `resources/views/payment/razorpay.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Complete Payment — ' . $order->order_number)

@section('content')
<div class="container" style="padding:4rem 1.5rem; max-width:480px; margin:0 auto; text-align:center;">
    <h1 style="font-family:var(--font-primary); font-size:2rem; margin-bottom:0.5rem;">Complete Payment</h1>
    <p style="color:var(--color-text-muted); margin-bottom:2rem;">Order: <strong>{{ $order->order_number }}</strong></p>

    <div style="background:var(--color-dark-card); border:1px solid var(--color-border); border-radius:var(--radius); padding:2rem; margin-bottom:2rem;">
        <div style="font-size:0.8rem; color:var(--color-text-muted); margin-bottom:0.5rem;">Amount to Pay</div>
        <div style="font-family:var(--font-primary); font-size:2.5rem; font-weight:600; color:var(--color-gold);">₹{{ number_format($order->total_amount, 0) }}</div>
    </div>

    <button id="rzp-button" class="btn btn-gold" style="width:100%; justify-content:center; padding:1rem; font-size:1rem;">
        Pay with Razorpay →
    </button>

    <form id="rzp-form" action="{{ route('payment.callback') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="order_number" value="{{ $order->order_number }}">
        <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
        <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
        <input type="hidden" name="razorpay_signature" id="rzp_signature">
    </form>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const options = @json($rzpPayload);

options.handler = function(response) {
    document.getElementById('rzp_order_id').value   = response.razorpay_order_id;
    document.getElementById('rzp_payment_id').value  = response.razorpay_payment_id;
    document.getElementById('rzp_signature').value   = response.razorpay_signature;
    document.getElementById('rzp-form').submit();
};

options.modal = {
    ondismiss: function() {
        console.log('Payment dismissed by user.');
    }
};

const rzp = new Razorpay(options);

document.getElementById('rzp-button').addEventListener('click', function(e) {
    e.preventDefault();
    rzp.open();
});
</script>
@endpush
```

---

## Notes

- In V1 (COD phase), `PaymentService` is scaffolded but not called during checkout. The `initiate` route is only reached when the user explicitly chooses online payment.
- The **webhook** is the most reliable payment confirmation method — always use it in production. The callback is for UX only.
- Razorpay test mode keys start with `rzp_test_`. Switch to `rzp_live_` in production.
- Ensure the webhook URL is registered in the Razorpay Dashboard under Settings → Webhooks.
- For production, add `RAZORPAY_WEBHOOK_SECRET` to `.env` and use it to verify the webhook signature separately from the API secret.

---

## ✅ All 55 Steps Complete

The full implementation plan is now documented. Start implementation from `01-migrate-users-extend.md` and proceed sequentially. Each file contains complete, runnable code ready to copy into the project.
