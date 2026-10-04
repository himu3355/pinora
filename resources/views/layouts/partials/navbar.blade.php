@php
    $wishlistCount = auth()->check() ? auth()->user()->wishlists()->count() : 0;
    $cartService = app(\App\Services\CartService::class);
    $pricingService = app(\App\Services\PricingService::class);
    $cartTotals = $cartService->totalsWithPricing($pricingService);
    $cartItems = $cartTotals['items'];
    $cartSubtotal = $cartTotals['subtotal'];
    $cartCount = $cartService->count();
    $goldRate = \App\Models\MetalRate::getLatestRate('gold', '22K');
@endphp

{{-- Desktop Top Announcement Bar --}}
<div class="bg-[#041F17] text-[#D8D1C5] text-[0.72rem] tracking-wider py-1.5 px-4 hidden md:block border-b border-[#083B2B]">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1.5 text-[#C59B27] font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-[#C59B27] inline-block animate-pulse"></span>
                @if($goldRate)
                    Today's 22K Gold Rate: ₹{{ number_format($goldRate->rate_per_gram, 2) }}/g
                @else
                    Today's 22K Gold Rate: ₹7,850/g
                @endif
            </span>
            <span class="text-white/20">|</span>
            <span class="text-white/80">Rajkot's Nose Pin Specialist</span>
        </div>
        <div class="flex items-center gap-6">
            <span class="flex items-center gap-1.5 text-white/90">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#C59B27]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                100% Certified & BIS Hallmarked
            </span>
            <span class="text-white/20">|</span>
            <span class="text-white/80">Free Insured Shipping Across India</span>
        </div>
    </div>
</div>

{{-- Main Navbar --}}
<nav class="sticky top-0 z-50 bg-[#062E23] text-white border-b border-[#0A3D2F] px-4 md:px-6 shadow-md transition-all duration-300">
    <div class="max-w-7xl mx-auto flex items-center justify-between h-[64px] md:h-[76px] gap-4">

        {{-- Mobile Left: Hamburger --}}
        <button id="mobile-menu-trigger" type="button" class="md:hidden flex items-center justify-center w-10 h-10 -ml-2 text-white/90 hover:text-[#C59B27] bg-transparent border-0 cursor-pointer" aria-label="Open Navigation Menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        {{-- Brand Logo & Tagline (Centered on Mobile, Left-aligned on Desktop) --}}
        <div class="flex-1 md:flex-none flex flex-col items-center md:items-start text-center md:text-left">
            <a href="{{ url('/') }}" class="flex flex-col items-center md:items-start group">
                <div class="flex items-center gap-2">
                    {{-- Pinora Golden Floral Emblem --}}
                    <svg class="w-7 h-7 md:w-8 md:h-8 text-[#C59B27] group-hover:rotate-45 transition-transform duration-500" viewBox="0 0 100 100" fill="currentColor">
                        <circle cx="50" cy="50" r="10" fill="#C59B27"/>
                        <circle cx="50" cy="20" r="12" fill="#E0C475" opacity="0.9"/>
                        <circle cx="50" cy="80" r="12" fill="#E0C475" opacity="0.9"/>
                        <circle cx="20" cy="50" r="12" fill="#E0C475" opacity="0.9"/>
                        <circle cx="80" cy="50" r="12" fill="#E0C475" opacity="0.9"/>
                        <circle cx="28.8" cy="28.8" r="9" fill="#C59B27" opacity="0.75"/>
                        <circle cx="71.2" cy="28.8" r="9" fill="#C59B27" opacity="0.75"/>
                        <circle cx="28.8" cy="71.2" r="9" fill="#C59B27" opacity="0.75"/>
                        <circle cx="71.2" cy="71.2" r="9" fill="#C59B27" opacity="0.75"/>
                    </svg>
                    <span class="font-primary text-2xl md:text-3xl font-semibold tracking-[0.18em] text-[#FAF8F5] uppercase">Pinora</span>
                </div>
                <span class="text-[0.55rem] md:text-[0.62rem] tracking-[0.25em] uppercase text-[#E0C475] font-medium -mt-0.5">
                    @yield('navbar_tagline', 'TINY JEWEL. BIG STYLE.')
                </span>
            </a>
        </div>

        {{-- Desktop Navigation Links --}}
        <ul class="hidden md:flex gap-7 list-none m-0 p-0 items-center">
            <li><a href="{{ url('/') }}" class="text-[0.82rem] tracking-wider uppercase text-white/80 font-medium hover:text-[#C59B27] transition-colors duration-200 {{ request()->routeIs('home') ? 'text-[#C59B27] font-semibold' : '' }}">Home</a></li>
            <li><a href="{{ route('shop.index') }}" class="text-[0.82rem] tracking-wider uppercase text-white/80 font-medium hover:text-[#C59B27] transition-colors duration-200 {{ request()->routeIs('shop.index') && !request('metal_type') ? 'text-[#C59B27] font-semibold' : '' }}">Nose Pins</a></li>
            
            {{-- Categories Dropdown / Mega-menu --}}
            <li class="relative group py-5">
                <a href="{{ route('shop.index') }}" class="text-[0.82rem] tracking-wider uppercase text-white/80 font-medium hover:text-[#C59B27] transition-colors duration-200 flex items-center gap-1.5">
                    <span>Collections</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-3.5 h-3.5 text-[#C59B27] group-hover:rotate-180 transition-transform duration-200"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                
                {{-- Mega Menu Popover --}}
                <div class="opacity-0 invisible group-hover:opacity-100 group-hover:visible translate-y-2 group-hover:translate-y-0 transition-all duration-200 ease-out absolute left-1/2 -translate-x-1/2 top-full pt-2 z-50 min-w-[560px]">
                    <div class="bg-white border border-[#EAE5DC] rounded-xl shadow-[0_16px_40px_rgba(8,59,43,0.15)] p-6 text-left text-[#142E25]">
                        <div class="flex items-center justify-between border-b border-[#EAE5DC] pb-3 mb-4">
                            <span class="text-xs uppercase tracking-widest text-[#083B2B] font-bold flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#C59B27]"></span>
                                Nose Pin Collections & Styles
                            </span>
                            <a href="{{ route('shop.index') }}" class="text-[0.75rem] text-[#C59B27] hover:underline font-semibold">View All Nose Pins &rarr;</a>
                        </div>
                        <div class="grid grid-cols-3 gap-6">
                            <div>
                                <h4 class="font-bold text-xs uppercase tracking-wider text-[#A37F18] mb-3">By Style</h4>
                                <ul class="space-y-2 text-sm text-[#4A5A54]">
                                    <li><a href="{{ route('shop.index', ['category' => 'daily-wear']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">Daily Wear Studs</a></li>
                                    <li><a href="{{ route('shop.index', ['category' => 'floral']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">Floral Designs</a></li>
                                    <li><a href="{{ route('shop.index', ['category' => 'studded']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">Studded & Solitaire</a></li>
                                    <li><a href="{{ route('shop.index', ['category' => 'premium']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">Premium Heritage</a></li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs uppercase tracking-wider text-[#A37F18] mb-3">By Purity</h4>
                                <ul class="space-y-2 text-sm text-[#4A5A54]">
                                    <li><a href="{{ route('shop.index', ['purity' => '22K']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">22K Gold (916)</a></li>
                                    <li><a href="{{ route('shop.index', ['purity' => '18K']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">18K Gold (750)</a></li>
                                    <li><a href="{{ route('shop.index', ['purity' => '14K']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">14K Rose/Yellow</a></li>
                                    <li><a href="{{ route('shop.index', ['metal_type' => 'silver']) }}" class="hover:text-[#083B2B] hover:translate-x-1 inline-block transition-transform">925 Pure Silver</a></li>
                                </ul>
                            </div>
                            <div class="bg-[#FAF8F5] p-3.5 rounded-lg border border-[#EAE5DC] flex flex-col justify-between">
                                <div>
                                    <span class="text-[0.65rem] font-bold tracking-wider uppercase text-[#083B2B] bg-[#083B2B]/10 px-2 py-0.5 rounded-full inline-block mb-2">Rajkot Craft</span>
                                    <p class="text-xs text-[#60706A] leading-relaxed mb-3">Authentic handcrafted 22K nose pins directly from Gujarat master artisans.</p>
                                </div>
                                <a href="{{ route('shop.index', ['featured' => 1]) }}" class="text-xs text-[#083B2B] font-bold hover:underline">Explore Bestsellers &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <li><a href="{{ route('shop.index', ['metal_type' => 'gold']) }}" class="text-[0.82rem] tracking-wider uppercase text-white/80 font-medium hover:text-[#C59B27] transition-colors duration-200 {{ request('metal_type') === 'gold' ? 'text-[#C59B27] font-semibold' : '' }}">22K Gold</a></li>
            <li><a href="{{ route('shop.index', ['metal_type' => 'silver']) }}" class="text-[0.82rem] tracking-wider uppercase text-white/80 font-medium hover:text-[#C59B27] transition-colors duration-200 {{ request('metal_type') === 'silver' ? 'text-[#C59B27] font-semibold' : '' }}">Silver</a></li>
        </ul>

        {{-- Right Actions (Wishlist, Cart, Account, Search) --}}
        <div class="flex items-center gap-3 md:gap-5">

            {{-- Desktop Search Trigger --}}
            <button id="desktop-search-trigger" class="hidden md:flex items-center justify-center w-9 h-9 rounded-full text-white/80 hover:text-[#C59B27] hover:bg-white/5 transition-colors" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            </button>

            {{-- Wishlist (Mobile Link) --}}
            <a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="flex md:hidden relative w-9 h-9 items-center justify-center text-white/90 hover:text-[#C59B27] transition-colors" aria-label="Wishlist">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5.5 h-5.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <span class="wishlist-count-badge absolute -top-0.5 -right-0.5 bg-[#C59B27] text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $wishlistCount > 0 ? '' : 'hidden' }}">{{ $wishlistCount }}</span>
            </a>

            {{-- Wishlist (Desktop Dropdown) --}}
            <div class="hidden md:block relative animate-dropdown" id="desktop-wishlist-dropdown">
                <button class="relative w-9 h-9 flex items-center justify-center rounded-full text-white/80 hover:text-[#C59B27] hover:bg-white/5 transition-colors cursor-pointer" aria-label="Wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <span class="wishlist-count-badge absolute -top-0.5 -right-0.5 bg-[#C59B27] text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $wishlistCount > 0 ? '' : 'hidden' }}">{{ $wishlistCount }}</span>
                </button>
                
                {{-- Dropdown Container --}}
                <div class="dropdown-menu absolute right-0 top-full mt-2 w-80 bg-white text-[#142E25] border border-[#EAE5DC] rounded-xl shadow-[0_12px_32px_rgba(8,59,43,0.12)] z-50 transform origin-top-right transition-all ease-out duration-150 opacity-0 scale-95 pointer-events-none">
                    <div class="p-4 border-b border-[#EAE5DC] flex items-center justify-between">
                        <span class="font-semibold text-sm text-[#083B2B]">My Wishlist</span>
                        <a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="text-xs text-[#C59B27] hover:underline">View All</a>
                    </div>
                    <div class="p-4">
                        @auth
                            @php
                                $recentWishlist = auth()->user()->wishlists()->latest()->take(3)->with('product')->get();
                            @endphp
                            <div class="wishlist-dropdown-empty-state {{ $recentWishlist->isEmpty() ? '' : 'hidden' }} text-center py-6 text-gray-500 text-sm">
                                Your wishlist is empty.
                            </div>
                            <div class="wishlist-dropdown-items-list max-h-60 overflow-y-auto space-y-3 {{ $recentWishlist->isEmpty() ? 'hidden' : '' }}">
                                @foreach($recentWishlist as $item)
                                    @if($item->product)
                                        <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-[#FAF8F5] rounded-lg px-2" data-wishlist-item="{{ $item->product->id }}">
                                            <a href="{{ route('product.show', $item->product->slug) }}" class="flex items-center gap-3 flex-grow min-w-0">
                                                <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}" class="w-12 h-12 object-cover rounded-lg bg-gray-100 flex-shrink-0">
                                                <div class="min-w-0">
                                                    <h4 class="text-xs font-semibold text-gray-900 truncate">{{ $item->product->name }}</h4>
                                                    <p class="text-xs text-[#083B2B] font-bold mt-0.5">
                                                        ₹{{ number_format($item->product->calculated_price, 0) }}
                                                    </p>
                                                </div>
                                            </a>
                                            <button type="button" data-remove-wishlist="{{ $item->product->id }}" class="text-gray-400 hover:text-red-500 bg-transparent border-0 cursor-pointer p-1" aria-label="Remove">
                                                &times;
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-500 text-sm">
                                Please <a href="{{ route('login') }}" class="text-[#083B2B] font-semibold underline">login</a> to view your saved items.
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- Cart (Mobile Link) --}}
            <a href="{{ route('cart.index') }}" class="flex md:hidden relative w-9 h-9 items-center justify-center text-white/90 hover:text-[#C59B27] transition-colors" aria-label="Cart">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5.5 h-5.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <span class="cart-count-badge absolute -top-0.5 -right-0.5 bg-[#C59B27] text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
            </a>

            {{-- Cart (Desktop Dropdown) --}}
            <div class="hidden md:block relative animate-dropdown" id="desktop-cart-dropdown">
                <button class="relative w-9 h-9 flex items-center justify-center rounded-full text-white/80 hover:text-[#C59B27] hover:bg-white/5 transition-colors cursor-pointer" aria-label="Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <span class="cart-count-badge absolute -top-0.5 -right-0.5 bg-[#C59B27] text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                </button>
                
                {{-- Dropdown Container --}}
                <div class="dropdown-menu absolute right-0 top-full mt-2 w-80 bg-white text-[#142E25] border border-[#EAE5DC] rounded-xl shadow-[0_12px_32px_rgba(8,59,43,0.12)] z-50 transform origin-top-right transition-all ease-out duration-150 opacity-0 scale-95 pointer-events-none">
                    <div class="p-4 border-b border-[#EAE5DC] flex items-center justify-between">
                        <span class="font-semibold text-sm text-[#083B2B]">Shopping Bag ({{ $cartCount }})</span>
                        <a href="{{ route('cart.index') }}" class="text-xs text-[#C59B27] hover:underline">View Bag</a>
                    </div>
                    <div class="p-4">
                        <div class="cart-dropdown-empty-state {{ empty($cartItems) ? '' : 'hidden' }} text-center py-6 text-gray-500 text-sm">
                            Your bag is currently empty.
                        </div>
                        
                        <div class="cart-dropdown-items-list max-h-60 overflow-y-auto space-y-3 {{ empty($cartItems) ? 'hidden' : '' }}">
                            @foreach($cartItems as $key => $item)
                                <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-[#FAF8F5] rounded-lg px-2" data-cart-item="{{ $key }}">
                                    <a href="{{ route('product.show', $item['slug']) }}" class="flex items-center gap-3 flex-grow min-w-0">
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}" class="w-12 h-12 object-cover rounded-lg bg-gray-100 flex-shrink-0">
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-semibold text-gray-900 truncate">{{ $item['product_name'] }}</h4>
                                            <p class="text-xs text-[#083B2B] font-bold mt-0.5">
                                                {{ $item['quantity'] }} &times; ₹{{ number_format($item['unit_price'], 0) }}
                                            </p>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="cart-dropdown-footer {{ empty($cartItems) ? 'hidden' : '' }} bg-[#FAF8F5] p-4 border-t border-[#EAE5DC] rounded-b-xl space-y-3">
                        <div class="flex items-center justify-between text-sm font-medium">
                            <span class="text-gray-500">Subtotal:</span>
                            <span class="cart-dropdown-subtotal text-[#083B2B] font-bold text-base">₹{{ number_format($cartSubtotal, 0) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('cart.index') }}" class="w-full text-center block border border-[#083B2B] text-[#083B2B] hover:bg-[#083B2B] hover:text-white font-semibold py-2 px-3 rounded-lg transition-colors duration-200 text-xs">
                                View Bag
                            </a>
                            <a href="{{ route('checkout.index') }}" class="w-full text-center block bg-[#C59B27] hover:bg-[#A37F18] text-white font-semibold py-2 px-3 rounded-lg transition-colors duration-200 text-xs">
                                Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account / Profile Desktop --}}
            @auth
                <div class="hidden md:block relative group" id="account-dropdown">
                    <button class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-white/20 bg-white/5 text-white/90 cursor-pointer font-secondary text-[0.8rem] hover:border-[#C59B27] hover:text-[#C59B27] transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        <span>{{ Str::limit(auth()->user()->name, 10) }}</span>
                    </button>
                    <div class="hidden group-hover:block group-[.open]:block absolute right-0 top-full pt-2 z-50">
                        <div class="bg-white text-[#142E25] border border-[#EAE5DC] rounded-xl min-w-[200px] overflow-hidden shadow-lg">
                            <div class="px-4 py-3 bg-[#FAF8F5] border-b border-[#EAE5DC]">
                                <p class="text-xs text-gray-500 font-medium">Signed in as</p>
                                <p class="text-xs font-bold text-[#083B2B] truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('account.dashboard') }}" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-[#FAF8F5] hover:text-[#083B2B] transition-colors">My Orders & Account</a>
                            <a href="{{ route('account.wishlist') }}" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-[#FAF8F5] hover:text-[#083B2B] transition-colors">Saved Nose Pins</a>
                            <a href="{{ route('account.profile') }}" class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-[#FAF8F5] hover:text-[#083B2B] transition-colors">Profile Settings</a>
                            @if(auth()->user()->isVendor())
                                <a href="{{ url('/vendor') }}" class="block px-4 py-2.5 text-xs text-[#083B2B] font-semibold hover:bg-[#FAF8F5] transition-colors border-t border-[#EAE5DC]">Vendor Portal</a>
                            @endif
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 transition-colors border-t border-[#EAE5DC]">Logout</a>
                        </div>
                    </div>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            @else
                <a href="{{ route('login') }}" class="hidden md:inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-[#C59B27] text-[#C59B27] hover:bg-[#C59B27] hover:text-white transition-all text-xs font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    <span>Login</span>
                </a>
            @endauth
        </div>
    </div>

    {{-- Mobile Dedicated Search Bar (Visible on mobile right beneath header, matching Mockup 1 & 3) --}}
    <div class="pb-3 pt-0 md:hidden">
        <form action="{{ route('shop.index') }}" method="GET" class="relative">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search for nose pins..." 
                   class="w-full bg-[#FAF8F5] text-[#142E25] placeholder-[#8E9E98] text-xs sm:text-sm pl-9 pr-10 py-2.5 rounded-xl border border-white/20 focus:outline-none focus:ring-2 focus:ring-[#C59B27] shadow-inner transition-all">
            
            {{-- Search icon left --}}
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8E9E98] pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </div>

            {{-- Scan / lens icon right --}}
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8E9E98] hover:text-[#083B2B] p-0.5 cursor-pointer" aria-label="Search Submit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                </svg>
            </button>
        </form>
    </div>
</nav>

{{-- Mobile Navigation Drawer --}}
<div id="mobile-menu-drawer" class="fixed inset-y-0 left-0 w-80 max-w-[85vw] bg-white text-[#142E25] z-50 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden shadow-[12px_0_36px_rgba(0,0,0,0.25)] flex flex-col justify-between">
    <div>
        {{-- Drawer Header --}}
        <div class="flex items-center justify-between px-5 py-4 bg-[#062E23] text-white">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-[#C59B27]" viewBox="0 0 100 100" fill="currentColor">
                    <circle cx="50" cy="50" r="12" fill="#C59B27"/>
                    <circle cx="50" cy="20" r="12" fill="#E0C475"/>
                    <circle cx="50" cy="80" r="12" fill="#E0C475"/>
                    <circle cx="20" cy="50" r="12" fill="#E0C475"/>
                    <circle cx="80" cy="50" r="12" fill="#E0C475"/>
                </svg>
                <span class="font-primary text-2xl font-bold tracking-widest uppercase">Pinora</span>
            </div>
            <button id="mobile-menu-close" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 border-0 cursor-pointer" aria-label="Close Menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Drawer Navigation Items --}}
        <div class="px-5 py-6 space-y-4 overflow-y-auto max-h-[calc(100vh-140px)]">
            <div class="border-b border-[#EAE5DC] pb-4">
                <a href="{{ url('/') }}" class="flex items-center justify-between text-sm font-semibold text-[#083B2B] py-2">
                    <span>Home</span>
                    <span class="text-xs text-gray-400">&rarr;</span>
                </a>
                <a href="{{ route('shop.index') }}" class="flex items-center justify-between text-sm font-semibold text-[#083B2B] py-2">
                    <span>All Nose Pins (48+ Styles)</span>
                    <span class="text-xs text-gray-400">&rarr;</span>
                </a>
            </div>

            <div>
                <p class="text-[0.7rem] uppercase tracking-widest text-[#A37F18] font-bold mb-2">Shop By Category</p>
                <div class="space-y-1 text-sm text-[#4A5A54]">
                    <a href="{{ route('shop.index', ['category' => 'daily-wear']) }}" class="block py-1.5 hover:text-[#083B2B]">Daily Wear Studs</a>
                    <a href="{{ route('shop.index', ['category' => 'floral']) }}" class="block py-1.5 hover:text-[#083B2B]">Floral Designs</a>
                    <a href="{{ route('shop.index', ['category' => 'studded']) }}" class="block py-1.5 hover:text-[#083B2B]">Studded Solitaires</a>
                    <a href="{{ route('shop.index', ['category' => 'premium']) }}" class="block py-1.5 hover:text-[#083B2B]">Premium Heritage</a>
                </div>
            </div>

            <div class="pt-3 border-t border-[#EAE5DC]">
                <p class="text-[0.7rem] uppercase tracking-widest text-[#A37F18] font-bold mb-2">Purity & Metals</p>
                <div class="space-y-1 text-sm text-[#4A5A54]">
                    <a href="{{ route('shop.index', ['purity' => '22K']) }}" class="block py-1.5 hover:text-[#083B2B]">22K BIS Hallmarked Gold</a>
                    <a href="{{ route('shop.index', ['purity' => '18K']) }}" class="block py-1.5 hover:text-[#083B2B]">18K Yellow & Rose Gold</a>
                    <a href="{{ route('shop.index', ['metal_type' => 'silver']) }}" class="block py-1.5 hover:text-[#083B2B]">925 Sterling Silver</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Drawer Bottom Details --}}
    <div class="p-5 bg-[#FAF8F5] border-t border-[#EAE5DC]">
        @auth
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Logged in as</p>
                    <p class="text-xs font-bold text-[#083B2B]">{{ auth()->user()->name }}</p>
                </div>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-xs text-red-600 font-semibold underline">Logout</a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('login') }}" class="text-center py-2 px-3 bg-[#083B2B] text-white rounded-lg text-xs font-semibold">Login</a>
                <a href="{{ route('register') }}" class="text-center py-2 px-3 border border-[#083B2B] text-[#083B2B] rounded-lg text-xs font-semibold">Register</a>
            </div>
        @endauth
    </div>
</div>

{{-- Global Search Overlay (Desktop Modal) --}}
<div id="global-search-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex flex-col items-center justify-center p-6 transition-all duration-300 opacity-0 pointer-events-none">
    <button id="search-close" class="absolute top-6 right-6 w-12 h-12 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 border-0 cursor-pointer transition-all duration-300" aria-label="Close Search">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <div class="w-full max-w-2xl bg-white p-8 rounded-2xl shadow-2xl text-center">
        <div class="flex items-center justify-center gap-2 mb-3">
            <svg class="w-6 h-6 text-[#C59B27]" viewBox="0 0 100 100" fill="currentColor">
                <circle cx="50" cy="50" r="12" fill="#C59B27"/>
                <circle cx="50" cy="20" r="12" fill="#E0C475"/>
                <circle cx="50" cy="80" r="12" fill="#E0C475"/>
                <circle cx="20" cy="50" r="12" fill="#E0C475"/>
                <circle cx="80" cy="50" r="12" fill="#E0C475"/>
            </svg>
            <h2 class="font-primary text-3xl font-semibold text-[#083B2B] tracking-wide">Search Pinora</h2>
        </div>
        <p class="text-xs text-gray-500 mb-6">Explore our curated catalogue of 22K gold nose pins, floral studs, & solitaires.</p>
        <form action="{{ route('shop.index') }}" method="GET" class="relative">
            <input type="text" id="global-search-input" name="search" placeholder="E.g. Floral, Diamond stud, 22K gold..." class="w-full bg-[#FAF8F5] border border-[#EAE5DC] rounded-xl px-5 py-3.5 text-[#142E25] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#083B2B] text-base transition-all">
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-5 py-2 rounded-lg bg-[#083B2B] text-white border-0 cursor-pointer hover:bg-[#062E23] transition-all text-sm font-semibold">
                Search
            </button>
        </form>
    </div>
</div>

{{-- Backdrop for Mobile Menu Drawer --}}
<div id="mobile-drawer-backdrop" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-45 hidden transition-opacity duration-300 opacity-0"></div>
