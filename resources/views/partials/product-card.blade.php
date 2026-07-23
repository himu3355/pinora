@php
    $pricing = app(\App\Services\PricingService::class)->calculate($product);
@endphp
<div class="product-card">
    <div class="product-card-img">
        <a href="{{ route('product.show', $product->slug) }}">
            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy">
        </a>
        @auth
        <button class="product-card-wishlist {{ auth()->user()->hasWishlisted($product->id) ? 'active' : '' }}"
            data-wishlist-toggle="{{ $product->id }}" aria-label="Toggle Wishlist">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </button>
        @endauth
    </div>
    <div class="product-card-body">
        <div class="product-card-vendor">{{ $product->vendor->store_name }}</div>
        <div class="product-card-name">
            <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="product-card-price">₹{{ number_format($pricing['final_price'], 0) }}</span>
            @if(isset($pricing['discount_amount']) && $pricing['discount_amount'] > 0)
                <span class="product-card-price-original">₹{{ number_format($pricing['original_price'], 0) }}</span>
            @endif
        </div>
    </div>
</div>
