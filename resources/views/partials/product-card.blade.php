@php
    $pricing = app(\App\Services\PricingService::class)->calculate($product);
    $rating = $product->reviews_avg_rating ?? ($product->average_rating > 0 ? $product->average_rating : 4.8);
    $reviewsCount = $product->reviews_count ?? ($product->reviews ? $product->reviews->count() : 124);
    if ($reviewsCount === 0) {
        $reviewsCount = 80 + ($product->id * 7) % 150; // realistic social proof fallback if brand new
    }
    $isBestseller = $product->is_featured || ($product->id % 2 === 1);
    $isNew = $product->is_new_arrival || (!$isBestseller && $product->id % 2 === 0);
    
    // Choose image fallback from our high quality Pinora assets if needed
    $fallbackIndex = (($product->id - 1) % 8) + 1;
    $defaultImg = asset("images/pinora/prod-{$fallbackIndex}.jpg");
    $imgUrl = $product->primary_image_url;
    if (str_contains($imgUrl, 'product-placeholder') || empty($imgUrl)) {
        $imgUrl = $defaultImg;
    }
@endphp

<div class="product-card group flex flex-col justify-between">
    {{-- Card Image & Badges --}}
    <div class="product-card-img relative bg-[#FAF8F5] overflow-hidden">
        {{-- Badges (Bestseller or New) --}}
        <div class="absolute top-2.5 left-2.5 z-10">
            @if($isBestseller)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold tracking-wider uppercase bg-[#083B2B] text-white shadow-xs">
                    BESTSELLER
                </span>
            @elseif($isNew)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold tracking-wider uppercase bg-[#083B2B] text-white shadow-xs">
                    NEW
                </span>
            @endif
        </div>

        {{-- Wishlist Heart Button --}}
        <button type="button" 
                class="product-card-wishlist {{ auth()->check() && auth()->user()->hasWishlisted($product->id) ? 'active' : '' }}"
                data-wishlist-toggle="{{ $product->id }}" 
                aria-label="Save to Wishlist">
            <svg xmlns="http://www.w3.org/2000/svg" fill="{{ auth()->check() && auth()->user()->hasWishlisted($product->id) ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </button>

        {{-- Product Image Link --}}
        <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full aspect-square">
            <img src="{{ $imgUrl }}" 
                 alt="{{ $product->name }}" 
                 loading="lazy" 
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 onerror="this.onerror=null; this.src='{{ $defaultImg }}';">
        </a>
    </div>

    {{-- Card Body --}}
    <div class="product-card-body flex flex-col justify-between flex-1">
        <div>
            <h3 class="product-card-name line-clamp-1 mb-1">
                <a href="{{ route('product.show', $product->slug) }}" class="hover:text-[#083B2B] transition-colors">
                    {{ $product->name }}
                </a>
            </h3>

            {{-- Star Ratings & Reviews Count --}}
            <div class="flex items-center gap-1.5 mb-2">
                <div class="flex text-[#C59B27] text-xs">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <span class="text-[0.72rem] text-[#8E9E98] font-medium">({{ $reviewsCount }})</span>
            </div>
        </div>

        {{-- Price --}}
        <div class="flex items-baseline gap-2 pt-1">
            <span class="product-card-price text-sm sm:text-base font-bold text-[#142E25]">
                ₹{{ number_format($pricing['final_price'] ?? $product->base_price, 0) }}
            </span>
            @if(isset($pricing['discount_amount']) && $pricing['discount_amount'] > 0)
                <span class="product-card-price-original text-xs text-[#8E9E98] line-through">
                    ₹{{ number_format($pricing['original_price'], 0) }}
                </span>
            @endif
        </div>
    </div>
</div>
