{{-- ======================================================== --}}
{{-- MAIN CATEGORIES CIRCLE STORIES SLIDER (Dynamic from DB)  --}}
{{-- ======================================================== --}}
<section class="py-4 bg-white border-b border-[#EAE5DC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between sm:justify-start gap-4 sm:gap-7 overflow-x-auto pb-1 scrollbar-none">
            
            @if(isset($categories) && $categories->isNotEmpty())
                @foreach($categories as $cat)
                    @php
                        $isActive = request('category') === $cat->slug;
                    @endphp
                    <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" 
                       class="flex flex-col items-center gap-1.5 flex-shrink-0 group"
                       title="{{ $cat->name }}">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#FAF8F5] border-2 {{ $isActive ? 'border-[#083B2B] ring-2 ring-[#083B2B]/20' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs">
                            @if(!empty($cat->image_url))
                                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform">
                            @else
                                <div class="w-full h-full rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B] group-hover:scale-110 transition-transform">
                                    @switch($cat->icon)
                                        @case('heroicon-o-star')
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385c.116.488-.415.874-.836.612l-4.717-2.94a.563.563 0 0 0-.586 0l-4.717 2.94c-.42.262-.952-.124-.836-.612l1.285-5.385a.563.563 0 0 0-.182-.557l-4.204-3.602c-.38-.325-.178-.948.32-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                                            @break
                                        @case('heroicon-o-sparkles')
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                                            @break
                                        @case('heroicon-o-moon')
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                                            @break
                                        @case('heroicon-o-cube')
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                            @break
                                        @case('heroicon-o-shopping-bag')
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                            @break
                                        @case('heroicon-o-circle-stack')
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                                            @break
                                        @default
                                            <span class="text-xs font-bold tracking-wider text-[#083B2B]">{{ strtoupper(substr($cat->name, 0, 2)) }}</span>
                                    @endswitch
                                </div>
                            @endif
                        </div>
                        <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B] whitespace-nowrap text-center max-w-[85px] sm:max-w-[100px] truncate {{ $isActive ? 'text-[#083B2B] font-bold' : '' }}">
                            {{ $cat->name }}
                        </span>
                    </a>
                @endforeach
            @endif

            {{-- View All Circle --}}
            @php
                $isAllActive = request()->routeIs('shop.index') && !request('category');
            @endphp
            <a href="{{ route('shop.index') }}" class="flex flex-col items-center gap-1.5 flex-shrink-0 group" title="View All Jewellery">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-[#083B2B]/5 border-2 {{ $isAllActive ? 'border-[#083B2B] ring-2 ring-[#083B2B]/20' : 'border-[#EAE5DC]' }} group-hover:border-[#C59B27] transition-all flex items-center justify-center overflow-hidden shadow-xs text-[#083B2B]">
                    <span class="text-xs font-bold">ALL</span>
                </div>
                <span class="text-[0.72rem] sm:text-xs font-medium text-[#142E25] group-hover:text-[#083B2B] whitespace-nowrap {{ $isAllActive ? 'text-[#083B2B] font-bold' : '' }}">View All</span>
            </a>

        </div>
    </div>
</section>
