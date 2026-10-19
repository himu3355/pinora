@extends('layouts.app')

@section('title', 'Complete Payment — ' . $order->order_number . ' | Pinora')

@section('content')
<div class="max-w-[540px] mx-auto px-6 py-16">
    <div class="bg-dark-card border border-border-gold rounded-xl p-8 text-center shadow-xl">
        {{-- Shield Icon --}}
        <div class="w-16 h-16 rounded-full bg-gold/10 border border-gold/30 flex items-center justify-center mx-auto mb-6 text-2xl text-gold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>

        <h1 class="font-primary text-3xl font-normal text-text-light mb-2">Complete Payment</h1>
        <p class="text-text-muted text-sm mb-6">Order: <span class="text-gold font-semibold">{{ $order->order_number }}</span></p>

        {{-- Amount Card --}}
        <div class="bg-dark-bg/60 border border-border-gold/60 rounded-lg p-6 mb-6">
            <div class="text-xs uppercase tracking-wider text-text-muted mb-1">Total Payable Amount</div>
            <div class="font-primary text-4xl font-semibold text-gold">₹{{ number_format($order->total_amount, 2) }}</div>
            <div class="text-xs text-text-muted mt-2">Includes all applicable GST & delivery charges</div>
        </div>

        {{-- Security Badge --}}
        <div class="flex items-center justify-center gap-2 text-xs text-text-muted mb-6">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>256-Bit SSL Encrypted & RBI-Approved Payment Processing</span>
        </div>

        {{-- Pay Button --}}
        <button id="rzp-button" type="button" class="btn btn-gold w-full justify-center py-4 text-base font-semibold tracking-wide shadow-lg">
            Pay with Razorpay →
        </button>

        {{-- Cancel / Later --}}
        <div class="mt-4">
            <a href="{{ route('account.orders') }}" class="text-xs text-text-muted hover:text-gold transition-colors">
                Cancel and pay later from your account
            </a>
        </div>
    </div>

    {{-- Hidden form for POST submission of payment confirmation --}}
    <form id="rzp-form" action="{{ route('payment.callback') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="order_number" value="{{ $order->order_number }}">
        <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
        <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
        <input type="hidden" name="razorpay_signature" id="rzp_signature">
    </form>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const options = @json($rzpPayload);

    options.handler = function (response) {
        document.getElementById('rzp_order_id').value   = response.razorpay_order_id;
        document.getElementById('rzp_payment_id').value  = response.razorpay_payment_id;
        document.getElementById('rzp_signature').value   = response.razorpay_signature;
        document.getElementById('rzp-form').submit();
    };

    options.modal = {
        ondismiss: function () {
            console.log('Razorpay payment modal closed by customer.');
        }
    };

    const rzp = new Razorpay(options);

    // Bind button
    const payBtn = document.getElementById('rzp-button');
    if (payBtn) {
        payBtn.addEventListener('click', function (e) {
            e.preventDefault();
            rzp.open();
        });
    }

    // Auto-open modal on page load for seamless checkout
    setTimeout(function () {
        try {
            rzp.open();
        } catch (err) {
            console.warn('Could not auto-open Razorpay modal:', err);
        }
    }, 500);
});
</script>
@endpush
