# Step 43: Homepage

**Goal:** Create the homepage for the Pinora storefront with all key sections.
**Depends On:** Step 42 (Layout), Step 14 (Category model), Step 16 (Product model), Step 13 (Vendor model)
**Next Step:** Step 44 (Shop/Product Listing)

---

## Files to Create

- `app/Http/Controllers/Frontend/HomeController.php`
- `resources/views/home.blade.php`
- Route added to `routes/web.php`

---

## 1. Route — `routes/web.php`

```php
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\VendorController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\AccountController;

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');
```

---

## 2. `app/Http/Controllers/Frontend/HomeController.php`

```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\MetalRate;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCategories = Category::active()
            ->topLevel()
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $featuredProducts = Product::active()
            ->featured()
            ->with(['primaryImage', 'vendor', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $newArrivals = Product::active()
            ->newArrivals()
            ->with(['primaryImage', 'vendor'])
            ->latest()
            ->limit(8)
            ->get();

        $featuredVendors = Vendor::approved()
            ->withCount('products')
            ->orderByDesc('total_sales')
            ->limit(6)
            ->get();

        $goldRate = MetalRate::getLatestRate('gold', '22K');

        return view('home', compact(
            'featuredCategories',
            'featuredProducts',
            'newArrivals',
            'featuredVendors',
            'goldRate'
        ));
    }
}
```

---

## 3. `resources/views/home.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Pinora — Timeless Jewellery, Infinite Craftsmanship')
@section('meta_description', 'Shop certified gold, silver & diamond jewellery from trusted artisan vendors across India.')

@section('content')

{{-- ========== HERO ========== --}}
<section class="relative min-h-[90vh] flex items-center overflow-hidden bg-gradient-to-br from-[#0d1b35] via-dark-bg to-dark-card">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=1600&q=80')] bg-center bg-cover bg-no-repeat opacity-12"></div>
    <div class="max-w-7xl mx-auto px-6 relative z-10 text-center py-16">
        <p class="text-[0.8rem] tracking-[0.3em] uppercase text-gold mb-6 font-secondary">Est. 2024 · India's Premium Marketplace</p>
        <h1 class="font-primary text-[clamp(2.5rem,7vw,5rem)] font-light leading-[1.1] mb-6 text-text-light">
            Timeless Jewellery,<br>
            <span class="text-gold italic font-semibold">Infinite Craftsmanship</span>
        </h1>
        <p class="text-[1.1rem] text-text-muted max-w-[560px] mx-auto mb-10 leading-relaxed">
            Discover certified gold, silver & diamond jewellery crafted by India's finest artisan vendors. Every piece, a legacy.
        </p>
        <div class="flex gap-4 justify-center flex-wrap">
            <a href="{{ route('shop.index') }}" class="btn btn-gold text-[0.9rem] px-10 py-3.5">Explore Collection</a>
            <a href="{{ route('vendors.index') }}" class="btn btn-outline-gold text-[0.9rem] px-10 py-3.5">Meet Our Vendors</a>
        </div>

        @if($goldRate)
        <div class="mt-12 inline-flex items-center gap-4 py-3 px-6 border border-border-gold rounded-full bg-gold/5">
            <span class="w-2 h-2 rounded-full bg-gold inline-block animate-pulse"></span>
            <span class="text-[0.8rem] text-text-muted">Today's Gold 22K Rate:</span>
            <span class="text-[0.95rem] font-semibold text-gold">₹{{ number_format($goldRate->rate_per_gram, 2) }}/gram</span>
        </div>
        @endif
    </div>
</section>

{{-- ========== TRUST BADGES ========== --}}
<section class="bg-dark-card border-t border-b border-border-gold py-8">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-center">
            @foreach([
                ['🏅', 'BIS Hallmarked', 'All gold jewellery is BIS certified'],
                ['✅', 'Verified Vendors', 'Every seller is ID & document verified'],
                ['🔒', 'Secure Payments', 'SSL encrypted Razorpay checkout'],
                ['↩️', 'Easy Returns', '7-day no-questions return policy'],
            ] as [$icon, $title, $sub])
            <div>
                <div class="text-3xl mb-2">{{ $icon }}</div>
                <div class="font-primary text-base font-semibold text-text-light mb-1">{{ $title }}</div>
                <div class="text-[0.78rem] text-text-muted">{{ $sub }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== SHOP BY CATEGORY ========== --}}
@if($featuredCategories->isNotEmpty())
<section class="section-padding">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <p class="text-xs tracking-widest uppercase text-gold mb-3">Explore</p>
            <h2 class="font-primary text-4xl font-normal text-text-light">Shop by Category</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-5">
            @foreach($featuredCategories as $cat)
            <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" class="block text-center p-6 bg-dark-card border border-border-gold rounded-lg transition-all duration-300 hover:border-gold">
                @if($cat->image)
                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-[60px] h-[60px] object-cover rounded-full mx-auto mb-3 border-2 border-border-gold">
                @else
                <div class="w-[60px] h-[60px] rounded-full bg-gold/10 border-2 border-border-gold mx-auto mb-3 flex items-center justify-center text-2xl">{{ $cat->icon ?? '💎' }}</div>
                @endif
                <div class="font-primary text-base font-semibold">{{ $cat->name }}</div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ========== FEATURED PRODUCTS ========== --}}
@if($featuredProducts->isNotEmpty())
<section class="section-padding bg-dark-surface/15">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-12 flex-wrap gap-4">
            <div>
                <p class="text-xs tracking-widest uppercase text-gold mb-3">Handpicked</p>
                <h2 class="font-primary text-4xl font-normal text-text-light">Featured Pieces</h2>
            </div>
            <a href="{{ route('shop.index', ['featured'=>1]) }}" class="btn btn-outline-gold">View All</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ========== NEW ARRIVALS ========== --}}
@if($newArrivals->isNotEmpty())
<section class="section-padding">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-12 flex-wrap gap-4">
            <div>
                <p class="text-xs tracking-widest uppercase text-gold mb-3">Just In</p>
                <h2 class="font-primary text-4xl font-normal text-text-light">New Arrivals</h2>
            </div>
            <a href="{{ route('shop.index', ['new_arrivals'=>1]) }}" class="btn btn-outline-gold">View All</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($newArrivals as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ========== SHOP BY METAL ========== --}}
<section class="section-padding bg-dark-surface/15">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="font-primary text-4xl font-normal text-text-light">Shop by Metal</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['gold', 'Gold', '#C9A84C', '22K / 18K certified pieces'],
                ['silver', 'Silver', '#C0C0C0', '925 sterling & fine silver'],
                ['platinum', 'Platinum', '#E5E4E2', 'Rare & luxurious pieces'],
                ['diamond', 'Diamond', '#b9f2ff', 'GIA certified stones'],
            ] as [$slug, $label, $color, $sub])
            <a href="{{ route('shop.index', ['metal_type'=>$slug]) }}" style="--hover-color: {{ $color }};" class="block py-10 px-6 bg-dark-card border border-border-gold rounded-lg text-center transition-all duration-300 hover:border-[var(--hover-color)]">
                <div style="background:{{ $color }};" class="w-14 h-14 rounded-full opacity-85 mx-auto mb-4"></div>
                <div style="color: {{ $color }};" class="font-primary text-2xl font-semibold mb-1">{{ $label }}</div>
                <div class="text-[0.78rem] text-text-muted">{{ $sub }}</div>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== FEATURED VENDORS ========== --}}
@if($featuredVendors->isNotEmpty())
<section class="section-padding">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <p class="text-xs tracking-widest uppercase text-gold mb-3">Trusted Sellers</p>
            <h2 class="font-primary text-4xl font-normal text-text-light">Featured Vendors</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach($featuredVendors as $vendor)
            <a href="{{ route('vendors.show', $vendor->store_slug) }}" class="block text-center py-8 px-4 bg-dark-card border border-border-gold rounded-lg transition-all duration-300 hover:border-gold">
                <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->store_name }}" class="w-18 h-18 rounded-full object-cover mx-auto mb-4 border-2 border-border-gold">
                <div class="font-primary text-lg font-semibold mb-1">{{ $vendor->store_name }}</div>
                <div class="text-[0.78rem] text-text-muted">{{ $vendor->products_count }} Products</div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

@push('styles')
<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
@media (max-width: 768px) {
    [style*="grid-template-columns:repeat(4"] { grid-template-columns: repeat(2,1fr) !important; }
}
</style>
@endpush
```

---

## 4. `resources/views/partials/product-card.blade.php`

```blade
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
```

---

## Notes

- `PricingService::calculate()` is defined in Step 51 — it returns an array with `final_price`, `original_price`, `discount_amount`.
- The gold rate ticker requires `MetalRate::getLatestRate()` from Step 15.
- The `partials/product-card.blade.php` component is reused across all listing pages.
- Responsive grids collapse from 4 columns → 2 columns on mobile via CSS media query in `@push('styles')`.
