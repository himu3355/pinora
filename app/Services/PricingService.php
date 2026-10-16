<?php

namespace App\Services;

use App\Models\MetalRate;
use App\Models\Product;
use App\Models\ProductVariant;
use InvalidArgumentException;

class PricingService
{
    // -------------------------------------------------------------------------
    // GST rate for jewellery in India (HSN 7113) — flat 3%
    // -------------------------------------------------------------------------
    public const GST_RATE = 3.0;

    // -------------------------------------------------------------------------
    // Public entry-points
    // -------------------------------------------------------------------------

    /**
     * Calculate the final total price (incl. GST) of a product.
     */
    public function calculateForProduct(Product $product): float
    {
        $breakdown = $this->getBreakdownForProduct($product);
        return $breakdown['total_price'];
    }

    /**
     * Calculate the final total price (incl. GST) of a product variant.
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
        return $this->calculateBreakdown(product: $product, variant: null);
    }

    /**
     * Get detailed price breakdown for a product variant.
     */
    public function getBreakdownForVariant(ProductVariant $variant): array
    {
        $product = $variant->product;
        if (!$product) {
            throw new InvalidArgumentException("Variant has no associated product.");
        }

        return $this->calculateBreakdown(product: $product, variant: $variant);
    }

    /**
     * Calculate and return shorthand final, original, and discount prices.
     * Used by cart / product-detail controllers.
     */
    public function calculate($product, ?int $variantId = null): array
    {
        if ($variantId) {
            $variant   = \App\Models\ProductVariant::findOrFail($variantId);
            $breakdown = $this->getBreakdownForVariant($variant);
        } else {
            $breakdown = $product instanceof \App\Models\ProductVariant
                ? $this->getBreakdownForVariant($product)
                : $this->getBreakdownForProduct($product);
        }

        $breakdown['metal_rate']     = $breakdown['metal_rate_used'];
        $breakdown['weight']         = $breakdown['weight_grams'];
        $breakdown['final_price']    = $breakdown['total_price'];
        $breakdown['original_price'] = round($breakdown['raw_cost'], 2);

        return $breakdown;
    }

    // -------------------------------------------------------------------------
    // Core calculation engine
    // -------------------------------------------------------------------------

    /**
     * Perform the full price breakdown calculation.
     *
     * Pricing rules by product_type:
     *
     * ┌─────────────┬──────────────────────────────────────────────────────┐
     * │ jewellery   │ net_weight × rate + labour% + GST 3%                 │
     * │             │  net_weight = weight_grams − loss                    │
     * │ coin_bar    │ net_weight × rate + labour% + GST 3% → floor()       │
     * │ mrp_fixed   │ base_price is the final MRP (GST-inclusive by law)   │
     * │ mrp_linked  │ (net_weight × rate + labour%) ÷ mrp_pieces = MRP/pc  │
     * └─────────────┴──────────────────────────────────────────────────────┘
     *
     * NOTE on purity vs rate:
     *   The MetalRate table stores the *alloy rate* for each purity
     *   (e.g. 22K gold = ₹13,435/g — already purity-adjusted).
     *   Therefore net_weight × alloy_rate gives the correct metal cost
     *   without an additional purity multiplier.
     */
    protected function calculateBreakdown(Product $product, ?ProductVariant $variant = null): array
    {
        $productType = $product->product_type ?? 'jewellery';

        // ── 1. MRP Fixed ─────────────────────────────────────────────────────
        // base_price IS the final consumer price (MRP is always GST-inclusive
        // under Indian law). No further labour or GST is added.
        if ($productType === 'mrp_fixed') {
            $mrp = $basePrice = (float) ($product->base_price ?? 0);
            return [
                'product_type'        => 'mrp_fixed',
                'is_price_on_request' => false,
                'use_override'        => true,
                'metal_type'          => $product->metal_type,
                'purity'              => $product->purity,
                'weight_grams'        => (float) ($product->weight_grams ?? 0),
                'net_weight'          => (float) ($product->weight_grams ?? 0),
                'metal_rate_used'     => 0,
                'metal_cost'          => 0,
                'making_charges_rate' => 0,
                'making_charges_type' => 'fixed',
                'making_charges'      => 0,
                'raw_cost'            => round($mrp, 2),
                'discount_percent'    => 0,
                'discount_amount'     => 0,
                'subtotal'            => round($mrp, 2),
                'gst_rate'            => 0,   // GST is already included in MRP
                'gst_amount'          => 0,
                'total_price'         => round($mrp, 2),
            ];
        }

        // ── 2. Resolve weight: gross weight minus loss ────────────────────────
        $grossWeight = (float) ($variant?->weight_grams ?? $product->weight_grams ?? 0);
        $loss        = (float) ($product->loss ?? 0);
        $netWeight   = max(0, $grossWeight - $loss);

        // ── 3. Fetch metal rate (purity-specific alloy rate) ──────────────────
        $metalRateUsed = 0.00;
        $metalCost     = 0.00;

        if (!empty($product->metal_type) && !empty($product->purity)) {
            $rateRecord    = MetalRate::getLatestRate($product->metal_type, $product->purity);
            $metalRateUsed = $rateRecord ? (float) $rateRecord->rate_per_gram : 0.00;
            $metalCost     = $netWeight * $metalRateUsed;
        }

        // ── 4. Making / Labour charges ────────────────────────────────────────
        $makingChargesRate = $variant && $variant->making_charges !== null
            ? (float) $variant->making_charges
            : (float) ($product->making_charges ?? 0);

        $chargesType = $product->making_charges_type ?? 'fixed';

        $makingChargesAmount = match ($chargesType) {
            'per_gram'   => $makingChargesRate * $netWeight,
            'percentage' => ($metalCost * $makingChargesRate) / 100,
            default      => $makingChargesRate,   // fixed
        };

        // ── 5. MRP Linked ─────────────────────────────────────────────────────
        // Total batch cost divided by number of pieces = per-piece MRP.
        // No additional GST is added (MRP is consumer-final by law).
        if ($productType === 'mrp_linked') {
            $totalBatchCost = $metalCost + $makingChargesAmount;
            $pieces         = max(1, (int) ($product->mrp_pieces ?? 1));
            $perPieceMrp    = round($totalBatchCost / $pieces, 2);

            return [
                'product_type'        => 'mrp_linked',
                'is_price_on_request' => false,
                'use_override'        => false,
                'metal_type'          => $product->metal_type,
                'purity'              => $product->purity,
                'weight_grams'        => round($grossWeight, 3),
                'net_weight'          => round($netWeight, 3),
                'metal_rate_used'     => round($metalRateUsed, 2),
                'metal_cost'          => round($metalCost, 2),
                'making_charges_rate' => $makingChargesRate,
                'making_charges_type' => $chargesType,
                'making_charges'      => round($makingChargesAmount, 2),
                'mrp_pieces'          => $pieces,
                'batch_cost'          => round($totalBatchCost, 2),
                'raw_cost'            => $perPieceMrp,
                'discount_percent'    => 0,
                'discount_amount'     => 0,
                'subtotal'            => $perPieceMrp,
                'gst_rate'            => 0,   // MRP is GST-inclusive by law
                'gst_amount'          => 0,
                'total_price'         => $perPieceMrp,
            ];
        }

        // ── 6. Jewellery & Coin/Bar — apply discount, then GST ───────────────
        $rawCost = $metalCost + $makingChargesAmount;

        $discountPercent = (float) ($product->discount_percent ?? 0);
        $discountAmount  = ($rawCost * $discountPercent) / 100;
        $subtotal        = $rawCost - $discountAmount;

        // 3% GST on subtotal (HSN 7113)
        $gstRate   = self::GST_RATE;
        $gstAmount = round($subtotal * $gstRate / 100, 2);
        $totalRaw  = $subtotal + $gstAmount;

        // Coin & Bar: floor to nearest rupee (industry standard)
        $totalPrice = ($productType === 'coin_bar')
            ? (float) (int) floor($totalRaw)
            : round($totalRaw, 2);

        return [
            'product_type'        => $productType,
            'is_price_on_request' => (bool) ($product->is_price_on_request ?? false),
            'use_override'        => false,
            'metal_type'          => $product->metal_type,
            'purity'              => $product->purity,
            'weight_grams'        => round($grossWeight, 3),
            'net_weight'          => round($netWeight, 3),
            'loss'                => round($loss, 3),
            'metal_rate_used'     => round($metalRateUsed, 2),
            'metal_cost'          => round($metalCost, 2),
            'making_charges_rate' => $makingChargesRate,
            'making_charges_type' => $chargesType,
            'making_charges'      => round($makingChargesAmount, 2),
            'raw_cost'            => round($rawCost, 2),
            'discount_percent'    => $discountPercent,
            'discount_amount'     => round($discountAmount, 2),
            'subtotal'            => round($subtotal, 2),
            'gst_rate'            => $gstRate,
            'gst_amount'          => $gstAmount,
            'total_price'         => $totalPrice,
        ];
    }
}
