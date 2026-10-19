<?php

namespace App\Filament\Components;

use App\Models\MetalRate;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;

class PricingCalculatorSection
{
    /**
     * Build the live pricing calculator section for product forms.
     *
     * All pricing-relevant fields (product_type, metal_type, purity,
     * weight_grams, loss, making_charges, making_charges_type,
     * base_price, mrp_pieces, discount_percent, is_price_on_request)
     * must have ->live(onBlur: true) for this to react to changes.
     */
    public static function make(): Section
    {
        return Section::make('💰 Pricing Calculator')
            ->schema([
                View::make('filament.components.pricing-calculator'),
            ])
            ->collapsible();
    }


    /**
     * Compute the pricing breakdown from raw form data.
     * Used by the Blade view via the Livewire component.
     *
     * Pricing rules by product_type:
     *
     *  jewellery  → net_weight × rate + labour% + GST 3%
     *  coin_bar   → net_weight × rate + labour% + GST 3% → floor()
     *  mrp_fixed  → base_price IS the final MRP (no additional GST)
     *  mrp_linked → (net_weight × rate + labour%) ÷ mrp_pieces = per-pc MRP
     *
     * NOTE on rates:
     *   MetalRate stores the *alloy rate* per purity (22K = ₹13,435/g).
     *   No additional purity multiplier is needed.
     */
    public static function calculateFromFormData(
        ?string $productType,
        ?string $metalType,
        ?string $purity,
        float|string|null $weightGrams,
        float|string|null $loss,
        float|string|null $makingCharges,
        ?string $makingChargesType,
        float|string|null $basePrice,
        int|string|null $mrpPieces,
        float|string|null $discountPercent,
        bool $isPriceOnRequest = false,
    ): array {
        $productType       = $productType ?: 'jewellery';
        $weightGrams       = (float) ($weightGrams ?? 0);
        $loss              = (float) ($loss ?? 0);
        $makingCharges     = (float) ($makingCharges ?? 0);
        $makingChargesType = $makingChargesType ?: 'fixed';
        $basePrice         = ($basePrice !== null && $basePrice !== '') ? (float) $basePrice : null;
        $mrpPieces         = max(1, (int) ($mrpPieces ?? 1));
        $discountPercent   = (float) ($discountPercent ?? 0);
        $netWeight         = max(0, $weightGrams - $loss);

        // ── Price On Request ──────────────────────────────────────────────────
        if ($isPriceOnRequest) {
            return [
                'product_type'              => $productType,
                'is_price_on_request'       => true,
                'use_override'              => false,
                'metal_type'                => $metalType,
                'purity'                    => $purity,
                'weight_grams'              => $weightGrams,
                'net_weight'                => $netWeight,
                'loss'                      => $loss,
                'metal_rate_used'           => 0,
                'metal_rate_effective_date' => null,
                'metal_cost'                => 0,
                'making_charges_rate'       => $makingCharges,
                'making_charges_type'       => $makingChargesType,
                'making_charges'            => 0,
                'raw_cost'                  => 0,
                'discount_percent'          => 0,
                'discount_amount'           => 0,
                'subtotal'                  => 0,
                'gst_rate'                  => 0,
                'gst_amount'                => 0,
                'total_price'               => 0,
            ];
        }

        // ── MRP Fixed ─────────────────────────────────────────────────────────
        // base_price IS the final consumer price. GST-inclusive by law.
        if ($productType === 'mrp_fixed') {
            $mrp = $basePrice ?? 0;
            return [
                'product_type'              => 'mrp_fixed',
                'is_price_on_request'       => false,
                'use_override'              => true,
                'metal_type'                => $metalType,
                'purity'                    => $purity,
                'weight_grams'              => round($weightGrams, 3),
                'net_weight'                => round($netWeight, 3),
                'loss'                      => round($loss, 3),
                'metal_rate_used'           => 0,
                'metal_rate_effective_date' => null,
                'metal_cost'                => 0,
                'making_charges_rate'       => $makingCharges,
                'making_charges_type'       => $makingChargesType,
                'making_charges'            => 0,
                'mrp_pieces'                => 1,
                'raw_cost'                  => round($mrp, 2),
                'discount_percent'          => 0,
                'discount_amount'           => 0,
                'subtotal'                  => round($mrp, 2),
                'gst_rate'                  => 0,
                'gst_amount'                => 0,
                'total_price'               => round($mrp, 2),
            ];
        }

        // ── Fetch metal rate ──────────────────────────────────────────────────
        $metalRateUsed          = 0.00;
        $metalCost              = 0.00;
        $metalRateEffectiveDate = null;

        if (!empty($metalType) && !empty($purity)) {
            $rateRecord             = MetalRate::getLatestRate($metalType, $purity);
            $metalRateUsed          = $rateRecord ? (float) $rateRecord->rate_per_gram : 0.00;
            $metalCost              = $netWeight * $metalRateUsed;
            $metalRateEffectiveDate = $rateRecord && $rateRecord->effective_date
                ? \Carbon\Carbon::parse($rateRecord->effective_date)->format('d M Y')
                : null;
        }

        // ── Making / Labour charges ───────────────────────────────────────────
        $makingChargesAmount = match ($makingChargesType) {
            'per_gram'   => $makingCharges * $netWeight,
            'percentage' => ($metalCost * $makingCharges) / 100,
            default      => $makingCharges,   // fixed
        };

        // ── MRP Linked ────────────────────────────────────────────────────────
        // total batch value ÷ pieces = per-piece MRP. No GST on top.
        if ($productType === 'mrp_linked') {
            $totalBatchCost = $metalCost + $makingChargesAmount;
            $perPieceMrp    = round($totalBatchCost / $mrpPieces, 2);

            return [
                'product_type'              => 'mrp_linked',
                'is_price_on_request'       => false,
                'use_override'              => false,
                'metal_type'                => $metalType,
                'purity'                    => $purity,
                'weight_grams'              => round($weightGrams, 3),
                'net_weight'                => round($netWeight, 3),
                'loss'                      => round($loss, 3),
                'metal_rate_used'           => round($metalRateUsed, 2),
                'metal_rate_effective_date' => $metalRateEffectiveDate,
                'metal_cost'                => round($metalCost, 2),
                'making_charges_rate'       => $makingCharges,
                'making_charges_type'       => $makingChargesType,
                'making_charges'            => round($makingChargesAmount, 2),
                'mrp_pieces'                => $mrpPieces,
                'batch_cost'                => round($totalBatchCost, 2),
                'raw_cost'                  => $perPieceMrp,
                'discount_percent'          => 0,
                'discount_amount'           => 0,
                'subtotal'                  => $perPieceMrp,
                'gst_rate'                  => 0,
                'gst_amount'                => 0,
                'total_price'               => $perPieceMrp,
            ];
        }

        // ── Jewellery & Coin/Bar: discount → GST 3% ───────────────────────────
        $rawCost        = $metalCost + $makingChargesAmount;
        $discountAmount = ($rawCost * $discountPercent) / 100;
        $subtotal       = $rawCost - $discountAmount;

        $gstRate   = 3.0;
        $gstAmount = round($subtotal * $gstRate / 100, 2);
        $totalRaw  = $subtotal + $gstAmount;

        // Coin & Bar: floor to nearest rupee (industry standard)
        $totalPrice = ($productType === 'coin_bar')
            ? (float) (int) floor($totalRaw)
            : round($totalRaw, 2);

        return [
            'product_type'              => $productType,
            'is_price_on_request'       => false,
            'use_override'              => false,
            'metal_type'                => $metalType,
            'purity'                    => $purity,
            'weight_grams'              => round($weightGrams, 3),
            'net_weight'                => round($netWeight, 3),
            'loss'                      => round($loss, 3),
            'metal_rate_used'           => round($metalRateUsed, 2),
            'metal_rate_effective_date' => $metalRateEffectiveDate,
            'metal_cost'                => round($metalCost, 2),
            'making_charges_rate'       => $makingCharges,
            'making_charges_type'       => $makingChargesType,
            'making_charges'            => round($makingChargesAmount, 2),
            'raw_cost'                  => round($rawCost, 2),
            'discount_percent'          => $discountPercent,
            'discount_amount'           => round($discountAmount, 2),
            'subtotal'                  => round($subtotal, 2),
            'gst_rate'                  => $gstRate,
            'gst_amount'                => $gstAmount,
            'total_price'               => $totalPrice,
        ];
    }
}
