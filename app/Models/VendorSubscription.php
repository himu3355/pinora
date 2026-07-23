<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'trial_ends_at',
        'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Determine if the subscription is currently active (trialing or active paid, including grace period).
     */
    public function isActive(): bool
    {
        // 1. Trialing check
        if ($this->status === 'trialing') {
            return $this->trial_ends_at && $this->trial_ends_at->isFuture();
        }

        // 2. Active paid check
        if ($this->status === 'active') {
            return true;
        }

        // 3. Grace period check (cancelled but not yet expired)
        if ($this->status === 'cancelled') {
            return $this->ends_at && $this->ends_at->isFuture();
        }

        return false;
    }
}
