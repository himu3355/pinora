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
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6 justify-center">
            @foreach($featuredCategories as $cat)
            <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" class="block text-center p-6 bg-dark-card border border-border-gold rounded-xl transition-all duration-300 hover:border-gold hover:shadow-[0_8px_24px_rgba(201,168,76,0.15)] group">
                @if($cat->image)
                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-[64px] h-[64px] object-cover rounded-full mx-auto mb-3 border-2 border-border-gold group-hover:scale-105 transition-transform duration-300">
                @else
                <div class="w-[64px] h-[64px] rounded-full bg-gold/10 border-2 border-border-gold mx-auto mb-3 flex items-center justify-center text-gold group-hover:scale-105 group-hover:bg-gold/20 transition-all duration-300">
                    @switch($cat->icon)
                        @case('heroicon-o-star')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385c.116.488-.415.874-.836.612l-4.717-2.94a.563.563 0 0 0-.586 0l-4.717 2.94c-.42.262-.952-.124-.836-.612l1.285-5.385a.563.563 0 0 0-.182-.557l-4.204-3.602c-.38-.325-.178-.948.32-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                            @break
                        @case('heroicon-o-sparkles')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg>
                            @break
                        @case('heroicon-o-moon')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                            @break
                        @case('heroicon-o-cube')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                            @break
                        @case('heroicon-o-shopping-bag')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                            @break
                        @default
                            @if(Str::startsWith($cat->icon, 'heroicon-'))
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /></svg>
                            @else
                                <span class="text-2xl">{{ $cat->icon ?? '💎' }}</span>
                            @endif
                    @endswitch
                </div>
                @endif
                <div class="font-primary text-base font-semibold text-text-light group-hover:text-gold transition-colors duration-300">{{ $cat->name }}</div>
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
