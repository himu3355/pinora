# Step 20 — Order & OrderItem Models

| Field        | Value                                        |
|-------------|----------------------------------------------|
| **Goal**    | Create `app/Models/Order.php` and `app/Models/OrderItem.php` |
| **Depends** | Step 09 (orders + order_items migrations)    |
| **Next**    | Step 21 (Commission & Payout Models)         |

---

## Goal Explanation

An `Order` is the top-level transaction record: it belongs to a user (or is a guest order), has a unique human-readable `order_number`, and stores consolidated financial totals including tax breakdown. An `Order` contains one or more `OrderItem` records, each belonging to a specific vendor's product. This split enables per-vendor fulfilment tracking: each item can be shipped independently by its vendor while the order as a whole tracks payment status.

---

## Files to Create

```
app/Models/Order.php
app/Models/OrderItem.php
```

---

## Complete PHP Code

### `app/Models/Order.php`

```php
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
        // Guest fields
        'guest_name',
        'guest_email',
        'guest_phone',
        // Address snapshot
        'shipping_name',
        'shipping_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_pincode',
        'shipping_country',
        // Financial
        'subtotal',
        'discount_code',
        'discount_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
        // Payment
        'payment_method',
        'payment_status',
        'payment_reference',
        'paid_at',
        // Order status
        'status',
        'notes',
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

    /**
     * All commissions generated from this order's items.
     */
    public function commissions(): HasManyThrough
    {
        return $this->hasManyThrough(Commission::class, OrderItem::class);
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
        return $this->user?->name ?? $this->guest_name ?? 'Guest';
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
```

---

### `app/Models/OrderItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'order_id',
        'vendor_id',
        'product_id',
        'product_variant_id',
        // Snapshot of product details at time of purchase
        'product_name',
        'variant_name',
        'sku',
        'metal_type',
        'purity',
        'weight_grams',
        'making_charges',
        // Pricing
        'unit_price',
        'quantity',
        'subtotal',
        'discount_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_price',
        // Fulfilment
        'fulfillment_status',
        'tracking_number',
        'courier_name',
        'shipped_at',
        'delivered_at',
        'notes',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'weight_grams'   => 'decimal:3',
            'making_charges' => 'decimal:2',
            'unit_price'     => 'decimal:2',
            'subtotal'       => 'decimal:2',
            'discount_amount'=> 'decimal:2',
            'cgst_amount'    => 'decimal:2',
            'sgst_amount'    => 'decimal:2',
            'igst_amount'    => 'decimal:2',
            'total_price'    => 'decimal:2',
            'quantity'       => 'integer',
            'shipped_at'     => 'datetime',
            'delivered_at'   => 'datetime',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByFulfillmentStatus(Builder $query, string $status): Builder
    {
        return $query->where('fulfillment_status', $status);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Returns true when the item has been shipped.
     */
    public function isShipped(): bool
    {
        return $this->fulfillment_status === 'shipped' && ! is_null($this->shipped_at);
    }

    /**
     * Returns true when the item has been delivered.
     */
    public function isDelivered(): bool
    {
        return $this->fulfillment_status === 'delivered' && ! is_null($this->delivered_at);
    }
}
```

---

## Notes

1. **Order number race condition** — The `generateOrderNumber()` method uses `orderByDesc('order_number')` + parsing. Under heavy load, two concurrent orders may read the same last sequence number. Mitigate with:
   - A DB sequence (PostgreSQL) or
   - A Redis atomic counter (`Redis::incr("order_seq:{$year}")`) or
   - A DB-level unique constraint on `order_number` combined with a retry loop.

2. **Snapshot fields on OrderItem** — `product_name`, `variant_name`, `sku`, `metal_type`, `purity`, `weight_grams` are snapshotted at checkout time. This ensures order history remains accurate even if the vendor later edits or deletes the product.

3. **Tax split** — `cgst_amount + sgst_amount` is used for intra-state transactions; `igst_amount` is used for inter-state. Only one pair should be non-zero per item. The `OrderService` (future step) handles this logic based on vendor state vs. customer shipping state.

4. **Guest orders** — `user_id` is nullable. Guest contact details are stored in `guest_name`, `guest_email`, `guest_phone`. After a guest places an order, prompt them to register — optionally linking the order to their new account.

5. **`hasManyThrough` commissions** — `Order::commissions()` gives a direct relationship to all `Commission` records via `OrderItem`. Useful for aggregating total platform earnings per order in admin reports.

6. **`product_variant_id` nullable** — If a product has no variants, this column is `null`. Always check `$item->variant` before accessing its properties.
