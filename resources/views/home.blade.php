@extends('layouts.app')

@section('title', 'Pinora — Rajkot\'s Nose Pin Specialist | Elegant 22K Gold Nose Pins')
@section('meta_description', 'Discover certified 22K gold nose pins handcrafted by master artisans in Rajkot. Daily wear, floral designs, studded solitaires, and heritage bridal pins.')

@section('content')

{{-- ======================================================== --}}
{{-- 1. MAIN CATEGORIES CIRCLE STORIES SLIDER (Dynamic from DB)--}}
{{-- ======================================================== --}}
@include('partials.category-circle-slider', ['categories' => $categories ?? $featuredCategories])

{{-- ======================================================== --}}
{{-- ======================================================== --}}
{{-- 2. HERO BANNER / SLIDER (Dynamic from DB)                 --}}
{{-- ======================================================== --}}
@php
    $hasSliders = isset($sliders) && $sliders->isNotEmpty();
    $slideCount = $hasSliders ? $sliders->count() : 1;
@endphp

<section class="relative bg-[#062E23] overflow-hidden text-white" 
         x-data="{ currentSlide: 0, total: {{ $slideCount }}, interval: null }"
         x-init="if(total > 1) { interval = setInterval(() => { currentSlide = (currentSlide + 1) % total }, 6000) }">
    {{-- Ambient bokeh background glow --}}
    <div class="absolute inset-0 pointer-events-none opacity-25">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#C59B27] blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-80 h-80 rounded-full bg-[#0D4E3A] blur-2xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        @if($hasSliders)
            @foreach($sliders as $idx => $slide)
                <div x-show="currentSlide === {{ $idx }}" 
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="grid grid-cols-1 lg:grid-cols-12 items-center min-h-[460px] md:min-h-[520px] py-8 lg:py-0 gap-6">

                    {{-- Left Content Area --}}
                    <div class="lg:col-span-6 z-10 text-center lg:text-left pt-4 lg:pt-0">
                        <div class="inline-block mb-3">
                            <span class="font-primary text-2xl md:text-3xl font-bold tracking-[0.2em] text-[#FAF8F5] uppercase block">
                                {{ app_setting('site_name', 'Pinora') }}
                            </span>
                            <span class="text-[0.68rem] md:text-xs tracking-[0.3em] uppercase text-[#E0C475] font-semibold block -mt-1">
                                — {{ app_setting('navbar_tagline', 'TINY JEWEL. BIG STYLE.') }} —
                            </span>
                        </div>

                        <div class="my-4">
                            @if($slide->badge_text)
                                <span class="block text-xs md:text-sm uppercase tracking-[0.25em] text-[#C59B27] font-bold mb-1">
                                    {{ $slide->badge_text }}
                                </span>
                            @endif
                            <h1 class="font-primary text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-medium leading-[1.15] text-[#FAF8F5]">
                                {{ $slide->title }}
                            </h1>
                        </div>

                        @if($slide->subtitle)
                            <p class="text-sm md:text-base text-[#D8D1C5] max-w-md mx-auto lg:mx-0 mb-6 leading-relaxed">
                                {{ $slide->subtitle }}
                            </p>
                        @endif

                        @if($slide->button_text)
                            <div class="flex justify-center lg:justify-start items-center gap-4">
                                <a href="{{ $slide->button_url ?: route('shop.index') }}" 
                                   class="inline-flex items-center gap-2.5 px-6 py-3 rounded-lg border border-[#C59B27] bg-[#083B2B] hover:bg-[#0A4A36] text-[#E0C475] hover:text-white font-medium text-xs md:text-sm tracking-wider uppercase transition-all duration-300 shadow-md group">
                                    <span>{{ $slide->button_text }}</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </a>
                            </div>
                        @endif

                        {{-- Indicators if multiple --}}
                        @if($slideCount > 1)
                            <div class="flex items-center justify-center lg:justify-start gap-1.5 mt-8">
                                @for($i = 0; $i < $slideCount; $i++)
                                    <button type="button" 
                                            @click="currentSlide = {{ $i }}" 
                                            :class="currentSlide === {{ $i }} ? 'w-6 bg-[#C59B27]' : 'w-2 bg-white/40 hover:bg-white/70'"
                                            class="h-1.5 rounded-full transition-all cursor-pointer" 
                                            aria-label="Go to slide {{ $i + 1 }}"></button>
                                @endfor
                            </div>
                        @endif
                    </div>

                    {{-- Right Image Area --}}
                    <div class="lg:col-span-6 relative flex justify-center lg:justify-end items-end h-full">
                        <div class="relative w-full max-w-[380px] sm:max-w-[420px] lg:max-w-[460px] aspect-[4/5] overflow-hidden rounded-2xl border border-[#0A4A36]/60 shadow-[0_20px_50px_rgba(0,0,0,0.4)]">
                            <img src="{{ $slide->desktop_image_url }}" 
                                 alt="{{ $slide->title }}" 
                                 class="w-full h-full object-cover object-center">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-[#062E23]/80 via-transparent to-transparent pointer-events-none"></div>

                            @if($slide->badge_pill_text)
                                <div class="absolute bottom-3 left-3 bg-[#062E23]/90 backdrop-blur-md border border-[#C59B27]/40 px-3 py-1.5 rounded-lg flex items-center gap-2 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#C59B27]" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/>
                                    </svg>
                                    <span class="text-[0.7rem] font-semibold tracking-wide">{{ $slide->badge_pill_text }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        @else
            {{-- Default Fallback Slide --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 items-center min-h-[460px] md:min-h-[520px] py-8 lg:py-0 gap-6">
                <div class="lg:col-span-6 z-10 text-center lg:text-left pt-4 lg:pt-0">
                    <div class="inline-block mb-3">
                        <span class="font-primary text-2xl md:text-3xl font-bold tracking-[0.2em] text-[#FAF8F5] uppercase block">
                            {{ app_setting('site_name', 'Pinora') }}
                        </span>
                        <span class="text-[0.68rem] md:text-xs tracking-[0.3em] uppercase text-[#E0C475] font-semibold block -mt-1">
                            — {{ app_setting('navbar_tagline', 'TINY JEWEL. BIG STYLE.') }} —
                        </span>
                    </div>

                    <div class="my-4">
                        <span class="block text-xs md:text-sm uppercase tracking-[0.25em] text-[#C59B27] font-bold mb-1">
                            {{ app_setting('specialist_title', "RAJKOT'S") }}
                        </span>
                        <h1 class="font-primary text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-medium leading-[1.15] text-[#FAF8F5]">
                            NOSE PIN <span class="italic text-[#E0C475] font-semibold">SPECIALIST</span>
                        </h1>
                    </div>

                    <p class="text-sm md:text-base text-[#D8D1C5] max-w-md mx-auto lg:mx-0 mb-6 leading-relaxed">
                        Elegant 22K Gold Nose Pins for Every Occasion.
                    </p>

                    <div class="flex justify-center lg:justify-start items-center gap-4">
                        <a href="{{ route('shop.index') }}" 
                           class="inline-flex items-center gap-2.5 px-6 py-3 rounded-lg border border-[#C59B27] bg-[#083B2B] hover:bg-[#0A4A36] text-[#E0C475] hover:text-white font-medium text-xs md:text-sm tracking-wider uppercase transition-all duration-300 shadow-md group">
                            <span>SHOP NOSE PINS</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-6 relative flex justify-center lg:justify-end items-end h-full">
                    <div class="relative w-full max-w-[380px] sm:max-w-[420px] lg:max-w-[460px] aspect-[4/5] overflow-hidden rounded-2xl border border-[#0A4A36]/60 shadow-[0_20px_50px_rgba(0,0,0,0.4)]">
                        <img src="{{ asset('images/pinora/hero-banner.jpg') }}" 
                             alt="Pinora 22K Gold Nose Pin" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#062E23]/80 via-transparent to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-3 left-3 bg-[#062E23]/90 backdrop-blur-md border border-[#C59B27]/40 px-3 py-1.5 rounded-lg flex items-center gap-2 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#C59B27]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/></svg>
                            <span class="text-[0.7rem] font-semibold tracking-wide">22K BIS Hallmarked</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- ======================================================== --}}
{{-- 3. BILINGUAL VALUE PROPOSITIONS (Dynamic from DB)         --}}
{{-- ======================================================== --}}
<section class="py-8 md:py-12 bg-[#FAF8F5]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            @if(isset($valueProps) && $valueProps->isNotEmpty())
                @foreach($valueProps as $prop)
                    <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                        <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                            @switch($prop->icon)
                                @case('hallmark')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#C59B27]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/></svg>
                                    @break
                                @case('shipping')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.5a1.125 1.125 0 011.125 1.125v4.5H14.25v-5.625zM3 14.25V6.75A2.25 2.25 0 015.25 4.5h9a2.25 2.25 0 012.25 2.25v7.5" /></svg>
                                    @break
                                @case('exchange')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                    @break
                                @case('craftsman')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456Z" /></svg>
                                    @break
                                @default
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                            @endswitch
                        </div>
                        <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">{{ $prop->title }}</h3>
                        @if($prop->subtitle)
                            <p class="text-[0.72rem] text-[#60706A]">{{ $prop->subtitle }}</p>
                        @endif
                    </div>
                @endforeach
            @else
                {{-- Fallback Cards --}}
                <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                    <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">22K Gold</h3>
                    <p class="text-[0.72rem] text-[#60706A]">શુદ્ધ સોનાની ખાતરી</p>
                </div>
                <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                    <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/></svg>
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">BIS Hallmarked</h3>
                    <p class="text-[0.72rem] text-[#60706A]">હોલમાર્ક પ્રમાણિત</p>
                </div>
                <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                    <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#C59B27]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456Z" /></svg>
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">Master Craft</h3>
                    <p class="text-[0.72rem] text-[#60706A]">રાજકોટના કુશળ કારીગરો</p>
                </div>
                <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                    <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.5a1.125 1.125 0 011.125 1.125v4.5H14.25v-5.625zM3 14.25V6.75A2.25 2.25 0 015.25 4.5h9a2.25 2.25 0 012.25 2.25v7.5" /></svg>
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">Insured Shipping</h3>
                    <p class="text-[0.72rem] text-[#60706A]">સુરક્ષિત ડિલિવરી સમગ્ર ભારત</p>
                </div>
                <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                    <div class="w-10 h-10 mb-2.5 rounded-full bg-[#083B2B]/5 flex items-center justify-center text-[#083B2B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">Easy 7-Day Exchange</h3>
                    <p class="text-[0.72rem] text-[#60706A]">સરળ ૭-દિવસ એક્સચેન્જ</p>
                </div>
                <div class="bg-white border border-[#EAE5DC] rounded-xl p-4 sm:p-5 text-center flex flex-col items-center justify-center hover:border-[#083B2B] hover:shadow-xs transition-all">
                    <div class="w-10 h-10 mb-2.5 rounded-full bg-[#25D366]/10 flex items-center justify-center text-[#25D366]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-[#142E25] mb-0.5">WhatsApp Support</h3>
                    <p class="text-[0.72rem] text-[#60706A]">ઝડપી ગ્રાહક સહાય</p>
                </div>
            @endif
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
