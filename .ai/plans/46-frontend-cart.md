# Step 46: Shopping Cart

**Goal:** Implement a session-based shopping cart with a CartService and cart page.
**Depends On:** Step 45 (Product detail routes), Step 51 (PricingService)
**Next Step:** Step 47 (Checkout)

---

## Files to Create

- `app/Services/CartService.php`
- `app/Http/Controllers/Frontend/CartController.php`
- `resources/views/cart/index.blade.php`
- Routes added to `routes/web.php`

---

## 1. Routes — `routes/web.php`

```php
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

// Wishlist toggle (used on product cards & account page)
Route::post('/wishlist/toggle/{productId}', [\App\Http\Controllers\Frontend\AccountController::class, 'toggleWishlist'])->name('wishlist.toggle')->middleware('auth');
```

---

## 2. `app/Services/CartService.php`

```php
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
            $product  = Product::find($item['product_id']);
            $breakdown = $product ? $pricing->calculate($product, $item['variant_id'] ?? null) : null;

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
```

---

## 3. `app/Http/Controllers/Frontend/CartController.php`

```php
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
        $totals = $this->cart->totalsWithPricing($this->pricing);
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
```

---

## 4. `resources/views/cart/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Your Cart — Pinora')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-16">
    <h1 class="font-primary text-4xl font-normal mb-10 text-text-light">Your Cart</h1>

    {{-- Empty State --}}
    <div id="cart-empty-state" class="{{ $isEmpty ? '' : 'hidden' }} text-center py-24 px-4 bg-dark-card border border-border-gold rounded-lg">
        <div class="text-6xl mb-4">🛒</div>
        <h2 class="font-primary text-3xl font-normal mb-3 text-text-light">Your cart is empty</h2>
        <p class="text-text-muted mb-8">Explore our curated collection and find something you love.</p>
        <a href="{{ route('shop.index') }}" class="btn btn-gold">Browse Jewellery</a>
    </div>

    {{-- Main Cart Grid --}}
    <div id="cart-main-grid" class="{{ $isEmpty ? 'hidden' : 'grid' }} grid-cols-1 lg:grid-cols-[1fr_340px] gap-10 items-start">

        {{-- Cart Items --}}
        <div class="flex flex-col gap-4" id="cart-items-container">
            @foreach($groups as $vendorId => $group)
            <div class="bg-dark-card border border-border-gold rounded-lg overflow-hidden vendor-group" data-vendor-id="{{ $vendorId }}">
                {{-- Vendor Header --}}
                <div class="py-3 px-5 border-b border-border-gold flex items-center gap-2">
                    <span class="text-xs text-gold tracking-wider uppercase font-semibold">{{ $group['vendor_name'] }}</span>
                </div>

                <div class="vendor-items">
                    @foreach($group['items'] as $key => $item)
                    <div class="grid grid-cols-[88px_1fr] sm:grid-cols-[88px_1fr_auto] gap-5 p-5 items-center border-b border-border-gold last:border-b-0 cart-item-row" data-cart-item="{{ $key }}">
                        {{-- Image --}}
                        <a href="{{ route('product.show', $item['slug']) }}">
                            <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}" class="w-[88px] h-[88px] object-cover rounded-md border border-border-gold">
                        </a>

                        {{-- Info --}}
                        <div>
                            <a href="{{ route('product.show', $item['slug']) }}" class="font-primary text-lg font-semibold block mb-1 text-text-light hover:text-gold transition-colors">{{ $item['product_name'] }}</a>
                            @if($item['variant_name'])
                            <div class="text-[0.8rem] text-text-muted mb-2">{{ $item['variant_name'] }}</div>
                            @endif
                            <div class="text-xs text-text-muted">₹{{ number_format($item['unit_price'], 0) }} / piece</div>
                            <div class="text-[0.8rem] text-text-muted mt-1">{{ ucfirst($item['metal_type']) }} {{ $item['purity'] }}</div>
                        </div>

                        {{-- Qty + Remove --}}
                        <div class="col-span-2 sm:col-span-1 flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-3 w-full sm:w-auto">
                            <div class="cart-item-line-total font-bold text-lg text-gold">₹{{ number_format($item['line_total'], 0) }}</div>
                            <form action="{{ route('cart.update', $key) }}" method="POST" class="cart-update-form flex items-center gap-1.5">
                                @csrf @method('PATCH')
                                <button type="submit" name="quantity" value="{{ max(0, $item['quantity']-1) }}" class="w-7 h-7 border border-border-gold rounded bg-transparent text-text-light cursor-pointer flex items-center justify-center hover:bg-gold/10 hover:text-gold transition-colors">−</button>
                                <span class="cart-item-qty text-sm min-w-[24px] text-center text-text-light">{{ $item['quantity'] }}</span>
                                <button type="submit" name="quantity" value="{{ $item['quantity']+1 }}" class="w-7 h-7 border border-border-gold rounded bg-transparent text-text-light cursor-pointer flex items-center justify-center hover:bg-gold/10 hover:text-gold transition-colors">+</button>
                            </form>
                            <form action="{{ route('cart.remove', $key) }}" method="POST" class="cart-remove-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[0.78rem] text-text-muted bg-transparent border-0 cursor-pointer underline hover:text-gold transition-colors">Remove</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Clear Cart --}}
            <div class="flex justify-between items-center py-2">
                <a href="{{ route('shop.index') }}" class="text-sm text-gold hover:text-gold-light transition-colors">← Continue Shopping</a>
                <form action="{{ route('cart.clear') }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-[0.78rem] text-text-muted bg-transparent border-0 cursor-pointer underline hover:text-gold transition-colors">Clear Cart</button>
                </form>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="bg-dark-card border border-border-gold rounded-lg p-6 lg:sticky lg:top-[90px]">
            <h3 class="font-primary text-xl mb-6 pb-3 border-b border-border-gold text-text-light">Order Summary</h3>

            <div class="flex justify-between mb-3 text-sm text-text-muted">
                <span>Subtotal</span>
                <span id="cart-summary-subtotal">₹{{ number_format($subtotal, 0) }}</span>
            </div>
            <div class="flex justify-between mb-3 text-sm text-text-muted">
                <span>GST (3%)</span>
                <span id="cart-summary-gst">₹{{ number_format($gstAmount, 0) }}</span>
            </div>
            <div class="flex justify-between mt-4 pt-4 border-t border-border-gold font-bold text-lg text-gold">
                <span>Total</span>
                <span id="cart-summary-total">₹{{ number_format($total, 0) }}</span>
            </div>

            <p class="text-xs text-text-muted my-4 mb-6">* GST split (CGST/SGST or IGST) will be finalized at checkout based on delivery address.</p>

            <a href="{{ route('checkout.index') }}" class="btn btn-gold w-full justify-center">Proceed to Checkout</a>
        </div>
    </div>
</div>
@endsection
```

---

## Notes

- Prices are recalculated live on every cart page load via `PricingService`. This ensures users always see the current metal rate price.
- Cart is stored entirely in the session under the `'cart'` key — no database table needed.
- The cart key format is `{product_id}-{variant_id}` (e.g. `42-0` or `42-3`).
- Register `CartService` as a singleton in `AppServiceProvider` for performance: `$this->app->singleton(CartService::class)`.
- On order placement (Step 54), snapshots of the calculated price are saved to `order_items.unit_price`.
