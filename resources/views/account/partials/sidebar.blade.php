<div class="bg-dark-card border border-border-gold rounded-2xl p-4 space-y-1.5 shadow-sm">
    <a href="{{ route('account.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'dashboard' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    
    <a href="{{ route('account.orders') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'orders' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        Order History
    </a>
    
    <a href="{{ route('account.wishlist') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'wishlist' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        Wishlist
    </a>
    
    <a href="{{ route('account.addresses') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'addresses' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Addresses
    </a>
    
    <a href="{{ route('account.profile') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'profile' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Profile Settings
    </a>

    <div class="pt-4 mt-4 border-t border-border-gold/30">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-bold text-red-500 rounded-xl hover:bg-red-500/10 transition duration-150">
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </form>
    </div>
</div>
