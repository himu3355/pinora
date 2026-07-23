# Step 47: Checkout Page with GST Calculation

**Goal:** Implement the checkout page with address selection, live GST breakdown, and order placement.
**Depends On:** Step 46 (CartService), Step 53 (GstService), Step 54 (OrderService), Step 18 (Address model)
**Next Step:** Step 48 (Order Confirmation)

---

## Files to Create

- `app/Http/Controllers/Frontend/CheckoutController.php`
- `resources/views/checkout/index.blade.php`
- `resources/views/checkout/partials/address-form.blade.php`
- Routes added to `routes/web.php`

---

## 1. Routes — `routes/web.php`

```php
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});
```

---

## 2. `app/Http/Controllers/Frontend/CheckoutController.php`

```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\CartService;
use App\Services\GstService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService   $cart,
        protected PricingService $pricing,
        protected GstService    $gst,
        protected OrderService  $orderService,
    ) {}

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $user      = auth()->user();
        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $totals    = $this->cart->totalsWithPricing($this->pricing);

        // Default to IGST preview until address is selected
        $gstBreakdown = [
            'type'        => 'IGST',
            'cgst'        => 0,
            'sgst'        => 0,
            'igst'        => $totals['gst_amount'],
            'total'       => $totals['gst_amount'],
        ];

        return view('checkout.index', [
            'addresses'    => $addresses,
            'items'        => $totals['items'],
            'subtotal'     => $totals['subtotal'],
            'gstBreakdown' => $gstBreakdown,
            'total'        => $totals['total'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id'       => 'required_without:new_address|nullable|exists:addresses,id',
            'new_address'      => 'required_without:address_id|nullable|array',
            'new_address.full_name'       => 'required_with:new_address|string|max:120',
            'new_address.phone'           => 'required_with:new_address|string|max:20',
            'new_address.address_line_1'  => 'required_with:new_address|string|max:255',
            'new_address.city'            => 'required_with:new_address|string|max:100',
            'new_address.state'           => 'required_with:new_address|string|max:100',
            'new_address.pincode'         => 'required_with:new_address|string|max:10',
            'payment_method'   => 'required|in:cod,razorpay',
        ]);

        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $user = auth()->user();

        // Resolve shipping address
        if ($request->filled('address_id')) {
            $address = Address::where('user_id', $user->id)->findOrFail($validated['address_id']);
        } else {
            $address = Address::create(array_merge($validated['new_address'], [
                'user_id' => $user->id,
                'type'    => 'shipping',
                'country' => 'India',
            ]));
        }

        // Place order via OrderService
        $order = $this->orderService->placeOrder(
            user:          $user,
            address:       $address,
            paymentMethod: $validated['payment_method'],
        );

        $this->cart->clear();

        return redirect()->route('order.confirmation', $order->order_number);
    }
}
```

---

## 3. `resources/views/checkout/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Checkout — Pinora')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-16">
    <h1 class="font-primary text-4xl font-normal mb-10 text-text-light">Checkout</h1>

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-10 items-start">

            {{-- ===== LEFT COLUMN ===== --}}
            <div class="flex flex-col gap-8">

                {{-- STEP 1: Delivery Address --}}
                <div class="bg-dark-card border border-border-gold rounded-lg overflow-hidden">
                    <div class="py-4 px-6 border-b border-border-gold flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-gold flex items-center justify-center text-[0.8rem] font-bold text-dark-bg">1</span>
                        <span class="font-primary text-lg font-semibold text-text-light">Delivery Address</span>
                    </div>
                    <div class="p-6">
                        {{-- Saved Addresses --}}
                        @if($addresses->isNotEmpty())
                        <div class="flex flex-col gap-3 mb-6">
                            @foreach($addresses as $addr)
                            <label class="flex gap-4 p-4 border border-border-gold rounded-lg cursor-pointer transition-all duration-300" id="addr-label-{{ $addr->id }}" onclick="selectAddress({{ $addr->id }})">
                                <input type="radio" name="address_id" value="{{ $addr->id }}" {{ $addr->is_default ? 'checked' : '' }} class="accent-gold mt-1">
                                <div class="text-sm leading-relaxed">
                                    <div class="font-semibold text-text-light mb-0.5">{{ $addr->full_name }} · {{ $addr->phone }}</div>
                                    <div class="text-text-muted">{{ $addr->address_line_1 }}@if($addr->address_line_2), {{ $addr->address_line_2 }}@endif, {{ $addr->city }}, {{ $addr->state }} — {{ $addr->pincode }}</div>
                                    @if($addr->is_default)<span class="text-[0.7rem] text-gold mt-1 inline-block">✓ Default</span>@endif
                                </div>
                            </label>
                            @endforeach
                        </div>

                        {{-- Toggle for new address --}}
                        <button type="button" onclick="toggleNewAddress()" class="text-sm text-gold bg-transparent border-0 cursor-pointer underline hover:text-gold-light transition-colors">+ Add a different address</button>
                        <div id="new-address-form" class="hidden mt-6">
                        @else
                        <div id="new-address-form">
                        @endif
                            @include('checkout.partials.address-form')
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Payment Method --}}
                <div class="bg-dark-card border border-border-gold rounded-lg overflow-hidden">
                    <div class="py-4 px-6 border-b border-border-gold flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-gold flex items-center justify-center text-[0.8rem] font-bold text-dark-bg">2</span>
                        <span class="font-primary text-lg font-semibold text-text-light">Payment Method</span>
                    </div>
                    <div class="p-6 flex flex-col gap-3">
                        <label class="flex gap-4 p-4 border border-border-gold rounded-lg cursor-pointer items-center hover:border-gold transition-colors">
                            <input type="radio" name="payment_method" value="cod" checked class="accent-gold">
                            <div>
                                <div class="font-semibold text-[0.9rem] text-text-light">Cash on Delivery</div>
                                <div class="text-[0.78rem] text-text-muted">Pay when you receive your order</div>
                            </div>
                        </label>
                        <label class="flex gap-4 p-4 border border-border-gold rounded-lg cursor-not-allowed items-center opacity-60">
                            <input type="radio" name="payment_method" value="razorpay" disabled class="accent-gold">
                            <div>
                                <div class="font-semibold text-[0.9rem] text-text-light">Online Payment <span class="text-gold text-[0.7rem]">(Coming Soon)</span></div>
                                <div class="text-[0.78rem] text-text-muted">UPI, Cards, Net Banking via Razorpay</div>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            {{-- ===== ORDER SUMMARY ===== --}}
            <div class="lg:sticky lg:top-[90px] flex flex-col gap-5">
                <div class="bg-dark-card border border-border-gold rounded-lg p-6">
                    <h3 class="font-primary text-lg mb-5 pb-3 border-b border-border-gold text-text-light">Order Summary</h3>

                    {{-- Items --}}
                    @foreach($items as $item)
                    <div class="flex justify-between items-start mb-3 gap-2">
                        <span class="text-[0.83rem] text-text-muted leading-relaxed flex-1">
                            {{ $item['product_name'] }}
                            @if($item['variant_name']) ({{ $item['variant_name'] }}) @endif
                            × {{ $item['quantity'] }}
                        </span>
                        <span class="text-sm text-text-light whitespace-nowrap">₹{{ number_format($item['line_total'], 0) }}</span>
                    </div>
                    @endforeach

                    <div class="border-t border-border-gold mt-4 pt-4">
                        <div class="flex justify-between mb-2 text-sm text-text-muted">
                            <span>Subtotal</span>
                            <span>₹{{ number_format($subtotal, 0) }}</span>
                        </div>

                        @if($gstBreakdown['type'] === 'split')
                        <div class="flex justify-between mb-1.5 text-xs text-text-muted">
                            <span>CGST (1.5%)</span>
                            <span>₹{{ number_format($gstBreakdown['cgst'], 0) }}</span>
                        </div>
                        <div class="flex justify-between mb-2 text-sm text-text-muted">
                            <span>SGST (1.5%)</span>
                            <span>₹{{ number_format($gstBreakdown['sgst'], 0) }}</span>
                        </div>
                        @else
                        <div class="flex justify-between mb-2 text-sm text-text-muted">
                            <span>IGST (3%)</span>
                            <span>₹{{ number_format($gstBreakdown['igst'], 0) }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between pt-3 border-t border-border-gold font-bold text-lg text-gold">
                            <span>Total</span>
                            <span>₹{{ number_format($total, 0) }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold w-full justify-center py-4 text-[0.95rem]">Place Order →</button>

                <p class="text-xs text-text-muted text-center leading-relaxed">By placing your order you agree to our <a href="#" class="text-gold hover:text-gold-light">Terms of Service</a> and <a href="#" class="text-gold hover:text-gold-light">Return Policy</a>.</p>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleNewAddress() {
    const form = document.getElementById('new-address-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
function selectAddress(id) {
    document.querySelectorAll('[id^="addr-label-"]').forEach(el => {
        el.classList.remove('border-gold');
        el.classList.add('border-border-gold');
    });
    const target = document.getElementById('addr-label-' + id);
    target.classList.add('border-gold');
    target.classList.remove('border-border-gold');
}
// Highlight default on load
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="address_id"]:checked');
    if (checked) selectAddress(checked.value);
});
</script>
@endpush
```

---

## 4. `resources/views/checkout/partials/address-form.blade.php`

```blade
<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Full Name *</label>
        <input type="text" name="new_address[full_name]" value="{{ old('new_address.full_name') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.full_name')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Phone *</label>
        <input type="tel" name="new_address[phone]" value="{{ old('new_address.phone') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.phone')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Pincode *</label>
        <input type="text" name="new_address[pincode]" value="{{ old('new_address.pincode') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.pincode')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-span-2">
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Address Line 1 *</label>
        <input type="text" name="new_address[address_line_1]" value="{{ old('new_address.address_line_1') }}" placeholder="Flat, House No., Building, Street" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.address_line_1')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-span-2">
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Address Line 2</label>
        <input type="text" name="new_address[address_line_2]" value="{{ old('new_address.address_line_2') }}" placeholder="Area, Colony, Landmark (optional)" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">City *</label>
        <input type="text" name="new_address[city]" value="{{ old('new_address.city') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.city')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">State *</label>
        <select name="new_address[state]" class="w-full bg-dark-bg border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm cursor-pointer outline-none focus:border-gold transition-colors">
            <option value="">Select State</option>
            @foreach(\App\Models\Address::INDIAN_STATES as $state)
            <option value="{{ $state }}" {{ old('new_address.state') === $state ? 'selected' : '' }}>{{ $state }}</option>
            @endforeach
        </select>
        @error('new_address.state')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
</div>
```

---

## Notes

- GST is determined by comparing the delivery state with `config('app.gst_state')`. This full logic lives in `GstService` (Step 53) and `OrderService` (Step 54).
- COD is the only active payment method in V1. Razorpay integration is scaffolded in Step 55.
- When new address is submitted, it is saved to the `addresses` table, so the customer can reuse it in future orders.
- The `Address::INDIAN_STATES` constant is defined in Step 18.
