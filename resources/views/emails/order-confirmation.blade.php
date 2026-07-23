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
        <div class="bg-[#1a1a2e] p-8 text-center" style="background-color: #1a1a2e;">
            <h1 class="text-gold text-2xl font-bold mb-1" style="color: #C9A84C;">Pinora</h1>
            <p class="text-text-muted text-sm m-0" style="color: #B0A9A0;">Timeless Jewellery, Infinite Craftsmanship</p>
        </div>
        <div class="p-8">
            <p class="text-base mb-4">Dear <strong>{{ $order->user->name }}</strong>,</p>
            <p class="text-[#555] mb-6 leading-relaxed">We're thrilled to confirm your order! Our vendors are already preparing your jewellery with care.</p>

            <div class="text-center mb-6" style="text-align: center;">
                <div class="text-center bg-[#f9f6ef] border border-[#e8d5a3] rounded-full inline-block py-2 px-8 text-lg font-bold text-[#9B7B2E] mx-auto mb-8" style="background-color: #f9f6ef; border: 1px solid #e8d5a3; color: #9B7B2E; display: inline-block; padding: 8px 32px; border-radius: 50px;">{{ $order->order_number }}</div>
            </div>

            <div style="display: flex; flex-wrap: wrap; margin-bottom: 24px; gap: 10px;">
                <div class="bg-[#f9f6ef] rounded-lg p-3.5" style="background-color: #f9f6ef; border-radius: 8px; padding: 14px; flex: 1; min-width: 120px;">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5" style="font-size: 10px; color: #9B7B2E; text-transform: uppercase;">Order Date</div>
                    <div class="text-sm font-semibold text-text-light" style="font-size: 14px; font-weight: bold;">{{ $order->created_at->format('d M Y') }}</div>
                </div>
                <div class="bg-[#f9f6ef] rounded-lg p-3.5" style="background-color: #f9f6ef; border-radius: 8px; padding: 14px; flex: 1; min-width: 120px;">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5" style="font-size: 10px; color: #9B7B2E; text-transform: uppercase;">Payment</div>
                    <div class="text-sm font-semibold text-text-light" style="font-size: 14px; font-weight: bold;">{{ strtoupper($order->payment_method) }}</div>
                </div>
                <div class="bg-[#f9f6ef] rounded-lg p-3.5" style="background-color: #f9f6ef; border-radius: 8px; padding: 14px; flex: 1; min-width: 120px;">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5" style="font-size: 10px; color: #9B7B2E; text-transform: uppercase;">Est. Delivery</div>
                    <div class="text-sm font-semibold text-text-light" style="font-size: 14px; font-weight: bold;">{{ now()->addDays(7)->format('d M') }} – {{ now()->addDays(10)->format('d M Y') }}</div>
                </div>
                <div class="bg-[#f9f6ef] rounded-lg p-3.5" style="background-color: #f9f6ef; border-radius: 8px; padding: 14px; flex: 1; min-width: 120px;">
                    <div class="text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] mb-1.5" style="font-size: 10px; color: #9B7B2E; text-transform: uppercase;">Total Amount</div>
                    <div class="text-sm font-semibold text-gold" style="font-size: 14px; font-weight: bold; color: #C9A84C;">₹{{ number_format($order->total_amount, 0) }}</div>
                </div>
            </div>

            <table class="w-full border-collapse mb-6" style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                <thead>
                    <tr>
                        <th class="text-left text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] border-b-2 border-[#e8d5a3] py-2" style="text-align: left; border-bottom: 2px solid #e8d5a3; padding: 8px 0; color: #9B7B2E;">Product</th>
                        <th class="text-right text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] border-b-2 border-[#e8d5a3] py-2 pr-2" style="text-align: right; border-bottom: 2px solid #e8d5a3; padding: 8px 8px 8px 0; color: #9B7B2E;">Qty</th>
                        <th class="text-right text-[0.7rem] tracking-wider uppercase text-[#9B7B2E] border-b-2 border-[#e8d5a3] py-2" style="text-align: right; border-bottom: 2px solid #e8d5a3; padding: 8px 0; color: #9B7B2E;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td class="py-3 border-b border-[#f0e8d8] text-sm align-top" style="border-bottom: 1px solid #f0e8d8; padding: 12px 0;">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant_name)<br><span style="color: #888; font-size: 12px;">{{ $item->variant_name }}</span>@endif
                            <br><span style="color: #888; font-size: 12px;">by {{ $item->vendor->store_name }}</span>
                        </td>
                        <td class="py-3 border-b border-[#f0e8d8] text-sm align-top text-right pr-2" style="border-bottom: 1px solid #f0e8d8; padding: 12px 8px 12px 0; text-align: right;">{{ $item->quantity }}</td>
                        <td class="py-3 border-b border-[#f0e8d8] text-sm align-top text-right font-semibold" style="border-bottom: 1px solid #f0e8d8; padding: 12px 0; text-align: right; font-weight: bold;">₹{{ number_format($item->total_price, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="bg-[#f9f6ef] p-4 rounded-lg flex justify-between font-bold text-lg text-[#9B7B2E]" style="background-color: #f9f6ef; padding: 16px; border-radius: 8px; font-weight: bold; color: #9B7B2E; display: flex; justify-content: space-between;">
                <span>Total</span>
                <span>₹{{ number_format($order->total_amount, 0) }}</span>
            </div>

            <div class="text-center mt-6" style="text-align: center; margin-top: 24px;">
                <a href="{{ route('order.show', $order->order_number) }}" style="display: inline-block; background-color: #C9A84C; color: #1a1a2e; padding: 12px 32px; border-radius: 8px; font-weight: bold; text-decoration: none; font-size: 14px;">Track Your Order →</a>
            </div>
        </div>
        <div class="bg-dark-bg p-6 text-center" style="background-color: #1a1a2e; padding: 24px; text-align: center; color: #B0A9A0; font-size: 12px;">
            <p style="margin: 4px 0;">Pinora — India's certified jewellery marketplace</p>
            <p style="margin: 4px 0;">Need help? Contact us at support@pinora.in</p>
        </div>
    </div>
</div>
</body>
</html>
