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
