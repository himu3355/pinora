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

    /**
     * Get the Razorpay API client instance.
     *
     * @throws \RuntimeException
     */
    public function getApi(): Api
    {
        if (! $this->razorpay) {
            throw new \RuntimeException('Razorpay credentials (RAZORPAY_KEY_ID / RAZORPAY_KEY_SECRET) are not configured.');
        }

        return $this->razorpay;
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
        $order->loadMissing('user');

        $rzpOrder = $this->getApi()->order->create([
            'amount'   => (int) round($order->total_amount * 100), // in paise
            'currency' => 'INR',
            'receipt'  => (string) $order->order_number,
            'notes'    => [
                'platform_order_id' => (string) $order->id,
                'order_number'      => (string) $order->order_number,
                'customer_email'    => (string) ($order->user->email ?? $order->guest_email ?? ''),
            ],
        ]);

        // Store the Razorpay order ID on our order for verification
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
            Log::error('Razorpay signature verification exception', [
                'error' => $e->getMessage(),
            ]);

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

        if (! $order->confirmation_email_sent_at) {
            $order->loadMissing(['items.product', 'items.vendor', 'user']);
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email ?? $order->guest_email)->queue(
                    new \App\Mail\OrderConfirmationMail($order)
                );
                $order->update(['confirmation_email_sent_at' => now()]);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Handle Razorpay webhook events.
     * Supports: payment.captured, order.paid, payment.failed, refund.created
     *
     * @param  array   $payload     Decoded webhook JSON body
     * @param  string  $signature   X-Razorpay-Signature header value
     * @param  string  $rawContent  Raw request body content
     * @return void
     */
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

    // ──────────────────────────────────────────────────
    // Private webhook event handlers
    // ──────────────────────────────────────────────────

    private function onPaymentCaptured(array $payload): void
    {
        $rzpOrderId   = $payload['payload']['payment']['entity']['order_id'] ?? null;
        $rzpPaymentId = $payload['payload']['payment']['entity']['id'] ?? null;

        if (! $rzpOrderId) {
            return;
        }

        $order = Order::where('payment_reference', $rzpOrderId)->first();

        if ($order && $order->payment_status !== 'paid') {
            $this->markOrderPaid($order, $rzpPaymentId ?: $rzpOrderId);
        }
    }

    private function onOrderPaid(array $payload): void
    {
        $rzpOrderId = $payload['payload']['order']['entity']['id'] ?? null;

        if (! $rzpOrderId) {
            return;
        }

        $order = Order::where('payment_reference', $rzpOrderId)->first();

        if ($order && $order->payment_status !== 'paid') {
            $this->markOrderPaid($order, $rzpOrderId);
        }
    }

    private function onPaymentFailed(array $payload): void
    {
        $rzpOrderId = $payload['payload']['payment']['entity']['order_id'] ?? null;

        if (! $rzpOrderId) {
            return;
        }

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
