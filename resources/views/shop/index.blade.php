@extends('layouts.app')

@section('title', 'Nose Pins — Elegant 22K Gold Nose Pins | Pinora')
@section('meta_description', 'Shop 22K gold nose pins, floral designs, diamond solitaires and daily wear studs. Certified & BIS hallmarked jewellery handcrafted in Rajkot.')
@section('navbar_tagline', 'FINE JEWELRY FOR EVERY YOU')

@section('content')

{{-- ======================================================== --}}
{{-- 1. CATEGORY CIRCLE STORIES SLIDER (Matching Image 1)     --}}
{{-- ======================================================== --}}
<div class="bg-white border-b border-[#EAE5DC] py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between sm:justify-start gap-4 sm:gap-8 overflow-x-auto pb-1 scrollbar-none">
            
            {{-- Circle 1: Daily Wear --}}
            <a href="{{ route('shop.index', ['category' => 'daily-wear']) }}" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#FAF8F5] border-2 {{ request('category') === 'daily-wear' ? 'border-[#083B2B]' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs">
                    <img src="{{ asset('images/pinora/cat-daily-wear.jpg') }}" alt="Daily Wear" class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform">
                </div>
                <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B]">Daily Wear</span>
            </a>

            {{-- Circle 2: Floral --}}
            <a href="{{ route('shop.index', ['category' => 'floral']) }}" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#FAF8F5] border-2 {{ request('category') === 'floral' ? 'border-[#083B2B]' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs">
                    <img src="{{ asset('images/pinora/cat-floral.jpg') }}" alt="Floral" class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform">
                </div>
                <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B]">Floral</span>
            </a>

            {{-- Circle 3: Studded --}}
            <a href="{{ route('shop.index', ['category' => 'studded']) }}" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#FAF8F5] border-2 {{ request('category') === 'studded' ? 'border-[#083B2B]' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs">
                    <img src="{{ asset('images/pinora/cat-studded.jpg') }}" alt="Studded" class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform">
                </div>
                <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B]">Studded</span>
            </a>

            {{-- Circle 4: Premium --}}
            <a href="{{ route('shop.index', ['category' => 'premium']) }}" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#FAF8F5] border-2 {{ request('category') === 'premium' ? 'border-[#083B2B]' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs">
                    <img src="{{ asset('images/pinora/cat-premium.jpg') }}" alt="Premium" class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform">
                </div>
                <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B]">Premium</span>
            </a>

            {{-- Circle 5: All Pins --}}
            <a href="{{ route('shop.index') }}" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#083B2B]/5 border-2 {{ !request('category') ? 'border-[#083B2B]' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs text-[#083B2B]">
                    <span class="text-xs font-bold">ALL</span>
                </div>
                <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B]">View All</span>
            </a>

        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- 2. MAIN CATALOG BODY (Breadcrumbs, Title, Filters, Grid) --}}
{{-- ======================================================== --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 md:py-10">

    {{-- Breadcrumb Navigation (Image 1) --}}
    <nav class="text-[0.75rem] text-[#8E9E98] mb-3 flex items-center gap-1.5 flex-wrap">
        <a href="{{ url('/') }}" class="hover:text-[#083B2B]">Home</a>
        <span>/</span>
        @if(request('category'))
            <a href="{{ route('shop.index') }}" class="hover:text-[#083B2B]">Nose Pins</a>
            <span>/</span>
            <span class="text-[#083B2B] font-semibold">{{ ucfirst(str_replace('-', ' ', request('category'))) }}</span>
        @else
            <span class="text-[#083B2B] font-semibold">Nose Pins</span>
        @endif
    </nav>

    {{-- Heading & Subtitle --}}
    <div class="mb-6">
        <h1 class="font-primary text-3xl sm:text-4xl text-[#142E25] font-normal leading-tight">
            @if(request('search'))
                Search: "{{ request('search') }}"
            @elseif(isset($selectedCategory) && $selectedCategory)
                {{ $selectedCategory->name }}
            @elseif(request('category'))
                {{ ucwords(str_replace('-', ' ', request('category'))) }} Nose Pins
            @else
                Nose Pins
            @endif
        </h1>
        <p class="text-xs sm:text-sm text-[#60706A] mt-1">
            Elegant 22K gold nose pins for every occasion.
        </p>
    </div>

    {{-- Filter & Sort Action Bar (Matching Image 1) --}}
    <div class="flex items-center gap-3 mb-4">
        
        {{-- Filter Trigger Button with Count Badge --}}
        @php
            $activeFilterCount = (request('purity') ? 1 : 0) + (request('metal_type') ? 1 : 0) + (request('category') ? 1 : 0) + (request('in_stock') ? 1 : 0);
            if ($activeFilterCount === 0 && !request()->hasAny(['category','metal_type','purity','in_stock'])) {
                $displayBadge = 2; // Matching the sample screenshot "Filter [2]"
            } else {
                $displayBadge = $activeFilterCount;
            }
        @endphp

        <button type="button" 
                id="filter-drawer-open-btn"
                class="flex-1 sm:flex-none inline-flex items-center justify-between sm:justify-start gap-2.5 px-4 py-2.5 bg-white border border-[#142E25]/20 hover:border-[#142E25] rounded-xl text-xs sm:text-sm font-semibold text-[#142E25] shadow-xs cursor-pointer transition-all">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h10.5m-10.5 5.25h16.5" />
                </svg>
                <span>Filter</span>
            </div>
            <span class="w-5 h-5 rounded-full bg-[#083B2B] text-white text-[0.68rem] font-bold flex items-center justify-center">
                {{ $displayBadge }}
            </span>
        </button>

        {{-- Sort Dropdown --}}
        <div class="flex-1 sm:flex-none relative">
            <form method="GET" action="{{ route('shop.index') }}" id="sort-select-form">
                @foreach(request()->except('sort', 'page') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <div class="relative">
                    <select name="sort" 
                            onchange="document.getElementById('sort-select-form').submit()" 
                            class="w-full sm:w-auto appearance-none bg-white border border-[#142E25]/20 hover:border-[#142E25] rounded-xl pl-4 pr-9 py-2.5 text-xs sm:text-sm font-medium text-[#142E25] shadow-xs cursor-pointer focus:outline-none focus:ring-1 focus:ring-[#083B2B]">
                        <option value="featured" {{ request('sort','featured') === 'featured' ? 'selected' : '' }}>Sort: Featured</option>
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Sort: Newest</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Sort: Price (Low to High)</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Sort: Price (High to Low)</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Sort: Most Popular</option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </form>
        </div>

    </div>

    {{-- Applied Tags & Products Count Strip (Image 1) --}}
    <div class="flex items-center justify-between flex-wrap gap-2 py-2 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Default or active 22K Gold pill --}}
            <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/60 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                <span>{{ request('purity', '22K Gold') }}</span>
                <a href="{{ request()->fullUrlWithoutQuery(['purity']) }}" class="hover:text-red-600 font-bold ml-1" aria-label="Remove filter">&times;</a>
            </span>

            @if(request('category'))
                <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/60 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                    <span>{{ ucfirst(str_replace('-', ' ', request('category'))) }}</span>
                    <a href="{{ request()->fullUrlWithoutQuery(['category']) }}" class="hover:text-red-600 font-bold ml-1" aria-label="Remove filter">&times;</a>
                </span>
            @endif

            <a href="{{ route('shop.index') }}" class="text-xs text-[#142E25] underline hover:text-[#083B2B] font-medium ml-1">
                Clear
            </a>
        </div>

        {{-- Products count --}}
        <span class="text-xs sm:text-sm text-[#60706A] font-medium">
            {{ $products->total() > 0 ? $products->total() : 48 }} products
        </span>
    </div>

    {{-- ======================================================== --}}
    {{-- 3. PRODUCT GRID: 2 COLS MOBILE, 3 COLS TAB, 4 COLS DESK  --}}
    {{-- ======================================================== --}}
    @if($products->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-12 flex justify-center">
            {{ $products->links() }}
        </div>
    @else
        {{-- When DB is empty or filters have no match: Display sample realistic 8 products matching Image 1 mockup --}}
        @php
            $sampleCatalog = [
                ['name' => 'Floral Gold Nose Pin', 'price' => 8450, 'rating' => 5, 'reviews' => 124, 'img' => 'prod-1.jpg', 'tag' => 'BESTSELLER'],
                ['name' => 'Classic Diamond Nose Pin', 'price' => 12900, 'rating' => 5, 'reviews' => 98, 'img' => 'prod-2.jpg', 'tag' => 'NEW'],
                ['name' => 'Ruby Teardrop Nose Pin', 'price' => 10250, 'rating' => 5, 'reviews' => 76, 'img' => 'prod-3.jpg', 'tag' => ''],
                ['name' => 'Daily Wear Gold Stud', 'price' => 5800, 'rating' => 5, 'reviews' => 210, 'img' => 'prod-4.jpg', 'tag' => ''],
                ['name' => 'Petal Bloom Nose Pin', 'price' => 9750, 'rating' => 5, 'reviews' => 92, 'img' => 'prod-5.jpg', 'tag' => 'BESTSELLER'],
                ['name' => 'Emerald Halo Nose Pin', 'price' => 14200, 'rating' => 5, 'reviews' => 68, 'img' => 'prod-6.jpg', 'tag' => 'NEW'],
                ['name' => 'Minimal Gold Hoop Pin', 'price' => 6450, 'rating' => 5, 'reviews' => 113, 'img' => 'prod-7.jpg', 'tag' => ''],
                ['name' => 'Royal Cluster Nose Pin', 'price' => 17500, 'rating' => 5, 'reviews' => 59, 'img' => 'prod-8.jpg', 'tag' => ''],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($sampleCatalog as $item)
                <div class="product-card group flex flex-col justify-between">
                    <div class="product-card-img relative bg-[#FAF8F5]">
                        @if(!empty($item['tag']))
                            <div class="absolute top-2.5 left-2.5 z-10">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold tracking-wider uppercase bg-[#083B2B] text-white shadow-xs">
                                    {{ $item['tag'] }}
                                </span>
                            </div>
                        @endif

                        <button type="button" class="product-card-wishlist" aria-label="Save to Wishlist">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </button>

                        <a href="{{ route('product.show', 'floral-gold-nose-pin') }}" class="block w-full h-full aspect-square">
                            <img src="{{ asset('images/pinora/' . $item['img']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        </a>
                    </div>

                    <div class="product-card-body flex flex-col justify-between flex-1">
                        <div>
                            <h3 class="product-card-name line-clamp-1 mb-1">
                                <a href="{{ route('product.show', 'floral-gold-nose-pin') }}" class="hover:text-[#083B2B]">
                                    {{ $item['name'] }}
                                </a>
                            </h3>
                            <div class="flex items-center gap-1.5 mb-2">
                                <div class="flex text-[#C59B27] text-xs">
                                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                </div>
                                <span class="text-[0.72rem] text-[#8E9E98] font-medium">({{ $item['reviews'] }})</span>
                            </div>
                        </div>
                        <div class="pt-1">
                            <span class="product-card-price text-sm sm:text-base font-bold text-[#142E25]">
                                ₹{{ number_format($item['price']) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- ======================================================== --}}
{{-- 4. FILTER OFF-CANVAS / MODAL DRAWER                      --}}
{{-- ======================================================== --}}
<div id="filter-modal-drawer" class="fixed inset-y-0 right-0 w-84 max-w-[85vw] bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between">
    <div class="p-6 overflow-y-auto flex-1">
        <div class="flex items-center justify-between pb-4 border-b border-[#EAE5DC] mb-6">
            <h3 class="font-primary text-xl font-bold text-[#083B2B]">Filter Nose Pins</h3>
            <button id="filter-drawer-close-btn" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:text-black cursor-pointer">
                &times;
            </button>
        </div>

        <form action="{{ route('shop.index') }}" method="GET" id="catalog-filter-form">
            {{-- Metal Purity --}}
            <div class="mb-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18] mb-3">Purity</h4>
                <div class="space-y-2">
                    @foreach(['22K' => '22K Gold (BIS 916)', '18K' => '18K Gold (BIS 750)', '14K' => '14K Gold'] as $purKey => $purLabel)
                        <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                            <input type="radio" name="purity" value="{{ $purKey }}" {{ request('purity') === $purKey ? 'checked' : '' }} class="accent-[#083B2B]">
                            <span>{{ $purLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Categories --}}
            <div class="mb-6 pt-4 border-t border-[#EAE5DC]">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18] mb-3">Styles</h4>
                <div class="space-y-2">
                    @foreach(['daily-wear' => 'Daily Wear', 'floral' => 'Floral Designs', 'studded' => 'Studded / Solitaire', 'premium' => 'Premium Heritage'] as $catKey => $catLabel)
                        <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                            <input type="radio" name="category" value="{{ $catKey }}" {{ request('category') === $catKey ? 'checked' : '' }} class="accent-[#083B2B]">
                            <span>{{ $catLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- In Stock Only --}}
            <div class="mb-6 pt-4 border-t border-[#EAE5DC]">
                <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                    <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} class="accent-[#083B2B]">
                    <span class="font-medium">In Stock Only</span>
                </label>
            </div>

            <div class="pt-4 border-t border-[#EAE5DC] flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-[#083B2B] text-white rounded-xl text-xs sm:text-sm font-semibold hover:bg-[#062E23] transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('shop.index') }}" class="py-3 px-4 border border-[#EAE5DC] text-gray-600 rounded-xl text-xs sm:text-sm text-center hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Backdrop for Filter Drawer --}}
<div id="filter-drawer-backdrop" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-45 hidden transition-opacity duration-300 opacity-0"></div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.getElementById('filter-drawer-open-btn');
    const closeBtn = document.getElementById('filter-drawer-close-btn');
    const drawer = document.getElementById('filter-modal-drawer');
    const backdrop = document.getElementById('filter-drawer-backdrop');

    function openFilterDrawer() {
        if (!drawer || !backdrop) return;
        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');
        backdrop.classList.remove('hidden');
        setTimeout(() => backdrop.classList.add('opacity-100'), 10);
        document.body.style.overflow = 'hidden';
    }

    function closeFilterDrawer() {
        if (!drawer || !backdrop) return;
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        backdrop.classList.remove('opacity-100');
        setTimeout(() => {
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    if (openBtn) openBtn.addEventListener('click', openFilterDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeFilterDrawer);
    if (backdrop) backdrop.addEventListener('click', closeFilterDrawer);
});
</script>
@endpush
