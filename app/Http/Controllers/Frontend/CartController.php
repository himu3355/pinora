<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService   $cart,
        protected PricingService $pricing
    ) {}

    public function index()
    {
        $countBefore = $this->cart->count();
        $totals = $this->cart->totalsWithPricing($this->pricing);
        $countAfter = $this->cart->count();

        if ($countAfter < $countBefore) {
            session()->flash('warning', 'Some items in your cart are no longer available because the seller is currently offline.');
        }

        $groups = $this->cart->groupByVendor($totals['items']);

        return view('cart.index', [
            'items'   => $totals['items'],
            'groups'  => $groups,
            'subtotal'=> $totals['subtotal'],
            'gstAmount' => $totals['gst_amount'],
            'total'   => $totals['total'],
            'isEmpty' => $this->cart->isEmpty(),
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1|max:50',
        ]);

        $this->cart->add(
            $validated['product_id'],
            $validated['variant_id'] ?? null,
            $validated['quantity']   ?? 1
        );

        if ($request->expectsJson()) {
            $totals = $this->cart->totalsWithPricing($this->pricing);
            return response()->json([
                'success' => true,
                'count' => $this->cart->count(),
                'items' => $totals['items'],
                'subtotal' => $totals['subtotal'],
                'message' => 'Product added to cart!'
            ]);
        }

        return back()->with('success', 'Product added to cart!');
    }

    public function update(Request $request, string $key)
    {
        $validated = $request->validate(['quantity' => 'required|integer|min:0|max:50']);
        $this->cart->update($key, $validated['quantity']);

        if ($request->expectsJson()) {
            $totals = $this->cart->totalsWithPricing($this->pricing);
            $item = $totals['items'][$key] ?? null;
            return response()->json([
                'success' => true,
                'count' => $this->cart->count(),
                'items' => $totals['items'],
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total' => $totals['total'],
                'line_total' => $item ? $item['line_total'] : 0,
                'quantity' => $item ? $item['quantity'] : 0,
                'isEmpty' => $this->cart->isEmpty()
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function remove(Request $request, string $key)
    {
        $this->cart->remove($key);

        if ($request->expectsJson()) {
            $totals = $this->cart->totalsWithPricing($this->pricing);
            return response()->json([
                'success' => true,
                'count' => $this->cart->count(),
                'items' => $totals['items'],
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total' => $totals['total'],
                'isEmpty' => $this->cart->isEmpty()
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed.');
    }

    public function clear()
    {
        $this->cart->clear();
        return redirect()->route('cart.index');
    }
}
