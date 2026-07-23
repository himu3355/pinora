@extends('layouts.app')

@section('title', 'My Account Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'dashboard'])
        </div>

        <!-- Main Panel Content -->
        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Welcome, {{ $user->name }}</h1>
                <p class="text-text-muted mt-1">From your dashboard you can track recent orders, manage addresses, and edit profile details.</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Orders</span>
                    <span class="text-3xl font-black text-gold mt-2">{{ $recentOrders->count() }}</span>
                </div>
                <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">Wishlist Items</span>
                    <span class="text-3xl font-black text-gold mt-2">{{ $wishlistCount }}</span>
                </div>
                <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">Phone</span>
                    <span class="text-md font-bold text-text-light mt-2 overflow-hidden truncate">{{ $user->phone ?? 'Not set' }}</span>
                </div>
            </div>

            <!-- Split Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Recent Orders -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-text-light">Recent Orders</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-gold hover:text-gold-light transition">View All</a>
                    </div>
                    @if($recentOrders->isEmpty())
                        <div class="border border-dashed border-border-gold/50 rounded-xl p-8 text-center text-text-muted text-sm">
                            No orders placed yet.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($recentOrders as $order)
                                <div class="flex items-center justify-between p-4 border border-border-gold/50 rounded-xl hover:bg-gold/5 transition">
                                    <div>
                                        <p class="text-sm font-bold text-text-light">{{ $order->order_number }}</p>
                                        <p class="text-xs text-text-muted">{{ $order->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gold">₹{{ number_format($order->total_amount, 2) }}</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gold/10 text-gold uppercase">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Default Address -->
                <div>
                    <h2 class="text-lg font-bold text-text-light mb-4">Default Shipping Address</h2>
                    @if(!$defaultAddress)
                        <div class="border border-dashed border-border-gold/50 rounded-xl p-8 text-center text-text-muted text-sm mb-4">
                            No default address saved.
                        </div>
                        <a href="{{ route('account.addresses') }}" class="btn btn-outline-gold w-full justify-center">
                            Add Address
                        </a>
                    @else
                        <div class="border border-border-gold p-5 rounded-2xl bg-dark-card shadow-sm relative">
                            <span class="absolute top-4 right-4 bg-gold/10 text-gold text-xs font-bold px-2.5 py-1 rounded-full uppercase">
                                Default
                            </span>
                            <p class="font-bold text-text-light text-base mb-1">{{ $defaultAddress->full_name }}</p>
                            <p class="text-sm text-gold uppercase tracking-wide font-medium mb-3">{{ $defaultAddress->type }}</p>
                            <p class="text-sm text-text-muted">{{ $defaultAddress->address_line_1 }}</p>
                            @if($defaultAddress->address_line_2)
                                <p class="text-sm text-text-muted">{{ $defaultAddress->address_line_2 }}</p>
                            @endif
                            <p class="text-sm text-text-muted">{{ $defaultAddress->city }}, {{ $defaultAddress->state }} - {{ $defaultAddress->pincode }}</p>
                            <p class="text-sm text-text-muted mt-2"><span class="font-semibold text-text-light">Phone:</span> {{ $defaultAddress->phone }}</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
