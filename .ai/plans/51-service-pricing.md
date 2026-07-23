# Step 51: PricingService

**Goal:** Implement the core business logic for jewellery pricing calculation based on dynamic metal rates, weights, making charges, stone charges, discounts, and GST.
**Depends On:** Step 15 (MetalRate model), Step 16 (Product & ProductVariant models)
**Next Step:** Step 52 (CommissionService)

---

## Goal Explanation

Unlike standard e-commerce products with static prices, fine jewellery pricing is highly dynamic. The price is derived from:
1. **Live Metal Cost:** The product's metal weight multiplied by the daily rate per gram for its specific metal type (Gold, Silver, Platinum) and purity (e.g., 22K, 18K).
2. **Making Charges:** Can be a fixed amount, a flat rate per gram, or a percentage of the metal cost.
3. **Stone Charges:** Flat value of diamonds or gemstones embedded in the piece.
4. **Base Price Override:** For select products, the vendor can specify a fixed base price that overrides the metal rate calculation.
5. **Discounts & Taxes:** Subtract vendor discount and add the flat 3% GST applicable to jewellery in India.

The `PricingService` centralises this math, exposing methods to calculate final prices and return a complete price breakdown (useful for frontend product pages, carts, and order snapshots).

---

## File to Create

- `app/Services/PricingService.php`

---

## Complete PHP Code

### `app/Services/PricingService.php`

```php
<?php

namespace App\Services;

use App\Models\MetalRate;
use App\Models\Product;
use App\Models\ProductVariant;
use InvalidArgumentException;

class PricingService
{
    /**
     * Calculate the final total price of a product.
     */
    public function calculateForProduct(Product $product): float
    {
        $breakdown = $this->getBreakdownForProduct($product);
        return $breakdown['total_price'];
    }

    /**
     * Calculate the final total price of a product variant.
     */
    public function calculateForVariant(ProductVariant $variant): float
    {
        $breakdown = $this->getBreakdownForVariant($variant);
        return $breakdown['total_price'];
    }

    /**
     * Get detailed price breakdown for a product.
     */
    public function getBreakdownForProduct(Product $product): array
    {
        return $this->calculateBreakdown(
            product: $product,
            variant: null
        );
    }

    /**
     * Get detailed price breakdown for a product variant.
     */
    public function getBreakdownForVariant(ProductVariant $variant): array
    {
        // Load parent product if not loaded
        $product = $variant->product;
        if (!$product) {
            throw new InvalidArgumentException("Variant has no associated product.");
        }

        return $this->calculateBreakdown(
            product: $product,
            variant: $variant
        );
    }

    /**
     * Perform the actual breakdown calculation.
     */
    protected function calculateBreakdown(Product $product, ?ProductVariant $variant = null): array
    {
        // 1. Determine override price or formula
        $basePrice = $variant ? $variant->base_price : $product->base_price;
        $weight = $variant ? $variant->weight_grams : $product->weight_grams;
        
        $metalRateUsed = 0.00;
        $metalCost = 0.00;
        $makingChargesAmount = 0.00;
        $stoneCharges = (float) ($product->stone_charges ?? 0.00);

        if ($basePrice !== null && (float) $basePrice > 0) {
            // If base price override exists, use it directly as the raw base cost
            $rawCost = (float) $basePrice;
        } else {
            // Otherwise, calculate dynamically from metal rate + weight + making charges
            if (empty($product->metal_type) || empty($product->purity)) {
                $rawCost = 0.00;
            } else {
                // Fetch the latest rate from MetalRate table
                $rateRecord = MetalRate::getLatestRate($product->metal_type, $product->purity);
                $metalRateUsed = $rateRecord ? (float) $rateRecord->rate_per_gram : 0.00;
                
                // Calculate metal cost
                $metalCost = $weight * $metalRateUsed;

                // Determine making charges
                $makingChargesRate = $variant && $variant->making_charges !== null 
                    ? (float) $variant->making_charges 
                    : (float) $product->making_charges;

                // Support different making charges types: 'fixed', 'per_gram', or 'percentage'
                $chargesType = $product->making_charges_type ?? 'fixed';

                if ($chargesType === 'per_gram') {
                    $makingChargesAmount = $makingChargesRate * $weight;
                } elseif ($chargesType === 'percentage') {
                    $makingChargesAmount = ($metalCost * $makingChargesRate) / 100;
                } else {
                    // Default to 'fixed'
                    $makingChargesAmount = $makingChargesRate;
                }

                // Raw cost is sum of Metal Cost, Making Charges, and Stone Charges
                $rawCost = $metalCost + $makingChargesAmount + $stoneCharges;
            }
        }

        // 2. Apply discount if any
        $discountPercent = (float) ($product->discount_percent ?? 0.00);
        $discountAmount = ($rawCost * $discountPercent) / 100;
        $subtotal = $rawCost - $discountAmount;

        // 3. Product price excludes GST (GST is calculated once during order checkout)
        $gstRate = 0.00;
        $gstAmount = 0.00;
        $totalPrice = $subtotal;

        return [
            'base_price_override'=> $basePrice ? (float) $basePrice : null,
            'metal_type'         => $product->metal_type,
            'purity'             => $product->purity,
            'weight_grams'       => (float) $weight,
            'metal_rate_used'    => $metalRateUsed,
            'metal_cost'         => round($metalCost, 2),
            'making_charges_rate'=> $variant && $variant->making_charges !== null ? (float) $variant->making_charges : (float) $product->making_charges,
            'making_charges_type'=> $product->making_charges_type ?? 'fixed',
            'making_charges'     => round($makingChargesAmount, 2),
            'stone_charges'      => round($stoneCharges, 2),
            'raw_cost'           => round($rawCost, 2),
            'discount_percent'   => $discountPercent,
            'discount_amount'    => round($discountAmount, 2),
            'subtotal'           => round($subtotal, 2),
            'gst_rate'           => $gstRate,
            'gst_amount'         => round($gstAmount, 2),
            'total_price'        => round($totalPrice, 2),
        ];
    }
}
```

---

## Notes

- **Decimal Precision:** All return values are rounded to 2 decimal places to prevent float rounding errors when storing values in orders and order items tables.
- **Cache Reliance:** `MetalRate::getLatestRate()` is cached, which prevents the pricing calculation from hammering the database when displaying product grids.
- **Variant Override Logic:** A variant's `weight_grams` and `making_charges` take precedence over the product's values. If not defined on the variant, it defaults back to the parent product values.
- **Tax Policy:** The GST rate of 3.00% is hardcoded into the calculation. This represents the standard consolidated GST rate on finished jewellery in India.
