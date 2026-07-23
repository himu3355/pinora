<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        // Signature verification is bypassed if webhook secret is not set (e.g. local testing without CLI)
        if ($webhookSecret) {
            try {
                $event = Webhook::constructEvent(
                    $payload, $sigHeader, $webhookSecret
                );
            } catch (\UnexpectedValueException $e) {
                Log::error('Stripe webhook failed: Invalid payload');
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                Log::error('Stripe webhook failed: Invalid signature: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        } else {
            // For local development testing without webhook secret verification
            $data = json_decode($payload, true);
            if (!$data || !isset($data['type'])) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }
            $event = json_decode($payload);
        }

        Log::info('Stripe webhook received: ' . $event->type);

        $stripe = new StripeClient(config('stripe.secret_key'));

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $vendorId = $session->metadata->vendor_id ?? null;
                $stripeSubscriptionId = $session->payment_intent ?? $session->id;
                $stripeCustomerId = $session->customer ?? null;

                if ($vendorId) {
                    $vendor = Vendor::find($vendorId);
                    if ($vendor) {
                        // Upsert the paid subscription
                        VendorSubscription::updateOrCreate(
                            ['vendor_id' => $vendor->id],
                            [
                                'stripe_subscription_id' => $stripeSubscriptionId,
                                'stripe_customer_id' => $stripeCustomerId,
                                'status' => 'active',
                                'ends_at' => now()->addYear(),
                                'trial_ends_at' => null, // clear trial
                            ]
                        );
                        Log::info("Vendor {$vendor->id} subscription activated via webhook one-time payment: {$stripeSubscriptionId}");
                    }
                }
                break;

            case 'customer.subscription.updated':
                $stripeSubObj = $event->data->object;
                $dbSub = VendorSubscription::where('stripe_subscription_id', $stripeSubObj->id)->first();
                if ($dbSub) {
                    $dbSub->update([
                        'status' => $stripeSubObj->status,
                        'ends_at' => $stripeSubObj->current_period_end 
                            ? Carbon::createFromTimestamp($stripeSubObj->current_period_end) 
                            : now()->addYear(),
                    ]);
                    Log::info("Vendor subscription updated: {$stripeSubObj->id} to status: {$stripeSubObj->status}");
                }
                break;

            case 'customer.subscription.deleted':
                $stripeSubObj = $event->data->object;
                $dbSub = VendorSubscription::where('stripe_subscription_id', $stripeSubObj->id)->first();
                if ($dbSub) {
                    $dbSub->update([
                        'status' => 'expired',
                        'ends_at' => now(),
                    ]);
                    Log::info("Vendor subscription deleted/expired: {$stripeSubObj->id}");
                }
                break;
        }

        return response()->json(['success' => true]);
    }
}
