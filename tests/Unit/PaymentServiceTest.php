<?php

namespace Tests\Unit;

use App\Services\PaymentService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_payment_service_instantiation(): void
    {
        $service = new PaymentService();
        $this->assertInstanceOf(PaymentService::class, $service);
    }

    public function test_verify_signature_returns_false_on_invalid_signature(): void
    {
        config([
            'services.razorpay.key_id'     => 'rzp_test_sample',
            'services.razorpay.key_secret' => 'test_secret_12345',
        ]);

        $service = new PaymentService();
        $result = $service->verifySignature('order_fake_123', 'pay_fake_456', 'invalid_signature_xyz');

        $this->assertFalse($result);
    }

    public function test_handle_webhook_aborts_on_invalid_signature(): void
    {
        config([
            'services.razorpay.webhook_secret' => 'secret_wh_123',
        ]);

        $this->expectException(HttpException::class);

        $service = new PaymentService();
        $payload = ['event' => 'payment.captured'];
        $raw = json_encode($payload);

        $service->handleWebhook($payload, 'invalid_sig', $raw);
    }
}
