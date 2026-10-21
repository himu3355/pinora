@extends('layouts.app')

@section('title', (isset($selectedCategory) && $selectedCategory ? $selectedCategory->name . ' — ' : '') . 'Shop Certified Jewellery | Pinora')
@section('meta_description', 'Shop 22K gold jewellery, floral designs, diamond solitaires and daily wear studs. Certified & BIS hallmarked jewellery handcrafted in Rajkot.')
@section('navbar_tagline', 'FINE JEWELRY FOR EVERY YOU')

@push('styles')
<style>
.range-thumb::-webkit-slider-thumb {
    pointer-events: auto;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    background-color: #083B2B;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.25);
    cursor: pointer;
    transition: transform 0.1s ease;
}
.range-thumb::-webkit-slider-thumb:hover {
    transform: scale(1.15);
}
.range-thumb::-moz-range-thumb {
    pointer-events: auto;
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    background-color: #083B2B;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.25);
    cursor: pointer;
    transition: transform 0.1s ease;
}
.range-thumb::-moz-range-thumb:hover {
    transform: scale(1.15);
}
</style>
@endpush

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
        <h1 id="shop-page-title" class="font-primary text-3xl sm:text-4xl text-[#142E25] font-normal leading-tight">
            @if(isset($title) && $title)
                {!! $title !!}
            @elseif(request('search'))
                Search: "{{ request('search') }}"
            @elseif(isset($selectedCategory) && $selectedCategory)
                {{ $selectedCategory->name }}
            @elseif(request('category'))
                {{ ucwords(str_replace('-', ' ', request('category'))) }}
            @elseif(request('metal_type'))
                @php
                    $mList = is_array(request('metal_type')) ? request('metal_type') : [request('metal_type')];
                @endphp
                {{ implode(' & ', array_map('ucfirst', array_filter($mList))) }} Jewellery
            @elseif(request('purity'))
                @php
                    $pList = is_array(request('purity')) ? request('purity') : [request('purity')];
                @endphp
                {{ implode(', ', array_filter($pList)) }} Jewellery
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
    <div class="flex items-center justify-between gap-3 mb-4">
        
        {{-- Mobile Filter Trigger Button with Dynamic Count Badge (Hidden on Desktop where sidebar is visible) --}}
        <button type="button" 
                id="filter-drawer-open-btn"
                class="lg:hidden flex-1 sm:flex-none inline-flex items-center justify-between sm:justify-start gap-2.5 px-4 py-2.5 bg-white border border-[#142E25]/20 hover:border-[#142E25] rounded-xl text-xs sm:text-sm font-semibold text-[#142E25] shadow-xs cursor-pointer transition-all">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h10.5m-10.5 5.25h16.5" />
                </svg>
                <span>Filter</span>
            </div>
            <span id="shop-filter-badge" class="w-5 h-5 rounded-full bg-[#083B2B] text-white text-[0.68rem] font-bold flex items-center justify-center {{ ($activeFilterCount ?? 0) > 0 ? '' : 'hidden' }}">
                {{ $activeFilterCount ?? 0 }}
            </span>
        </button>

        {{-- Desktop Active Items Summary Placeholder --}}
        <div class="hidden lg:block text-xs sm:text-sm text-[#60706A]">
            Handcrafted with precision & hallmarked for purity
        </div>

        {{-- Sort Dropdown --}}
        <div class="flex-1 sm:flex-none relative">
            <div class="relative">
                <select id="shop-sort-select" 
                        name="sort"
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
        </div>

    </div>

    {{-- ======================================================== --}}
    {{-- 3. MAIN SHOP LAYOUT: DESKTOP SIDEBAR + PRODUCT GRID AREA --}}
    {{-- ======================================================== --}}
    <div class="flex items-start gap-8">
        
        {{-- DESKTOP PERSISTENT STICKY SIDEBAR (Hidden on mobile/tablet) --}}
        <aside class="hidden lg:block w-72 shrink-0">
            <div class="sticky top-24 bg-white border border-[#EAE5DC] rounded-2xl p-5 shadow-xs max-h-[calc(100vh-7rem)] overflow-y-auto">
                <div class="flex items-center justify-between pb-3.5 border-b border-[#EAE5DC] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="font-primary text-lg font-bold text-[#083B2B]">Filters</span>
                        <span id="desktop-filter-count-badge" class="px-2 py-0.5 rounded-full bg-[#083B2B] text-white text-[0.65rem] font-bold {{ ($activeFilterCount ?? 0) > 0 ? '' : 'hidden' }}">
                            {{ $activeFilterCount ?? 0 }}
                        </span>
                    </div>
                    <button type="button" class="text-xs text-[#083B2B] hover:underline font-semibold cursor-pointer shop-clear-all-btn">
                        Reset All
                    </button>
                </div>

                <form id="desktop-filter-form" class="shop-filter-form space-y-6">
                    @include('shop.partials.filter-form-fields', ['bounds' => $bounds])
                </form>
            </div>
        </aside>

        {{-- PRODUCTS & ACTIVE FILTERS AREA --}}
        <div class="flex-1 min-w-0">
            
            {{-- Active Filter Tags Strip --}}
            <div id="shop-active-filters-container">
                @include('shop.partials.active-filters', [
                    'products' => $products,
                    'selectedCategory' => $selectedCategory,
                    'vendors' => $vendors
                ])
            </div>

            {{-- Products Grid with Loading State Overlay --}}
            <div class="relative min-h-[300px]">
                
                {{-- Dynamic AJAX Loading Spinner --}}
                <div id="shop-grid-loading" class="absolute inset-0 bg-white/70 backdrop-blur-2xs z-20 flex items-center justify-center rounded-2xl hidden opacity-0 transition-opacity duration-200">
                    <div class="inline-flex items-center gap-3 px-5 py-3 bg-white border border-[#083B2B]/20 rounded-full shadow-lg text-xs sm:text-sm font-semibold text-[#083B2B]">
                        <svg class="animate-spin h-4 w-4 text-[#083B2B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Loading jewellery...</span>
                    </div>
                </div>

                {{-- Product Grid Rendered Target --}}
                <div id="shop-products-grid-container">
                    @include('shop.partials.products-grid', ['products' => $products])
                </div>
            </div>

            {{-- Dynamic Pagination Links Target --}}
            <div id="shop-pagination-container" class="mt-12 flex justify-center">
                {{ $products->links() }}
            </div>

        </div>

    </div>

</div>

{{-- ======================================================== --}}
{{-- 4. MOBILE FILTER OFF-CANVAS DRAWER                       --}}
{{-- ======================================================== --}}
<div id="filter-modal-drawer" class="lg:hidden fixed inset-y-0 right-0 w-84 max-w-[88vw] bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between">
    <div class="p-5 overflow-y-auto flex-1">
        <div class="flex items-center justify-between pb-3.5 border-b border-[#EAE5DC] mb-5">
            <h3 class="font-primary text-xl font-bold text-[#083B2B]">Filter Products</h3>
            <button id="filter-drawer-close-btn" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:text-black cursor-pointer">
                &times;
            </button>
        </div>

        <form id="mobile-filter-form" class="shop-filter-form space-y-6">
            @include('shop.partials.filter-form-fields', ['bounds' => $bounds])
        </form>
    </div>

    {{-- Sticky Footer in Mobile Drawer --}}
    <div class="p-4 border-t border-[#EAE5DC] bg-white flex gap-3 shadow-lg">
        <button type="button" id="mobile-drawer-apply-btn" class="flex-1 py-3 bg-[#083B2B] text-white rounded-xl text-xs sm:text-sm font-semibold hover:bg-[#062E23] transition-colors cursor-pointer text-center">
            Show <span id="mobile-drawer-count">{{ $products->total() }}</span> Products
        </button>
        <button type="button" class="py-3 px-4 border border-[#EAE5DC] text-gray-600 rounded-xl text-xs sm:text-sm text-center hover:bg-gray-50 cursor-pointer shop-clear-all-btn">
            Reset
        </button>
    </div>
</div>

{{-- Backdrop for Filter Drawer --}}
<div id="filter-drawer-backdrop" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-45 hidden transition-opacity duration-300 opacity-0"></div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // 1. Mobile Drawer Toggle Handlers
    // -------------------------------------------------------------
    const openBtn = document.getElementById('filter-drawer-open-btn');
    const closeBtn = document.getElementById('filter-drawer-close-btn');
    const drawer = document.getElementById('filter-modal-drawer');
    const backdrop = document.getElementById('filter-drawer-backdrop');
    const mobileApplyBtn = document.getElementById('mobile-drawer-apply-btn');

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
    if (mobileApplyBtn) mobileApplyBtn.addEventListener('click', () => {
        closeFilterDrawer();
    });

    // -------------------------------------------------------------
    // 2. Dual-Thumb Range Slider Controller
    // -------------------------------------------------------------
    function initDualSliders() {
        const sections = document.querySelectorAll('.filter-section[data-slider-type]');
        sections.forEach(section => {
            const isPrice = section.dataset.sliderType === 'price';
            const wrap = section.querySelector('.range-slider-wrap');
            if (!wrap) return;

            const minBound = parseFloat(wrap.dataset.minBound);
            const maxBound = parseFloat(wrap.dataset.maxBound);
            const step = parseFloat(wrap.dataset.step) || 1;

            const thumbMin = wrap.querySelector('.range-thumb-min');
            const thumbMax = wrap.querySelector('.range-thumb-max');
            const fill = wrap.querySelector('.range-track-fill');
            const inputMin = section.querySelector('.filter-min-input');
            const inputMax = section.querySelector('.filter-max-input');
            const display = section.querySelector(isPrice ? '.price-range-display' : '.weight-range-display');

            function updateFill() {
                const minVal = parseFloat(thumbMin.value);
                const maxVal = parseFloat(thumbMax.value);

                const minPct = Math.max(0, Math.min(100, ((minVal - minBound) / (maxBound - minBound)) * 100));
                const maxPct = Math.max(0, Math.min(100, ((maxVal - minBound) / (maxBound - minBound)) * 100));

                if (fill) {
                    fill.style.left = `${minPct}%`;
                    fill.style.right = `${100 - maxPct}%`;
                }

                if (display) {
                    if (isPrice) {
                        display.textContent = `₹${Math.round(minVal).toLocaleString('en-IN')} – ₹${Math.round(maxVal).toLocaleString('en-IN')}`;
                    } else {
                        display.textContent = `${minVal.toFixed(1)}g – ${maxVal.toFixed(1)}g`;
                    }
                }
            }

            // Sync from thumbs to inputs
            thumbMin.addEventListener('input', () => {
                if (parseFloat(thumbMin.value) > parseFloat(thumbMax.value) - step) {
                    thumbMin.value = parseFloat(thumbMax.value) - step;
                }
                if (inputMin) inputMin.value = thumbMin.value;
                updateFill();
                triggerFilterDebounced();
            });

            thumbMax.addEventListener('input', () => {
                if (parseFloat(thumbMax.value) < parseFloat(thumbMin.value) + step) {
                    thumbMax.value = parseFloat(thumbMin.value) + step;
                }
                if (inputMax) inputMax.value = thumbMax.value;
                updateFill();
                triggerFilterDebounced();
            });

            // Sync from inputs to thumbs
            if (inputMin) {
                inputMin.addEventListener('input', () => {
                    const val = parseFloat(inputMin.value);
                    if (!isNaN(val)) {
                        thumbMin.value = Math.max(minBound, Math.min(parseFloat(thumbMax.value) - step, val));
                        updateFill();
                    }
                    triggerFilterDebounced();
                });
            }

            if (inputMax) {
                inputMax.addEventListener('input', () => {
                    const val = parseFloat(inputMax.value);
                    if (!isNaN(val)) {
                        thumbMax.value = Math.min(maxBound, Math.max(parseFloat(thumbMin.value) + step, val));
                        updateFill();
                    }
                    triggerFilterDebounced();
                });
            }

            // Initial fill calculation
            updateFill();
        });
    }

    initDualSliders();

    // -------------------------------------------------------------
    // 3. Quick Preset Chips Handler
    // -------------------------------------------------------------
    document.addEventListener('click', (e) => {
        const chip = e.target.closest('.price-chip, .weight-chip');
        if (!chip) return;

        const isPrice = chip.classList.contains('price-chip');
        const minVal = chip.dataset.presetMin;
        const maxVal = chip.dataset.presetMax;

        const forms = document.querySelectorAll('.shop-filter-form');
        forms.forEach(form => {
            const minInput = form.querySelector(isPrice ? 'input[name="min_price"]' : 'input[name="min_weight"]');
            const maxInput = form.querySelector(isPrice ? 'input[name="max_price"]' : 'input[name="max_weight"]');
            const section = form.querySelector(isPrice ? '[data-slider-type="price"]' : '[data-slider-type="weight"]');

            if (minInput) minInput.value = minVal;
            if (maxInput) maxInput.value = maxVal;

            if (section) {
                const thumbMin = section.querySelector('.range-thumb-min');
                const thumbMax = section.querySelector('.range-thumb-max');
                if (thumbMin && minVal !== '') thumbMin.value = minVal;
                if (thumbMax && maxVal !== '') thumbMax.value = maxVal;
                thumbMin?.dispatchEvent(new Event('input'));
            }
        });

        triggerFilterImmediate();
    });

    // -------------------------------------------------------------
    // 4. Two-Way Form Sync (Desktop <-> Mobile)
    // -------------------------------------------------------------
    document.addEventListener('change', (e) => {
        const input = e.target.closest('.filter-input');
        if (!input) return;

        const name = input.name;
        const value = input.value;
        const type = input.type;

        document.querySelectorAll(`.shop-filter-form [name="${name}"]`).forEach(other => {
            if (other === input) return;
            if (type === 'checkbox') {
                if (other.value === value) other.checked = input.checked;
            } else if (type === 'radio') {
                if (other.value === value) other.checked = input.checked;
            } else {
                other.value = input.value;
            }
        });

        triggerFilterImmediate();
    });

    // -------------------------------------------------------------
    // 5. AJAX Live Querying Engine (No Page Reload)
    // -------------------------------------------------------------
    let filterDebounceTimer = null;
    let currentAbortCtrl = null;

    function triggerFilterDebounced() {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(triggerFilterImmediate, 350);
    }

    function triggerFilterImmediate() {
        clearTimeout(filterDebounceTimer);
        const params = collectActiveFilterParams();
        fetchShopResults(params);
    }

    function collectActiveFilterParams() {
        const form = document.querySelector('#desktop-filter-form') || document.querySelector('#mobile-filter-form');
        const params = new URLSearchParams();

        if (form) {
            // Category radio
            const cat = form.querySelector('input[name="category"]:checked');
            if (cat && cat.value) params.set('category', cat.value);

            // Metal checkboxes
            form.querySelectorAll('input[name="metal_type[]"]:checked').forEach(cb => {
                params.append('metal_type[]', cb.value);
            });

            // Purity checkboxes
            form.querySelectorAll('input[name="purity[]"]:checked').forEach(cb => {
                params.append('purity[]', cb.value);
            });

            // Price range inputs
            const minPrice = form.querySelector('input[name="min_price"]')?.value.trim();
            const maxPrice = form.querySelector('input[name="max_price"]')?.value.trim();
            if (minPrice) params.set('min_price', minPrice);
            if (maxPrice) params.set('max_price', maxPrice);

            // Weight range inputs
            const minWeight = form.querySelector('input[name="min_weight"]')?.value.trim();
            const maxWeight = form.querySelector('input[name="max_weight"]')?.value.trim();
            if (minWeight) params.set('min_weight', minWeight);
            if (maxWeight) params.set('max_weight', maxWeight);

            // In Stock
            const stock = form.querySelector('input[name="in_stock"]:checked');
            if (stock) params.set('in_stock', '1');
        }

        // Sort
        const sortSelect = document.getElementById('shop-sort-select');
        if (sortSelect && sortSelect.value && sortSelect.value !== 'featured') {
            params.set('sort', sortSelect.value);
        }

        // Preserve search if present in URL
        const currentUrlParams = new URLSearchParams(window.location.search);
        if (currentUrlParams.has('search')) {
            params.set('search', currentUrlParams.get('search'));
        }
        if (currentUrlParams.has('vendor_id')) {
            params.set('vendor_id', currentUrlParams.get('vendor_id'));
        }

        return params;
    }

    async function fetchShopResults(params, updateHistory = true) {
        const queryString = params instanceof URLSearchParams ? params.toString() : params;
        const targetUrl = `${window.location.pathname}${queryString ? '?' + queryString : ''}`;

        // Update URL bar without reload
        if (updateHistory) {
            window.history.pushState(null, '', targetUrl);
        }

        // Show loading state
        const loadingOverlay = document.getElementById('shop-grid-loading');
        if (loadingOverlay) {
            loadingOverlay.classList.remove('hidden');
            setTimeout(() => loadingOverlay.classList.add('opacity-100'), 10);
        }

        if (currentAbortCtrl) currentAbortCtrl.abort();
        currentAbortCtrl = new AbortController();

        try {
            const res = await fetch(targetUrl, {
                signal: currentAbortCtrl.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('Network response failed');
            const data = await res.json();

            // Update DOM targets
            const gridContainer = document.getElementById('shop-products-grid-container');
            if (gridContainer && data.grid_html) {
                gridContainer.innerHTML = data.grid_html;
            }

            const paginationContainer = document.getElementById('shop-pagination-container');
            if (paginationContainer) {
                paginationContainer.innerHTML = data.pagination_html || '';
            }

            const filtersContainer = document.getElementById('shop-active-filters-container');
            if (filtersContainer && data.active_filters_html) {
                filtersContainer.innerHTML = data.active_filters_html;
            }

            const pageTitle = document.getElementById('shop-page-title');
            if (pageTitle && data.title) {
                pageTitle.innerHTML = data.title;
            }

            const mobileDrawerCount = document.getElementById('mobile-drawer-count');
            if (mobileDrawerCount && data.total_count !== undefined) {
                mobileDrawerCount.textContent = data.total_count;
            }

            // Update badges
            const count = data.active_filter_count || 0;
            const mobileBadge = document.getElementById('shop-filter-badge');
            const deskBadge = document.getElementById('desktop-filter-count-badge');

            if (mobileBadge) {
                mobileBadge.textContent = count;
                mobileBadge.classList.toggle('hidden', count === 0);
            }
            if (deskBadge) {
                deskBadge.textContent = count;
                deskBadge.classList.toggle('hidden', count === 0);
            }

        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error('Failed to fetch filtered products:', err);
            }
        } finally {
            if (loadingOverlay) {
                loadingOverlay.classList.remove('opacity-100');
                setTimeout(() => loadingOverlay.classList.add('hidden'), 200);
            }
        }
    }

    // -------------------------------------------------------------
    // 6. Sort Dropdown Listener
    // -------------------------------------------------------------
    const sortSelect = document.getElementById('shop-sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            triggerFilterImmediate();
        });
    }

    // -------------------------------------------------------------
    // 7. Pagination Links Interception
    // -------------------------------------------------------------
    document.addEventListener('click', (e) => {
        const pagLink = e.target.closest('#shop-pagination-container a');
        if (!pagLink) return;

        e.preventDefault();
        const href = pagLink.getAttribute('href');
        if (!href) return;

        const url = new URL(href, window.location.origin);
        fetchShopResults(url.searchParams);

        // Smooth scroll to top of product grid
        const grid = document.getElementById('shop-products-grid-container');
        if (grid) {
            grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    // -------------------------------------------------------------
    // 8. Active Filter Pill Removals
    // -------------------------------------------------------------
    document.addEventListener('click', (e) => {
        const pill = e.target.closest('.filter-pill-remove');
        if (!pill) return;

        e.preventDefault();
        const removeKey = pill.dataset.removeKey;
        const removeMetal = pill.dataset.removeMetal;
        const removePurity = pill.dataset.removePurity;

        // Uncheck/clear inputs in forms
        if (removeKey === 'category') {
            document.querySelectorAll('input[name="category"]').forEach(r => r.checked = (r.value === ''));
        } else if (removeMetal) {
            document.querySelectorAll(`input[name="metal_type[]"][value="${removeMetal}"]`).forEach(cb => cb.checked = false);
        } else if (removePurity) {
            document.querySelectorAll(`input[name="purity[]"][value="${removePurity}"]`).forEach(cb => cb.checked = false);
        } else if (removeKey === 'price') {
            document.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(inp => inp.value = '');
            document.querySelectorAll('[data-slider-type="price"]').forEach(sec => {
                const wrap = sec.querySelector('.range-slider-wrap');
                if (wrap) {
                    const minBound = wrap.dataset.minBound;
                    const maxBound = wrap.dataset.maxBound;
                    const thumbMin = sec.querySelector('.range-thumb-min');
                    const thumbMax = sec.querySelector('.range-thumb-max');
                    if (thumbMin) thumbMin.value = minBound;
                    if (thumbMax) thumbMax.value = maxBound;
                    thumbMin?.dispatchEvent(new Event('input'));
                }
            });
        } else if (removeKey === 'weight') {
            document.querySelectorAll('input[name="min_weight"], input[name="max_weight"]').forEach(inp => inp.value = '');
            document.querySelectorAll('[data-slider-type="weight"]').forEach(sec => {
                const wrap = sec.querySelector('.range-slider-wrap');
                if (wrap) {
                    const minBound = wrap.dataset.minBound;
                    const maxBound = wrap.dataset.maxBound;
                    const thumbMin = sec.querySelector('.range-thumb-min');
                    const thumbMax = sec.querySelector('.range-thumb-max');
                    if (thumbMin) thumbMin.value = minBound;
                    if (thumbMax) thumbMax.value = maxBound;
                    thumbMin?.dispatchEvent(new Event('input'));
                }
            });
        } else if (removeKey === 'in_stock') {
            document.querySelectorAll('input[name="in_stock"]').forEach(cb => cb.checked = false);
        } else if (removeKey === 'search') {
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.delete('search');
            window.history.pushState(null, '', `${window.location.pathname}?${currentParams.toString()}`);
        }

        triggerFilterImmediate();
    });

    // -------------------------------------------------------------
    // 9. Clear All / Reset Button Handlers
    // -------------------------------------------------------------
    document.addEventListener('click', (e) => {
        const clearBtn = e.target.closest('.shop-clear-all-btn');
        if (!clearBtn) return;

        e.preventDefault();

        // Reset all forms
        document.querySelectorAll('.shop-filter-form').forEach(form => {
            form.reset();
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            form.querySelectorAll('input[name="category"]').forEach(r => r.checked = (r.value === ''));
            form.querySelectorAll('input[type="number"]').forEach(inp => inp.value = '');
        });

        // Reset sliders
        initDualSliders();

        // Reset sort
        if (sortSelect) sortSelect.value = 'featured';

        // Clear query params & reload
        fetchShopResults(new URLSearchParams());
    });

    // -------------------------------------------------------------
    // 10. Browser Back/Forward (Popstate) Sync
    // -------------------------------------------------------------
    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(window.location.search);
        
        // Sync inputs from URL
        document.querySelectorAll('input[name="category"]').forEach(r => {
            r.checked = (r.value === (params.get('category') || ''));
        });

        const metals = params.getAll('metal_type[]');
        document.querySelectorAll('input[name="metal_type[]"]').forEach(cb => {
            cb.checked = metals.includes(cb.value);
        });

        const purities = params.getAll('purity[]');
        document.querySelectorAll('input[name="purity[]"]').forEach(cb => {
            cb.checked = purities.includes(cb.value);
        });

        document.querySelectorAll('input[name="min_price"]').forEach(inp => inp.value = params.get('min_price') || '');
        document.querySelectorAll('input[name="max_price"]').forEach(inp => inp.value = params.get('max_price') || '');
        document.querySelectorAll('input[name="min_weight"]').forEach(inp => inp.value = params.get('min_weight') || '');
        document.querySelectorAll('input[name="max_weight"]').forEach(inp => inp.value = params.get('max_weight') || '');
        document.querySelectorAll('input[name="in_stock"]').forEach(cb => cb.checked = params.has('in_stock'));

        if (sortSelect) {
            sortSelect.value = params.get('sort') || 'featured';
        }

        initDualSliders();
        fetchShopResults(params, false);
    });
});
</script>
@endpush
