@extends('layouts.app')

@section('title', (isset($selectedCategory) && $selectedCategory ? $selectedCategory->name . ' — ' : '') . 'Shop Certified Jewellery | Pinora')
@section('meta_description', 'Shop 22K gold jewellery, floral designs, diamond solitaires and daily wear studs. Certified & BIS hallmarked jewellery handcrafted in Rajkot.')
@section('navbar_tagline', 'FINE JEWELRY FOR EVERY YOU')

@section('content')

{{-- ======================================================== --}}
{{-- 1. CATEGORY CIRCLE STORIES SLIDER (Dynamic from DB)       --}}
{{-- ======================================================== --}}
@include('partials.category-circle-slider', [
    'categories' => $categories,
    'sliderCategories' => $sliderCategories ?? null,
    'isSubCategoryMode' => $isSubCategoryMode ?? false,
    'parentCategory' => $parentCategory ?? null,
])

{{-- ======================================================== --}}
{{-- 2. MAIN CATALOG BODY (Breadcrumbs, Title, Filters, Grid) --}}
{{-- ======================================================== --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 md:py-10">

    {{-- Breadcrumb Navigation --}}
    <nav class="text-[0.75rem] text-[#8E9E98] mb-3 flex items-center gap-1.5 flex-wrap">
        <a href="{{ url('/') }}" class="hover:text-[#083B2B]">Home</a>
        <span>/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-[#083B2B]">Shop</a>
        @if(isset($selectedCategory) && $selectedCategory)
            @foreach($selectedCategory->breadcrumbs() as $crumb)
                <span>/</span>
                @if($loop->last)
                    <span class="text-[#083B2B] font-semibold">{{ $crumb->name }}</span>
                @else
                    <a href="{{ route('shop.index', ['category' => $crumb->slug]) }}" class="hover:text-[#083B2B]">{{ $crumb->name }}</a>
                @endif
            @endforeach
        @elseif(request('category'))
            <span>/</span>
            <span class="text-[#083B2B] font-semibold">{{ ucfirst(str_replace('-', ' ', request('category'))) }}</span>
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
                {{ ucwords(str_replace('-', ' ', request('category'))) }}
            @elseif(request('metal_type'))
                {{ ucfirst(request('metal_type')) }} Jewellery
            @elseif(request('purity'))
                {{ request('purity') }} Jewellery
            @else
                All Jewellery
            @endif
        </h1>
        <p class="text-xs sm:text-sm text-[#60706A] mt-1">
            @if(isset($selectedCategory) && $selectedCategory->description)
                {{ $selectedCategory->description }}
            @else
                Discover handcrafted certified & BIS hallmarked jewellery for every occasion.
            @endif
        </p>
    </div>

    {{-- Filter & Sort Action Bar --}}
    <div class="flex items-center gap-3 mb-4">
        
        {{-- Filter Trigger Button with Dynamic Count Badge --}}
        @php
            $activeFilterKeys = array_filter(['category', 'metal_type', 'purity', 'vendor_id', 'in_stock', 'search'], fn($k) => request()->filled($k));
            $activeFilterCount = count($activeFilterKeys);
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
            @if($activeFilterCount > 0)
                <span class="w-5 h-5 rounded-full bg-[#083B2B] text-white text-[0.68rem] font-bold flex items-center justify-center">
                    {{ $activeFilterCount }}
                </span>
            @endif
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

    {{-- Applied Tags & Products Count Strip --}}
    <div class="flex items-center justify-between flex-wrap gap-2 py-2 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            @if($activeFilterCount > 0)
                <span class="text-xs text-[#8E9E98] mr-1">Active:</span>

                {{-- Category Pill --}}
                @if(request()->filled('category'))
                    @php
                        $catName = isset($selectedCategory) && $selectedCategory ? $selectedCategory->name : ucfirst(str_replace('-', ' ', request('category')));
                    @endphp
                    <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                        <span>Category: {{ $catName }}</span>
                        <a href="{{ request()->fullUrlWithoutQuery(['category', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm" aria-label="Remove category filter">&times;</a>
                    </span>
                @endif

                {{-- Metal Type Pill --}}
                @if(request()->filled('metal_type'))
                    <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                        <span>Metal: {{ ucfirst(request('metal_type')) }}</span>
                        <a href="{{ request()->fullUrlWithoutQuery(['metal_type', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm" aria-label="Remove metal filter">&times;</a>
                    </span>
                @endif

                {{-- Purity Pill (Only shown when explicitly set in request) --}}
                @if(request()->filled('purity'))
                    <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                        <span>Purity: {{ request('purity') }}</span>
                        <a href="{{ request()->fullUrlWithoutQuery(['purity', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm" aria-label="Remove purity filter">&times;</a>
                    </span>
                @endif

                {{-- In Stock Pill --}}
                @if(request()->boolean('in_stock'))
                    <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                        <span>In Stock Only</span>
                        <a href="{{ request()->fullUrlWithoutQuery(['in_stock', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm" aria-label="Remove stock filter">&times;</a>
                    </span>
                @endif

                {{-- Search Pill --}}
                @if(request()->filled('search'))
                    <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                        <span>Search: "{{ request('search') }}"</span>
                        <a href="{{ request()->fullUrlWithoutQuery(['search', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm" aria-label="Remove search filter">&times;</a>
                    </span>
                @endif

                {{-- Vendor Pill --}}
                @if(request()->filled('vendor_id') && isset($vendors))
                    @php
                        $activeVendor = $vendors->firstWhere('id', request('vendor_id'));
                    @endphp
                    @if($activeVendor)
                        <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                            <span>Vendor: {{ $activeVendor->store_name }}</span>
                            <a href="{{ request()->fullUrlWithoutQuery(['vendor_id', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm" aria-label="Remove vendor filter">&times;</a>
                        </span>
                    @endif
                @endif

                <a href="{{ route('shop.index') }}" class="text-xs text-[#083B2B] hover:underline font-semibold ml-1">
                    Clear All
                </a>
            @endif
        </div>

        {{-- Dynamic Products Count --}}
        <span class="text-xs sm:text-sm text-[#60706A] font-medium">
            {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }}
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
        <div class="py-16 text-center bg-white border border-[#EAE5DC] rounded-2xl p-8">
            <div class="text-4xl mb-3">💎</div>
            <h3 class="font-primary text-xl font-bold text-[#142E25] mb-1">No products found</h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-6">We couldn't find any products matching your current filter criteria.</p>
            <a href="{{ route('shop.index') }}" class="btn btn-green text-xs px-6 py-2.5 rounded-xl font-semibold">
                Clear All Filters
            </a>
        </div>
    @endif

</div>

{{-- ======================================================== --}}
{{-- 4. FILTER OFF-CANVAS / MODAL DRAWER (Dynamic from DB)    --}}
{{-- ======================================================== --}}
<div id="filter-modal-drawer" class="fixed inset-y-0 right-0 w-84 max-w-[85vw] bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between">
    <div class="p-6 overflow-y-auto flex-1">
        <div class="flex items-center justify-between pb-4 border-b border-[#EAE5DC] mb-6">
            <h3 class="font-primary text-xl font-bold text-[#083B2B]">Filter Products</h3>
            <button id="filter-drawer-close-btn" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:text-black cursor-pointer">
                &times;
            </button>
        </div>

        <form action="{{ route('shop.index') }}" method="GET" id="catalog-filter-form">
            {{-- Preserve current sort and search if present --}}
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            {{-- Categories Filter (Dynamic from DB) --}}
            @if(isset($categories) && $categories->isNotEmpty())
            <div class="mb-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18] mb-3">Categories</h4>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                        <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} class="accent-[#083B2B]">
                        <span>All Categories</span>
                    </label>
                    @foreach($categories as $cat)
                        <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                            <input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'checked' : '' }} class="accent-[#083B2B]">
                            <span class="font-medium">{{ $cat->name }}</span>
                        </label>
                        @if($cat->children && $cat->children->isNotEmpty())
                            @foreach($cat->children as $sub)
                                <label class="flex items-center gap-2.5 text-xs text-[#60706A] pl-5 cursor-pointer">
                                    <input type="radio" name="category" value="{{ $sub->slug }}" {{ request('category') === $sub->slug ? 'checked' : '' }} class="accent-[#083B2B]">
                                    <span>{{ $sub->name }}</span>
                                </label>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Metal Type --}}
            <div class="mb-6 pt-4 border-t border-[#EAE5DC]">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18] mb-3">Metal Type</h4>
                <div class="space-y-2">
                    <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                        <input type="radio" name="metal_type" value="" {{ !request('metal_type') ? 'checked' : '' }} class="accent-[#083B2B]">
                        <span>All Metals</span>
                    </label>
                    @foreach(['gold' => 'Gold', 'silver' => 'Silver', 'platinum' => 'Platinum'] as $metalKey => $metalLabel)
                        <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                            <input type="radio" name="metal_type" value="{{ $metalKey }}" {{ request('metal_type') === $metalKey ? 'checked' : '' }} class="accent-[#083B2B]">
                            <span>{{ $metalLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Metal Purity --}}
            <div class="mb-6 pt-4 border-t border-[#EAE5DC]">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18] mb-3">Purity</h4>
                <div class="space-y-2">
                    <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                        <input type="radio" name="purity" value="" {{ !request('purity') ? 'checked' : '' }} class="accent-[#083B2B]">
                        <span>All Purities</span>
                    </label>
                    @foreach(['22K' => '22K Gold (916 BIS)', '18K' => '18K Gold (750)', '14K' => '14K Gold', '925' => '925 Sterling Silver'] as $purKey => $purLabel)
                        <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer">
                            <input type="radio" name="purity" value="{{ $purKey }}" {{ request('purity') === $purKey ? 'checked' : '' }} class="accent-[#083B2B]">
                            <span>{{ $purLabel }}</span>
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
                <button type="submit" class="flex-1 py-3 bg-[#083B2B] text-white rounded-xl text-xs sm:text-sm font-semibold hover:bg-[#062E23] transition-colors cursor-pointer">
                    Apply Filters
                </button>
                <a href="{{ route('shop.index') }}" class="py-3 px-4 border border-[#EAE5DC] text-gray-600 rounded-xl text-xs sm:text-sm text-center hover:bg-gray-50 flex items-center justify-center">
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
