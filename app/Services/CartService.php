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
     */
    public function totalsWithPricing(PricingService $pricing): array
    {
        $subtotal = 0;
        $items    = [];

        foreach ($this->items() as $key => $item) {
            $product  = Product::active()->find($item['product_id']);
            if (!$product) {
                $this->remove($key);
                continue;
            }
            $breakdown = $pricing->calculate($product, $item['variant_id'] ?? null);

            $unitPrice = $breakdown ? $breakdown['final_price'] : 0;
            $lineTotal = $unitPrice * $item['quantity'];
            $subtotal += $lineTotal;

            $items[$key] = array_merge($item, [
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'pricing'    => $breakdown,
            ]);
        }

        $gstAmount = round($subtotal * 0.03, 2);
        $total     = $subtotal + $gstAmount;

        return [
            'items'      => $items,
            'subtotal'   => $subtotal,
            'gst_amount' => $gstAmount,
            'total'      => $total,
        ];
    }
}
