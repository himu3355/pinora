@extends('layouts.app')

@section('title', 'Shop — Pinora Jewellery')
@section('meta_description', 'Browse certified gold, silver and diamond jewellery from verified vendors.')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-12">

    {{-- Page Header --}}
    <div class="mb-10">
        <h1 id="shop-title" class="font-primary text-4xl font-normal mb-2 text-text-light">
            @if(request('search'))
                Search: "{{ request('search') }}"
            @elseif(isset($selectedCategory) && $selectedCategory)
                {{ $selectedCategory->name }}
            @elseif(request('metal_type'))
                {{ ucfirst(request('metal_type')) }} Jewellery
            @else
                All Jewellery
            @endif
        </h1>
        <p id="shop-count" class="text-text-muted text-[0.9rem]">
            {{ $products->total() }} {{ Str::plural('product', $products->total()) }} found
        </p>
    </div>

    {{-- Active Filters --}}
    <div id="active-filters-container">
        @include('shop.partials.active-filters')
    </div>

    {{-- Sub-Category Quick Filter Bar --}}
    @if(isset($subCategories) && $subCategories->isNotEmpty())
        <div class="mb-8 flex items-center gap-2" data-scroll-wrapper>
            <span class="text-xs uppercase tracking-wider text-gold font-semibold whitespace-nowrap mr-1 flex items-center gap-1 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 11h.01M7 15h.01M10 7h10M10 11h10M10 15h10"/></svg>
                Sub-Categories:
            </span>

            {{-- Left Arrow --}}
            <button type="button" data-scroll-btn="left" class="shrink-0 w-7 h-7 rounded-full bg-dark-card hover:bg-gold hover:text-dark-bg text-gold border border-border-gold flex items-center justify-center transition-all duration-200 cursor-pointer disabled:opacity-20 disabled:cursor-not-allowed" aria-label="Scroll left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div data-scroll-container class="flex items-center gap-2 overflow-x-auto pb-1.5 pt-0.5 scrollbar-none scroll-smooth flex-1">
                <a href="{{ route('shop.index', request()->except('category')) }}" 
                   class="px-3.5 py-1.5 rounded-full text-xs font-medium border transition-all duration-200 whitespace-nowrap shrink-0 {{ !request('category') ? 'bg-gold text-dark-bg border-gold font-semibold shadow-md' : 'bg-dark-card text-text-muted border-border-gold hover:border-gold hover:text-gold' }}">
                    All Sub-Categories
                </a>
                @foreach($subCategories as $subCat)
                    <a href="{{ route('shop.index', array_merge(request()->query(), ['category' => $subCat->slug])) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-medium border transition-all duration-200 whitespace-nowrap shrink-0 {{ request('category') === $subCat->slug ? 'bg-gold text-dark-bg border-gold font-semibold shadow-md' : 'bg-dark-card text-text-muted border-border-gold hover:border-gold hover:text-gold' }}">
                        {{ $subCat->name }}
                    </a>
                @endforeach
            </div>

            {{-- Right Arrow --}}
            <button type="button" data-scroll-btn="right" class="shrink-0 w-7 h-7 rounded-full bg-dark-card hover:bg-gold hover:text-dark-bg text-gold border border-border-gold flex items-center justify-center transition-all duration-200 cursor-pointer disabled:opacity-20 disabled:cursor-not-allowed" aria-label="Scroll right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8 items-start">

        {{-- ===== SIDEBAR FILTERS ===== --}}
        <aside class="bg-dark-card border border-border-gold rounded-lg p-6 lg:sticky lg:top-[90px]">
            <h3 class="font-primary text-xl mb-6 pb-3 border-b border-border-gold text-text-light">Filter</h3>

            <form method="GET" action="{{ route('shop.index') }}" id="filter-form">
                {{-- Main Category --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-3 font-semibold">Primary Category</h4>
                    @foreach($categories as $cat)
                    <label class="flex items-center gap-2 mb-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'checked' : '' }} class="accent-gold">
                        {{ $cat->name }}
                    </label>
                    @endforeach
                </div>

                {{-- Sub Category Menu Separately --}}
                @if(isset($subCategories) && $subCategories->isNotEmpty())
                <div class="mb-6 pt-4 border-t border-border-gold/30">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-3 font-semibold">Sub Category</h4>
                    @foreach($subCategories as $subCat)
                    <label class="flex items-center gap-2 mb-2 cursor-pointer text-[0.85rem] text-text-muted hover:text-gold transition-colors">
                        <input type="radio" name="category" value="{{ $subCat->slug }}" {{ request('category') === $subCat->slug ? 'checked' : '' }} class="accent-gold">
                        {{ $subCat->name }}
                    </label>
                    @endforeach
                </div>
                @endif

                {{-- Metal Type --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Metal Type</h4>
                    @foreach(['gold'=>'Gold','silver'=>'Silver','platinum'=>'Platinum','other'=>'Other'] as $val => $label)
                    <label class="flex items-center gap-2 mb-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="radio" name="metal_type" value="{{ $val }}" {{ request('metal_type') === $val ? 'checked' : '' }} class="accent-gold">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                {{-- Purity --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Purity</h4>
                    @foreach(['24K','22K','18K','14K','999','925','950'] as $purity)
                    <label class="flex items-center gap-2 mb-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="radio" name="purity" value="{{ $purity }}" {{ request('purity') === $purity ? 'checked' : '' }} class="accent-gold">
                        {{ $purity }}
                    </label>
                    @endforeach
                </div>

                {{-- In Stock --}}
                <div class="mb-6">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} class="accent-gold">
                        In Stock Only
                    </label>
                </div>

                {{-- Vendor --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Vendor</h4>
                    <select name="vendor_id" class="w-full bg-white/5 border border-border-gold rounded-lg py-2 px-3 text-text-light font-secondary text-sm cursor-pointer outline-none focus:border-gold transition-colors">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->store_name }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ route('shop.index') }}" class="btn btn-outline-gold w-full justify-center mt-2 data-filter-clear">Clear Filters</a>
            </form>
        </aside>

        {{-- ===== PRODUCT GRID ===== --}}
        <div>

            {{-- Sort Bar --}}
            <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
                <span id="showing-results-text" class="text-sm text-text-muted">
                    @if($products->total() > 0)
                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
                    @else
                        Showing 0–0 of 0
                    @endif
                </span>
                <form method="GET" action="{{ route('shop.index') }}" id="sort-form">
                    <select name="sort" class="bg-dark-card border border-border-gold rounded-lg py-2 px-4 text-text-light font-secondary text-sm cursor-pointer outline-none focus:border-gold transition-colors">
                        <option value="newest" {{ request('sort','newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                    </select>
                </form>
            </div>

            {{-- Products --}}
            <div id="products-grid-container" class="transition-opacity duration-200">
                @include('shop.partials.products-grid')
            </div>

            {{-- Pagination --}}
            <div id="pagination-container" class="mt-12">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
