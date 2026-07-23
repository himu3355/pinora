@extends('layouts.app')

@section('title', 'Artisan & Jewellery Vendors')

@section('content')
<!-- Hero Header -->
<div class="bg-dark-card/50 py-16 border-b border-border-gold">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-extrabold text-text-light tracking-tight sm:text-5xl font-primary">Our Jewellery Partners</h1>
        <p class="mt-4 max-w-2xl mx-auto text-lg text-text-muted">Explore verified workshops, local artisans, and premium jewellery brands selling directly to you.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($vendors->isEmpty())
        <div class="text-center py-20 bg-dark-card border border-border-gold rounded-3xl shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gold/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3 class="mt-4 text-lg font-bold text-text-light font-primary">No active vendors</h3>
            <p class="mt-2 text-sm text-text-muted">Check back later as we verify and onboard new jewellery designers.</p>
        </div>
    @else
        <!-- Vendors Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($vendors as $vendor)
                <div class="group bg-dark-card border border-border-gold rounded-3xl overflow-hidden shadow-sm hover:shadow-gold/20 hover:shadow-md transition duration-300 flex flex-col justify-between">
                    
                    <!-- Card Banner -->
                    <div class="h-32 bg-dark-bg relative overflow-hidden shrink-0">
                        @if($vendor->store_banner)
                            <img src="{{ Storage::url($vendor->store_banner) }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-500" alt="{{ $vendor->store_name }} banner">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-gold/10 to-gold-dark/25"></div>
                        @endif
                    </div>

                    <!-- Store Logo and Info -->
                    <div class="p-6 relative flex-grow flex flex-col items-center text-center">
                        
                        <!-- Logo badge overlapping the banner -->
                        <div class="w-20 h-20 bg-dark-surface border-2 border-gold rounded-2xl overflow-hidden shadow-sm -mt-16 mb-4 flex items-center justify-center shrink-0">
                            @if($vendor->store_logo)
                                <img src="{{ Storage::url($vendor->store_logo) }}" class="object-cover w-full h-full" alt="{{ $vendor->store_name }} logo">
                            @else
                                <span class="text-2xl font-black text-gold font-primary">{{ substr($vendor->store_name, 0, 1) }}</span>
                            @endif
                        </div>

                        <h3 class="text-xl font-bold text-text-light leading-tight mb-2 font-primary">
                            <a href="{{ route('vendors.show', $vendor->store_slug) }}" class="hover:text-gold transition">{{ $vendor->store_name }}</a>
                        </h3>
                        <p class="text-sm text-text-muted line-clamp-3 mb-6">
                            {{ $vendor->store_description ?? 'No description provided by this vendor.' }}
                        </p>
                    </div>

                    <!-- View Store CTA -->
                    <div class="px-6 pb-6 pt-2">
                        <a href="{{ route('vendors.show', $vendor->store_slug) }}" class="btn btn-outline-gold w-full justify-center">
                            Visit Storefront
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $vendors->links() }}
        </div>
    @endif
</div>
@endsection
