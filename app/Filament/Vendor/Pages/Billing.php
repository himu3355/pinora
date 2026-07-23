<?php

namespace App\Filament\Vendor\Pages;

use App\Models\SubscriptionPlan;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Stripe\StripeClient;

class Billing extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Billing';
    protected static ?string $title = 'Billing & Subscriptions';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.vendor.pages.billing';

    public $status = '';
    public $trialDays = 0;
    public $endsAt = null;
    public $plans = [];
    public $isStripeConfigured = false;

    public function mount()
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $sub = $vendor->subscription;
        if ($sub) {
            $this->status = $sub->status;
            $this->trialDays = $vendor->trialDaysRemaining();
            $this->endsAt = $sub->ends_at;
        }

        $this->plans = SubscriptionPlan::where('is_active', true)->get()->toArray();
        $this->isStripeConfigured = !empty(config('stripe.secret_key'));

        $sessionId = request()->query('session_id');
        if ($sessionId) {
            $stripeSecret = config('stripe.secret_key');
            if ($stripeSecret) {
                try {
                    $stripe = new StripeClient($stripeSecret);
                    $session = $stripe->checkout->sessions->retrieve($sessionId);

                    if ($session->payment_status === 'paid' && isset($session->metadata->vendor_id) && $session->metadata->vendor_id == $vendor->id) {
                        $stripeSubscriptionId = $session->payment_intent ?? $sessionId;
                        $stripeCustomerId = $session->customer;

                        $sub = \App\Models\VendorSubscription::updateOrCreate(
                            ['vendor_id' => $vendor->id],
                            [
                                'stripe_subscription_id' => $stripeSubscriptionId,
                                'stripe_customer_id' => $stripeCustomerId,
                                'status' => 'active',
                                'ends_at' => now()->addYear(),
                                'trial_ends_at' => null,
                            ]
                        );

                        $this->status = $sub->status;
                        $this->trialDays = 0;
                        $this->endsAt = $sub->ends_at;

                        Notification::make()
                            ->title('Payment Completed & Activated!')
                            ->body('Your subscription has been successfully activated.')
                            ->success()
                            ->send();
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Stripe Sync on Redirect Failed: ' . $e->getMessage());
                    Notification::make()
                        ->title('Payment completed!')
                        ->body('Your subscription payment is complete. It may take a moment for your portal features to activate.')
                        ->success()
                        ->send();
                }
            }
        }
    }

    public function subscribe($planId)
    {
        $vendor = auth()->user()->vendor;
        $plan = SubscriptionPlan::find($planId);

        if (!$plan || !$plan->is_active) {
            Notification::make()
                ->title('Invalid Plan')
                ->body('The selected subscription plan is not active or does not exist.')
                ->danger()
                ->send();
            return;
        }

        $stripeSecret = config('stripe.secret_key');
        if (!$stripeSecret) {
            Notification::make()
                ->title('Stripe Configuration Missing')
                ->body('Stripe credentials (STRIPE_SECRET_KEY) are not configured on the server. Please contact support.')
                ->danger()
                ->send();
            return;
        }

        if (!$plan->stripe_price_id) {
            Notification::make()
                ->title('Subscription Plan Unavailable')
                ->body('This plan has not been synced with Stripe. Please contact the administrator.')
                ->danger()
                ->send();
            return;
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('filament.vendor.pages.billing') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('filament.vendor.pages.billing'),
                'metadata' => [
                    'vendor_id' => $vendor->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Stripe Checkout Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
