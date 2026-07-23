# Step 48: Order Confirmation Page & Email

**Goal:** Show order confirmation after checkout and send a transactional email to the customer.
**Depends On:** Step 47 (Checkout/OrderService), Step 20 (Order model)
**Next Step:** Step 49 (Customer Account)

---

## Files to Create

- `app/Http/Controllers/Frontend/OrderController.php`
- `resources/views/orders/confirmation.blade.php`
- `app/Mail/OrderConfirmationMail.php`
- `resources/views/emails/order-confirmation.blade.php`
- Routes added to `routes/web.php`

---

## 1. Routes — `routes/web.php`

```php
Route::middleware('auth')->group(function () {
    Route::get('/orders/{orderNumber}/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('order.show');
});
```

---

## 2. `app/Http/Controllers/Frontend/OrderController.php`

```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function confirmation(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->with(['items.product.primaryImage', 'items.vendor'])
            ->firstOrFail();

        // Send confirmation email (only if not already sent)
        if (! $order->confirmation_email_sent_at) {
            Mail::to($order->user->email)->queue(new OrderConfirmationMail($order));
            $order->update(['confirmation_email_sent_at' => now()]);
        }

        return view('orders.confirmation', compact('order'));
    }

    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->with(['items.product.primaryImage', 'items.vendor'])
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }
}
```

---

## 3. `app/Mail/OrderConfirmationMail.php`

```php
<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmed — ' . $this->order->order_number . ' | Pinora',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }
}
```

---

## 4. `resources/views/orders/confirmation.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Order Confirmed — ' . $order->order_number . ' | Pinora')

@section('content')
<div class="max-w-[760px] mx-auto px-6 py-16">

    {{-- Success Header --}}
    <div class="text-center mb-12">
        <div class="w-20 h-20 rounded-full bg-[#28a745]/15 border-2 border-[#28a745]/40 flex items-center justify-center mx-auto mb-6 text-3xl text-[#6fcf97]">✓</div>
        <h1 class="font-primary text-4xl font-normal mb-3 text-text-light">
            Order Confirmed!
        </h1>
        <p class="text-text-muted text-base mb-2">
            Thank you, <strong class="text-text-light">{{ $order->user->name }}</strong>! Your order has been placed successfully.
        </p>
        <div class="inline-block bg-gold/10 border border-border-gold rounded-full py-2 px-6 font-semibold text-gold text-[1.1rem] mt-2">
            {{ $order->order_number }}
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Order Date</div>
            <div class="text-[0.9rem] text-text-light">{{ $order->created_at->format('d M Y, h:i A') }}</div>
        </div>
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Payment</div>
            <div class="text-[0.9rem] text-text-light">{{ strtoupper($order->payment_method) }} · <span class="{{ $order->payment_status === 'paid' ? 'text-[#6fcf97]' : 'text-text-muted' }}">{{ ucfirst($order->payment_status) }}</span></div>
        </div>
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Estimated Delivery</div>
            <div class="text-[0.9rem] text-text-light">{{ now()->addDays(7)->format('d M Y') }} – {{ now()->addDays(10)->format('d M Y') }}</div>
        </div>
        <div class="bg-dark-card border border-border-gold rounded-lg p-5">
            <div class="text-xs tracking-wider uppercase text-gold mb-2 font-semibold">Order Status</div>
            <div class="text-[0.9rem] text-gold font-medium">{{ ucfirst($order->status) }}</div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-dark-card border border-border-gold rounded-lg mb-8 overflow-hidden">
        <div class="py-4 px-6 border-b border-border-gold">
            <h3 class="font-primary text-lg font-semibold text-text-light">Items Ordered</h3>
        </div>
        @foreach($order->items as $item)
        <div class="grid grid-cols-[64px_1fr_auto] gap-4 py-4 px-6 border-b border-border-gold items-center">
            <img src="{{ $item->product->primary_image_url ?? '' }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-md border border-border-gold">
            <div>
                <div class="font-semibold text-[0.9rem] text-text-light mb-1">{{ $item->product_name }}</div>
                @if($item->variant_name)<div class="text-[0.8rem] text-text-muted">{{ $item->variant_name }}</div>@endif
                <div class="text-[0.78rem] text-text-muted">Sold by: {{ $item->vendor->store_name }} · Qty: {{ $item->quantity }}</div>
            </div>
            <div class="text-right font-semibold text-gold">₹{{ number_format($item->total_price, 0) }}</div>
        </div>
        @endforeach

        {{-- Totals --}}
        <div class="py-5 px-6 bg-dark-bg/10">
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>Subtotal</span><span>₹{{ number_format($order->subtotal, 0) }}</span>
            </div>
            @if($order->cgst_amount > 0)
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>CGST</span><span>₹{{ number_format($order->cgst_amount, 0) }}</span>
            </div>
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>SGST</span><span>₹{{ number_format($order->sgst_amount, 0) }}</span>
            </div>
            @elseif($order->igst_amount > 0)
            <div class="flex justify-between mb-2 text-sm text-text-muted">
                <span>IGST</span><span>₹{{ number_format($order->igst_amount, 0) }}</span>
            </div>
            @endif
            <div class="flex justify-between pt-3 border-t border-border-gold font-bold text-lg text-gold">
                <span>Total Paid</span><span>₹{{ number_format($order->total_amount, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Delivery Address --}}
    <div class="bg-dark-card border border-border-gold rounded-lg p-6 mb-10">
        <h3 class="font-primary text-lg mb-3 text-text-light">Delivery Address</h3>
        <p class="text-sm text-text-muted leading-relaxed">
            {{ $order->shipping_full_name }}<br>
            {{ $order->shipping_address_line_1 }}@if($order->shipping_address_line_2), {{ $order->shipping_address_line_2 }}@endif<br>
            {{ $order->shipping_city }}, {{ $order->shipping_state }} — {{ $order->shipping_pincode }}<br>
            Phone: {{ $order->shipping_phone }}
        </p>
    </div>

    {{-- Email note --}}
    <div class="text-center text-text-muted text-xs mb-10">
        📧 A confirmation email has been sent to <strong>{{ $order->user->email }}</strong>
    </div>

    {{-- Actions --}}
    <div class="flex gap-4 justify-center flex-wrap">
        <a href="{{ route('order.show', $order->order_number) }}" class="btn btn-outline-gold">Track Order</a>
        <a href="{{ route('shop.index') }}" class="btn btn-gold">Continue Shopping</a>
    </div>
</div>
@endsection
```

---

## 5. `resources/views/emails/order-confirmation.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed — {{ $order->order_number }}</title>
</head>
<body class="m-0 p-0 font-sans bg-[#f4f0e8] text-[#1a1a2e]">
<div class="max-w-[600px] mx-auto py-8 px-4">
    <div class="bg-white rounded-lg overflow-hidden shadow-md">
        <div class="bg-dark-bg p-8 text-center">
            <h1 class="text-gold text-2xl font-bold mb-1">Pinora</h1>
            <p class="text-text-muted text-sm m-0">Timeless Jewellery, Infinite Craftsmanship</p>
        </div>
        <div class="p-8">
            <p class="text-base mb-4">Dear <strong>{{ $order->user->name }}</strong>,</p>
            <p class="text-[#555] mb-6 leading-relaxed">We're thrilled to confirm your order! Our vendors are already preparing your jewellery with care.</p>

            <div class="text-center mb-6">
                <div class="text-center bg-[#f9f6ef] border border-[#e8d5a3] rounded-full inline-block py-2 px-8 text-lg font-bold text-[#9B7B2E] mx-auto mb-8">{{ $order->order_number }}</div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-[#f9f6ef] rounded-lg p-3.5">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5">Order Date</div>
                    <div class="text-sm font-semibold text-text-light">{{ $order->created_at->format('d M Y') }}</div>
                </div>
                <div class="bg-[#f9f6ef] rounded-lg p-3.5">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5">Payment</div>
                    <div class="text-sm font-semibold text-text-light">{{ strtoupper($order->payment_method) }}</div>
                </div>
                <div class="bg-[#f9f6ef] rounded-lg p-3.5">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5">Est. Delivery</div>
                    <div class="text-sm font-semibold text-text-light">{{ now()->addDays(7)->format('d M') }} – {{ now()->addDays(10)->format('d M Y') }}</div>
                </div>
                <div class="bg-[#f9f6ef] rounded-lg p-3.5">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5">Total Amount</div>
                    <div class="text-sm font-semibold text-gold">₹{{ number_format($order->total_amount, 0) }}</div>
                </div>
            </div>

            <table class="w-full border-collapse mb-6">
                <thead>
                    <tr>
                        <th class="text-left text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] border-b-2 border-[#e8d5a3] py-2">Product</th>
                        <th class="text-right text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] border-b-2 border-[#e8d5a3] py-2 pr-2">Qty</th>
                        <th class="text-right text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] border-b-2 border-[#e8d5a3] py-2">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td class="py-3 border-b border-[#f0e8d8] text-sm align-top">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant_name)<br><span class="text-gray-400 text-xs">{{ $item->variant_name }}</span>@endif
                            <br><span class="text-gray-400 text-xs">by {{ $item->vendor->store_name }}</span>
                        </td>
                        <td class="py-3 border-b border-[#f0e8d8] text-sm align-top text-right pr-2">{{ $item->quantity }}</td>
                        <td class="py-3 border-b border-[#f0e8d8] text-sm align-top text-right font-semibold">₹{{ number_format($item->total_price, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="bg-[#f9f6ef] p-4 rounded-lg flex justify-between font-bold text-lg text-[#9B7B2E]">
                <span>Total</span>
                <span>₹{{ number_format($order->total_amount, 0) }}</span>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('order.show', $order->order_number) }}" class="inline-block bg-gold text-dark-bg py-3 px-8 rounded-lg font-bold text-sm no-underline mt-6 hover:bg-gold-light transition-colors">Track Your Order →</a>
            </div>
        </div>
        <div class="bg-dark-bg p-6 text-center">
            <p class="text-text-muted text-xs my-1">Pinora — India's certified jewellery marketplace</p>
            <p class="text-text-muted text-xs my-1">Need help? Contact us at support@pinora.in</p>
        </div>
    </div>
</div>
</body>
</html>
```

---

## Artisan Commands

```bash
# Add confirmation_email_sent_at column to orders table (add to orders migration Step 09)
# Or create a new migration:
php artisan make:migration add_confirmation_email_sent_at_to_orders_table
```

Migration content:
```php
$table->timestamp('confirmation_email_sent_at')->nullable()->after('notes');
```

---

## Notes

- Emails are sent via `Mail::to()->queue()` — requires a queue worker. For development use `QUEUE_CONNECTION=sync` in `.env`.
- The "Track Order" button links to `route('order.show', $order->order_number)` — the full order detail page (same controller, `show` method).
- Add `confirmation_email_sent_at` column to the `orders` migration (Step 09) or via a separate migration, so duplicate emails are prevented.
- The email view uses inline CSS to ensure maximum email client compatibility.
