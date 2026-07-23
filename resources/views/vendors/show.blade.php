@extends('layouts.app')

@section('title', $vendor->store_name . ' - Storefront')

@section('content')
<!-- Hero Banner Header -->
<div class="relative bg-dark-surface h-64 sm:h-80 overflow-hidden">
    @if($vendor->store_banner)
        <img src="{{ Storage::url($vendor->store_banner) }}" class="object-cover w-full h-full" alt="{{ $vendor->store_name }} banner">
    @else
        <div class="w-full h-full bg-gradient-to-r from-gold/10 via-gold-dark/20 to-gold/10"></div>
    @endif
    <div class="absolute inset-0 bg-black/10"></div>
</div>

<!-- Profile Info Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 sm:-mt-32 relative z-10 pb-6 mb-10">
    <div class="bg-dark-card border border-border-gold rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row gap-6 md:items-end justify-between">
        
        <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6 text-center sm:text-left">
            <!-- Logo badge -->
            <div class="w-32 h-32 bg-dark-surface border-4 border-border-gold rounded-3xl overflow-hidden shadow-md flex items-center justify-center shrink-0">
                @if($vendor->store_logo)
                    <img src="{{ Storage::url($vendor->store_logo) }}" class="object-cover w-full h-full" alt="{{ $vendor->store_name }} logo">
                @else
                    <span class="text-5xl font-black text-gold font-primary">{{ substr($vendor->store_name, 0, 1) }}</span>
                @endif
            </div>

            <div>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    <h1 class="text-3xl font-extrabold text-text-light tracking-tight font-primary">{{ $vendor->store_name }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20">
                        Verified Vendor
                    </span>
                </div>
                <p class="text-text-muted mt-2 max-w-xl text-sm leading-relaxed">
                    {{ $vendor->store_description ?? 'Welcome to our storefront. Explore our exclusive range of gold, silver, and diamond designs.' }}
                </p>
            </div>
        </div>

        <div class="flex justify-center shrink-0">
            <div class="bg-dark-bg border border-border-gold rounded-2xl px-6 py-4 text-center">
                <span class="text-xs font-bold text-text-muted uppercase tracking-wide">Collection Size</span>
                <p class="text-3xl font-black text-gold mt-0.5">{{ $products->total() }} Products</p>
            </div>
        </div>

    </div>
</div>

<!-- Page Builder Blocks -->
@if($vendor->pageBuilderBlocks && $vendor->pageBuilderBlocks->isNotEmpty())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16 space-y-12">
        @foreach($vendor->pageBuilderBlocks as $block)
            @php
                $blockClass = $block->block_type;
                $view = $blockClass::getView();
            @endphp
            @if($view)
                @include($view, ['block' => $block->data])
            @endif
        @endforeach
    </div>
@endif

<!-- Products Grid Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 mb-16">
    <h2 class="text-2xl font-extrabold text-text-light tracking-tight mb-8 font-primary">Products in Stock</h2>
    
    @if($products->isEmpty())
        <div class="text-center py-20 border border-dashed border-border-gold/50 rounded-3xl">
            <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="mt-4 text-text-muted font-medium">This vendor has not posted any products yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="group bg-dark-card border border-border-gold rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:shadow-gold/10 transition duration-200 flex flex-col h-full">
                    
                    <!-- Product Image -->
                    <div class="relative aspect-square bg-dark-bg overflow-hidden">
                        <img src="{{ $product->primary_image_url }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-300" alt="{{ $product->name }}">
                        @if($product->discount_percent > 0)
                            <span class="absolute top-3 left-3 bg-red-500 text-white text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                {{ number_format($product->discount_percent, 0) }}% Off
                            </span>
                        @endif
                    </div>

                    <!-- Product Details -->
                    <div class="p-5 flex flex-col justify-between flex-grow">
                        <div>
                            <h3 class="font-bold text-text-light text-sm line-clamp-2 h-10 mb-2 leading-tight font-primary">
                                <a href="{{ route('product.show', $product->slug) }}" class="hover:text-gold transition">{{ $product->name }}</a>
                            </h3>
                            <div class="mb-4 flex items-baseline gap-2">
                                @if($product->is_price_on_request)
                                    <span class="text-sm font-semibold text-text-muted">Price on Request</span>
                                @else
                                    <span class="text-lg font-black text-gold">₹{{ number_format($product->calculated_price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('product.show', $product->slug) }}" class="btn btn-outline-gold w-full justify-center">
                                View Product
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
