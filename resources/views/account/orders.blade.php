@extends('layouts.app')

@section('title', 'My Order History')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'orders'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Order History</h1>
                <p class="text-text-muted mt-1">Review status and details of all your past and pending orders.</p>
            </div>

            @if($orders->isEmpty())
                <div class="text-center py-16 border border-dashed border-border-gold/50 rounded-2xl">
                    <p class="text-text-muted text-lg">You haven't placed any orders yet.</p>
                    <a href="/" class="btn btn-gold mt-4">
                        Start Shopping
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-gold/50">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Order No.</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Payment</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Total</th>
                                <th scope="col" class="relative px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-gold/20 bg-transparent">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gold/5 transition">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-text-light">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-text-muted">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase 
                                            @if($order->status === 'delivered') bg-green-500/10 text-green-400
                                            @elseif($order->status === 'cancelled') bg-red-500/10 text-red-400
                                            @elseif($order->status === 'pending') bg-yellow-500/10 text-yellow-400
                                            @else bg-blue-500/10 text-blue-400 @endif">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-text-muted">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            @if($order->payment_status === 'paid') bg-green-500/10 text-green-400
                                            @elseif($order->payment_status === 'failed') bg-red-500/10 text-red-400
                                            @else bg-gold/10 text-gold @endif">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-extrabold text-gold text-right">
                                        ₹{{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('account.orders.show', $order->id) }}" class="text-gold hover:text-gold-light font-bold transition">View Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
