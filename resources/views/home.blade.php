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
                <img src="{{ $vendor->logo_url ?? 'https://images.unsplash.com/photo-1541336032412-2048a678540d?w=200' }}" alt="{{ $vendor->store_name }}" class="w-18 h-18 rounded-full object-cover mx-auto mb-4 border-2 border-border-gold">
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
