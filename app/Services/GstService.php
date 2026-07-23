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
