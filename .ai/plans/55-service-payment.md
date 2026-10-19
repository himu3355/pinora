# Step 55: PaymentService & Razorpay Integration

**Goal:** Complete Razorpay online payment integration for orders, supporting UPI, Credit/Debit cards, Net Banking, signature verification, and automated webhooks.
**Depends On:** Step 54 (OrderService), Step 20 (Order model), Step 47 (Checkout)
**Status:** ✅ Fully Implemented (Stripe removed from project).

---

## Files Created & Updated

- `app/Services/PaymentService.php` (Created)
- `app/Http/Controllers/Frontend/PaymentController.php` (Created)
- `resources/views/payment/razorpay.blade.php` (Created)
- `config/services.php` (Updated with `razorpay` config)
- `.env` & `.env.example` (Updated with Razorpay keys)
- `routes/web.php` (Updated with payment initiate, callback, and webhook routes)
- `bootstrap/app.php` (Updated with CSRF exemption for `payment/webhook`)
- `app/Http/Controllers/Frontend/CheckoutController.php` (Updated with redirect to `payment.initiate`)
- `resources/views/checkout/index.blade.php` (Updated with active Razorpay payment option)
- `resources/views/account/order-detail.blade.php` (Updated with "Complete Payment" button for pending orders)
- `tests/Unit/PaymentServiceTest.php` (Unit test suite for signature verification & webhook handling)

---

## 1. Razorpay SDK Installation

```bash
composer require razorpay/razorpay
```

---

## 2. Environment Variables (`.env`)

```ini
# Razorpay Payment Gateway
RAZORPAY_KEY_ID=rzp_live_XXXXXXXXXXXXXX
RAZORPAY_KEY_SECRET=your_live_secret_key_here
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
```

---

## 3. Configuration — `config/services.php`

```php
'razorpay' => [
    'key_id'         => env('RAZORPAY_KEY_ID'),
    'key_secret'     => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
],
```

---

## 4. Payment Service — `app/Services/PaymentService.php`

```php
<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class PaymentService
{
    protected ?Api $razorpay = null;

    public function __construct()
    {
        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (! empty($keyId) && ! empty($keySecret)) {
            $this->razorpay = new Api($keyId, $keySecret);
        }
    }

    public function getApi(): Api
    {
        if (! $this->razorpay) {
            throw new \RuntimeException('Razorpay credentials (RAZORPAY_KEY_ID / RAZORPAY_KEY_SECRET) are not configured.');
        }

        return $this->razorpay;
    }

    public function createRazorpayOrder(Order $order): array
    {
        $order->loadMissing('user');

        $rzpOrder = $this->getApi()->order->create([
            'amount'   => (int) round($order->total_amount * 100),
            'currency' => 'INR',
            'receipt'  => (string) $order->order_number,
            'notes'    => [
                'platform_order_id' => (string) $order->id,
                'order_number'      => (string) $order->order_number,
                'customer_email'    => (string) ($order->user->email ?? $order->guest_email ?? ''),
            ],
        ]);

        $order->update([
            'payment_reference' => $rzpOrder->id,
        ]);

        return [
            'key'         => config('services.razorpay.key_id'),
            'amount'      => (int) round($order->total_amount * 100),
            'currency'    => 'INR',
            'name'        => config('app.name', 'Pinora Jewellery'),
            'description' => 'Order ' . $order->order_number,
            'order_id'    => $rzpOrder->id,
            'prefill'     => [
                'name'    => $order->user->name ?? $order->shipping_name,
                'email'   => $order->user->email ?? $order->guest_email ?? '',
                'contact' => $order->shipping_phone ?? ($order->user->phone ?? ''),
            ],
            'theme'       => [
                'color' => '#C9A84C',
            ],
        ];
    }

    public function verifySignature(
        string $rzpOrderId,
        string $rzpPaymentId,
        string $rzpSignature
    ): bool {
        try {
            $this->getApi()->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $rzpOrderId,
                'razorpay_payment_id' => $rzpPaymentId,
                'razorpay_signature'  => $rzpSignature,
            ]);

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay payment signature verification failed', [
                'order_id'   => $rzpOrderId,
                'payment_id' => $rzpPaymentId,
                'error'      => $e->getMessage(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Razorpay signature verification exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function markOrderPaid(Order $order, string $rzpPaymentId): void
    {
        $order->update([
            'payment_status'    => 'paid',
            'payment_reference' => $rzpPaymentId,
            'paid_at'           => now(),
            'status'            => 'confirmed',
        ]);
    }

    public function handleWebhook(array $payload, string $signature, string $rawContent = ''): void
    {
        $webhookSecret = config('services.razorpay.webhook_secret') ?: config('services.razorpay.key_secret');

        if (! empty($webhookSecret) && ! empty($signature)) {
            $body = ! empty($rawContent) ? $rawContent : json_encode($payload);
            $expectedSig = hash_hmac('sha256', $body, $webhookSecret);

            if (! hash_equals($expectedSig, $signature)) {
                Log::warning('Invalid Razorpay webhook signature received');
                abort(400, 'Invalid webhook signature.');
            }
        }

        $event = $payload['event'] ?? null;
        Log::info('Razorpay webhook received: ' . $event, ['event' => $event]);

        match ($event) {
            'payment.captured' => $this->onPaymentCaptured($payload),
            'order.paid'       => $this->onOrderPaid($payload),
            'payment.failed'   => $this->onPaymentFailed($payload),
            'refund.created'   => $this->onRefundCreated($payload),
            default            => null,
        };
    }

    private function onPaymentCaptured(array $payload): void
    {
        $rzpOrderId   = $payload['payload']['payment']['entity']['order_id'] ?? null;
        $rzpPaymentId = $payload['payload']['payment']['entity']['id'] ?? null;

        if (! $rzpOrderId) return;

        $order = Order::where('payment_reference', $rzpOrderId)->first();
        if ($order && $order->payment_status !== 'paid') {
            $this->markOrderPaid($order, $rzpPaymentId ?: $rzpOrderId);
        }
    }

    private function onOrderPaid(array $payload): void
    {
        $rzpOrderId = $payload['payload']['order']['entity']['id'] ?? null;
        if (! $rzpOrderId) return;

        $order = Order::where('payment_reference', $rzpOrderId)->first();
        if ($order && $order->payment_status !== 'paid') {
            $this->markOrderPaid($order, $rzpOrderId);
        }
    }

    private function onPaymentFailed(array $payload): void
    {
        $rzpOrderId = $payload['payload']['payment']['entity']['order_id'] ?? null;
        if (! $rzpOrderId) return;

        $order = Order::where('payment_reference', $rzpOrderId)->first();
        if ($order && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed']);
        }
    }

    private function onRefundCreated(array $payload): void
    {
        Log::info('Razorpay refund created', $payload);
    }
}
```

---

## 5. Payment Controller — `app/Http/Controllers/Frontend/PaymentController.php`

```php
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

    public function webhook(Request $request)
    {
        $signature  = $request->header('X-Razorpay-Signature', '');
        $payload    = $request->json()->all();
        $rawContent = $request->getContent();

        $this->paymentService->handleWebhook($payload, $signature, $rawContent);

        return response()->json(['status' => 'ok']);
    }
}
```

---

## 6. Routes Registered

```php
// In routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/payment/{orderNumber}/pay', [\App\Http\Controllers\Frontend\PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::post('/payment/callback', [\App\Http\Controllers\Frontend\PaymentController::class, 'callback'])->name('payment.callback');
});

// Razorpay Webhook (excluded from CSRF in bootstrap/app.php)
Route::post('/payment/webhook', [\App\Http\Controllers\Frontend\PaymentController::class, 'webhook'])->name('payment.webhook');
```

---

## 7. CSRF Exemption — `bootstrap/app.php`

```php
$middleware->validateCsrfTokens(except: [
    'payment/webhook',
]);
```

---

## 8. Razorpay Webhook Configuration in Razorpay Dashboard

1. Navigate to **Razorpay Dashboard** → **Settings** → **Webhooks**.
2. Click **Add New Webhook**.
3. **Webhook URL:** `https://yourdomain.com/payment/webhook`
4. **Secret:** Set a strong secret and save it to `.env` as `RAZORPAY_WEBHOOK_SECRET`.
5. **Active Events:**
   - `payment.captured`
   - `payment.failed`
   - `order.paid`
   - `refund.created`
