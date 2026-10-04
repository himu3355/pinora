@php
    $wishlistCount = auth()->check() ? auth()->user()->wishlists()->count() : 0;
    $cartService = app(\App\Services\CartService::class);
    $cartCount = $cartService->count();
    $isHome = request()->routeIs('home');
    $isShop = request()->routeIs('shop.*');
    $isWishlist = request()->routeIs('account.wishlist');
    $isAccount = request()->routeIs('account.*') && !$isWishlist;
@endphp

<nav class="fixed bottom-0 left-0 right-0 h-16 bg-white/95 backdrop-blur-md border-t border-[#EAE5DC] flex items-center justify-around px-2 z-40 md:hidden shadow-[0_-4px_20px_rgba(0,0,0,0.06)]" aria-label="Mobile Navigation">
    
    {{-- Home --}}
    <a href="{{ url('/') }}" class="flex flex-col items-center justify-center gap-1 w-16 h-12 transition-colors duration-200 {{ $isHome ? 'text-[#083B2B] font-semibold' : 'text-[#60706A] hover:text-[#083B2B]' }}" aria-label="Home">
        @if($isHome)
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
        @endif
        <span class="text-[0.65rem] tracking-wide {{ $isHome ? 'font-semibold text-[#083B2B]' : 'font-medium' }}">Home</span>
    </a>

    {{-- Categories --}}
    <a href="{{ route('shop.index') }}" class="flex flex-col items-center justify-center gap-1 w-16 h-12 transition-colors duration-200 {{ $isShop ? 'text-[#083B2B] font-semibold' : 'text-[#60706A] hover:text-[#083B2B]' }}" aria-label="Categories">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
        </svg>
        <span class="text-[0.65rem] tracking-wide {{ $isShop ? 'font-semibold text-[#083B2B]' : 'font-medium' }}">Categories</span>
    </a>

    {{-- Wishlist --}}
    <a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="relative flex flex-col items-center justify-center gap-1 w-16 h-12 transition-colors duration-200 {{ $isWishlist ? 'text-[#083B2B] font-semibold' : 'text-[#60706A] hover:text-[#083B2B]' }}" aria-label="Wishlist">
        <svg xmlns="http://www.w3.org/2000/svg" fill="{{ $isWishlist ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
        </svg>
        <span class="wishlist-count-badge absolute top-1 right-3.5 bg-[#C59B27] text-white text-[0.55rem] font-bold w-3.5 h-3.5 rounded-full flex items-center justify-center shadow-xs {{ $wishlistCount > 0 ? '' : 'hidden' }}">{{ $wishlistCount }}</span>
        <span class="text-[0.65rem] tracking-wide {{ $isWishlist ? 'font-semibold text-[#083B2B]' : 'font-medium' }}">Wishlist</span>
    </a>

    {{-- Account --}}
    @auth
        <a href="{{ route('account.dashboard') }}" class="flex flex-col items-center justify-center gap-1 w-16 h-12 transition-colors duration-200 {{ $isAccount ? 'text-[#083B2B] font-semibold' : 'text-[#60706A] hover:text-[#083B2B]' }}" aria-label="Account">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <span class="text-[0.65rem] tracking-wide {{ $isAccount ? 'font-semibold text-[#083B2B]' : 'font-medium' }}">Account</span>
        </a>
    @else
        <a href="{{ route('login') }}" class="flex flex-col items-center justify-center gap-1 w-16 h-12 transition-colors duration-200 text-[#60706A] hover:text-[#083B2B]" aria-label="Login">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <span class="text-[0.65rem] tracking-wide font-medium">Account</span>
        </a>
    @endauth

</nav>
