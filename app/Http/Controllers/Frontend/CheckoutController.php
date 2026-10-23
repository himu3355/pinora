<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\CartService;
use App\Services\GstService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $totals = $this->cart->totalsWithPricing($this->pricing);

        // Pre-validate stock before allowing checkout
        foreach ($totals['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            $variant = !empty($item['variant_id']) ? \App\Models\ProductVariant::find($item['variant_id']) : null;
            $stockHolder = $variant ?? $product;

            if (! $stockHolder || $stockHolder->stock_quantity < $item['quantity']) {
                $available = $stockHolder ? $stockHolder->stock_quantity : 0;
                return redirect()->route('cart.index')->with(
                    'error',
                    "Insufficient stock for '{$item['product_name']}'. Only {$available} available in stock. Please adjust your cart quantity."
                );
            }
        }

        $user      = auth()->user();
        $addresses = $user->addresses()->orderByDesc('is_default')->get();

        // Resolve vendor state from first cart item if available
        $vendorState = 'Maharashtra';
        if (!empty($totals['items'])) {
            $firstItem = reset($totals['items']);
            $firstProduct = \App\Models\Product::find($firstItem['product_id']);
            $vendorState = $firstProduct?->vendor?->state ?? 'Maharashtra';
        }

        // Calculate GST breakdown based on default address or fallback
        $defaultAddress = $addresses->first();
        if ($defaultAddress && !empty($defaultAddress->state)) {
            $calculatedGst = $this->gst->calculate($totals['subtotal'], $vendorState, $defaultAddress->state);
            $gstBreakdown = [
                'type'  => $calculatedGst['type'],
                'cgst'  => $calculatedGst['cgst'],
                'sgst'  => $calculatedGst['sgst'],
                'igst'  => $calculatedGst['igst'],
                'total' => $calculatedGst['total_gst'],
            ];
        } else {
            // Default to IGST preview until address is selected
            $gstBreakdown = [
                'type'  => 'igst',
                'cgst'  => 0,
                'sgst'  => 0,
                'igst'  => $totals['gst_amount'],
                'total' => $totals['gst_amount'],
            ];
        }

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
        $user = auth()->user();
        $hasSavedAddress = $request->filled('address_id');

        $rules = [
            'payment_method' => 'required|in:cod,razorpay',
        ];

        if ($hasSavedAddress) {
            $rules['address_id'] = [
                'required',
                Rule::exists('addresses', 'id')->where('user_id', $user->id),
            ];
        } else {
            $rules['new_address']               = 'required|array';
            $rules['new_address.full_name']      = 'required|string|max:120';
            $rules['new_address.phone']          = 'required|string|max:20';
            $rules['new_address.address_line_1'] = 'required|string|max:255';
            $rules['new_address.address_line_2'] = 'nullable|string|max:255';
            $rules['new_address.landmark']       = 'nullable|string|max:255';
            $rules['new_address.city']           = 'required|string|max:100';
            $rules['new_address.state']          = 'required|string|max:100';
            $rules['new_address.pincode']        = 'required|string|max:10';
        }

        $validated = $request->validate($rules);

        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Resolve shipping address
        if ($hasSavedAddress) {
            // Using existing saved address: NEVER creates a new database row
            $address = Address::where('user_id', $user->id)->findOrFail($validated['address_id']);
        } else {
            $newAddr = $validated['new_address'];

            // Check if user already has an identical address in their address book
            $existingAddress = Address::where('user_id', $user->id)
                ->where('address_line_1', $newAddr['address_line_1'])
                ->where('pincode', $newAddr['pincode'])
                ->where('city', $newAddr['city'])
                ->first();

            if ($existingAddress) {
                $address = $existingAddress;
            } elseif ($request->boolean('save_address', true)) {
                $address = Address::create(array_merge($newAddr, [
                    'user_id' => $user->id,
                    'type'    => 'shipping',
                    'country' => 'India',
                ]));
            } else {
                // Non-persisted instance for order snapshot without adding to address book
                $address = new Address(array_merge($newAddr, [
                    'user_id' => $user->id,
                    'type'    => 'shipping',
                    'country' => 'India',
                ]));
            }
        }

        try {
            // Place order via OrderService
            $order = $this->orderService->placeOrder(
                user:          $user,
                address:       $address,
                paymentMethod: $validated['payment_method'],
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('checkout.index')->with('error', 'Unable to process checkout: ' . $e->getMessage());
        }

        $this->cart->clear();

        if ($validated['payment_method'] === 'razorpay') {
            return redirect()->route('payment.initiate', $order->order_number);
        }

        return redirect()->route('order.confirmation', $order->order_number);
    }
}
