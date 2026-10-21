@php
    $selectedMetals = array_filter(is_array(request('metal_type')) ? request('metal_type') : (request()->filled('metal_type') ? explode(',', (string)request('metal_type')) : []));
    $selectedPurities = array_filter(is_array(request('purity')) ? request('purity') : (request()->filled('purity') ? explode(',', (string)request('purity')) : []));
    
    $pMinBound = (int)($bounds['min_price'] ?? 0);
    $pMaxBound = (int)($bounds['max_price'] ?? 350000);
    $currMinPrice = request('min_price') !== null ? (int)request('min_price') : $pMinBound;
    $currMaxPrice = request('max_price') !== null ? (int)request('max_price') : $pMaxBound;

    $wMinBound = (float)($bounds['min_weight'] ?? 0.1);
    $wMaxBound = (float)($bounds['max_weight'] ?? 30.0);
    $currMinWeight = request('min_weight') !== null ? (float)request('min_weight') : $wMinBound;
    $currMaxWeight = request('max_weight') !== null ? (float)request('max_weight') : $wMaxBound;
@endphp

{{-- 1. CATEGORIES FILTER --}}
@if(isset($categories) && $categories->isNotEmpty())
<div class="filter-section">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18]">Categories</h4>
    </div>
    <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
        <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer hover:text-[#083B2B] transition-colors">
            <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} class="accent-[#083B2B] filter-input">
            <span class="font-normal">All Categories</span>
        </label>
        @foreach($categories as $cat)
            <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer hover:text-[#083B2B] transition-colors">
                <input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'checked' : '' }} class="accent-[#083B2B] filter-input">
                <span class="font-medium">{{ $cat->name }}</span>
            </label>
            @if($cat->children && $cat->children->isNotEmpty())
                @foreach($cat->children as $sub)
                    <label class="flex items-center gap-2.5 text-xs text-[#60706A] pl-5 cursor-pointer hover:text-[#083B2B] transition-colors">
                        <input type="radio" name="category" value="{{ $sub->slug }}" {{ request('category') === $sub->slug ? 'checked' : '' }} class="accent-[#083B2B] filter-input">
                        <span>{{ $sub->name }}</span>
                    </label>
                @endforeach
            @endif
        @endforeach
    </div>
</div>
@endif

{{-- 2. DYNAMIC PRICE RANGE FILTER --}}
<div class="filter-section pt-5 border-t border-[#EAE5DC]" data-slider-type="price">
    <div class="flex items-center justify-between mb-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18]">Price Range</h4>
        <span class="text-[0.7rem] text-gray-500 font-medium price-range-display">
            ₹{{ number_format($currMinPrice) }} – ₹{{ number_format($currMaxPrice) }}
        </span>
    </div>

    {{-- Dual Range Slider --}}
    <div class="range-slider-wrap relative h-7 flex items-center my-2 select-none" 
         data-min-bound="{{ $pMinBound }}" 
         data-max-bound="{{ $pMaxBound }}"
         data-step="500">
        <div class="range-track-bg absolute w-full h-1.5 bg-[#EAE5DC] rounded-full"></div>
        <div class="range-track-fill absolute h-1.5 bg-[#083B2B] rounded-full" style="left: 0%; right: 0%;"></div>
        <input type="range" 
               class="range-thumb range-thumb-min absolute w-full appearance-none bg-transparent pointer-events-none cursor-pointer" 
               min="{{ $pMinBound }}" 
               max="{{ $pMaxBound }}" 
               step="500" 
               value="{{ $currMinPrice }}">
        <input type="range" 
               class="range-thumb range-thumb-max absolute w-full appearance-none bg-transparent pointer-events-none cursor-pointer" 
               min="{{ $pMinBound }}" 
               max="{{ $pMaxBound }}" 
               step="500" 
               value="{{ $currMaxPrice }}">
    </div>

    {{-- Number Inputs --}}
    <div class="flex items-center gap-2 pt-1 mb-3">
        <div class="relative flex-1">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400">₹</span>
            <input type="number" 
                   name="min_price" 
                   class="filter-min-input filter-input w-full pl-6 pr-2 py-1.5 text-xs border border-[#EAE5DC] rounded-lg focus:outline-none focus:ring-1 focus:ring-[#083B2B] text-[#142E25]"
                   min="{{ $pMinBound }}" 
                   max="{{ $pMaxBound }}"
                   value="{{ request('min_price') ?? '' }}" 
                   placeholder="{{ number_format($pMinBound) }}">
        </div>
        <span class="text-xs text-gray-400 font-medium">to</span>
        <div class="relative flex-1">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400">₹</span>
            <input type="number" 
                   name="max_price" 
                   class="filter-max-input filter-input w-full pl-6 pr-2 py-1.5 text-xs border border-[#EAE5DC] rounded-lg focus:outline-none focus:ring-1 focus:ring-[#083B2B] text-[#142E25]"
                   min="{{ $pMinBound }}" 
                   max="{{ $pMaxBound }}"
                   value="{{ request('max_price') ?? '' }}" 
                   placeholder="{{ number_format($pMaxBound) }}">
        </div>
    </div>

    {{-- Quick Preset Chips --}}
    <div class="flex flex-wrap gap-1.5">
        <button type="button" data-preset-min="0" data-preset-max="10000" class="price-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            Under ₹10k
        </button>
        <button type="button" data-preset-min="10000" data-preset-max="25000" class="price-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            ₹10k–₹25k
        </button>
        <button type="button" data-preset-min="25000" data-preset-max="50000" class="price-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            ₹25k–₹50k
        </button>
        <button type="button" data-preset-min="50000" data-preset-max="100000" class="price-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            ₹50k–₹1L
        </button>
        <button type="button" data-preset-min="100000" data-preset-max="" class="price-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            Above ₹1L
        </button>
    </div>
</div>

{{-- 3. DYNAMIC WEIGHT (GRAMS) FILTER --}}
<div class="filter-section pt-5 border-t border-[#EAE5DC]" data-slider-type="weight">
    <div class="flex items-center justify-between mb-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18]">Weight (Grams)</h4>
        <span class="text-[0.7rem] text-gray-500 font-medium weight-range-display">
            {{ $currMinWeight }}g – {{ $currMaxWeight }}g
        </span>
    </div>

    {{-- Dual Range Slider for Weight --}}
    <div class="range-slider-wrap relative h-7 flex items-center my-2 select-none" 
         data-min-bound="{{ $wMinBound }}" 
         data-max-bound="{{ $wMaxBound }}"
         data-step="0.1">
        <div class="range-track-bg absolute w-full h-1.5 bg-[#EAE5DC] rounded-full"></div>
        <div class="range-track-fill absolute h-1.5 bg-[#083B2B] rounded-full" style="left: 0%; right: 0%;"></div>
        <input type="range" 
               class="range-thumb range-thumb-min absolute w-full appearance-none bg-transparent pointer-events-none cursor-pointer" 
               min="{{ $wMinBound }}" 
               max="{{ $wMaxBound }}" 
               step="0.1" 
               value="{{ $currMinWeight }}">
        <input type="range" 
               class="range-thumb range-thumb-max absolute w-full appearance-none bg-transparent pointer-events-none cursor-pointer" 
               min="{{ $wMinBound }}" 
               max="{{ $wMaxBound }}" 
               step="0.1" 
               value="{{ $currMaxWeight }}">
    </div>

    {{-- Number Inputs --}}
    <div class="flex items-center gap-2 pt-1 mb-3">
        <div class="relative flex-1">
            <input type="number" 
                   step="0.1"
                   name="min_weight" 
                   class="filter-min-input filter-input w-full px-2.5 py-1.5 text-xs border border-[#EAE5DC] rounded-lg focus:outline-none focus:ring-1 focus:ring-[#083B2B] text-[#142E25]"
                   min="{{ $wMinBound }}" 
                   max="{{ $wMaxBound }}"
                   value="{{ request('min_weight') ?? '' }}" 
                   placeholder="{{ $wMinBound }}g">
        </div>
        <span class="text-xs text-gray-400 font-medium">to</span>
        <div class="relative flex-1">
            <input type="number" 
                   step="0.1"
                   name="max_weight" 
                   class="filter-max-input filter-input w-full px-2.5 py-1.5 text-xs border border-[#EAE5DC] rounded-lg focus:outline-none focus:ring-1 focus:ring-[#083B2B] text-[#142E25]"
                   min="{{ $wMinBound }}" 
                   max="{{ $wMaxBound }}"
                   value="{{ request('max_weight') ?? '' }}" 
                   placeholder="{{ $wMaxBound }}g">
        </div>
    </div>

    {{-- Quick Preset Chips for Weight --}}
    <div class="flex flex-wrap gap-1.5">
        <button type="button" data-preset-min="0" data-preset-max="1" class="weight-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            &lt; 1g
        </button>
        <button type="button" data-preset-min="1" data-preset-max="2" class="weight-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            1g–2g
        </button>
        <button type="button" data-preset-min="2" data-preset-max="4" class="weight-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            2g–4g
        </button>
        <button type="button" data-preset-min="4" data-preset-max="8" class="weight-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            4g–8g
        </button>
        <button type="button" data-preset-min="8" data-preset-max="" class="weight-chip text-[0.7rem] px-2.5 py-1 rounded-md border border-[#EAE5DC] hover:border-[#083B2B] text-[#142E25] bg-gray-50/70 hover:bg-[#083B2B]/5 cursor-pointer transition-all">
            &gt; 8g
        </button>
    </div>
</div>

{{-- 4. METAL TYPE (Multi-Select Checkboxes) --}}
<div class="filter-section pt-5 border-t border-[#EAE5DC]">
    <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18] mb-3">Metal Type</h4>
    <div class="space-y-2">
        @foreach(['gold' => 'Gold', 'silver' => 'Silver', 'platinum' => 'Platinum'] as $metalKey => $metalLabel)
            <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer hover:text-[#083B2B]">
                <input type="checkbox" 
                       name="metal_type[]" 
                       value="{{ $metalKey }}" 
                       {{ in_array($metalKey, $selectedMetals) ? 'checked' : '' }} 
                       class="accent-[#083B2B] rounded filter-input">
                <span>{{ $metalLabel }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- 5. METAL PURITY (Multi-Select Checkboxes) --}}
<div class="filter-section pt-5 border-t border-[#EAE5DC]">
    <h4 class="text-xs font-bold uppercase tracking-wider text-[#A37F18] mb-3">Purity</h4>
    <div class="space-y-2">
        @foreach(['22K' => '22K Gold (916 BIS)', '18K' => '18K Gold (750)', '14K' => '14K Gold', '925' => '925 Sterling Silver'] as $purKey => $purLabel)
            <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer hover:text-[#083B2B]">
                <input type="checkbox" 
                       name="purity[]" 
                       value="{{ $purKey }}" 
                       {{ in_array($purKey, $selectedPurities) ? 'checked' : '' }} 
                       class="accent-[#083B2B] rounded filter-input">
                <span>{{ $purLabel }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- 6. IN STOCK ONLY --}}
<div class="filter-section pt-5 border-t border-[#EAE5DC]">
    <label class="flex items-center gap-2.5 text-xs sm:text-sm text-[#142E25] cursor-pointer hover:text-[#083B2B]">
        <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} class="accent-[#083B2B] rounded filter-input">
        <span class="font-medium">In Stock Only</span>
    </label>
</div>
