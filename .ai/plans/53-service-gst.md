# Step 53: GstService

**Goal:** Determine whether to apply intra-state (CGST + SGST) or inter-state (IGST) tax, and compute amounts.
**Depends On:** Step 09 (Orders migration — cgst_amount, sgst_amount, igst_amount columns)
**Next Step:** Step 54 (OrderService)

---

## Files to Create

- `app/Services/GstService.php`

---

## 1. `app/Services/GstService.php`

```php
<?php

namespace App\Services;

class GstService
{
    /**
     * Total GST rate for jewellery in India is 3%.
     */
    private const GST_RATE = 3.0;

    /**
     * For intra-state: each component is half of total (1.5% each).
     */
    private const HALF_GST = 1.5;

    /**
     * Calculate the GST breakdown for a given subtotal based on vendor state and delivery state.
     *
     * Intra-state (same state as vendor): CGST + SGST each 1.5%
     * Inter-state (different state): IGST 3%
     *
     * @param  float   $subtotal       The taxable amount (before GST)
     * @param  string  $vendorState    The state where the vendor is located
     * @param  string  $deliveryState  The state where delivery is happening
     * @return array{
     *     type:        string,  'split' | 'igst'
     *     cgst:        float,
     *     sgst:        float,
     *     igst:        float,
     *     total_gst:   float,
     *     grand_total: float,
     * }
     */
    public function calculate(float $subtotal, string $vendorState, string $deliveryState): array
    {
        $isIntraState  = strcasecmp(trim($deliveryState), trim($vendorState)) === 0;

        $totalGst = round($subtotal * self::GST_RATE / 100, 2);

        if ($isIntraState) {
            $cgst = round($subtotal * self::HALF_GST / 100, 2);
            $sgst = round($subtotal * self::HALF_GST / 100, 2);
            // Adjust for rounding difference
            if ($cgst + $sgst !== $totalGst) {
                $sgst = $totalGst - $cgst;
            }

            return [
                'type'        => 'split',
                'cgst'        => $cgst,
                'sgst'        => $sgst,
                'igst'        => 0.0,
                'total_gst'   => $totalGst,
                'grand_total' => round($subtotal + $totalGst, 2),
            ];
        }

        return [
            'type'        => 'igst',
            'cgst'        => 0.0,
            'sgst'        => 0.0,
            'igst'        => $totalGst,
            'total_gst'   => $totalGst,
            'grand_total' => round($subtotal + $totalGst, 2),
        ];
    }

    /**
     * Convenience method: calculate GST for an order item.
     * Jewellery (HSN 7113) attracts 3% GST.
     *
     * @param  float   $unitPrice      Price of one unit (excl. GST)
     * @param  int     $quantity
     * @param  string  $vendorState
     * @param  string  $deliveryState
     * @return array   Same shape as calculate()
     */
    public function calculateForItem(float $unitPrice, int $quantity, string $vendorState, string $deliveryState): array
    {
        $subtotal = round($unitPrice * $quantity, 2);
        return $this->calculate($subtotal, $vendorState, $deliveryState);
    }

    /**
     * Get the GST rate as a decimal multiplier (e.g. 1.03 for 3%).
     */
    public function getMultiplier(): float
    {
        return 1 + (self::GST_RATE / 100);
    }

    /**
     * Extract GST from an inclusive price (i.e., price already includes 3% GST).
     *
     * @param  float  $inclusivePrice
     * @return array{ subtotal: float, gst: float }
     */
    public function extractFromInclusive(float $inclusivePrice): array
    {
        $subtotal = round($inclusivePrice / $this->getMultiplier(), 2);
        $gst      = round($inclusivePrice - $subtotal, 2);

        return ['subtotal' => $subtotal, 'gst' => $gst];
    }
}
```

---

## Notes

- GST for jewellery in India: **3%** flat (HSN 7113). Source: GST Council.
- The GST breakdown is calculated per item based on the specific **Vendor's state** relative to the **Customer's shipping state**.
- If delivery state matches vendor state → CGST (1.5%) + SGST (1.5%) — intra-state.
- If delivery state differs from vendor state → IGST (3%) — inter-state.
- The state comparison uses `strcasecmp` to handle case sensitivity (e.g. "Maharashtra" vs "maharashtra").
- The return `type` key (`'split'` vs `'igst'`) drives the display in the checkout summary and is saved on each Order Item and summarized on the Order record.
