<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stripe_product_id',
        'stripe_price_id',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (SubscriptionPlan $plan) {
            // Only sync if price or name or description changed, or if Stripe IDs are missing
            if ($plan->isDirty(['name', 'price', 'description']) || !$plan->stripe_price_id || !$plan->stripe_product_id) {
                $stripeSecret = config('stripe.secret_key');
                if ($stripeSecret) {
                    try {
                        $stripe = new \Stripe\StripeClient($stripeSecret);

                        // 1. Ensure Stripe Product exists
                        if (!$plan->stripe_product_id) {
                            $product = $stripe->products->create([
                                'name' => $plan->name,
                                'description' => $plan->description,
                            ]);
                            $plan->stripe_product_id = $product->id;
                        } else {
                            $stripe->products->update($plan->stripe_product_id, [
                                'name' => $plan->name,
                                'description' => $plan->description,
                            ]);
                        }

                        // 2. Create Stripe Price if amount is new/dirty or price id is missing
                        if ($plan->isDirty('price') || !$plan->stripe_price_id) {
                            $price = $stripe->prices->create([
                                'unit_amount' => (int) ($plan->price * 100), // in cents
                                'currency' => 'inr',
                                'product' => $plan->stripe_product_id,
                            ]);
                            $plan->stripe_price_id = $price->id;
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Stripe Plan Sync Failed: ' . $e->getMessage());
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'name' => 'Failed to sync plan with Stripe: ' . $e->getMessage(),
                        ]);
                    }
                }
            }
        });
    }
}
