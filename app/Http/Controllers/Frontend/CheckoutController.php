<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\CartService;
use App\Services\GstService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService   $cart,
        protected PricingService $pricing,
        protected GstService    $gst,
        protected OrderService  $orderService,
    ) {}

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $user      = auth()->user();
        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $totals    = $this->cart->totalsWithPricing($this->pricing);

        // Default to IGST preview until address is selected
        $gstBreakdown = [
            'type'        => 'IGST',
            'cgst'        => 0,
            'sgst'        => 0,
            'igst'        => $totals['gst_amount'],
            'total'       => $totals['gst_amount'],
        ];

        return view('checkout.index', [
            'addresses'    => $addresses,
            'items'        => $totals['items'],
            'subtotal'     => $totals['subtotal'],
            'gstBreakdown' => $gstBreakdown,
            'total'        => $totals['total'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id'       => 'required_without:new_address|nullable|exists:addresses,id',
            'new_address'      => 'required_without:address_id|nullable|array',
            'new_address.full_name'       => 'required_with:new_address|string|max:120',
            'new_address.phone'           => 'required_with:new_address|string|max:20',
            'new_address.address_line_1'  => 'required_with:new_address|string|max:255',
            'new_address.address_line_2'  => 'nullable|string|max:255',
            'new_address.landmark'        => 'nullable|string|max:255',
            'new_address.city'            => 'required_with:new_address|string|max:100',
            'new_address.state'           => 'required_with:new_address|string|max:100',
            'new_address.pincode'         => 'required_with:new_address|string|max:10',
            'payment_method'   => 'required|in:cod,razorpay',
        ]);

        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $user = auth()->user();

        // Resolve shipping address
        if ($request->filled('address_id')) {
            $address = Address::where('user_id', $user->id)->findOrFail($validated['address_id']);
        } else {
            $address = Address::create(array_merge($validated['new_address'], [
                'user_id' => $user->id,
                'type'    => 'shipping',
                'country' => 'India',
            ]));
        }

        // Place order via OrderService
        $order = $this->orderService->placeOrder(
            user:          $user,
            address:       $address,
            paymentMethod: $validated['payment_method'],
        );

        $this->cart->clear();

        return redirect()->route('order.confirmation', $order->order_number);
    }
}
