<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function items(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(int $productId, ?int $variantId = null, int $qty = 1): void
    {
        $product = Product::with('vendor', 'primaryImage')->findOrFail($productId);

        $variant = $variantId
            ? ProductVariant::where('product_id', $productId)->findOrFail($variantId)
            : null;

        $key = $productId . '-' . ($variantId ?? '0');

        $cart = $this->items();

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'product_id'   => $product->id,
                'variant_id'   => $variantId,
                'product_name' => $product->name,
                'variant_name' => $variant?->name,
                'image_url'    => $product->primary_image_url,
                'vendor_id'    => $product->vendor_id,
                'vendor_name'  => $product->vendor->store_name,
                'metal_type'   => $product->metal_type,
                'purity'       => $product->purity,
                'weight_grams' => $variant ? $variant->weight_grams : $product->weight_grams,
                'quantity'     => $qty,
                'slug'         => $product->slug,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    public function update(string $key, int $qty): void
    {
        $cart = $this->items();

        if (isset($cart[$key])) {
            if ($qty <= 0) {
                $this->remove($key);
            } else {
                $cart[$key]['quantity'] = $qty;
                Session::put(self::SESSION_KEY, $cart);
            }
        }
    }

    public function remove(string $key): void
    {
        $cart = $this->items();
        unset($cart[$key]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'quantity'));
    }

    public function isEmpty(): bool
    {
        return empty($this->items());
    }

    /**
     * Group cart items by vendor.
     */
    public function groupByVendor(?array $items = null): array
    {
        $groups = [];
        $items = $items ?? $this->items();
        foreach ($items as $key => $item) {
            $groups[$item['vendor_id']]['vendor_name'] = $item['vendor_name'];
            $groups[$item['vendor_id']]['items'][$key]  = $item;
        }
        return $groups;
    }

    /**
     * Compute live prices for all items using PricingService.
     * Option 2: All-inclusive retail pricing where displayed total matches product page,
     * and subtotal/gst are clean pre-tax and tax components (no double tax).
     */
    public function totalsWithPricing(PricingService $pricing): array
    {
        $taxableSubtotal = 0.0;
        $totalGst        = 0.0;
        $grandTotal      = 0.0;
        $items           = [];

        foreach ($this->items() as $key => $item) {
            $product = Product::active()->find($item['product_id']);
            if (!$product) {
                $this->remove($key);
                continue;
            }
            $breakdown = $pricing->calculate($product, $item['variant_id'] ?? null);

            // Final consumer inclusive unit price (e.g. ₹10,300)
            $inclusiveUnitPrice = $breakdown ? (float) $breakdown['final_price'] : 0.0;

            // Taxable unit price before 3% GST (e.g. ₹10,000)
            $taxableUnitPrice = isset($breakdown['subtotal']) && $breakdown['subtotal'] > 0
                ? (float) $breakdown['subtotal']
                : round($inclusiveUnitPrice / 1.03, 2);

            $lineTaxable   = round($taxableUnitPrice * $item['quantity'], 2);
            $lineInclusive = round($inclusiveUnitPrice * $item['quantity'], 2);
            $lineGst       = round($lineInclusive - $lineTaxable, 2);

            $taxableSubtotal += $lineTaxable;
            $totalGst        += $lineGst;
            $grandTotal      += $lineInclusive;

            $items[$key] = array_merge($item, [
                'unit_price'           => $taxableUnitPrice,       // Pre-tax base for order/tax calculations
                'unit_price_inclusive' => $inclusiveUnitPrice,     // Inclusive display price
                'line_total'           => $lineInclusive,          // Inclusive line total matching product page
                'taxable_line_total'   => $lineTaxable,
                'gst_amount'           => $lineGst,
                'pricing'              => $breakdown,
            ]);
        }

        return [
            'items'      => $items,
            'subtotal'   => round($taxableSubtotal, 2),
            'gst_amount' => round($totalGst, 2),
            'total'      => round($grandTotal, 2),
        ];
    }
}
