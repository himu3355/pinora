@php
    $inSubCategoryMode = !empty($isSubCategoryMode) && isset($sliderCategories) && $sliderCategories->isNotEmpty();
    $displayCategories = $inSubCategoryMode ? $sliderCategories : ($categories ?? collect());
    $isShopRoute = request()->routeIs('shop.*');
    $isAllActive = $isShopRoute && !request()->filled('category');
@endphp

<section class="py-4 bg-white border-b border-[#EAE5DC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between sm:justify-start gap-4 sm:gap-7 overflow-x-auto pb-1 scrollbar-none">
            
            {{-- "Parent / All" Circle: Removes category filter to display all categories, while preserving other active filters --}}
            <a href="{{ $isShopRoute ? request()->fullUrlWithoutQuery(['category', 'page']) : route('shop.index') }}" 
               class="flex flex-col items-center gap-1.5 flex-shrink-0 group" 
               title="View All Categories">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#083B2B]/5 border-2 {{ $isAllActive ? 'border-[#083B2B] ring-2 ring-[#083B2B]/20' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs text-[#083B2B]">
                    <span class="text-xs font-bold">ALL</span>
                </div>
                <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B] whitespace-nowrap {{ $isAllActive ? 'text-[#083B2B] font-bold' : '' }}">
                    {{ $inSubCategoryMode ? 'All' : 'View All' }}
                </span>
            </a>

            {{-- Categories / Sub-categories Circles --}}
            @if($displayCategories->isNotEmpty())
                @foreach($displayCategories as $cat)
                    @php
                        $isActive = request('category') === $cat->slug;
                        // Sub-category Images: If image is not available, display generic gray placeholder image
                        $catImg = $cat->image_url ?: asset('images/product-placeholder.png');
                        $catUrl = $isShopRoute ? request()->fullUrlWithQuery(['category' => $cat->slug, 'page' => null]) : route('shop.index', ['category' => $cat->slug]);
                    @endphp
                    <a href="{{ $catUrl }}" 
                       class="flex flex-col items-center gap-1.5 flex-shrink-0 group"
                       title="{{ $cat->name }}">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#FAF8F5] border-2 {{ $isActive ? 'border-[#083B2B] ring-2 ring-[#083B2B]/20' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs">
                            <img src="{{ $catImg }}" 
                                 alt="{{ $cat->name }}" 
                                 class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform"
                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.png') }}';">
                        </div>
                        <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B] whitespace-nowrap text-center max-w-[85px] sm:max-w-[100px] truncate {{ $isActive ? 'text-[#083B2B] font-bold' : '' }}">
                            {{ $cat->name }}
                        </span>
                    </a>
                @endforeach
            @endif

        </div>
    </div>
</section>
