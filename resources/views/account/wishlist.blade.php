@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'wishlist'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">My Wishlist</h1>
                <p class="text-text-muted mt-1">Keep track of your favorite jewellery pieces and add them directly to your cart.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if($wishlistItems->isEmpty())
                <div class="text-center py-16 border border-dashed border-border-gold/50 rounded-2xl">
                    <p class="text-text-muted text-lg">Your wishlist is currently empty.</p>
                    <a href="/" class="btn btn-gold mt-4">
                        Explore Products
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($wishlistItems as $item)
                        @php $product = $item->product; @endphp
                        @if($product)
                            <div class="group bg-dark-card border border-border-gold/50 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition duration-200 flex flex-col h-full">
                                
                                <!-- Product Image & Remove Action -->
                                <div class="relative aspect-square bg-dark-bg/50 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $product->primary_image_url }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-300" alt="{{ $product->name }}">
                                    
                                    <form action="{{ route('account.wishlist.remove', $product->id) }}" method="POST" class="absolute top-3 right-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-dark-bg/85 backdrop-blur-sm p-2 rounded-full text-red-500 hover:bg-dark-bg hover:scale-110 transition shadow-sm" title="Remove from Wishlist">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </form>
                                </div>

                                <!-- Product Info -->
                                <div class="p-5 flex flex-col justify-between flex-grow">
                                    <div>
                                        @if($product->vendor)
                                            <p class="text-xs text-gold font-semibold uppercase tracking-wider mb-1">{{ $product->vendor->store_name }}</p>
                                        @endif
                                        <h3 class="font-bold text-text-light text-sm line-clamp-2 h-10 mb-2 leading-tight">
                                            <a href="{{ route('product.show', $product->slug) }}" class="hover:text-gold transition">{{ $product->name }}</a>
                                        </h3>
                                        <div class="mb-4">
                                            @if($product->is_price_on_request)
                                                <span class="text-sm font-semibold text-text-muted">Price on Request</span>
                                            @else
                                                <span class="text-lg font-black text-gold">₹{{ number_format($product->calculated_price, 2) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        @if($product->stock_quantity > 0 && !$product->is_price_on_request)
                                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-gold hover:bg-gold-light text-dark-bg font-bold text-xs rounded-xl shadow-sm transition">
                                                    Add To Cart
                                                </button>
                                            </form>
                                        @else
                                            <button disabled class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-dark-bg/50 text-text-muted font-bold text-xs rounded-xl cursor-not-allowed border border-border-gold/50">
                                                {{ $product->is_price_on_request ? 'Contact Vendor' : 'Out of Stock' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
