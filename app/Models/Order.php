<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'order_number',
        'user_id',
        'guest_email',
        'guest_phone',
        'status',
        'shipping_name',
        'shipping_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_landmark',
        'shipping_city',
        'shipping_state',
        'shipping_pincode',
        'shipping_country',
        'subtotal',
        'discount_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'paid_at',
        'notes',
        'confirmation_email_sent_at',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'cgst_amount'     => 'decimal:2',
            'sgst_amount'     => 'decimal:2',
            'igst_amount'     => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'paid_at'         => 'datetime',
            'confirmation_email_sent_at' => 'datetime',
        ];
    }

    // =========================================================================
    // Boot — auto-generate order_number
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generates a unique order number in the format PIN-YYYY-NNNNN.
     * Example: PIN-2026-00042
     */
    protected static function generateOrderNumber(): string
    {
        $year = Carbon::now()->year;

        // Find the highest sequence number for the current year
        $last = static::where('order_number', 'like', "PIN-{$year}-%")
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = 1;
        if ($last) {
            $parts    = explode('-', $last);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('PIN-%d-%05d', $year, $sequence);
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus(Builder $query, string $status): Builder
    {
        return $query->where('payment_status', $status);
    }

    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Returns true when this order was placed by a guest (no user account).
     */
    public function isGuest(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * The display name of the customer (registered or guest).
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->user?->name ?? $this->shipping_name ?? 'Guest';
    }

    /**
     * The email address of the customer (registered or guest).
     */
    public function getCustomerEmailAttribute(): string
    {
        return $this->user?->email ?? $this->guest_email ?? '';
    }

    /**
     * Formatted shipping address as a multi-line string.
     */
    public function getFormattedShippingAddressAttribute(): string
    {
        return implode("\n", array_filter([
            $this->shipping_name,
            $this->shipping_phone,
            $this->shipping_address_line_1,
            $this->shipping_address_line_2,
            $this->shipping_landmark ? "Near {$this->shipping_landmark}" : null,
            "{$this->shipping_city} — {$this->shipping_pincode}",
            "{$this->shipping_state}, {$this->shipping_country}",
        ]));
    }

    /**
     * Returns true if the order has been fully paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
