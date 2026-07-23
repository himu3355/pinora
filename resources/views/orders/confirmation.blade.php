@extends('layouts.app')

@section('title', 'Order Confirmed — ' . $order->order_number . ' | Pinora')

@section('content')
<div class="max-w-[760px] mx-auto px-6 py-16">

    {{-- Success Header --}}
    <div class="text-center mb-12">
        <div class="w-20 h-20 rounded-full bg-[#28a745]/15 border-2 border-[#28a745]/40 flex items-center justify-center mx-auto mb-6 text-3xl text-[#6fcf97]">✓</div>
        <h1 class="font-primary text-4xl font-normal mb-3 text-text-light">
            Order Confirmed!
        </h1>
        <p class="text-text-muted text-base mb-2">
            Thank you, <strong class="text-text-light">{{ $order->user->name }}</strong>! Your order has been placed successfully.
        </p>
        <div class="inline-block bg-gold/10 border border-border-gold rounded-full py-2 px-6 font-semibold text-gold text-[1.1rem] mt-2">
            {{ $order->order_number }}
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Order Date</div>
            <div class="text-[0.9rem] text-text-light">{{ $order->created_at->format('d M Y, h:i A') }}</div>
        </div>
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Payment</div>
            <div class="text-[0.9rem] text-text-light">{{ strtoupper($order->payment_method) }} · <span class="{{ $order->payment_status === 'paid' ? 'text-[#6fcf97]' : 'text-text-muted' }}">{{ ucfirst($order->payment_status) }}</span></div>
        </div>
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Estimated Delivery</div>
            <div class="text-[0.9rem] text-text-light">{{ now()->addDays(7)->format('d M Y') }} – {{ now()->addDays(10)->format('d M Y') }}</div>
        </div>
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Order Status</div>
            <div class="text-[0.9rem] text-gold font-medium">{{ ucfirst($order->status) }}</div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-dark-card border border-border-gold rounded-lg mb-8 overflow-hidden">
        <div class="py-4 px-6 border-b border-border-gold">
            <h3 class="font-primary text-lg font-semibold text-text-light">Items Ordered</h3>
        </div>
        @foreach($order->items as $item)
        <div class="grid grid-cols-[64px_1fr_auto] gap-4 py-4 px-6 border-b border-border-gold items-center">
            <img src="{{ $item->product->primary_image_url ?? '' }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-md border border-border-gold">
            <div>
                <div class="font-semibold text-[0.9rem] text-text-light mb-1">{{ $item->product_name }}</div>
                @if($item->variant_name)<div class="text-[0.8rem] text-text-muted">{{ $item->variant_name }}</div>@endif
                <div class="text-[0.78rem] text-text-muted">Sold by: {{ $item->vendor->store_name }} · Qty: {{ $item->quantity }}</div>
            </div>
            <div class="text-right font-semibold text-gold">₹{{ number_format($item->total_price, 0) }}</div>
        </div>
        @endforeach

        {{-- Totals --}}
        <div class="py-5 px-6 bg-dark-bg/10">
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>Subtotal</span><span>₹{{ number_format($order->subtotal, 0) }}</span>
            </div>
            @if($order->cgst_amount > 0)
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>CGST</span><span>₹{{ number_format($order->cgst_amount, 0) }}</span>
            </div>
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>SGST</span><span>₹{{ number_format($order->sgst_amount, 0) }}</span>
            </div>
            @elseif($order->igst_amount > 0)
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>IGST</span><span>₹{{ number_format($order->igst_amount, 0) }}</span>
            </div>
            @endif
            <div class="flex justify-between pt-3 border-t border-border-gold font-bold text-lg text-gold">
                <span>Total Paid</span><span>₹{{ number_format($order->total_amount, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Delivery Address --}}
    <div class="bg-dark-card border border-border-gold rounded-lg p-6 mb-10">
        <h3 class="font-primary text-lg mb-3 text-text-light">Delivery Address</h3>
        <p class="text-sm text-text-muted leading-relaxed">
            {{ $order->shipping_name }}<br>
            {{ $order->shipping_address_line_1 }}@if($order->shipping_address_line_2), {{ $order->shipping_address_line_2 }}@endif<br>
            {{ $order->shipping_city }}, {{ $order->shipping_state }} — {{ $order->shipping_pincode }}<br>
            Phone: {{ $order->shipping_phone }}
        </p>
    </div>

    {{-- Email note --}}
    <div class="text-center text-text-muted text-xs mb-10">
        📧 A confirmation email has been sent to <strong>{{ $order->user->email }}</strong>
    </div>

    {{-- Actions --}}
    <div class="flex gap-4 justify-center flex-wrap">
        <a href="{{ route('order.show', $order->order_number) }}" class="btn btn-outline-gold">Track Order</a>
        <a href="{{ route('shop.index') }}" class="btn btn-gold">Continue Shopping</a>
    </div>
</div>
@endsection
