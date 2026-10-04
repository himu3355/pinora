@extends('layouts.app')

@section('title', 'Pinora — Rajkot\'s Nose Pin Specialist | Elegant 22K Gold Nose Pins')
@section('meta_description', 'Discover certified 22K gold nose pins handcrafted by master artisans in Rajkot. Daily wear, floral designs, studded solitaires, and heritage bridal pins.')

@section('content')

{{-- ======================================================== --}}
{{-- 1. CATEGORY STORY CARDS SLIDER (Matching Image 3)        --}}
{{-- ======================================================== --}}
<section class="py-4 md:py-6 bg-white border-b border-[#EAE5DC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none scroll-smooth">
            
            {{-- Category 1: Daily Wear --}}
            <a href="{{ route('shop.index', ['category' => 'daily-wear']) }}" 
               class="flex-shrink-0 flex items-center gap-3 bg-[#FAF8F5] hover:bg-[#F3EFE6] border border-[#EAE5DC] hover:border-[#C59B27] rounded-xl px-3.5 py-2.5 transition-all duration-200 group">
                <div class="w-11 h-11 rounded-lg bg-white overflow-hidden border border-[#EAE5DC] flex-shrink-0 p-1 flex items-center justify-center">
                    <img src="{{ asset('images/pinora/cat-daily-wear.jpg') }}" alt="Daily Wear Nose Pins" class="w-full h-full object-cover rounded-md group-hover:scale-110 transition-transform">
                </div>
                <div class="flex items-center gap-1.5 pr-1">
                    <span class="text-xs sm:text-sm font-semibold text-[#142E25] whitespace-nowrap">Daily Wear</span>
                    <span class="text-xs text-[#083B2B] group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            {{-- Category 2: Floral Designs --}}
            <a href="{{ route('shop.index', ['category' => 'floral']) }}" 
               class="flex-shrink-0 flex items-center gap-3 bg-[#FAF8F5] hover:bg-[#F3EFE6] border border-[#EAE5DC] hover:border-[#C59B27] rounded-xl px-3.5 py-2.5 transition-all duration-200 group">
                <div class="w-11 h-11 rounded-lg bg-white overflow-hidden border border-[#EAE5DC] flex-shrink-0 p-1 flex items-center justify-center">
                    <img src="{{ asset('images/pinora/cat-floral.jpg') }}" alt="Floral Nose Pins" class="w-full h-full object-cover rounded-md group-hover:scale-110 transition-transform">
                </div>
                <div class="flex items-center gap-1.5 pr-1">
                    <span class="text-xs sm:text-sm font-semibold text-[#142E25] whitespace-nowrap">Floral Designs</span>
                    <span class="text-xs text-[#083B2B] group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            {{-- Category 3: Studded --}}
            <a href="{{ route('shop.index', ['category' => 'studded']) }}" 
               class="flex-shrink-0 flex items-center gap-3 bg-[#FAF8F5] hover:bg-[#F3EFE6] border border-[#EAE5DC] hover:border-[#C59B27] rounded-xl px-3.5 py-2.5 transition-all duration-200 group">
                <div class="w-11 h-11 rounded-lg bg-white overflow-hidden border border-[#EAE5DC] flex-shrink-0 p-1 flex items-center justify-center">
                    <img src="{{ asset('images/pinora/cat-studded.jpg') }}" alt="Studded Nose Pins" class="w-full h-full object-cover rounded-md group-hover:scale-110 transition-transform">
                </div>
                <div class="flex items-center gap-1.5 pr-1">
                    <span class="text-xs sm:text-sm font-semibold text-[#142E25] whitespace-nowrap">Studded</span>
                    <span class="text-xs text-[#083B2B] group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            {{-- Category 4: Premium Heritage --}}
            <a href="{{ route('shop.index', ['category' => 'premium']) }}" 
               class="flex-shrink-0 flex items-center gap-3 bg-[#FAF8F5] hover:bg-[#F3EFE6] border border-[#EAE5DC] hover:border-[#C59B27] rounded-xl px-3.5 py-2.5 transition-all duration-200 group">
                <div class="w-11 h-11 rounded-lg bg-white overflow-hidden border border-[#EAE5DC] flex-shrink-0 p-1 flex items-center justify-center">
                    <img src="{{ asset('images/pinora/cat-premium.jpg') }}" alt="Premium Nose Pins" class="w-full h-full object-cover rounded-md group-hover:scale-110 transition-transform">
                </div>
                <div class="flex items-center gap-1.5 pr-1">
                    <span class="text-xs sm:text-sm font-semibold text-[#142E25] whitespace-nowrap">Premium</span>
                    <span class="text-xs text-[#083B2B] group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            {{-- Explore All Pill --}}
            <a href="{{ route('shop.index') }}" 
               class="flex-shrink-0 flex items-center gap-2 bg-[#083B2B] text-white hover:bg-[#062E23] rounded-xl px-4 py-3 text-xs sm:text-sm font-semibold transition-all">
                <span>View All (48+)</span>
                <span>&rarr;</span>
            </a>

        </div>
    </div>
</section>

{{-- ======================================================== --}}
{{-- 2. HERO BANNER: RAJKOT'S NOSE PIN SPECIALIST (Image 3)   --}}
{{-- ======================================================== --}}
<section class="relative bg-[#062E23] overflow-hidden text-white">
    {{-- Ambient bokeh background glow --}}
    <div class="absolute inset-0 pointer-events-none opacity-25">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#C59B27] blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-80 h-80 rounded-full bg-[#0D4E3A] blur-2xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 items-center min-h-[460px] md:min-h-[520px] py-8 lg:py-0 gap-6">

            {{-- Left Content Area --}}
            <div class="lg:col-span-6 z-10 text-center lg:text-left pt-4 lg:pt-0">
                
                {{-- Brand Header Subtitle --}}
                <div class="inline-block mb-3">
                    <span class="font-primary text-2xl md:text-3xl font-bold tracking-[0.2em] text-[#FAF8F5] uppercase block">
                        Pinora
                    </span>
                    <span class="text-[0.68rem] md:text-xs tracking-[0.3em] uppercase text-[#E0C475] font-semibold block -mt-1">
                        — TINY JEWEL. BIG STYLE —
                    </span>
                </div>

                {{-- Specialist Title --}}
                <div class="my-4">
                    <span class="block text-xs md:text-sm uppercase tracking-[0.25em] text-[#C59B27] font-bold mb-1">
                        RAJKOT'S
                    </span>
                    <h1 class="font-primary text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-medium leading-[1.15] text-[#FAF8F5]">
                        NOSE PIN <span class="italic text-[#E0C475] font-semibold">SPECIALIST</span>
                    </h1>
                </div>

                {{-- Tagline --}}
                <p class="text-sm md:text-base text-[#D8D1C5] max-w-md mx-auto lg:mx-0 mb-6 leading-relaxed">
                    Elegant 22K Gold Nose Pins for Every Occasion.
                </p>

                {{-- CTA Button --}}
                <div class="flex justify-center lg:justify-start items-center gap-4">
                    <a href="{{ route('shop.index') }}" 
                       class="inline-flex items-center gap-2.5 px-6 py-3 rounded-lg border border-[#C59B27] bg-[#083B2B] hover:bg-[#0A4A36] text-[#E0C475] hover:text-white font-medium text-xs md:text-sm tracking-wider uppercase transition-all duration-300 shadow-md group">
                        <span>SHOP NOSE PINS</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                </div>

                {{-- Carousel Indicator Dots (Image 3) --}}
                <div class="flex items-center justify-center lg:justify-start gap-1.5 mt-8">
                    <span class="w-6 h-1.5 rounded-full bg-[#C59B27]"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                </div>

            </div>

            {{-- Right Model Image (Responsive on Mobile and Desktop) --}}
            <div class="lg:col-span-6 relative flex justify-center lg:justify-end items-end h-full">
                <div class="relative w-full max-w-[380px] sm:max-w-[420px] lg:max-w-[460px] aspect-[4/5] overflow-hidden rounded-2xl border border-[#0A4A36]/60 shadow-[0_20px_50px_rgba(0,0,0,0.4)]">
                    <img src="{{ asset('images/pinora/hero-banner.jpg') }}" 
                         alt="Indian model wearing Pinora 22K Gold Nose Pin" 
                         class="w-full h-full object-cover object-center">
                    
                    {{-- Soft gradient overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-[#062E23]/80 via-transparent to-transparent pointer-events-none"></div>

                    {{-- Floating Hallmark Badge Overlay --}}
                    <div class="absolute bottom-3 left-3 bg-[#062E23]/90 backdrop-blur-md border border-[#C59B27]/40 px-3 py-1.5 rounded-lg flex items-center gap-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#C59B27]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/>
                        </svg>
                        <span class="text-[0.7rem] font-semibold tracking-wide">22K BIS Hallmarked</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ======================================================== --}}
{{-- 3. BILINGUAL VALUE PROPOSITIONS (Image 3: 6 CARDS GRID)   --}}
{{-- ======================================================== --}}
<section class="py-8 md:py-12 bg-[#FAF8F5]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            
            {{-- Card 1: 22K Gold --}}
            <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                    {{-- Gold bar icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </div>
                <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">22K Gold</h3>
                <p class="text-[0.72rem] text-[#60706A]">શુદ્ધ સોનાની ખાતરી</p>
            </div>

            {{-- Card 2: BIS Hallmarked --}}
            <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                    {{-- Hallmark triangle symbol --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">BIS Hallmarked</h3>
                <p class="text-[0.72rem] text-[#60706A]">હોલમાર્ક પ્રમાણિત</p>
            </div>

            {{-- Card 3: Secure Payment --}}
            <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                    {{-- Shield with check --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">Secure Payment</h3>
                <p class="text-[0.72rem] text-[#60706A]">સુરક્ષિત પેમેન્ટ</p>
            </div>

            {{-- Card 4: Safe Delivery --}}
            <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                    {{-- Delivery truck --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.5a1.125 1.125 0 011.125 1.125v4.5H14.25v-5.625zM3 14.25V6.75A2.25 2.25 0 015.25 4.5h9a2.25 2.25 0 012.25 2.25v7.5" />
                    </svg>
                </div>
                <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">Safe Delivery</h3>
                <p class="text-[0.72rem] text-[#60706A]">સુરક્ષિત ડિલિવરી</p>
            </div>

            {{-- Card 5: Easy Exchange --}}
            <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                    {{-- Return / exchange arrows --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </div>
                <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">Easy Exchange*</h3>
                <p class="text-[0.72rem] text-[#60706A]">સરળ એક્સચેન્જ*</p>
            </div>

            {{-- Card 6: WhatsApp Support --}}
            <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                <div class="w-10 h-10 mb-2.5 rounded-full bg-[#25D366]/10 flex items-center justify-center text-[#25D366]">
                    {{-- WhatsApp chat --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">WhatsApp Support</h3>
                <p class="text-[0.72rem] text-[#60706A]">ઝડપી ગ્રાહક સહાય</p>
            </div>

        </div>

    </div>
</section>

{{-- ======================================================== --}}
{{-- 4. FEATURED NOSE PINS SECTION (Matching Image 1 cards)    --}}
{{-- ======================================================== --}}
<section class="py-8 md:py-14 bg-white border-t border-b border-[#EAE5DC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        {{-- Section Header --}}
        <div class="flex items-end justify-between mb-8 pb-4 border-b border-[#EAE5DC]">
            <div>
                <span class="text-xs uppercase tracking-widest text-[#C59B27] font-bold block mb-1">Handcrafted in 22K Gold</span>
                <h2 class="font-primary text-2xl sm:text-3xl md:text-4xl text-[#142E25] font-semibold">Featured Nose Pins</h2>
            </div>
            <a href="{{ route('shop.index') }}" class="text-xs sm:text-sm font-semibold text-[#083B2B] hover:text-[#C59B27] flex items-center gap-1">
                <span>View All 48 Designs</span>
                <span>&rarr;</span>
            </a>
        </div>

        {{-- 2 Columns on Mobile, 3 on Tablet, 4 on Desktop --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @forelse($featuredProducts as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <div class="col-span-full py-12 text-center text-gray-500">
                    <p class="text-sm">No nose pins found in catalog.</p>
                </div>
            @endforelse
        </div>

    </div>
</section>

{{-- ======================================================== --}}
{{-- 5. RAJKOT ARTISAN CRAFT STORY BANNER                     --}}
{{-- ======================================================== --}}
<section class="py-12 md:py-16 bg-[#083B2B] text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <span class="text-xs uppercase tracking-widest text-[#E0C475] font-bold block mb-2">The Legacy of Rajkot Goldsmiths</span>
                <h2 class="font-primary text-3xl sm:text-4xl font-normal leading-tight text-[#FAF8F5] mb-4">
                    Precision Crafting for <span class="text-[#E0C475] italic">Tiny Masterpieces</span>
                </h2>
                <p class="text-sm text-[#D8D1C5] leading-relaxed mb-6">
                    A nose pin isn't just an accessory — it's an emblem of grace and personal identity. At Pinora, every single piece is handcrafted by master artisans in Rajkot with pure 22K gold, pristine gemstones, and tamper-proof BIS hallmarking.
                </p>
                <div class="flex flex-wrap gap-4 items-center">
                    <a href="{{ route('shop.index') }}" class="btn btn-gold text-xs sm:text-sm px-6 py-3 font-semibold uppercase">Explore Catalog</a>
                    <a href="https://wa.me/919999999999?text=Hi%20Pinora" target="_blank" class="btn btn-outline-gold text-xs sm:text-sm px-6 py-3">Talk to Artisan</a>
                </div>
            </div>
            <div class="flex justify-center md:justify-end">
                <div class="bg-white/5 border border-white/15 p-6 rounded-2xl max-w-md w-full backdrop-blur-xs">
                    <h3 class="font-primary text-xl text-[#E0C475] font-semibold mb-3">Why Trust Pinora?</h3>
                    <ul class="space-y-3 text-xs sm:text-sm text-[#FAF8F5]/90">
                        <li class="flex items-start gap-2.5">
                            <span class="text-[#C59B27] text-base">✓</span>
                            <span><strong>100% 22K BIS Hallmarked:</strong> Every pin carries authentic laser-etched HUID certification.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-[#C59B27] text-base">✓</span>
                            <span><strong>Comfort Screw & Wire Fitting:</strong> Tailored specifically for comfortable all-day wear.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-[#C59B27] text-base">✓</span>
                            <span><strong>Transparent Pricing:</strong> Clear breakdown of gold rate, weight, making charges & GST.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
