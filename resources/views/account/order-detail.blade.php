@extends('layouts.app')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'orders'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center pb-6 border-b border-border-gold/50 mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Order {{ $order->order_number }}</h1>
                    <p class="text-text-muted mt-1">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-gold/10 text-gold uppercase">
                        Order Status: {{ $order->status }}
                    </span>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase 
                        @if($order->payment_status === 'paid') bg-green-500/10 text-green-400
                        @else bg-yellow-500/10 text-yellow-400 @endif">
                        Payment: {{ $order->payment_status }}
                    </span>
                </div>
            </div>

            <!-- Items summary -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-text-light mb-4">Ordered Items</h2>
                <div class="divide-y divide-border-gold/50 border border-border-gold rounded-2xl overflow-hidden">
                    @foreach($order->items as $item)
                        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center gap-6 bg-transparent">
                            <div class="w-20 h-20 bg-dark-bg/50 border border-border-gold rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
                                <img src="{{ $item->product ? $item->product->primary_image_url : asset('images/product-placeholder.png') }}" class="object-cover w-full h-full" alt="{{ $item->product_name }}">
                            </div>

                            <div class="flex-grow">
                                <h3 class="font-bold text-text-light text-base leading-snug">{{ $item->product_name }}</h3>
                                @if($item->variant_name)
                                    <p class="text-xs text-text-muted mt-0.5">Variant: {{ $item->variant_name }}</p>
                                @endif
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-text-muted font-medium">
                                    @if($item->metal_type)
                                        <span>Metal: <span class="text-gold font-semibold">{{ ucfirst($item->metal_type) }} ({{ $item->purity }})</span></span>
                                    @endif
                                    @if($item->weight_grams)
                                        <span>Weight: <span class="text-gold font-semibold">{{ number_format($item->weight_grams, 3) }}g</span></span>
                                    @endif
                                    @if($item->vendor)
                                        <span>Vendor: <span class="text-gold font-semibold">{{ $item->vendor->store_name }}</span></span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col items-start sm:items-end w-full sm:w-auto shrink-0 mt-4 sm:mt-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-border-gold/50">
                                <p class="text-sm text-text-muted font-medium">₹{{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}</p>
                                <p class="text-base font-extrabold text-gold mt-0.5">₹{{ number_format($item->subtotal, 2) }}</p>
                                <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-gold/10 text-gold">
                                    Fulfillment: {{ $item->fulfillment_status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Two Column Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Shipping Details -->
                <div>
                    <h2 class="text-xl font-bold text-text-light mb-4">Shipping Information</h2>
                    <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50">
                        <p class="font-extrabold text-text-light text-lg mb-2">{{ $order->shipping_name }}</p>
                        <p class="text-sm text-text-muted leading-relaxed">{{ $order->shipping_address_line_1 }}</p>
                        @if($order->shipping_address_line_2)
                            <p class="text-sm text-text-muted leading-relaxed">{{ $order->shipping_address_line_2 }}</p>
                        @endif
                        @if($order->shipping_landmark)
                            <p class="text-sm text-text-muted leading-relaxed">Landmark: {{ $order->shipping_landmark }}</p>
                        @endif
                        <p class="text-sm text-text-muted leading-relaxed">{{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}</p>
                        <p class="text-sm text-text-muted leading-relaxed mt-1">{{ $order->shipping_country }}</p>
                        <p class="text-sm text-text-light font-semibold mt-4">Phone: {{ $order->shipping_phone }}</p>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div>
                    <h2 class="text-xl font-bold text-text-light mb-4">Financial Summary</h2>
                    <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-text-muted font-medium">Subtotal</span>
                            <span class="text-text-light font-bold">₹{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sm text-red-400 font-semibold">
                                <span>Discount</span>
                                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->cgst_amount > 0)
                            <div class="flex justify-between text-sm text-text-muted">
                                <span>CGST</span>
                                <span class="text-text-light font-semibold">₹{{ number_format($order->cgst_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->sgst_amount > 0)
                            <div class="flex justify-between text-sm text-text-muted">
                                <span>SGST</span>
                                <span class="text-text-light font-semibold">₹{{ number_format($order->sgst_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->igst_amount > 0)
                            <div class="flex justify-between text-sm text-text-muted">
                                <span>IGST (3%)</span>
                                <span class="text-text-light font-semibold">₹{{ number_format($order->igst_amount, 2) }}</span>
                            </div>
                        @endif
                        
                        <div class="border-t border-border-gold/50 pt-4 flex justify-between items-baseline">
                            <span class="text-base font-extrabold text-text-light">Grand Total</span>
                            <span class="text-2xl font-black text-gold">₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
