@extends('layouts.app')

@section('title', $product->name . ' — 22K Gold Nose Pin | Pinora')
@section('meta_description', $product->short_description ?? 'Shop ' . $product->name . ' at Pinora. Pure 22K BIS Hallmarked gold nose pin handcrafted by Rajkot specialist artisans.')
@section('navbar_tagline', 'PURE GOLD. YOU.')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 pb-28 md:pb-16">

    {{-- Breadcrumb (Image 2) --}}
    <nav class="text-[0.75rem] text-[#8E9E98] mb-4 md:mb-6 flex items-center gap-1.5 flex-wrap">
        <a href="{{ url('/') }}" class="hover:text-[#083B2B]">Home</a>
        <span>/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-[#083B2B]">Nose Pins</a>
        <span>/</span>
        <span class="text-[#083B2B] font-semibold">{{ $product->name }}</span>
    </nav>

    {{-- Main Product Layout (2 Columns on Desktop, Stacked on Mobile) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-start">

        @php
            $galleryImages = [];
            if (isset($product->images) && $product->images->isNotEmpty()) {
                foreach ($product->images as $img) {
                    $galleryImages[] = [
                        'url' => $img->url,
                        'alt' => $img->alt ?? $product->name,
                    ];
                }
            } else {
                $galleryImages[] = [
                    'url' => asset('images/pinora/pdp-main-1.jpg'),
                    'alt' => $product->name,
                ];
            }

            $certBadges = $product->certification_badges;
            if (empty($certBadges)) {
                $certBadges = [
                    'bis_hallmark'        => \App\Models\Product::AVAILABLE_CERTIFICATIONS['bis_hallmark'],
                    'certified_jewellery' => \App\Models\Product::AVAILABLE_CERTIFICATIONS['certified_jewellery'],
                    'lifetime_exchange'   => \App\Models\Product::AVAILABLE_CERTIFICATIONS['lifetime_exchange'],
                ];
            }
        @endphp

        {{-- ======================================================== --}}
        {{-- LEFT COLUMN: PRODUCT GALLERY (Matching Image 2)          --}}
        {{-- ======================================================== --}}
        <div class="lg:col-span-7">
            
            {{-- Main Image Frame with Navigation Arrows & Slide Counter --}}
            <div class="relative bg-white border border-[#EAE5DC] rounded-2xl overflow-hidden aspect-square shadow-xs group">
                <img id="main-product-img" 
                     src="{{ $galleryImages[0]['url'] }}" 
                     alt="{{ $galleryImages[0]['alt'] }}" 
                     class="w-full h-full object-cover transition-transform duration-300">

                @if(count($galleryImages) > 1)
                {{-- Left Carousel Arrow --}}
                <button type="button" 
                        onclick="prevPdpImage()" 
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-gray-700 shadow-md flex items-center justify-center transition-all cursor-pointer" 
                        aria-label="Previous image">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                </button>

                {{-- Right Carousel Arrow --}}
                <button type="button" 
                        onclick="nextPdpImage()" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-gray-700 shadow-md flex items-center justify-center transition-all cursor-pointer" 
                        aria-label="Next image">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </button>

                {{-- Slide Indicator Badge (e.g. 1/4) --}}
                <div class="absolute bottom-3 right-3 bg-black/60 backdrop-blur-xs text-white text-[0.7rem] font-semibold px-2.5 py-1 rounded-full shadow-sm" id="pdp-counter-badge">
                    1/{{ count($galleryImages) }}
                </div>
                @endif
            </div>

            @if(count($galleryImages) > 1)
            {{-- Thumbnail Images Below (Matching Image 2) --}}
            <div class="grid grid-cols-4 gap-2.5 sm:gap-3 mt-3.5">
                @foreach($galleryImages as $gIdx => $gImg)
                <button type="button" 
                        onclick="setPdpImage({{ $gIdx }}, '{{ $gImg['url'] }}')" 
                        class="pdp-thumb-btn aspect-square rounded-xl overflow-hidden border-2 {{ $gIdx === 0 ? 'border-[#083B2B]' : 'border-[#EAE5DC] hover:border-[#083B2B]' }} bg-white cursor-pointer transition-all p-0.5 shadow-xs" 
                        data-index="{{ $gIdx }}">
                    <img src="{{ $gImg['url'] }}" alt="{{ $gImg['alt'] }}" class="w-full h-full object-cover rounded-lg">
                </button>
                @endforeach
            </div>
            @endif

        </div>

        {{-- ======================================================== --}}
        {{-- RIGHT COLUMN: PRODUCT DETAILS & BUY ACTIONS (Image 2)     --}}
        {{-- ======================================================== --}}
        <div class="lg:col-span-5 flex flex-col justify-start">
            
            {{-- Bestseller Pill Badge --}}
            <div class="mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-widest uppercase bg-[#F3EFE6] text-[#A37F18] border border-[#E0C475]">
                    BESTSELLER
                </span>
            </div>

            {{-- Title --}}
            <h1 class="font-primary text-2xl sm:text-3xl md:text-4xl text-[#142E25] font-normal leading-tight mb-2">
                {{ $product->name }}
            </h1>

            {{-- Rating Stars & Count --}}
            <div class="flex items-center gap-2 mb-3">
                <div class="flex text-[#C59B27] text-sm">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <span class="text-xs sm:text-sm font-semibold text-[#142E25]">4.8</span>
                <span class="text-xs text-[#8E9E98]">(126 reviews)</span>
            </div>

            {{-- Short Description --}}
            @if(!empty($product->short_description))
            <p class="text-xs sm:text-sm text-[#60706A] leading-relaxed mb-3">
                {{ $product->short_description }}
            </p>
            @endif

            {{-- Price Display --}}
            @php
                $finalPrice = $pricing['final_price'] ?? ($product->base_price > 0 ? $product->base_price : 8450);
            @endphp
            <div class="mb-3">
                <div class="text-2xl sm:text-3xl font-bold text-[#142E25]" id="product-price-display">
                    ₹{{ number_format($finalPrice, 0) }}
                </div>
                <p class="text-[0.75rem] text-[#60706A]">Inclusive of all taxes</p>
            </div>

            {{-- Dynamic BIS Hallmarked / Certified Badge --}}
            <div class="inline-flex items-center gap-2 mb-4 text-[#083B2B] text-xs font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#083B2B]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/>
                </svg>
                <span>
                    @if($product->metal_type === 'gold')
                        {{ $product->purity }} BIS Hallmarked Gold
                    @elseif($product->metal_type === 'silver')
                        {{ $product->purity }} Certified Sterling Silver
                    @else
                        100% Certified {{ ucfirst($product->metal_type ?? 'Jewellery') }}
                    @endif
                </span>
            </div>

            {{-- Delivery Checker Box --}}
            <div class="bg-white border border-[#EAE5DC] rounded-xl p-3 sm:p-4 mb-5 shadow-xs">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="text-[#083B2B] flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.5a1.125 1.125 0 011.125 1.125v4.5H14.25v-5.625zM3 14.25V6.75A2.25 2.25 0 015.25 4.5h9a2.25 2.25 0 012.25 2.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-[#142E25]" id="pincode-est-date">Delivery by {{ date('d M', strtotime('+3 days')) }}</span>
                            <span class="block text-[0.7rem] text-[#8E9E98]">Enter pincode to check delivery date</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <input type="text" 
                               id="pincode-input" 
                               maxlength="6" 
                               placeholder="Enter Pincode" 
                               class="w-24 sm:w-28 bg-[#FAF8F5] border border-[#EAE5DC] rounded-lg px-2.5 py-1.5 text-xs text-[#142E25] focus:outline-none focus:ring-1 focus:ring-[#083B2B]">
                        <button type="button" 
                                onclick="checkPincodeDelivery()" 
                                class="bg-[#083B2B] hover:bg-[#062E23] text-white px-3 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-colors">
                            Check
                        </button>
                    </div>
                </div>
                <div id="pincode-feedback" class="text-[0.72rem] text-emerald-700 font-semibold mt-1.5 hidden">
                    ✓ Available for express delivery with free shipping.
                </div>
            </div>

            {{-- Certified Metal & Purity Details --}}
            <div class="mb-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-[#142E25]">Metal & Certified Purity</label>
                    <span class="text-[0.7rem] text-emerald-700 font-semibold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        Guaranteed Authentic
                    </span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold border-2 border-[#083B2B] bg-[#083B2B]/5 text-[#083B2B]">
                        <span class="w-2 h-2 rounded-full bg-[#C59B27]"></span>
                        <span>{{ $product->purity }} {{ ucfirst($product->metal_type) }}</span>
                        @if($product->metal_type === 'gold' && $product->purity === '24K')
                            <span class="text-[0.7rem] font-normal text-[#60706A]">(99.9% Pure Gold)</span>
                        @elseif($product->metal_type === 'gold' && $product->purity === '22K')
                            <span class="text-[0.7rem] font-normal text-[#60706A]">(91.6% Pure Gold)</span>
                        @elseif($product->metal_type === 'gold' && $product->purity === '18K')
                            <span class="text-[0.7rem] font-normal text-[#60706A]">(75.0% Gold)</span>
                        @elseif($product->metal_type === 'gold' && $product->purity === '14K')
                            <span class="text-[0.7rem] font-normal text-[#60706A]">(58.5% Gold)</span>
                        @elseif($product->metal_type === 'silver' && $product->purity === '925')
                            <span class="text-[0.7rem] font-normal text-[#60706A]">(92.5% Sterling Silver)</span>
                        @elseif($product->metal_type === 'silver' && $product->purity === '999')
                            <span class="text-[0.7rem] font-normal text-[#60706A]">(99.9% Pure Silver)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Dynamic Variant / Size Selector (Only when product has real variants) --}}
            @if($variants->isNotEmpty())
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-[#142E25]">
                        @if(stripos($product->category?->name ?? '', 'ring') !== false || stripos($product->name, 'ring') !== false)
                            Choose Ring Size
                        @elseif(stripos($product->category?->name ?? '', 'bangle') !== false || stripos($product->name, 'bangle') !== false)
                            Choose Bangle Size
                        @else
                            Available Sizes / Options
                        @endif
                    </label>
                    <span id="variant-stock-hint" class="text-[0.7rem]">
                        @if($selectedVariant && $selectedVariant->stock_quantity > 0)
                            <span class="text-emerald-700 font-medium">✓ In Stock</span>
                        @else
                            <span class="text-red-500 font-semibold">Out of Stock</span>
                        @endif
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    @foreach($variants as $variant)
                        @php
                            $isSelected = $selectedVariant && $selectedVariant->id === $variant->id;
                            $inStock = $variant->stock_quantity > 0;
                        @endphp
                        <button type="button" 
                                onclick="selectProductVariant({{ $variant->id }})" 
                                data-variant-id="{{ $variant->id }}"
                                class="variant-btn px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-1.5 {{ $isSelected ? 'border-2 border-[#083B2B] bg-[#083B2B]/5 text-[#083B2B] font-semibold shadow-xs' : 'border border-[#EAE5DC] bg-white text-[#60706A] font-medium hover:border-[#083B2B]' }} {{ !$inStock ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <span>{{ $variant->name }}</span>
                            @if($variant->weight_grams)
                                <span class="text-[0.7rem] opacity-75">({{ number_format((float) $variant->weight_grams, 2) }}g)</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Desktop Buy Box Action Buttons (Hidden on mobile sticky, visible on md+) --}}
            @php
                $currentStock = $selectedVariant ? $selectedVariant->stock_quantity : $product->stock_quantity;
                $isOutOfStock = $currentStock <= 0;
            @endphp
            <div class="hidden md:flex flex-col gap-3 mb-8">
                <form action="{{ route('cart.add') }}" method="POST" id="desktop-add-cart-form" class="grid grid-cols-2 gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" id="desktop-variant-id" value="{{ $selectedVariant?->id ?? '' }}">
                    <input type="hidden" name="quantity" value="1">
                    
                    {{-- Add to Cart --}}
                    <button type="submit" id="desktop-add-btn" {{ $isOutOfStock ? 'disabled' : '' }} class="flex items-center justify-center gap-2 py-3.5 px-4 bg-[#083B2B] hover:bg-[#062E23] text-white rounded-xl text-sm font-semibold transition-all shadow-md cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                        <span id="desktop-add-btn-text">{{ $isOutOfStock ? 'Out of Stock' : 'Add to Cart' }}</span>
                    </button>

                    {{-- Buy Now --}}
                    <button type="button" onclick="triggerBuyNow('desktop-add-cart-form')" id="desktop-buy-btn" {{ $isOutOfStock ? 'disabled' : '' }} class="flex items-center justify-center gap-2 py-3.5 px-4 bg-[#E0C475] hover:bg-[#C59B27] text-[#083B2B] font-bold rounded-xl text-sm transition-all shadow-md cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 01.359.852L12.982 9.75h7.268a.75.75 0 01.548 1.262l-10.5 11.25a.75.75 0 01-1.272-.71l1.992-7.302H3.75a.75.75 0 01-.548-1.262l10.5-11.25a.75.75 0 01.913-.143z" clip-rule="evenodd" /></svg>
                        <span>Buy Now</span>
                    </button>
                </form>

                {{-- Wishlist button desktop --}}
                <button type="button" 
                        class="w-full py-2.5 px-4 border border-[#EAE5DC] hover:border-[#083B2B] text-xs font-semibold text-[#142E25] rounded-xl flex items-center justify-center gap-2 transition-colors cursor-pointer"
                        data-wishlist-toggle="{{ $product->id }}">
                    <span>♡</span>
                    <span>Save to Wishlist</span>
                </button>
            </div>

            {{-- ==================================================== --}}
            {{-- COLLAPSIBLE ACCORDIONS (Matching Image 2)            --}}
            {{-- ==================================================== --}}
            <div class="space-y-2 border-t border-[#EAE5DC] pt-4">
                
                {{-- Accordion 1: Description --}}
                <div class="border border-[#EAE5DC] rounded-xl bg-white overflow-hidden">
                    <button type="button" 
                            onclick="toggleAccordion('acc-description')" 
                            class="w-full flex items-center justify-between p-3.5 sm:p-4 text-left cursor-pointer hover:bg-[#FAF8F5] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 text-[#083B2B]">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                            </svg>
                            <span class="text-xs sm:text-sm font-bold text-[#142E25]">Description</span>
                        </div>
                        <svg id="icon-acc-description" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div id="content-acc-description" class="p-4 pt-1 border-t border-[#EAE5DC] text-xs text-[#60706A] leading-relaxed hidden">
                        @if(!empty($product->description))
                            <div class="prose prose-sm max-w-none text-[#60706A] prose-p:my-1.5 prose-headings:text-[#142E25]">
                                {!! $product->description !!}
                            </div>
                        @elseif(!empty($product->short_description))
                            <p>{{ $product->short_description }}</p>
                        @else
                            <p>Handcrafted fine jewellery by Pinora master artisans, celebrating authentic craftsmanship and certified precious metals.</p>
                        @endif
                    </div>
                </div>

                {{-- Accordion 2: Product Details --}}
                <div class="border border-[#EAE5DC] rounded-xl bg-white overflow-hidden">
                    <button type="button" 
                            onclick="toggleAccordion('acc-details')" 
                            class="w-full flex items-center justify-between p-3.5 sm:p-4 text-left cursor-pointer hover:bg-[#FAF8F5] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 text-[#083B2B]">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span class="text-xs sm:text-sm font-bold text-[#142E25]">Product Details</span>
                        </div>
                        <svg id="icon-acc-details" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div id="content-acc-details" class="p-4 pt-1 border-t border-[#EAE5DC] text-xs text-[#60706A] leading-relaxed hidden">
                        <table class="w-full text-xs">
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Metal & Purity</td>
                                <td class="py-1.5 text-right font-semibold text-[#142E25]">{{ ucfirst($product->metal_type) }} {{ $product->purity }}</td>
                            </tr>
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Gross Weight</td>
                                <td class="py-1.5 text-right font-semibold text-[#142E25]" id="details-gross-weight">{{ number_format((float) ($selectedVariant?->weight_grams ?? $product->weight_grams ?? 0), 3) }} g</td>
                            </tr>
                            @if ((float) ($product->loss ?? 0) > 0)
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-red-500">Less Weight (Loss)</td>
                                <td class="py-1.5 text-right font-semibold text-red-500">− {{ number_format((float) $product->loss, 3) }} g</td>
                            </tr>
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-emerald-700">Net Weight</td>
                                <td class="py-1.5 text-right font-semibold text-emerald-700" id="details-net-weight">{{ number_format(max(0, (float)($selectedVariant?->weight_grams ?? $product->weight_grams) - (float)$product->loss), 3) }} g</td>
                            </tr>
                            @endif
                            @if(!empty($product->stone_type))
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Stone</td>
                                <td class="py-1.5 text-right font-semibold text-[#142E25]">
                                    {{ $product->stone_type }}
                                    @if($product->stone_weight_carats) · {{ $product->stone_weight_carats }} ct @endif
                                    @if($product->stone_quality) · {{ $product->stone_quality }} @endif
                                </td>
                            </tr>
                            @endif
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Pricing Type</td>
                                <td class="py-1.5 text-right font-semibold text-[#142E25]">
                                    @php
                                        $typeMap = [
                                            'jewellery'  => 'Jewellery (Weight × Rate + Labour + GST)',
                                            'coin_bar'   => 'Coin & Bar (Floor Rounding)',
                                            'mrp_fixed'  => 'Direct MRP (Fixed Price)',
                                            'mrp_linked' => 'Rate Linked MRP (Batch Cost ÷ Pieces)',
                                        ];
                                    @endphp
                                    {{ $typeMap[$product->product_type] ?? 'Standard Jewellery' }}
                                </td>
                            </tr>
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Origin / Artisan</td>
                                <td class="py-1.5 text-right font-semibold text-[#142E25]">{{ $product->vendor->store_name ?? 'Pinora Certified' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Accordion 3: Certification & Authenticity --}}
                <div class="border border-[#EAE5DC] rounded-xl bg-white overflow-hidden">
                    <button type="button" 
                            onclick="toggleAccordion('acc-certification')" 
                            class="w-full flex items-center justify-between p-3.5 sm:p-4 text-left cursor-pointer hover:bg-[#FAF8F5] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 text-[#083B2B]">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                            <span class="text-xs sm:text-sm font-bold text-[#142E25]">Certification & Authenticity</span>
                        </div>
                        <svg id="icon-acc-certification" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div id="content-acc-certification" class="p-4 pt-1 border-t border-[#EAE5DC] text-xs text-[#60706A] leading-relaxed hidden">
                        <table class="w-full text-xs mb-3">
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Hallmarking Standard</td>
                                <td class="py-1.5 text-right font-semibold text-[#142E25]">
                                    @if($product->metal_type === 'gold')
                                        {{ $product->purity }} BIS Hallmarked Gold
                                    @elseif($product->metal_type === 'silver')
                                        {{ $product->purity }} Certified Sterling Silver
                                    @else
                                        100% Certified Authentic
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Certification Type</td>
                                <td class="py-1.5 text-right font-semibold text-[#142E25]">
                                    @if($product->certification_type && $product->certification_type !== 'none')
                                        {{ strtoupper($product->certification_type) }} Certified
                                    @elseif($product->metal_type === 'gold')
                                        BIS Hallmark Certified
                                    @else
                                        Pinora Authenticity Certificate
                                    @endif
                                </td>
                            </tr>
                            @if($product->certification_number)
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Certificate ID</td>
                                <td class="py-1.5 text-right font-semibold text-[#083B2B] font-mono tracking-wider">{{ $product->certification_number }}</td>
                            </tr>
                            @endif
                            @if($product->certification_file)
                            <tr class="border-b border-[#FAF8F5]">
                                <td class="py-1.5 font-medium text-gray-500">Certificate Document</td>
                                <td class="py-1.5 text-right font-semibold">
                                    <a href="{{ asset('storage/' . $product->certification_file) }}" target="_blank" class="text-[#083B2B] hover:underline inline-flex items-center gap-1 font-semibold">
                                        <span>View Document</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </td>
                            </tr>
                            @endif
                        </table>

                        {{-- Certification Badges --}}
                        <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-[#FAF8F5]">
                            @foreach($certBadges as $badge)
                            <div class="flex items-center gap-2 p-2 rounded-lg bg-[#FAF8F5] border border-[#EAE5DC]">
                                @if(file_exists(public_path($badge['logo'])))
                                    <img src="{{ asset($badge['logo']) }}" alt="{{ $badge['label'] }}" class="w-6 h-6 object-contain shrink-0">
                                @else
                                    <span class="text-sm">🏅</span>
                                @endif
                                <span class="text-[0.7rem] font-semibold text-[#142E25] leading-tight">{{ $badge['label'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Accordion 4: Shipping & Returns --}}
                <div class="border border-[#EAE5DC] rounded-xl bg-white overflow-hidden">
                    <button type="button" 
                            onclick="toggleAccordion('acc-shipping')" 
                            class="w-full flex items-center justify-between p-3.5 sm:p-4 text-left cursor-pointer hover:bg-[#FAF8F5] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 text-[#083B2B]">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                            <span class="text-xs sm:text-sm font-bold text-[#142E25]">Shipping & Returns</span>
                        </div>
                        <svg id="icon-acc-shipping" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div id="content-acc-shipping" class="p-4 pt-1 border-t border-[#EAE5DC] text-xs text-[#60706A] leading-relaxed hidden">
                        <ul class="space-y-1.5 list-disc pl-4">
                            <li>100% Free insured delivery via BlueDart / Sequel across India.</li>
                            <li>Dispatch within 24 to 48 hours in tamper-evident security vault packaging.</li>
                            <li>7-day easy exchange guarantee on unworn pieces with certificate.</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>

    </div>

    {{-- ======================================================== --}}
    {{-- PRODUCT DESCRIPTION & CERTIFICATION SECTION              --}}
    {{-- ======================================================== --}}
    <div class="mt-14 pt-10 border-t border-[#EAE5DC]">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            {{-- Left: Full Story & Description --}}
            <div class="lg:col-span-7 bg-white border border-[#EAE5DC] rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 mb-5 pb-3 border-b border-[#FAF8F5]">
                    <div class="w-9 h-9 rounded-full bg-[#083B2B]/10 flex items-center justify-center text-[#083B2B]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-primary text-xl sm:text-2xl font-bold text-[#142E25]">The Product Story</h2>
                        <span class="text-xs text-[#8E9E98]">Artisanal craftsmanship & design details</span>
                    </div>
                </div>

                @if(!empty($product->description))
                    <div class="prose prose-sm sm:prose max-w-none text-[#60706A] leading-relaxed prose-headings:text-[#142E25] prose-strong:text-[#142E25]">
                        {!! $product->description !!}
                    </div>
                @else
                    <p class="text-xs sm:text-sm text-[#60706A] leading-relaxed mb-4">
                        {{ $product->short_description ?? 'Designed and crafted with perfection by Pinora master craftsmen. Each piece represents our dedication to timeless beauty, ethical sourcing, and uncompromising gold purity.' }}
                    </p>
                @endif
            </div>

            {{-- Right: Certification & Quality Promise --}}
            <div class="lg:col-span-5 bg-[#FAF8F5] border border-[#EAE5DC] rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 mb-5 pb-3 border-b border-[#EAE5DC]">
                    <div class="w-9 h-9 rounded-full bg-[#E0C475]/30 flex items-center justify-center text-[#A37F18]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-primary text-xl font-bold text-[#142E25]">Certified Authenticity</h3>
                        <span class="text-xs text-[#8E9E98]">Guaranteed Hallmarked Jewellery</span>
                    </div>
                </div>

                <p class="text-xs sm:text-sm text-[#60706A] mb-5 leading-relaxed">
                    Every piece created at Pinora is accompanied by official hallmark certifications verifying precious metal purity and gemstone genuineness.
                </p>

                {{-- Badges Stack --}}
                <div class="space-y-3 mb-5">
                    @foreach($certBadges as $badge)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-[#EAE5DC]">
                        @if(file_exists(public_path($badge['logo'])))
                            <img src="{{ asset($badge['logo']) }}" alt="{{ $badge['label'] }}" class="w-8 h-8 object-contain shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-full bg-[#083B2B]/10 flex items-center justify-center text-[#083B2B] text-base shrink-0">🏅</div>
                        @endif
                        <div>
                            <span class="block text-xs font-bold text-[#142E25]">{{ $badge['label'] }}</span>
                            <span class="block text-[0.7rem] text-[#8E9E98]">Guaranteed genuine by Pinora</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($product->certification_number)
                <div class="p-3 bg-white border border-[#EAE5DC] rounded-xl flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">Certificate ID</span>
                    <span class="font-bold text-[#083B2B] font-mono tracking-wider">{{ $product->certification_number }}</span>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- RELATED PRODUCTS: YOU MAY ALSO LIKE                      --}}
    {{-- ======================================================== --}}
    <div class="mt-14 pt-10 border-t border-[#EAE5DC]">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-primary text-2xl sm:text-3xl font-semibold text-[#142E25]">You May Also Like</h2>
            <a href="{{ route('shop.index') }}" class="text-xs sm:text-sm font-semibold text-[#083B2B] hover:underline">
                Explore All &rarr;
            </a>
        </div>

        @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                @foreach($relatedProducts as $relProduct)
                    @include('partials.product-card', ['product' => $relProduct])
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-sm text-[#8E9E98] bg-white border border-[#EAE5DC] rounded-2xl">
                Explore our full <a href="{{ route('shop.index') }}" class="text-[#083B2B] font-semibold underline">Nose Pin Collection</a> to discover more designs.
            </div>
        @endif
    </div>

</div>

{{-- ============================================================ --}}
{{-- STICKY BOTTOM ACTION BAR (MOBILE VIEW ONLY - Matching Image 2)--}}
{{-- ============================================================ --}}
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-[#EAE5DC] px-3.5 py-2.5 flex items-center justify-between gap-3 shadow-[0_-4px_24px_rgba(0,0,0,0.1)] md:hidden">
    
    {{-- Price Info Left --}}
    <div class="flex-shrink-0">
        <div id="mobile-price-display" class="text-base sm:text-lg font-bold text-[#142E25] leading-tight">
            ₹{{ number_format($pricing['final_price'], 0) }}
        </div>
        <span class="text-[0.62rem] text-[#8E9E98] block">Inclusive of all taxes</span>
    </div>

    {{-- Buttons Right --}}
    <div class="flex items-center gap-2 flex-1 justify-end">
        {{-- Add to Cart --}}
        <form action="{{ route('cart.add') }}" method="POST" id="mobile-add-cart-form" class="m-0">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variant_id" id="mobile-variant-id" value="{{ $selectedVariant?->id ?? '' }}">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" id="mobile-add-btn" {{ $isOutOfStock ? 'disabled' : '' }} class="flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-lg bg-[#083B2B] text-white text-xs font-semibold cursor-pointer shadow-xs whitespace-nowrap active:scale-95 transition-transform disabled:opacity-50 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                <span id="mobile-add-btn-text">{{ $isOutOfStock ? 'Out of Stock' : 'Add to Cart' }}</span>
            </button>
        </form>

        {{-- Buy Now --}}
        <button type="button" onclick="triggerBuyNow('mobile-add-cart-form')" id="mobile-buy-btn" {{ $isOutOfStock ? 'disabled' : '' }} class="flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-lg bg-[#E0C475] hover:bg-[#C59B27] text-[#083B2B] text-xs font-bold cursor-pointer shadow-xs whitespace-nowrap active:scale-95 transition-transform disabled:opacity-50 disabled:cursor-not-allowed">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 01.359.852L12.982 9.75h7.268a.75.75 0 01.548 1.262l-10.5 11.25a.75.75 0 01-1.272-.71l1.992-7.302H3.75a.75.75 0 01-.548-1.262l10.5-11.25a.75.75 0 01.913-.143z" clip-rule="evenodd" /></svg>
            <span>Buy Now</span>
        </button>
    </div>

</div>

@endsection

@push('scripts')
<script>
const pdpImages = @json(array_column($galleryImages, 'url'));
const variantsData = @json($variantData);
let currentPdpIdx = 0;

function setPdpImage(idx, src) {
    if (!pdpImages || pdpImages.length === 0) return;
    currentPdpIdx = idx;
    const mainImg = document.getElementById('main-product-img');
    if (mainImg) mainImg.src = src;
    const badge = document.getElementById('pdp-counter-badge');
    if (badge) badge.textContent = (currentPdpIdx + 1) + '/' + pdpImages.length;
    
    document.querySelectorAll('.pdp-thumb-btn').forEach(btn => {
        if (parseInt(btn.dataset.index) === idx) {
            btn.classList.add('border-[#083B2B]');
            btn.classList.remove('border-[#EAE5DC]');
        } else {
            btn.classList.remove('border-[#083B2B]');
            btn.classList.add('border-[#EAE5DC]');
        }
    });
}

function nextPdpImage() {
    if (!pdpImages || pdpImages.length <= 1) return;
    currentPdpIdx = (currentPdpIdx + 1) % pdpImages.length;
    setPdpImage(currentPdpIdx, pdpImages[currentPdpIdx]);
}

function prevPdpImage() {
    if (!pdpImages || pdpImages.length <= 1) return;
    currentPdpIdx = (currentPdpIdx - 1 + pdpImages.length) % pdpImages.length;
    setPdpImage(currentPdpIdx, pdpImages[currentPdpIdx]);
}

function selectProductVariant(variantId) {
    const data = variantsData[variantId];
    if (!data) return;

    // 1. Highlight selected button
    document.querySelectorAll('.variant-btn').forEach(btn => {
        const id = parseInt(btn.getAttribute('data-variant-id'));
        if (id === variantId) {
            btn.classList.remove('border-[#EAE5DC]', 'bg-white', 'text-[#60706A]', 'font-medium');
            btn.classList.add('border-2', 'border-[#083B2B]', 'bg-[#083B2B]/5', 'text-[#083B2B]', 'font-semibold', 'shadow-xs');
        } else {
            btn.classList.remove('border-2', 'border-[#083B2B]', 'bg-[#083B2B]/5', 'text-[#083B2B]', 'font-semibold', 'shadow-xs');
            btn.classList.add('border', 'border-[#EAE5DC]', 'bg-white', 'text-[#60706A]', 'font-medium');
        }
    });

    // 2. Update hidden form inputs
    const desktopVarInput = document.getElementById('desktop-variant-id');
    if (desktopVarInput) desktopVarInput.value = variantId;

    const mobileVarInput = document.getElementById('mobile-variant-id');
    if (mobileVarInput) mobileVarInput.value = variantId;

    // 3. Update price display
    const priceDisplay = document.getElementById('product-price-display');
    if (priceDisplay && data.pricing) {
        priceDisplay.innerHTML = '₹' + data.pricing.formatted_final_price;
    }

    const mobilePriceDisplay = document.getElementById('mobile-price-display');
    if (mobilePriceDisplay && data.pricing) {
        mobilePriceDisplay.innerHTML = '₹' + data.pricing.formatted_final_price;
    }

    // 4. Update stock hint and button availability
    const stockHint = document.getElementById('variant-stock-hint');
    const desktopAddBtn = document.getElementById('desktop-add-btn');
    const desktopAddBtnText = document.getElementById('desktop-add-btn-text');
    const desktopBuyBtn = document.getElementById('desktop-buy-btn');
    const mobileAddBtn = document.getElementById('mobile-add-btn');
    const mobileAddBtnText = document.getElementById('mobile-add-btn-text');
    const mobileBuyBtn = document.getElementById('mobile-buy-btn');

    if (data.in_stock) {
        if (stockHint) stockHint.innerHTML = '<span class="text-emerald-700 font-medium">✓ In Stock</span>';
        if (desktopAddBtn) desktopAddBtn.disabled = false;
        if (desktopAddBtnText) desktopAddBtnText.textContent = 'Add to Cart';
        if (desktopBuyBtn) desktopBuyBtn.disabled = false;
        if (mobileAddBtn) mobileAddBtn.disabled = false;
        if (mobileAddBtnText) mobileAddBtnText.textContent = 'Add to Cart';
        if (mobileBuyBtn) mobileBuyBtn.disabled = false;
    } else {
        if (stockHint) stockHint.innerHTML = '<span class="text-red-500 font-semibold">Out of Stock</span>';
        if (desktopAddBtn) desktopAddBtn.disabled = true;
        if (desktopAddBtnText) desktopAddBtnText.textContent = 'Out of Stock';
        if (desktopBuyBtn) desktopBuyBtn.disabled = true;
        if (mobileAddBtn) mobileAddBtn.disabled = true;
        if (mobileAddBtnText) mobileAddBtnText.textContent = 'Out of Stock';
        if (mobileBuyBtn) mobileBuyBtn.disabled = true;
    }

    // 5. Update accordion weights
    const grossWeight = document.getElementById('details-gross-weight');
    if (grossWeight) grossWeight.textContent = data.formatted_gross_weight;

    const netWeight = document.getElementById('details-net-weight');
    if (netWeight) netWeight.textContent = data.formatted_net_weight;

    // 6. Update price breakup table if present
    if (data.pricing) {
        const metalCost = document.getElementById('breakup-metal-cost');
        if (metalCost) metalCost.textContent = '₹' + data.pricing.formatted_metal_cost;

        const makingCharges = document.getElementById('breakup-making-charges');
        if (makingCharges) makingCharges.textContent = '₹' + data.pricing.formatted_making_charges;

        const gst = document.getElementById('breakup-gst');
        if (gst) gst.textContent = '₹' + data.pricing.formatted_gst_amount;

        const finalAmount = document.getElementById('breakup-final-amount');
        if (finalAmount) finalAmount.textContent = '₹' + data.pricing.formatted_final_price;
    }
}

async function triggerBuyNow(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const formData = new FormData(form);
    
    // Add loading indicator to button
    const btn = form.querySelector('button[type="button"]') || event?.currentTarget;
    if (btn) {
        btn.classList.add('opacity-75', 'cursor-wait');
    }

    try {
        const response = await fetch("{{ route('cart.add') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (result && result.success) {
            window.location.href = "{{ route('checkout.index') }}";
        } else {
            window.location.href = "{{ route('checkout.index') }}";
        }
    } catch (e) {
        // Fallback: standard submit
        form.submit();
    }
}

function checkPincodeDelivery() {
    const input = document.getElementById('pincode-input');
    const feedback = document.getElementById('pincode-feedback');
    if (!input || !input.value.trim() || input.value.trim().length < 6) {
        alert('Please enter a valid 6-digit postal pincode.');
        return;
    }
    feedback.classList.remove('hidden');
}

function toggleAccordion(id) {
    const content = document.getElementById('content-' + id);
    const icon = document.getElementById('icon-' + id);
    if (!content) return;
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        if (icon) icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        if (icon) icon.classList.remove('rotate-180');
    }
}
</script>
@endpush
