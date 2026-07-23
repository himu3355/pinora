# Step 52: CommissionService

**Goal:** Calculate platform commission and vendor earnings for each order item at checkout.
**Depends On:** Step 21 (Commission model), Step 13 (Vendor model)
**Next Step:** Step 53 (GstService)

---

## Files to Create

- `app/Services/CommissionService.php`

---

## `app/Services/CommissionService.php`

```php
<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\OrderItem;
use App\Models\Vendor;

class CommissionService
{
    /**
     * Calculate commission amounts for a given order item total and vendor.
     *
     * @param  float   $orderAmount  The order item's total price (excluding GST)
     * @param  Vendor  $vendor       The vendor for this item
     * @return array{
     *     order_amount:       float,
     *     commission_rate:    float,
     *     commission_amount:  float,
     *     vendor_earnings:    float,
     * }
     */
    public function calculate(float $orderAmount, Vendor $vendor): array
    {
        $commissionRate   = (float) $vendor->commission_rate; // e.g. 10.00 for 10%
        $commissionAmount = round($orderAmount * $commissionRate / 100, 2);
        $vendorEarnings   = round($orderAmount - $commissionAmount, 2);

        return [
            'order_amount'      => $orderAmount,
            'commission_rate'   => $commissionRate,
            'commission_amount' => $commissionAmount,
            'vendor_earnings'   => $vendorEarnings,
        ];
    }

    /**
     * Record a commission entry in the database for a placed order item.
     *
     * Called from OrderService after each OrderItem is created.
     *
     * @param  OrderItem  $orderItem  The just-created order item
     * @param  Vendor     $vendor     The vendor associated with the item
     * @return Commission
     */
    public function record(OrderItem $orderItem, Vendor $vendor): Commission
    {
        // Commission is calculated on the item subtotal (before GST)
        $orderAmount = (float) $orderItem->subtotal;

        $breakdown = $this->calculate($orderAmount, $vendor);

        return Commission::create([
            'order_item_id'    => $orderItem->id,
            'vendor_id'        => $vendor->id,
            'order_amount'     => $breakdown['order_amount'],
            'commission_rate'  => $breakdown['commission_rate'],
            'commission_amount'=> $breakdown['commission_amount'],
            'vendor_earnings'  => $breakdown['vendor_earnings'],
            'status'           => 'pending',
        ]);
    }

    /**
     * Get the total pending earnings for a vendor (unpaid commissions).
     *
     * @param  int  $vendorId
     * @return float
     */
    public function pendingEarnings(int $vendorId): float
    {
        return (float) Commission::where('vendor_id', $vendorId)
            ->where('status', 'pending')
            ->sum('vendor_earnings');
    }

    /**
     * Get total commission amount earned by the platform from a vendor.
     *
     * @param  int  $vendorId
     * @return float
     */
    public function totalPlatformEarnings(int $vendorId): float
    {
        return (float) Commission::where('vendor_id', $vendorId)
            ->sum('commission_amount');
    }

    /**
     * Mark a list of commission IDs as paid against a payout.
     *
     * @param  array  $commissionIds
     * @param  int    $payoutId
     * @return void
     */
    public function markAsPaid(array $commissionIds, int $payoutId): void
    {
        Commission::whereIn('id', $commissionIds)
            ->update([
                'payout_id' => $payoutId,
                'status'    => 'paid',
            ]);
    }
}
```

---

## Notes

- Commission is always calculated on the **item subtotal (before GST)**. GST is collected on behalf of the government and is not included in the vendor revenue share.
- `commission_rate` is stored per vendor in `vendors.commission_rate` (set by admin at approval time or updated later). Admin can change the rate independently per vendor.
- The `record()` method is called inside `OrderService::placeOrder()` (Step 54) within the same database transaction so no commission is saved if the order fails.
- A `Commission` record has `status = 'pending'` until the admin generates a payout (Step 31) and marks it `paid`.
