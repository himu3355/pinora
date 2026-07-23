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
     * All pricing-relevant fields (metal_type, purity, weight_grams,
     * making_charges, making_charges_type, base_price, discount_percent,
     * is_price_on_request) must have ->live(onBlur: true) for this to
     * react to changes.
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
     */
    public static function calculateFromFormData(
        ?string $metalType,
        ?string $purity,
        float|string|null $weightGrams,
        float|string|null $makingCharges,
        ?string $makingChargesType,
        float|string|null $basePrice,
        float|string|null $discountPercent,
        bool $isPriceOnRequest = false,
    ): array {
        $weightGrams = (float) ($weightGrams ?? 0);
        $makingCharges = (float) ($makingCharges ?? 0);
        $makingChargesType = $makingChargesType ?: 'fixed';
        $basePrice = $basePrice !== null && $basePrice !== '' ? (float) $basePrice : null;
        $discountPercent = (float) ($discountPercent ?? 0);

        $metalRateUsed = 0.00;
        $metalCost = 0.00;
        $makingChargesAmount = 0.00;
        $useOverride = $basePrice !== null && $basePrice > 0;

        if ($isPriceOnRequest) {
            return [
                'is_price_on_request' => true,
                'use_override'        => false,
                'metal_type'          => $metalType,
                'purity'              => $purity,
                'weight_grams'        => $weightGrams,
                'metal_rate_used'     => 0,
                'metal_cost'          => 0,
                'making_charges_rate' => $makingCharges,
                'making_charges_type' => $makingChargesType,
                'making_charges'      => 0,
                'raw_cost'            => 0,
                'discount_percent'    => 0,
                'discount_amount'     => 0,
                'subtotal'            => 0,
                'gst_rate'            => 0.00,
                'gst_amount'          => 0,
                'total_price'         => 0,
            ];
        }

        if ($useOverride) {
            $rawCost = $basePrice;
        } else {
            if (empty($metalType) || empty($purity)) {
                $rawCost = 0.00;
            } else {
                $rateRecord = MetalRate::getLatestRate($metalType, $purity);
                $metalRateUsed = $rateRecord ? (float) $rateRecord->rate_per_gram : 0.00;
                $metalCost = $weightGrams * $metalRateUsed;

                if ($makingChargesType === 'per_gram') {
                    $makingChargesAmount = $makingCharges * $weightGrams;
                } elseif ($makingChargesType === 'percentage') {
                    $makingChargesAmount = ($metalCost * $makingCharges) / 100;
                } else {
                    $makingChargesAmount = $makingCharges;
                }

                $rawCost = $metalCost + $makingChargesAmount;
            }
        }

        $discountAmount = ($rawCost * $discountPercent) / 100;
        $subtotal = $rawCost - $discountAmount;
        $gstRate = 0.00;
        $gstAmount = 0.00;
        $totalPrice = $subtotal;

        return [
            'is_price_on_request' => false,
            'use_override'        => $useOverride,
            'metal_type'          => $metalType,
            'purity'              => $purity,
            'weight_grams'        => round($weightGrams, 3),
            'metal_rate_used'     => round($metalRateUsed, 2),
            'metal_cost'          => round($metalCost, 2),
            'making_charges_rate' => $makingCharges,
            'making_charges_type' => $makingChargesType,
            'making_charges'      => round($makingChargesAmount, 2),
            'raw_cost'            => round($rawCost, 2),
            'discount_percent'    => $discountPercent,
            'discount_amount'     => round($discountAmount, 2),
            'subtotal'            => round($subtotal, 2),
            'gst_rate'            => $gstRate,
            'gst_amount'          => round($gstAmount, 2),
            'total_price'         => round($totalPrice, 2),
        ];
    }
}
