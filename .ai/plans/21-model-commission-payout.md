# Step 21 — Commission & Payout Models

| Field        | Value                                            |
|-------------|--------------------------------------------------|
| **Goal**    | Create `app/Models/Commission.php` and `app/Models/Payout.php` |
| **Depends** | Step 10 (commissions + payouts migrations)       |
| **Next**    | Step 22 (Review Model)                           |

---

## Goal Explanation

When an order item is fulfilled, the platform calculates its commission from the vendor's configured `commission_rate`. A `Commission` record stores the financial split between the platform and the vendor. Periodically (weekly/monthly), an admin generates a `Payout` that bundles all pending commissions for a vendor into a single bank transfer record. Payout references are auto-generated in the same style as order numbers for easy support tracking.

---

## Files to Create

```
app/Models/Commission.php
app/Models/Payout.php
```

---

## Complete PHP Code

### `app/Models/Commission.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'order_item_id',
        'vendor_id',
        'order_amount',
        'commission_rate',
        'commission_amount',
        'vendor_earnings',
        'payout_id',
        'status',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'order_amount'      => 'decimal:2',
            'commission_rate'   => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'vendor_earnings'   => 'decimal:2',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Commissions that have not yet been included in any payout.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Commissions that have been paid to the vendor via a payout.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Commissions for a specific vendor.
     */
    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Commissions that are pending for a specific vendor — convenience composite scope.
     */
    public function scopePendingForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->pending()->forVendor($vendorId);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Calculate the commission amount and vendor earnings for a given order
     * amount and rate (percentage). Does NOT save the model.
     *
     * Usage:
     *   [$commissionAmt, $vendorEarnings] = Commission::calculate(5000.00, 12.00);
     *
     * @param  float  $orderAmount     Total item value
     * @param  float  $commissionRate  Platform commission % (e.g. 12.00 = 12%)
     * @return array{0: float, 1: float}  [commissionAmount, vendorEarnings]
     */
    public static function calculate(float $orderAmount, float $commissionRate): array
    {
        $commissionAmount = round($orderAmount * $commissionRate / 100, 2);
        $vendorEarnings   = round($orderAmount - $commissionAmount, 2);

        return [$commissionAmount, $vendorEarnings];
    }
}
```

---

### `app/Models/Payout.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Payout extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'payout_reference',
        'vendor_id',
        'period_from',
        'period_to',
        'total_order_amount',
        'total_commission_amount',
        'total_vendor_earnings',
        'tax_deducted_at_source',
        'net_payable',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
        'paid_at',
        'processed_by',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'period_from'             => 'date',
            'period_to'               => 'date',
            'total_order_amount'      => 'decimal:2',
            'total_commission_amount' => 'decimal:2',
            'total_vendor_earnings'   => 'decimal:2',
            'tax_deducted_at_source'  => 'decimal:2',
            'net_payable'             => 'decimal:2',
            'paid_at'                 => 'datetime',
        ];
    }

    // =========================================================================
    // Boot — auto-generate payout_reference
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payout $payout): void {
            if (empty($payout->payout_reference)) {
                $payout->payout_reference = static::generatePayoutReference();
            }
        });
    }

    /**
     * Generates a unique payout reference in the format PAY-YYYY-NNNNN.
     * Example: PAY-2026-00007
     */
    protected static function generatePayoutReference(): string
    {
        $year = Carbon::now()->year;

        $last = static::where('payout_reference', 'like', "PAY-{$year}-%")
            ->orderByDesc('payout_reference')
            ->value('payout_reference');

        $sequence = 1;
        if ($last) {
            $parts    = explode('-', $last);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('PAY-%d-%05d', $year, $sequence);
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * All commission records bundled into this payout.
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * The admin user who processed / confirmed this payout.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Payouts that are still being prepared (not yet sent to the bank).
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Payouts that have been successfully transferred.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Payouts for a specific vendor.
     */
    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Marks the payout as paid and updates all linked commissions to 'paid'.
     * Should be called inside a DB transaction.
     *
     * @param  int  $processedByUserId
     */
    public function markAsPaid(int $processedByUserId): void
    {
        $this->update([
            'status'       => 'paid',
            'paid_at'      => now(),
            'processed_by' => $processedByUserId,
        ]);

        $this->commissions()->update(['status' => 'paid']);
    }

    /**
     * Returns a formatted period string for display.
     * Example: "01 Jun 2026 – 30 Jun 2026"
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->period_from->format('d M Y')
            . ' – '
            . $this->period_to->format('d M Y');
    }
}
```

---

## Notes

1. **`Commission::calculate()` static method** — Intentionally kept as a pure calculation helper (no DB side-effects) so it can be unit-tested without hitting the database. The `PayoutService` (future step) will call it during order fulfilment.

2. **TDS (Tax Deducted at Source)** — The `tax_deducted_at_source` column on `Payout` stores any TDS amount deducted before the bank transfer (applicable if vendor earnings exceed the TDS threshold under Indian tax law). The `net_payable = total_vendor_earnings - tax_deducted_at_source`.

3. **Payout lifecycle** — Typical status flow:
   ```
   draft → processing → paid
                      ↘ failed
   ```
   Use an `App\Enums\PayoutStatus` enum class to enforce valid transitions.

4. **`markAsPaid()` transaction** — Always call within `DB::transaction()` in the Filament action or service:
   ```php
   DB::transaction(fn () => $payout->markAsPaid(auth()->id()));
   ```

5. **Commission status on payout creation** — When a `Payout` record is created (status = `draft`), commissions are linked by setting their `payout_id` but keeping `status = 'pending'`. The `markAsPaid()` call flips them to `'paid'` atomically.

6. **Concurrent payout reference generation** — Same race condition caveat as `Order::generateOrderNumber()`. Use a Redis counter or DB sequence in production.
