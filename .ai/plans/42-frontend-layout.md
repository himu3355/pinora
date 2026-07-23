# Step 42: Master Layout, Navbar, Footer

**Goal:** Create the master Blade layout and CSS design system for the customer storefront.
**Depends On:** Step 01 (Laravel install, Vite)
**Next Step:** Step 43 (Homepage)

---

## Files to Create/Modify

- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/partials/navbar.blade.php`
- `resources/views/layouts/partials/footer.blade.php`
- `resources/css/app.css`
- `resources/js/app.js`

---

## 1. `resources/css/app.css`

```css
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap');
@import "tailwindcss";

@theme {
  --color-gold: #C9A84C;
  --color-gold-light: #E8D5A3;
  --color-gold-dark: #9B7B2E;
  --color-dark-bg: #1A1A2E;
  --color-dark-card: #16213E;
  --color-dark-surface: #0F3460;
  --color-text-light: #F5F0E8;
  --color-text-muted: #B0A9A0;
  --color-border-gold: rgba(201, 168, 76, 0.25);
  --font-primary: 'Cormorant Garamond', serif;
  --font-secondary: 'Montserrat', sans-serif;
}

@layer base {
  *, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }
  body {
    font-family: var(--font-secondary);
    background-color: var(--color-dark-bg);
    color: var(--color-text-light);
    line-height: 1.6;
  }
  a {
    color: inherit;
    text-decoration: none;
    transition: all 0.3s ease;
  }
  img {
    max-width: 100%;
    display: block;
  }
}

/* Global Utility Classes */
@utility section-padding {
  padding-top: 5rem;
  padding-bottom: 5rem;
}

@utility text-muted {
  color: var(--color-text-muted);
}

@utility font-primary {
  font-family: var(--font-primary);
}

@utility btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  font-family: var(--font-secondary);
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  cursor: pointer;
  border: none;
  transition: all 0.3s ease;
}

@utility btn-gold {
  background: linear-gradient(135deg, var(--color-gold), var(--color-gold-dark));
  color: var(--color-dark-bg);
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(201, 168, 76, 0.2);
  }
}

@utility btn-outline-gold {
  background: transparent;
  border: 1px solid var(--color-gold);
  color: var(--color-gold);
  &:hover {
    background: var(--color-gold);
    color: var(--color-dark-bg);
  }
}

@utility alert {
  padding: 1rem 1.5rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

@utility alert-success {
  background: rgba(40,167,69,0.15);
  border: 1px solid rgba(40,167,69,0.4);
  color: #6fcf97;
}

@utility alert-error {
  background: rgba(220,53,69,0.15);
  border: 1px solid rgba(220,53,69,0.4);
  color: #f08080;
}

/* Product Card Component Utility */
@utility product-card {
  background: var(--color-dark-card);
  border: 1px solid var(--color-border-gold);
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 20px rgba(201, 168, 76, 0.2);
  }
}

@utility product-card-img {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  background: #0d1b35;
  & img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
  }
}

.product-card:hover .product-card-img img {
  transform: scale(1.06);
}

@utility product-card-wishlist {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: rgba(26,26,46,0.8);
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-text-muted);
  transition: all 0.3s ease;
  &:hover, &.active {
    color: #e74c3c;
  }
}

@utility product-card-body {
  padding: 1rem;
}

@utility product-card-vendor {
  font-size: 0.75rem;
  color: var(--color-gold);
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-bottom: 0.25rem;
}

@utility product-card-name {
  font-family: var(--font-primary);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  line-height: 1.3;
}

@utility product-card-price {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-gold);
}

@utility product-card-price-original {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  text-decoration: line-through;
  margin-left: 0.5rem;
}

/* Custom scrollbar utility */
@utility custom-scrollbar {
  &::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  &::-webkit-scrollbar-track {
    background: var(--color-dark-bg);
  }
  &::-webkit-scrollbar-thumb {
    background: var(--color-dark-surface);
    border-radius: 4px;
  }
  &::-webkit-scrollbar-thumb:hover {
    background: var(--color-gold);
  }
}
```

---

## 2. `resources/views/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Pinora — Timeless Jewellery')</title>
    <meta name="description" content="@yield('meta_description', 'Shop certified gold, silver & diamond jewellery from trusted artisan vendors across India.')">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-secondary bg-dark-bg text-text-light leading-relaxed pb-20 md:pb-0">

    @include('layouts.partials.navbar')

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="alert alert-error">{{ session('error') }}</div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
```

---

## 3. `resources/views/layouts/partials/navbar.blade.php`

```blade
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
        <ul class="hidden md:flex gap-8 list-none m-0 p-0">
            <li><a href="{{ url('/') }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Home</a></li>
            <li><a href="{{ route('shop.index') }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Shop</a></li>
            <li><a href="{{ route('shop.index', ['metal_type' => 'gold']) }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Gold</a></li>
            <li><a href="{{ route('shop.index', ['metal_type' => 'silver']) }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Silver</a></li>
            <li><a href="{{ route('vendors.index') }}" class="text-[0.85rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Vendors</a></li>
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
        <div class="flex-1 overflow-y-auto px-6 py-8">
            <ul class="flex flex-col gap-6 list-none m-0 p-0">
                <li><a href="{{ url('/') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Home</a></li>
                <li><a href="{{ route('shop.index') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Shop All</a></li>
                <li><a href="{{ route('shop.index', ['metal_type' => 'gold']) }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Gold Jewellery</a></li>
                <li><a href="{{ route('shop.index', ['metal_type' => 'silver']) }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Silver Jewellery</a></li>
                <li><a href="{{ route('vendors.index') }}" class="block text-[1rem] tracking-wider uppercase text-text-muted font-medium hover:text-gold transition-colors duration-300">Our Vendors</a></li>
            </ul>
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
```

---

## 4. `resources/views/layouts/partials/footer.blade.php`

```blade
<footer class="bg-dark-card border-t border-border-gold py-16 pb-8 font-secondary">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 mb-12">

            {{-- Brand --}}
            <div class="lg:col-span-2">
                <div class="font-primary text-3xl text-gold mb-4">Pinora</div>
                <p class="text-text-muted text-sm leading-relaxed mb-6 font-secondary">Curating the finest jewellery from trusted artisan vendors across India. Every piece is certified, every seller is verified.</p>
                <div class="flex gap-2 mt-4 max-w-md">
                    <input type="email" placeholder="Your email address" class="flex-1 bg-white/5 border border-border-gold rounded-lg px-4 py-2.5 text-text-light font-secondary text-sm outline-none placeholder:text-text-muted focus:border-gold transition-colors">
                    <button class="btn btn-gold px-4 py-2.5 text-sm">Subscribe</button>
                </div>
            </div>

            {{-- Shop --}}
            <div class="lg:col-span-1">
                <div class="text-xs tracking-[0.15em] uppercase text-gold mb-5 font-semibold">Shop</div>
                <ul class="list-none flex flex-col gap-2.5 p-0 m-0">
                    <li><a href="{{ route('shop.index', ['metal_type'=>'gold']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Gold Jewellery</a></li>
                    <li><a href="{{ route('shop.index', ['metal_type'=>'silver']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Silver Jewellery</a></li>
                    <li><a href="{{ route('shop.index', ['metal_type'=>'platinum']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Platinum</a></li>
                    <li><a href="{{ route('shop.index', ['certification'=>'bis_hallmark']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">BIS Hallmark</a></li>
                    <li><a href="{{ route('vendors.index') }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">All Vendors</a></li>
                </ul>
            </div>

            {{-- Customer Service --}}
            <div class="lg:col-span-1">
                <div class="text-xs tracking-[0.15em] uppercase text-gold mb-5 font-semibold">Customer Service</div>
                <ul class="list-none flex flex-col gap-2.5 p-0 m-0">
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Track Order</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Return Policy</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Size Guide</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">FAQ</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Contact Us</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div class="lg:col-span-1">
                <div class="text-xs tracking-[0.15em] uppercase text-gold mb-5 font-semibold">Company</div>
                <ul class="list-none flex flex-col gap-2.5 p-0 m-0">
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">About Pinora</a></li>
                    <li><a href="{{ route('vendor.apply') }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Sell With Us</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Privacy Policy</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Terms of Service</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-border-gold pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-text-muted text-[0.8rem] m-0 font-secondary">&copy; {{ date('Y') }} Pinora. All rights reserved. GST registered platform.</p>
            <p class="text-text-muted text-[0.8rem] m-0 font-secondary">Secure payments by Razorpay. All transactions are encrypted.</p>
        </div>
    </div>
</footer>
```

---

## 5. `resources/js/app.js`

```js
// Wishlist toggle helper (used on product cards)
document.addEventListener('DOMContentLoaded', () => {
    // Helper to rebuild wishlist dropdown menu dynamically
    function rebuildWishlistDropdown(items) {
        const itemsList = document.querySelector('.wishlist-dropdown-items-list');
        const emptyState = document.querySelector('.wishlist-dropdown-empty-state');
        
        if (!itemsList || !emptyState) return;
        
        if (!items || items.length === 0) {
            itemsList.classList.add('hidden');
            itemsList.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            itemsList.classList.remove('hidden');
            
            let html = '';
            items.forEach(item => {
                const priceHtml = item.is_price_on_request 
                    ? 'Price on Request' 
                    : '₹' + parseFloat(item.price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    
                html += `
                    <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-gray-50 rounded px-2" data-wishlist-item="${item.product_id}">
                        <a href="/product/${item.slug}" class="flex items-center gap-3 flex-grow min-w-0">
                            <img src="${item.image_url}" alt="${item.name}" class="w-12 h-12 object-cover rounded bg-gray-100 flex-shrink-0">
                            <div class="min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate">${item.name}</h4>
                                <p class="text-xs text-amber-600 font-semibold mt-0.5">${priceHtml}</p>
                            </div>
                        </a>
                        <button type="button" data-remove-wishlist="${item.product_id}" class="text-gray-400 hover:text-red-500 bg-transparent border-0 cursor-pointer p-1" aria-label="Remove from Wishlist">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                `;
            });
            itemsList.innerHTML = html;
        }
    }

    document.querySelectorAll('[data-wishlist-toggle]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const productId = btn.dataset.wishlistToggle;
            const res = await fetch(`/wishlist/toggle/${productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (res.status === 401) {
                window.location.href = '/login';
                return;
            }
            const data = await res.json();
            
            // Toggle active state for heart icons
            btn.classList.toggle('active', data.wishlisted);
            
            // Update button text if it is the detail page button
            if (btn.classList.contains('btn-outline-gold')) {
                btn.textContent = data.wishlisted ? '♥ Remove from Wishlist' : '♡ Add to Wishlist';
            }
            
            // Update all wishlist count badges in real-time
            document.querySelectorAll('.wishlist-count-badge').forEach(badge => {
                badge.textContent = data.count;
                if (data.count > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });

            // Rebuild wishlist dropdown
            rebuildWishlistDropdown(data.recent);
        });
    });

    // Account dropdown menu toggle for mobile/touch screens (desktop view fallback & click behavior)
    const accountDropdown = document.getElementById('account-dropdown');
    if (accountDropdown) {
        const btn = accountDropdown.querySelector('button');
        
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            accountDropdown.classList.toggle('open');
        });
        
        document.addEventListener('click', (e) => {
            if (!accountDropdown.contains(e.target)) {
                accountDropdown.classList.remove('open');
            }
        });

        accountDropdown.addEventListener('mouseleave', () => {
            accountDropdown.classList.remove('open');
        });
    }

    // Desktop Dropdowns (Wishlist & Cart)
    const wishlistDropdown = document.getElementById('desktop-wishlist-dropdown');
    const cartDropdown = document.getElementById('desktop-cart-dropdown');
    
    function toggleDropdown(dropdown) {
        if (!dropdown) return;
        const menu = dropdown.querySelector('.dropdown-menu');
        if (!menu) return;
        
        const isOpen = menu.classList.contains('opacity-100');
        
        closeAllDesktopDropdowns();
        
        if (!isOpen) {
            menu.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
            menu.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
        }
    }
    
    function closeAllDesktopDropdowns() {
        [wishlistDropdown, cartDropdown].forEach(dropdown => {
            if (!dropdown) return;
            const menu = dropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            }
        });
    }
    
    if (wishlistDropdown) {
        const btn = wishlistDropdown.querySelector('button');
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(wishlistDropdown);
        });
    }
    
    if (cartDropdown) {
        const btn = cartDropdown.querySelector('button');
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(cartDropdown);
        });
    }

    // Drawer Management Helper
    const backdrop = document.getElementById('mobile-drawer-backdrop');
    
    function openDrawer(drawer) {
        if (!drawer) return;
        closeAllDesktopDropdowns(); // Close dropdowns when mobile menu opens
        drawer.classList.remove('-translate-x-full', 'translate-x-full');
        drawer.classList.add('translate-x-0');
        if (backdrop) {
            backdrop.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
            }, 10);
        }
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeAllDrawers() {
        const menuDrawer = document.getElementById('mobile-menu-drawer');
        const accountDrawer = document.getElementById('mobile-account-drawer');
        
        if (menuDrawer) {
            menuDrawer.classList.remove('translate-x-0');
            menuDrawer.classList.add('-translate-x-full');
        }
        if (accountDrawer) {
            accountDrawer.classList.remove('translate-x-0');
            accountDrawer.classList.add('translate-x-full');
        }
        
        if (backdrop) {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Mobile Menu Drawer
    const menuTrigger = document.getElementById('mobile-menu-trigger');
    const menuClose = document.getElementById('mobile-menu-close');
    const menuDrawer = document.getElementById('mobile-menu-drawer');
    
    if (menuTrigger) {
        menuTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            openDrawer(menuDrawer);
        });
    }
    if (menuClose) {
        menuClose.addEventListener('click', closeAllDrawers);
    }
    
    // Mobile Account Drawer
    const accountTrigger = document.getElementById('mobile-account-trigger');
    const accountClose = document.getElementById('mobile-account-close');
    const accountDrawer = document.getElementById('mobile-account-drawer');
    
    if (accountTrigger) {
        accountTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            openDrawer(accountDrawer);
        });
    }
    if (accountClose) {
        accountClose.addEventListener('click', closeAllDrawers);
    }
    
    // Backdrop click close action
    if (backdrop) {
        backdrop.addEventListener('click', closeAllDrawers);
    }

    // Search Overlay Toggles
    const desktopSearchBtn = document.getElementById('desktop-search-trigger');
    const mobileSearchBtn = document.getElementById('mobile-search-trigger');
    const searchOverlay = document.getElementById('global-search-overlay');
    const searchClose = document.getElementById('search-close');
    const searchInput = document.getElementById('global-search-input');
    
    function openSearch() {
        if (!searchOverlay) return;
        closeAllDesktopDropdowns(); // Close dropdowns when search opens
        searchOverlay.classList.remove('opacity-0', 'pointer-events-none');
        setTimeout(() => {
            if (searchInput) searchInput.focus();
        }, 100);
        document.body.style.overflow = 'hidden';
    }
    
    function closeSearch() {
        if (!searchOverlay) return;
        searchOverlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }
    
    if (desktopSearchBtn) desktopSearchBtn.addEventListener('click', openSearch);
    if (mobileSearchBtn) mobileSearchBtn.addEventListener('click', openSearch);
    if (searchClose) searchClose.addEventListener('click', closeSearch);
    
    // Document click listener to close everything when clicking outside
    document.addEventListener('click', (e) => {
        // Close desktop dropdowns if clicked outside
        if (wishlistDropdown && !wishlistDropdown.contains(e.target)) {
            const menu = wishlistDropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            }
        }
        if (cartDropdown && !cartDropdown.contains(e.target)) {
            const menu = cartDropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            }
        }
    });

    // Escape key listener to close overlay/drawers/dropdowns
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSearch();
            closeAllDrawers();
            closeAllDesktopDropdowns();
        }
    });

    // ==========================================
    // DYNAMIC CART & WISHLIST AJAX HANDLERS
    // ==========================================

    // Helper to update all cart count badges in real-time
    function updateCartBadges(count) {
        document.querySelectorAll('.cart-count-badge').forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
    }

    // Helper to rebuild cart dropdown menu dynamically
    function rebuildCartDropdown(items, subtotal) {
        const itemsList = document.querySelector('.cart-dropdown-items-list');
        const emptyState = document.querySelector('.cart-dropdown-empty-state');
        const footer = document.querySelector('.cart-dropdown-footer');
        const subtotalEl = document.querySelector('.cart-dropdown-subtotal');
        
        if (!itemsList || !emptyState || !footer) return;
        
        const itemKeys = Object.keys(items);
        
        if (itemKeys.length === 0) {
            itemsList.classList.add('hidden');
            itemsList.innerHTML = '';
            footer.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            itemsList.classList.remove('hidden');
            footer.classList.remove('hidden');
            if (subtotalEl) {
                subtotalEl.textContent = '₹' + parseFloat(subtotal).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            
            let html = '';
            for (const key of itemKeys) {
                const item = items[key];
                const variantNameHtml = item.variant_name 
                    ? `<p class="text-[0.7rem] text-gray-400 truncate">${item.variant_name}</p>` 
                    : '';
                const unitPriceFormatted = parseFloat(item.unit_price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                html += `
                    <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-gray-50 rounded px-2" data-cart-item="${key}">
                        <a href="/product/${item.slug}" class="flex items-center gap-3 flex-grow min-w-0">
                            <img src="${item.image_url}" alt="${item.product_name}" class="w-12 h-12 object-cover rounded bg-gray-100 flex-shrink-0">
                            <div class="min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate">${item.product_name}</h4>
                                ${variantNameHtml}
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Qty: <span class="cart-dropdown-qty">${item.quantity}</span> &times; <span class="font-semibold text-amber-600">₹${unitPriceFormatted}</span>
                                </p>
                            </div>
                        </a>
                        <button type="button" data-remove-cart="${key}" class="text-gray-400 hover:text-red-500 bg-transparent border-0 cursor-pointer p-1" aria-label="Remove from Cart">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                `;
            }
            itemsList.innerHTML = html;
        }
    }

    // Helper to update the cart summary elements on /cart page
    function updateCartSummary(subtotal, gstAmount, total) {
        const subtotalEl = document.getElementById('cart-summary-subtotal');
        const gstEl = document.getElementById('cart-summary-gst');
        const totalEl = document.getElementById('cart-summary-total');
        
        const formatPrice = val => '₹' + parseFloat(val).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

        if (subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
        if (gstEl) gstEl.textContent = formatPrice(gstAmount);
        if (totalEl) totalEl.textContent = formatPrice(total);
    }

    // Intercept Add to Cart form submission
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Sync quantity value
            const qtyInput = document.getElementById('qty-input');
            const formQtyInput = document.getElementById('form_qty');
            if (qtyInput && formQtyInput) {
                formQtyInput.value = qtyInput.value;
            }
            
            const formData = new FormData(addToCartForm);
            const action = addToCartForm.getAttribute('action');
            
            const btn = addToCartForm.querySelector('button[type="submit"]');
            const originalText = btn ? btn.textContent : '';
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Adding...';
            }
            
            try {
                const response = await fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                
                const data = await response.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    if (btn) {
                        btn.textContent = 'Added!';
                        btn.classList.remove('btn-gold');
                        btn.classList.add('bg-green-600', 'text-white');
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.textContent = originalText;
                            btn.classList.add('btn-gold');
                            btn.classList.remove('bg-green-600', 'text-white');
                        }, 2000);
                    }
                }
            } catch (err) {
                console.error(err);
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            }
        });
    }

    // Event delegation for Wishlist Dropdown Removals
    document.addEventListener('click', async (e) => {
        const removeWishlistBtn = e.target.closest('[data-remove-wishlist]');
        if (removeWishlistBtn) {
            e.preventDefault();
            e.stopPropagation();
            const productId = removeWishlistBtn.dataset.removeWishlist;
            
            try {
                const res = await fetch(`/wishlist/toggle/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (res.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                const data = await res.json();
                
                // Update heart icons on cards
                document.querySelectorAll(`[data-wishlist-toggle="${productId}"]`).forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.classList.contains('btn-outline-gold')) {
                        btn.textContent = '♡ Add to Wishlist';
                    }
                });
                
                // Update all wishlist count badges
                document.querySelectorAll('.wishlist-count-badge').forEach(badge => {
                    badge.textContent = data.count;
                    if (data.count > 0) {
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
                
                // Rebuild wishlist dropdown
                rebuildWishlistDropdown(data.recent);
            } catch (err) {
                console.error(err);
            }
        }
    });

    // Event delegation for Cart Dropdown Removals
    document.addEventListener('click', async (e) => {
        const removeCartBtn = e.target.closest('[data-remove-cart]');
        if (removeCartBtn) {
            e.preventDefault();
            e.stopPropagation();
            const key = removeCartBtn.dataset.removeCart;
            
            try {
                const res = await fetch(`/cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    // Synchronize with /cart page if the user is currently on it
                    const cartPageItemRow = document.querySelector(`.cart-item-row[data-cart-item="${key}"]`);
                    if (cartPageItemRow) {
                        const vendorGroup = cartPageItemRow.closest('.vendor-group');
                        cartPageItemRow.remove();
                        if (vendorGroup) {
                            const remainingItems = vendorGroup.querySelectorAll('.cart-item-row');
                            if (remainingItems.length === 0) {
                                vendorGroup.remove();
                            }
                        }
                        updateCartSummary(data.subtotal, data.gst_amount, data.total);
                        
                        if (data.isEmpty) {
                            document.getElementById('cart-main-grid')?.classList.add('hidden');
                            document.getElementById('cart-empty-state')?.classList.remove('hidden');
                        }
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

    // Intercept quantity updates (+ / - buttons) on /cart page
    document.addEventListener('submit', async (e) => {
        const updateForm = e.target.closest('.cart-update-form');
        if (updateForm) {
            e.preventDefault();
            
            const submitter = e.submitter;
            if (!submitter) return;
            
            const newQty = submitter.value;
            const action = updateForm.getAttribute('action');
            const itemRow = updateForm.closest('.cart-item-row');
            if (!itemRow) return;
            const key = itemRow.dataset.cartItem;
            
            try {
                const res = await fetch(action, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity: newQty })
                });
                
                const data = await res.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    if (data.quantity <= 0) {
                        const vendorGroup = itemRow.closest('.vendor-group');
                        itemRow.remove();
                        if (vendorGroup) {
                            const remainingItems = vendorGroup.querySelectorAll('.cart-item-row');
                            if (remainingItems.length === 0) {
                                vendorGroup.remove();
                            }
                        }
                    } else {
                        // Update line total
                        const lineTotalEl = itemRow.querySelector('.cart-item-line-total');
                        if (lineTotalEl) {
                            lineTotalEl.textContent = '₹' + parseFloat(data.line_total).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        }
                        
                        // Update qty span
                        const qtySpan = itemRow.querySelector('.cart-item-qty');
                        if (qtySpan) {
                            qtySpan.textContent = data.quantity;
                        }
                        
                        // Update forms decrement value
                        const decBtn = updateForm.querySelector('button[value]');
                        if (decBtn) {
                            decBtn.value = Math.max(0, data.quantity - 1);
                        }
                        const incBtn = updateForm.querySelectorAll('button[value]')[1];
                        if (incBtn) {
                            incBtn.value = data.quantity + 1;
                        }
                    }
                    
                    updateCartSummary(data.subtotal, data.gst_amount, data.total);
                    
                    if (data.isEmpty) {
                        document.getElementById('cart-main-grid')?.classList.add('hidden');
                        document.getElementById('cart-empty-state')?.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

    // Intercept item removals on /cart page
    document.addEventListener('submit', async (e) => {
        const removeForm = e.target.closest('.cart-remove-form');
        if (removeForm) {
            e.preventDefault();
            
            const action = removeForm.getAttribute('action');
            const itemRow = removeForm.closest('.cart-item-row');
            if (!itemRow) return;
            const key = itemRow.dataset.cartItem;
            
            try {
                const res = await fetch(action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                
                const data = await res.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    const vendorGroup = itemRow.closest('.vendor-group');
                    itemRow.remove();
                    if (vendorGroup) {
                        const remainingItems = vendorGroup.querySelectorAll('.cart-item-row');
                        if (remainingItems.length === 0) {
                            vendorGroup.remove();
                        }
                    }
                    
                    updateCartSummary(data.subtotal, data.gst_amount, data.total);
                    
                    if (data.isEmpty) {
                        document.getElementById('cart-main-grid')?.classList.add('hidden');
                        document.getElementById('cart-empty-state')?.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }
    });
});
```

---

## Artisan / NPM Commands

```bash
npm run build
npm run dev
```

---

## Notes

- CSS custom properties (variables) allow theme-wide changes from `:root`.
- Navbar uses CSS `backdrop-filter` for the frosted-glass effect.
- `CartService` is injected via `app()` helper in the navbar partial — ensure it is bound in the service container.
- Cart/Wishlist badges update on page load.
- Interactive drawers (menu, profile), global search overlay, and desktop dropdown menus (Wishlist, Cart) are responsive, utilizing optimized transitions and mouseout/outside-click events.
