@php
    $wishlistCount = auth()->check() ? auth()->user()->wishlists()->count() : 0;
    $cartService = app(\App\Services\CartService::class);
    $pricingService = app(\App\Services\PricingService::class);
    $cartTotals = $cartService->totalsWithPricing($pricingService);
    $cartItems = $cartTotals['items'];
    $cartSubtotal = $cartTotals['subtotal'];
    $cartCount = $cartService->count();
@endphp

<nav class="sticky top-0 z-50 bg-dark-bg/90 backdrop-blur-md border-b border-border-gold px-6">
    <div class="max-w-7xl mx-auto flex items-center justify-between h-[72px] gap-8">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="font-primary text-3xl font-semibold text-gold tracking-wide whitespace-nowrap">Pinora</a>

        {{-- Nav Links --}}
        <ul class="hidden md:flex gap-8 list-none m-0 p-0 items-center">
            <li><a href="{{ url('/') }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Home</a></li>
            <li><a href="{{ route('shop.index') }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Shop</a></li>
            
            {{-- Sub-Categories Mega Menu Dropdown --}}
            <li class="relative group py-5">
                <a href="{{ route('shop.index') }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300 flex items-center gap-1">
                    <span>Categories</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-3 h-3 text-gold/70 group-hover:rotate-180 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                
                {{-- Mega Menu Popover --}}
                <div class="opacity-0 invisible group-hover:opacity-100 group-hover:visible translate-y-2 group-hover:translate-y-0 transition-all duration-200 ease-out absolute left-1/2 -translate-x-1/2 top-full pt-1 z-50 min-w-[580px]">
                    <div class="bg-[#121829]/98 border border-border-gold/40 rounded-2xl shadow-[0_16px_48px_rgba(0,0,0,0.8)] p-5 backdrop-blur-2xl text-left">
                        <div class="flex items-center justify-between border-b border-border-gold/20 pb-3 mb-4">
                            <span class="text-xs uppercase tracking-widest text-gold font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 text-gold"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                All Jewellery Sub-Categories
                            </span>
                            <a href="{{ route('shop.index') }}" class="text-[0.7rem] text-text-muted hover:text-gold transition-colors font-medium">Browse All &rarr;</a>
                        </div>
                        <div class="grid grid-cols-3 gap-6">
                            @if(isset($menuCategories) && $menuCategories->isNotEmpty())
                                @foreach($menuCategories as $parentCat)
                                    <div>
                                        <a href="{{ route('shop.index', ['category' => $parentCat->slug]) }}" class="font-bold text-[0.8rem] text-gold hover:underline block mb-2 tracking-wide">
                                            {{ $parentCat->name }}
                                        </a>
                                        @if($parentCat->children->isNotEmpty())
                                            <div class="space-y-1">
                                                @foreach($parentCat->children as $subCat)
                                                    <a href="{{ route('shop.index', ['category' => $subCat->slug]) }}" class="block text-xs text-text-muted hover:text-gold transition-colors hover:translate-x-0.5 transform duration-150 py-0.5">
                                                        {{ $subCat->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </li>

            <li><a href="{{ route('shop.index', ['metal_type' => 'gold']) }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Gold</a></li>
            <li><a href="{{ route('shop.index', ['metal_type' => 'silver']) }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Silver</a></li>
            {{-- <li><a href="{{ route('vendors.index') }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Vendors</a></li> --}}
        </ul>

        {{-- Right Icons --}}
        <div class="flex items-center gap-4">

            {{-- Search --}}
            <button id="desktop-search-trigger" class="hidden md:flex relative w-10 h-10 items-center justify-center rounded-full bg-transparent border-0 cursor-pointer text-text-muted hover:text-gold hover:bg-gold/10 transition-all duration-300" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            </button>

            {{-- Wishlist (Mobile Link) --}}
            <a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="flex md:hidden relative w-10 h-10 items-center justify-center rounded-full text-text-muted hover:text-gold transition-all duration-300" aria-label="Wishlist">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <span class="wishlist-count-badge absolute top-0.5 right-0.5 bg-red-600 text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $wishlistCount > 0 ? '' : 'hidden' }}">{{ $wishlistCount }}</span>
            </a>

            {{-- Wishlist (Desktop Dropdown) --}}
            <div class="hidden md:block relative animate-dropdown" id="desktop-wishlist-dropdown">
                <button class="relative w-10 h-10 flex items-center justify-center rounded-full bg-transparent border-0 cursor-pointer text-text-muted hover:text-gold hover:bg-gold/10 transition-all duration-300" aria-label="Wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="wishlist-count-badge absolute top-0.5 right-0.5 bg-red-600 text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $wishlistCount > 0 ? '' : 'hidden' }}">{{ $wishlistCount }}</span>
                </button>
                
                {{-- Dropdown Container --}}
                <div class="dropdown-menu absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg ring-1 ring-black/5 z-50 transform origin-top-right transition-all ease-out duration-100 opacity-0 scale-95 pointer-events-none">
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
                                        <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-gray-50 rounded px-2" data-wishlist-item="{{ $item->product->id }}">
                                            <a href="{{ route('product.show', $item->product->slug) }}" class="flex items-center gap-3 flex-grow min-w-0">
                                                <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}" class="w-12 h-12 object-cover rounded bg-gray-100 flex-shrink-0">
                                                <div class="min-w-0">
                                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item->product->name }}</h4>
                                                    <p class="text-xs text-amber-600 font-semibold mt-0.5">
                                                        @if($item->product->is_price_on_request)
                                                            Price on Request
                                                        @else
                                                            ₹{{ number_format($item->product->calculated_price, 2) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </a>
                                            <button type="button" data-remove-wishlist="{{ $item->product->id }}" class="text-gray-400 hover:text-red-500 bg-transparent border-0 cursor-pointer p-1" aria-label="Remove from Wishlist">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-500 text-sm">
                                Please <a href="{{ route('login') }}" class="text-amber-600 hover:underline">login</a> to view your wishlist.
                            </div>
                        @endauth
                    </div>
                    
                    <div class="bg-gray-50 p-3 border-t border-gray-100 sticky bottom-0 rounded-b-md">
                        <a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="w-full text-center block bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200 text-sm">
                            View Full Wishlist
                        </a>
                    </div>
                </div>
            </div>

            {{-- Cart (Mobile Link) --}}
            <a href="{{ route('cart.index') }}" class="flex md:hidden relative w-10 h-10 items-center justify-center rounded-full text-text-muted hover:text-gold transition-all duration-300" aria-label="Cart">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="cart-count-badge absolute top-0.5 right-0.5 bg-red-600 text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
            </a>

            {{-- Cart (Desktop Dropdown) --}}
            <div class="hidden md:block relative animate-dropdown" id="desktop-cart-dropdown">
                <button class="relative w-10 h-10 flex items-center justify-center rounded-full bg-transparent border-0 cursor-pointer text-text-muted hover:text-gold hover:bg-gold/10 transition-all duration-300" aria-label="Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="cart-count-badge absolute top-0.5 right-0.5 bg-red-600 text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                </button>
                
                {{-- Dropdown Container --}}
                <div class="dropdown-menu absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg ring-1 ring-black/5 z-50 transform origin-top-right transition-all ease-out duration-100 opacity-0 scale-95 pointer-events-none">
                    <div class="p-4">
                        <div class="cart-dropdown-empty-state {{ empty($cartItems) ? '' : 'hidden' }} text-center py-6 text-gray-500 text-sm">
                            Your cart is currently empty.
                        </div>
                        
                        <div class="cart-dropdown-items-list max-h-60 overflow-y-auto space-y-3 {{ empty($cartItems) ? 'hidden' : '' }}">
                            @foreach($cartItems as $key => $item)
                                <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-gray-50 rounded px-2" data-cart-item="{{ $key }}">
                                    <a href="{{ route('product.show', $item['slug']) }}" class="flex items-center gap-3 flex-grow min-w-0">
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}" class="w-12 h-12 object-cover rounded bg-gray-100 flex-shrink-0">
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['product_name'] }}</h4>
                                            @if(!empty($item['variant_name']))
                                                <p class="text-[0.7rem] text-gray-400 truncate">{{ $item['variant_name'] }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                Qty: <span class="cart-dropdown-qty">{{ $item['quantity'] }}</span> &times; <span class="font-semibold text-amber-600">₹{{ number_format($item['unit_price'], 2) }}</span>
                                            </p>
                                        </div>
                                    </a>
                                    <button type="button" data-remove-cart="{{ $key }}" class="text-gray-400 hover:text-red-500 bg-transparent border-0 cursor-pointer p-1" aria-label="Remove from Cart">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="cart-dropdown-footer {{ empty($cartItems) ? 'hidden' : '' }} bg-gray-50 p-4 border-t border-gray-100 sticky bottom-0 rounded-b-md space-y-3">
                        <div class="flex items-center justify-between text-sm font-medium">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="cart-dropdown-subtotal text-gray-900 font-bold text-base">₹{{ number_format($cartSubtotal, 2) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('cart.index') }}" class="w-full text-center block border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold py-2 px-3 rounded-md transition-colors duration-200 text-xs">
                                View Cart
                            </a>
                            <a href="{{ route('checkout.index') }}" class="w-full text-center block bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 px-3 rounded-md transition-colors duration-200 text-xs">
                                Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account --}}
            @auth
                <div class="hidden md:block relative group" id="account-dropdown">
                    <button class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-gold bg-transparent text-text-light cursor-pointer font-secondary text-[0.85rem] hover:border-gold hover:text-gold transition-all duration-300">
                        {{ Str::limit(auth()->user()->name, 12) }}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="hidden group-hover:block group-[.open]:block absolute right-0 top-full pt-3 z-50">
                        <div class="bg-dark-card border border-border-gold rounded-lg min-w-[180px] overflow-hidden shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
                            <a href="{{ route('account.dashboard') }}" class="block px-4 py-3 text-[0.85rem] text-text-muted border-b border-border-gold last:border-b-0 hover:bg-gold/10 hover:text-gold transition-colors duration-300">My Account</a>
                            <a href="{{ route('account.orders') }}" class="block px-4 py-3 text-[0.85rem] text-text-muted border-b border-border-gold last:border-b-0 hover:bg-gold/10 hover:text-gold transition-colors duration-300">My Orders</a>
                            <a href="{{ route('account.wishlist') }}" class="block px-4 py-3 text-[0.85rem] text-text-muted border-b border-border-gold last:border-b-0 hover:bg-gold/10 hover:text-gold transition-colors duration-300">Wishlist</a>
                            <a href="{{ route('account.profile') }}" class="block px-4 py-3 text-[0.85rem] text-text-muted border-b border-border-gold last:border-b-0 hover:bg-gold/10 hover:text-gold transition-colors duration-300">Profile</a>
                            @if(auth()->user()->isVendor())
                                <a href="{{ url('/vendor') }}" class="block px-4 py-3 text-[0.85rem] text-text-muted border-b border-border-gold last:border-b-0 hover:bg-gold/10 hover:text-gold transition-colors duration-300">Vendor Panel</a>
                            @endif
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-3 text-[0.85rem] text-text-muted border-b border-border-gold last:border-b-0 hover:bg-gold/10 hover:text-gold transition-colors duration-300">Logout</a>
                        </div>
                    </div>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            @else
                <a href="{{ route('login') }}" class="hidden md:inline-flex btn btn-outline-gold px-5 py-2 text-xs">Login</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Secondary Top Navbar: Popular Sub-Categories Strip --}}
<div class="bg-[#0f1422]/95 border-b border-border-gold/30 px-6 hidden md:block z-40 relative backdrop-blur-md shadow-md">
    <div class="max-w-7xl mx-auto flex items-center h-11 text-xs">
        <div class="flex items-center gap-2 shrink-0 text-gold font-bold uppercase tracking-widest text-[0.72rem] mr-3 pr-4 border-r border-border-gold/30">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-gold"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span>Popular Sub-Categories:</span>
        </div>

        <div class="flex items-center gap-1.5 flex-1 min-w-0" data-scroll-wrapper>
            {{-- Left Arrow --}}
            <button type="button" data-scroll-btn="left" class="shrink-0 w-6 h-6 rounded-full bg-gold/10 hover:bg-gold hover:text-dark-bg text-gold border border-gold/30 flex items-center justify-center transition-all duration-200 cursor-pointer disabled:opacity-20 disabled:cursor-not-allowed" aria-label="Scroll left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            {{-- Scrollable Container --}}
            <div data-scroll-container class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none scroll-smooth flex-1">
                @if(isset($allSubCategories) && $allSubCategories->isNotEmpty())
                    @foreach($allSubCategories as $subCat)
                        <a href="{{ route('shop.index', ['category' => $subCat->slug]) }}" class="text-text-muted hover:text-gold font-medium whitespace-nowrap text-[0.78rem] bg-gold/5 hover:bg-gold/20 border border-border-gold/30 hover:border-gold/60 px-3 py-1 rounded-full transition-all duration-300 shadow-xs hover:shadow-[0_2px_10px_rgba(201,168,76,0.2)] shrink-0">
                            {{ $subCat->name }}
                        </a>
                    @endforeach
                @endif
            </div>

            {{-- Right Arrow --}}
            <button type="button" data-scroll-btn="right" class="shrink-0 w-6 h-6 rounded-full bg-gold/10 hover:bg-gold hover:text-dark-bg text-gold border border-gold/30 flex items-center justify-center transition-all duration-200 cursor-pointer disabled:opacity-20 disabled:cursor-not-allowed" aria-label="Scroll right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- Mobile Bottom Navbar --}}
<div class="fixed bottom-0 left-0 right-0 h-16 bg-dark-bg/95 backdrop-blur-md border-t border-border-gold/50 flex items-center justify-around px-2 z-40 md:hidden shadow-[0_-4px_24px_rgba(0,0,0,0.6)]">
    
    {{-- Menu Trigger --}}
    <button id="mobile-menu-trigger" class="flex flex-col items-center justify-center gap-1 text-text-muted hover:text-gold bg-transparent border-0 cursor-pointer w-14 h-12 transition-colors duration-300" aria-label="Menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5.5 h-5.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <span class="text-[0.65rem] uppercase tracking-wider font-medium">Menu</span>
    </button>

    {{-- Search Trigger --}}
    <button id="mobile-search-trigger" class="flex flex-col items-center justify-center gap-1 text-text-muted hover:text-gold bg-transparent border-0 cursor-pointer w-14 h-12 transition-colors duration-300" aria-label="Search">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5.5 h-5.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
        <span class="text-[0.65rem] uppercase tracking-wider font-medium">Search</span>
    </button>

    {{-- Wishlist --}}
    <a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="relative flex flex-col items-center justify-center gap-1 text-text-muted hover:text-gold w-14 h-12 transition-colors duration-300" aria-label="Wishlist">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5.5 h-5.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <span class="wishlist-count-badge absolute top-1 right-2 bg-red-600 text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-md {{ $wishlistCount > 0 ? '' : 'hidden' }}">{{ $wishlistCount }}</span>
        <span class="text-[0.65rem] uppercase tracking-wider font-medium">Wishlist</span>
    </a>

    {{-- Cart --}}
    <a href="{{ route('cart.index') }}" class="relative flex flex-col items-center justify-center gap-1 text-text-muted hover:text-gold w-14 h-12 transition-colors duration-300" aria-label="Cart">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5.5 h-5.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span class="cart-count-badge absolute top-1 right-2 bg-red-600 text-white text-[0.6rem] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-md {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
        <span class="text-[0.65rem] uppercase tracking-wider font-medium">Cart</span>
    </a>

    {{-- Account Trigger --}}
    @auth
        <button id="mobile-account-trigger" class="flex flex-col items-center justify-center gap-1 text-text-muted hover:text-gold bg-transparent border-0 cursor-pointer w-14 h-12 transition-colors duration-300" aria-label="Account">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5.5 h-5.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-[0.65rem] uppercase tracking-wider font-medium">Profile</span>
        </button>
    @else
        <a href="{{ route('login') }}" class="flex flex-col items-center justify-center gap-1 text-text-muted hover:text-gold w-14 h-12 transition-colors duration-300" aria-label="Login">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5.5 h-5.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h5a3 3 0 013 3v1"/></svg>
            <span class="text-[0.65rem] uppercase tracking-wider font-medium">Login</span>
        </a>
    @endauth

</div>

{{-- Mobile Navigation Drawer (Pages Menu) --}}
<div id="mobile-menu-drawer" class="fixed inset-y-0 left-0 w-80 max-w-[85vw] bg-dark-bg/95 backdrop-blur-lg border-r border-border-gold/30 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden shadow-[8px_0_32px_rgba(0,0,0,0.6)]">
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border-gold/20">
            <span class="font-primary text-2xl font-semibold text-gold tracking-wide">Pinora</span>
            <button id="mobile-menu-close" class="w-8 h-8 flex items-center justify-center rounded-full bg-gold/10 text-gold hover:bg-gold/20 border-0 cursor-pointer transition-all duration-300" aria-label="Close Menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Content / Links --}}
        <div class="flex-1 overflow-y-auto px-6 py-6">
            <ul class="flex flex-col gap-5 list-none m-0 p-0">
                <li><a href="{{ url('/') }}" class="block text-[0.95rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Home</a></li>
                <li><a href="{{ route('shop.index') }}" class="block text-[0.95rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Shop All</a></li>
                <li><a href="{{ route('shop.index', ['metal_type' => 'gold']) }}" class="block text-[0.95rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Gold Jewellery</a></li>
                <li><a href="{{ route('shop.index', ['metal_type' => 'silver']) }}" class="block text-[0.95rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Silver Jewellery</a></li>
                {{-- <li><a href="{{ route('vendors.index') }}" class="block text-[0.95rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Our Vendors</a></li> --}}
            </ul>

            @if(isset($menuCategories) && $menuCategories->isNotEmpty())
                <div class="mt-6 pt-6 border-t border-border-gold/20">
                    <h3 class="text-xs uppercase tracking-wider text-gold font-semibold mb-4">Sub-Categories Menu</h3>
                    <div class="space-y-4">
                        @foreach($menuCategories as $parentCat)
                            <div>
                                <a href="{{ route('shop.index', ['category' => $parentCat->slug]) }}" class="font-medium text-sm text-text-light hover:text-gold block mb-2">
                                    {{ $parentCat->name }}
                                </a>
                                @if($parentCat->children->isNotEmpty())
                                    <div class="pl-3 border-l border-border-gold/30 space-y-1.5">
                                        @foreach($parentCat->children as $subCat)
                                            <a href="{{ route('shop.index', ['category' => $subCat->slug]) }}" class="block text-xs text-text-muted hover:text-gold transition-colors">
                                                {{ $subCat->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer/Help info --}}
        <div class="px-6 py-6 border-t border-border-gold/20 text-center text-text-muted/60 text-xs">
            © {{ date('Y') }} Pinora. All Rights Reserved.
        </div>
    </div>
</div>

@auth
{{-- Mobile Account Drawer --}}
<div id="mobile-account-drawer" class="fixed inset-y-0 right-0 w-80 max-w-[85vw] bg-dark-bg/95 backdrop-blur-lg border-l border-border-gold/30 z-50 transform translate-x-full transition-transform duration-300 ease-in-out md:hidden shadow-[-8px_0_32px_rgba(0,0,0,0.6)]">
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border-gold/20">
            <div>
                <span class="block text-xs uppercase tracking-wider text-text-muted font-medium">Welcome back,</span>
                <span class="font-secondary text-lg font-semibold text-gold">{{ Str::limit(auth()->user()->name, 16) }}</span>
            </div>
            <button id="mobile-account-close" class="w-8 h-8 flex items-center justify-center rounded-full bg-gold/10 text-gold hover:bg-gold/20 border-0 cursor-pointer transition-all duration-300" aria-label="Close Menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Links --}}
        <div class="flex-1 overflow-y-auto px-6 py-8">
            <ul class="flex flex-col gap-6 list-none m-0 p-0">
                <li><a href="{{ route('account.dashboard') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">My Account</a></li>
                <li><a href="{{ route('account.orders') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">My Orders</a></li>
                <li><a href="{{ route('account.wishlist') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Wishlist</a></li>
                <li><a href="{{ route('account.profile') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Profile Settings</a></li>
                @if(auth()->user()->isVendor())
                    <li><a href="{{ url('/vendor') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Vendor Panel</a></li>
                @endif
                <li>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block text-[1rem] tracking-wider uppercase text-red-500 font-medium hover:text-red-400 transition-colors duration-300">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</div>
@endauth

{{-- Global Search Overlay --}}
<div id="global-search-overlay" class="fixed inset-0 bg-dark-bg/95 backdrop-blur-md z-50 flex flex-col items-center justify-center p-6 transition-all duration-300 opacity-0 pointer-events-none">
    <button id="search-close" class="absolute top-6 right-6 w-12 h-12 flex items-center justify-center rounded-full bg-gold/10 text-gold hover:bg-gold/20 border-0 cursor-pointer transition-all duration-300" aria-label="Close Search">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <div class="w-full max-w-2xl text-center">
        <h2 class="font-primary text-3xl font-semibold text-gold mb-8 tracking-wide">Search Pinora</h2>
        <form action="{{ route('shop.index') }}" method="GET" class="relative">
            <input type="text" id="global-search-input" name="search" placeholder="Search for collections, metal types, products..." class="w-full bg-dark-card/50 border border-border-gold rounded-full px-6 py-4 text-text-light placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-gold/50 text-lg transition-all duration-300">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-gold text-dark-bg border-0 cursor-pointer hover:bg-gold-light transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            </button>
        </form>
        <p class="text-text-muted/60 mt-4 text-sm font-secondary">Type and press enter to search</p>
    </div>
</div>

{{-- Mobile Drawer Backdrop --}}
<div id="mobile-drawer-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-45 hidden transition-opacity duration-300 opacity-0"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function initScrollWrappers() {
        document.querySelectorAll('[data-scroll-wrapper]').forEach(function(wrapper) {
            const container = wrapper.querySelector('[data-scroll-container]');
            const btnLeft = wrapper.querySelector('[data-scroll-btn="left"]');
            const btnRight = wrapper.querySelector('[data-scroll-btn="right"]');

            if (!container || !btnLeft || !btnRight) return;

            function updateArrows() {
                const maxScrollLeft = container.scrollWidth - container.clientWidth;
                btnLeft.disabled = container.scrollLeft <= 5;
                btnRight.disabled = container.scrollLeft >= maxScrollLeft - 5;
            }

            btnLeft.onclick = function() {
                container.scrollBy({ left: -260, behavior: 'smooth' });
            };

            btnRight.onclick = function() {
                container.scrollBy({ left: 260, behavior: 'smooth' });
            };

            container.onscroll = updateArrows;
            updateArrows();
            setTimeout(updateArrows, 300);
        });
    }

    initScrollWrappers();
    window.addEventListener('resize', initScrollWrappers);
});
</script>
