@extends('layouts.app')

@section('title', 'Checkout — Pinora')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-16">
    <h1 class="font-primary text-4xl font-normal mb-10 text-text-light">Checkout</h1>

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-10 items-start">

            {{-- ===== LEFT COLUMN ===== --}}
            <div class="flex flex-col gap-8">

                {{-- STEP 1: Delivery Address --}}
                <div class="bg-dark-card border border-border-gold rounded-lg overflow-hidden">
                    <div class="py-4 px-6 border-b border-border-gold flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-gold flex items-center justify-center text-[0.8rem] font-bold text-dark-bg">1</span>
                        <span class="font-primary text-lg font-semibold text-text-light">Delivery Address</span>
                    </div>
                    <div class="p-6">
                        {{-- Saved Addresses --}}
                        @if($addresses->isNotEmpty())
                        <div class="flex flex-col gap-3 mb-6">
                            @foreach($addresses as $addr)
                            <label class="flex gap-4 p-4 border border-border-gold rounded-lg cursor-pointer transition-all duration-300" id="addr-label-{{ $addr->id }}" onclick="selectAddress({{ $addr->id }})">
                                <input type="radio" name="address_id" value="{{ $addr->id }}" {{ $addr->is_default ? 'checked' : '' }} class="accent-gold mt-1">
                                <div class="text-sm leading-relaxed">
                                    <div class="font-semibold text-text-light mb-0.5">{{ $addr->full_name }} · {{ $addr->phone }}</div>
                                    <div class="text-text-muted">{{ $addr->address_line_1 }}@if($addr->address_line_2), {{ $addr->address_line_2 }}@endif, {{ $addr->city }}, {{ $addr->state }} — {{ $addr->pincode }}</div>
                                    @if($addr->is_default)<span class="text-[0.7rem] text-gold mt-1 inline-block">✓ Default</span>@endif
                                </div>
                            </label>
                            @endforeach
                        </div>

                        {{-- Toggle for new address --}}
                        <button type="button" onclick="toggleNewAddress()" class="text-sm text-gold bg-transparent border-0 cursor-pointer underline hover:text-gold-light transition-colors">+ Add a different address</button>
                        <div id="new-address-form" class="hidden mt-6">
                        @else
                        <div id="new-address-form">
                        @endif
                            @include('checkout.partials.address-form')
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Payment Method --}}
                <div class="bg-dark-card border border-border-gold rounded-lg overflow-hidden">
                    <div class="py-4 px-6 border-b border-border-gold flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-gold flex items-center justify-center text-[0.8rem] font-bold text-dark-bg">2</span>
                        <span class="font-primary text-lg font-semibold text-text-light">Payment Method</span>
                    </div>
                    <div class="p-6 flex flex-col gap-3">
                        <label class="flex gap-4 p-4 border border-border-gold rounded-lg cursor-pointer items-center hover:border-gold transition-colors">
                            <input type="radio" name="payment_method" value="cod" checked class="accent-gold">
                            <div>
                                <div class="font-semibold text-[0.9rem] text-text-light">Cash on Delivery</div>
                                <div class="text-[0.78rem] text-text-muted">Pay when you receive your order</div>
                            </div>
                        </label>
                        <label class="flex gap-4 p-4 border border-border-gold rounded-lg cursor-not-allowed items-center opacity-60">
                            <input type="radio" name="payment_method" value="razorpay" disabled class="accent-gold">
                            <div>
                                <div class="font-semibold text-[0.9rem] text-text-light">Online Payment <span class="text-gold text-[0.7rem]">(Coming Soon)</span></div>
                                <div class="text-[0.78rem] text-text-muted">UPI, Cards, Net Banking via Razorpay</div>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            {{-- ===== ORDER SUMMARY ===== --}}
            <div class="lg:sticky lg:top-[90px] flex flex-col gap-5">
                <div class="bg-dark-card border border-border-gold rounded-lg p-6">
                    <h3 class="font-primary text-lg mb-5 pb-3 border-b border-border-gold text-text-light">Order Summary</h3>

                    {{-- Items --}}
                    @foreach($items as $item)
                    <div class="flex justify-between items-start mb-3 gap-2">
                        <span class="text-[0.83rem] text-text-muted leading-relaxed flex-1">
                            {{ $item['product_name'] }}
                            @if($item['variant_name']) ({{ $item['variant_name'] }}) @endif
                            × {{ $item['quantity'] }}
                        </span>
                        <span class="text-sm text-text-light whitespace-nowrap">₹{{ number_format($item['line_total'], 0) }}</span>
                    </div>
                    @endforeach

                    <div class="border-t border-border-gold mt-4 pt-4">
                        <div class="flex justify-between mb-2 text-sm text-text-muted">
                            <span>Subtotal</span>
                            <span>₹{{ number_format($subtotal, 0) }}</span>
                        </div>

                        @if($gstBreakdown['type'] === 'split')
                        <div class="flex justify-between mb-1.5 text-xs text-text-muted">
                            <span>CGST (1.5%)</span>
                            <span>₹{{ number_format($gstBreakdown['cgst'], 0) }}</span>
                        </div>
                        <div class="flex justify-between mb-2 text-sm text-text-muted">
                            <span>SGST (1.5%)</span>
                            <span>₹{{ number_format($gstBreakdown['sgst'], 0) }}</span>
                        </div>
                        @else
                        <div class="flex justify-between mb-2 text-sm text-text-muted">
                            <span>IGST (3%)</span>
                            <span>₹{{ number_format($gstBreakdown['igst'], 0) }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between pt-3 border-t border-border-gold font-bold text-lg text-gold">
                            <span>Total</span>
                            <span>₹{{ number_format($total, 0) }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold w-full justify-center py-4 text-[0.95rem]">Place Order →</button>

                <p class="text-xs text-text-muted text-center leading-relaxed">By placing your order you agree to our <a href="#" class="text-gold hover:text-gold-light">Terms of Service</a> and <a href="#" class="text-gold hover:text-gold-light">Return Policy</a>.</p>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleNewAddress() {
    const form = document.getElementById('new-address-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
function selectAddress(id) {
    document.querySelectorAll('[id^="addr-label-"]').forEach(el => {
        el.classList.remove('border-gold');
        el.classList.add('border-border-gold');
    });
    const target = document.getElementById('addr-label-' + id);
    target.classList.add('border-gold');
    target.classList.remove('border-border-gold');
}
// Highlight default on load
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="address_id"]:checked');
    if (checked) selectAddress(checked.value);
});
</script>
@endpush
