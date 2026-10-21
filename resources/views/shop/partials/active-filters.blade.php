@php
    $selectedMetals = array_filter(is_array(request('metal_type')) ? request('metal_type') : (request()->filled('metal_type') ? explode(',', (string)request('metal_type')) : []));
    $selectedPurities = array_filter(is_array(request('purity')) ? request('purity') : (request()->filled('purity') ? explode(',', (string)request('purity')) : []));
    $hasPriceFilter = request()->filled('price_range') || request()->filled('min_price') || request()->filled('max_price');
    $hasWeightFilter = request()->filled('weight_range') || request()->filled('min_weight') || request()->filled('max_weight');

    $activeFilterCount = 0;
    if (request()->filled('category')) $activeFilterCount++;
    $activeFilterCount += count($selectedMetals);
    $activeFilterCount += count($selectedPurities);
    if ($hasPriceFilter) $activeFilterCount++;
    if ($hasWeightFilter) $activeFilterCount++;
    if (request()->boolean('in_stock')) $activeFilterCount++;
    if (request()->filled('search')) $activeFilterCount++;
    if (request()->filled('vendor_id')) $activeFilterCount++;
@endphp

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
                    <a href="{{ request()->fullUrlWithoutQuery(['category', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-key="category" aria-label="Remove category filter">&times;</a>
                </span>
            @endif

            {{-- Metal Type Pills (Multi-select) --}}
            @foreach($selectedMetals as $metal)
                @php
                    $newMetals = array_values(array_diff($selectedMetals, [$metal]));
                    $metalParams = request()->query();
                    unset($metalParams['page']);
                    if (empty($newMetals)) {
                        unset($metalParams['metal_type']);
                    } else {
                        $metalParams['metal_type'] = $newMetals;
                    }
                @endphp
                <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                    <span>Metal: {{ ucfirst($metal) }}</span>
                    <a href="{{ route('shop.index', $metalParams) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-metal="{{ $metal }}" aria-label="Remove {{ $metal }} filter">&times;</a>
                </span>
            @endforeach

            {{-- Purity Pills (Multi-select) --}}
            @foreach($selectedPurities as $pur)
                @php
                    $newPurities = array_values(array_diff($selectedPurities, [$pur]));
                    $purityParams = request()->query();
                    unset($purityParams['page']);
                    if (empty($newPurities)) {
                        unset($purityParams['purity']);
                    } else {
                        $purityParams['purity'] = $newPurities;
                    }
                @endphp
                <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                    <span>Purity: {{ $pur }}</span>
                    <a href="{{ route('shop.index', $purityParams) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-purity="{{ $pur }}" aria-label="Remove {{ $pur }} filter">&times;</a>
                </span>
            @endforeach

            {{-- Price Filter Pill --}}
            @if($hasPriceFilter)
                @php
                    $priceParams = request()->query();
                    unset($priceParams['page'], $priceParams['price_range'], $priceParams['min_price'], $priceParams['max_price']);

                    $priceLabel = 'Price Filter';
                    if (request('price_range') === 'under_5000') {
                        $priceLabel = 'Price: Under ₹5,000';
                    } elseif (request('price_range') === '5000_10000') {
                        $priceLabel = 'Price: ₹5,000 – ₹10,000';
                    } elseif (request('price_range') === 'under_10000') {
                        $priceLabel = 'Price: Under ₹10,000';
                    } elseif (request('price_range') === '10000_25000') {
                        $priceLabel = 'Price: ₹10,000 – ₹25,000';
                    } elseif (request('price_range') === '25000_50000') {
                        $priceLabel = 'Price: ₹25,000 – ₹50,000';
                    } elseif (request('price_range') === '50000_100000') {
                        $priceLabel = 'Price: ₹50,000 – ₹1,00,000';
                    } elseif (request('price_range') === 'above_50000') {
                        $priceLabel = 'Price: Above ₹50,000';
                    } elseif (request('price_range') === 'above_100000') {
                        $priceLabel = 'Price: Above ₹1,00,000';
                    } elseif (request()->filled('min_price') && request()->filled('max_price')) {
                        $priceLabel = 'Price: ₹' . number_format((float)request('min_price')) . ' – ₹' . number_format((float)request('max_price'));
                    } elseif (request()->filled('min_price')) {
                        $priceLabel = 'Price: Above ₹' . number_format((float)request('min_price'));
                    } elseif (request()->filled('max_price')) {
                        $priceLabel = 'Price: Up to ₹' . number_format((float)request('max_price'));
                    }
                @endphp
                <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                    <span>{{ $priceLabel }}</span>
                    <a href="{{ route('shop.index', $priceParams) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-key="price" aria-label="Remove price filter">&times;</a>
                </span>
            @endif

            {{-- Weight Filter Pill --}}
            @if($hasWeightFilter)
                @php
                    $weightParams = request()->query();
                    unset($weightParams['page'], $weightParams['weight_range'], $weightParams['min_weight'], $weightParams['max_weight']);

                    $weightLabel = 'Weight Filter';
                    if (request('weight_range') === 'under_1g') {
                        $weightLabel = 'Weight: Under 1g';
                    } elseif (request('weight_range') === '1g_2g') {
                        $weightLabel = 'Weight: 1g – 2g';
                    } elseif (request('weight_range') === '2g_4g') {
                        $weightLabel = 'Weight: 2g – 4g';
                    } elseif (request('weight_range') === '2g_5g') {
                        $weightLabel = 'Weight: 2g – 5g';
                    } elseif (request('weight_range') === '4g_8g') {
                        $weightLabel = 'Weight: 4g – 8g';
                    } elseif (request('weight_range') === 'above_4g') {
                        $weightLabel = 'Weight: Above 4g';
                    } elseif (request('weight_range') === 'above_8g') {
                        $weightLabel = 'Weight: Above 8g';
                    } elseif (request()->filled('min_weight') && request()->filled('max_weight')) {
                        $weightLabel = 'Weight: ' . request('min_weight') . 'g – ' . request('max_weight') . 'g';
                    } elseif (request()->filled('min_weight')) {
                        $weightLabel = 'Weight: Above ' . request('min_weight') . 'g';
                    } elseif (request()->filled('max_weight')) {
                        $weightLabel = 'Weight: Up to ' . request('max_weight') . 'g';
                    }
                @endphp
                <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                    <span>{{ $weightLabel }}</span>
                    <a href="{{ route('shop.index', $weightParams) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-key="weight" aria-label="Remove weight filter">&times;</a>
                </span>
            @endif

            {{-- In Stock Pill --}}
            @if(request()->boolean('in_stock'))
                <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                    <span>In Stock Only</span>
                    <a href="{{ request()->fullUrlWithoutQuery(['in_stock', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-key="in_stock" aria-label="Remove stock filter">&times;</a>
                </span>
            @endif

            {{-- Search Pill --}}
            @if(request()->filled('search'))
                <span class="inline-flex items-center gap-1.5 bg-[#EAE5DC]/70 border border-[#D8D1C5] text-[#142E25] px-3 py-1 rounded-full text-xs font-medium">
                    <span>Search: "{{ request('search') }}"</span>
                    <a href="{{ request()->fullUrlWithoutQuery(['search', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-key="search" aria-label="Remove search filter">&times;</a>
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
                        <a href="{{ request()->fullUrlWithoutQuery(['vendor_id', 'page']) }}" class="hover:text-red-600 font-bold ml-0.5 text-sm filter-pill-remove" data-remove-key="vendor_id" aria-label="Remove vendor filter">&times;</a>
                    </span>
                @endif
            @endif

            <button type="button" class="text-xs text-[#083B2B] hover:underline font-semibold ml-1 cursor-pointer shop-clear-all-btn">
                Clear All
            </button>
        @endif
    </div>

    {{-- Dynamic Products Count --}}
    <span id="shop-products-count" class="text-xs sm:text-sm text-[#60706A] font-medium">
        {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }}
    </span>
</div>
