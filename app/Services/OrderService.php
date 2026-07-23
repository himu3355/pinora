<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function __construct(
        protected CartService    $cart,
        protected PricingService $pricing,
        protected GstService     $gst,
    ) {}

    /**
     * Place a complete order inside a single database transaction.
     *
     * @param  User     $user           The authenticated customer
     * @param  Address  $address        Resolved shipping address
     * @param  string   $paymentMethod  'cod' | 'razorpay'
     * @return Order
     *
     * @throws \Throwable
     */
    public function placeOrder(User $user, Address $address, string $paymentMethod): Order
    {
        $cartItems = $this->cart->totalsWithPricing($this->pricing);

        if (empty($cartItems['items'])) {
            throw new \RuntimeException('Cannot place order: cart is empty.');
        }

        return DB::transaction(function () use ($user, $address, $paymentMethod, $cartItems) {

            // ── 1. Calculate totals & item GST ───────────────────────────
            $subtotal  = 0.0;
            $cgstTotal = 0.0;
            $sgstTotal = 0.0;
            $igstTotal = 0.0;
            $lineData  = [];

            foreach ($cartItems['items'] as $key => $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $variant = $item['variant_id']
                    ? ProductVariant::lockForUpdate()->findOrFail($item['variant_id'])
                    : null;

                // Validate stock
                $stockHolder = $variant ?? $product;
                if ($stockHolder->stock_quantity < $item['quantity']) {
                    throw new \RuntimeException(
                        "Insufficient stock for: {$product->name}. Available: {$stockHolder->stock_quantity}"
                    );
                }

                $lineSubtotal = round((float) $item['unit_price'] * $item['quantity'], 2);
                $subtotal    += $lineSubtotal;

                // Calculate GST for this item based on vendor's state vs shipping state
                $vendor = $product->vendor;
                $vendorState = $vendor->state ?? 'Maharashtra'; // fallback to platform state if not set
                $itemGst = $this->gst->calculate($lineSubtotal, $vendorState, $address->state);

                $cgstTotal += $itemGst['cgst'];
                $sgstTotal += $itemGst['sgst'];
                $igstTotal += $itemGst['igst'];

                $lineData[$key] = [
                    'product'       => $product,
                    'variant'       => $variant,
                    'item'          => $item,
                    'line_subtotal' => $lineSubtotal,
                    'item_gst'      => $itemGst,
                ];
            }

            $totalAmount = round($subtotal + $cgstTotal + $sgstTotal + $igstTotal, 2);

            // ── 2. Create Order ───────────────────────────────────────────
            $order = Order::create([
                'user_id'                 => $user->id,
                'status'                  => 'pending',
                'payment_method'          => $paymentMethod,
                'payment_status'          => 'pending',
                'subtotal'                => $subtotal,
                'discount_amount'         => 0.0,
                'cgst_amount'             => $cgstTotal,
                'sgst_amount'             => $sgstTotal,
                'igst_amount'             => $igstTotal,
                'total_amount'            => $totalAmount,
                'notes'                   => null,
                // Shipping address snapshot
                'shipping_name'           => $address->full_name,
                'shipping_phone'          => $address->phone,
                'shipping_address_line_1' => $address->address_line_1,
                'shipping_address_line_2' => $address->address_line_2,
                'shipping_landmark'       => $address->landmark,
                'shipping_city'           => $address->city,
                'shipping_state'          => $address->state,
                'shipping_pincode'        => $address->pincode,
                'shipping_country'        => $address->country ?? 'India',
            ]);

            // ── 3. Create OrderItems and deduct stock ────
            foreach ($lineData as $data) {
                $product = $data['product'];
                $variant = $data['variant'];
                $item    = $data['item'];
                $itemGst = $data['item_gst'];

                $orderItem = OrderItem::create([
                    'order_id'          => $order->id,
                    'vendor_id'         => $product->vendor_id,
                    'product_id'        => $product->id,
                    'product_variant_id'=> $variant?->id,
                    'product_name'      => $product->name,
                    'variant_name'      => $variant?->name,
                    'sku'               => $variant?->sku ?? $product->sku ?? null,
                    'metal_type'        => $product->metal_type,
                    'purity'            => $product->purity,
                    'weight_grams'      => $variant ? $variant->weight_grams : $product->weight_grams,
                    'making_charges'    => $variant ? $variant->making_charges : $product->making_charges,
                    'quantity'          => $item['quantity'],
                    'unit_price'        => round($item['unit_price'], 2),
                    'subtotal'          => $data['line_subtotal'],
                    'cgst_amount'       => $itemGst['cgst'],
                    'sgst_amount'       => $itemGst['sgst'],
                    'igst_amount'       => $itemGst['igst'],
                    'total_price'       => $itemGst['grand_total'],
                    'fulfillment_status'=> 'pending',
                ]);

                // Deduct stock
                $stockHolder = $variant ?? $product;
                $stockHolder->decrement('stock_quantity', $item['quantity']);

            }

            // ── 4. Send confirmation email (queued) ───────────────────────
            $order->load(['items.product', 'items.vendor', 'user']);
            try {
                Mail::to($user->email)->queue(new OrderConfirmationMail($order));
                $order->update(['confirmation_email_sent_at' => now()]);
            } catch (\Exception $e) {
                // Log mail sending failure but do not roll back the order placement transaction
                report($e);
            }

            return $order;
        });
    }

    /**
     * Cancel an order (admin or customer).
     * - Only orders in 'pending' status can be cancelled.
     * - Restores inventory.
     * - Sets all order items to 'cancelled'.
     *
     * @param  Order   $order
     * @param  string  $reason
     * @return void
     */
    public function cancelOrder(Order $order, string $reason = ''): void
    {
        if (! in_array($order->status, ['pending', 'confirmed'])) {
            throw new \RuntimeException('Only pending or confirmed orders can be cancelled.');
        }

        DB::transaction(function () use ($order, $reason) {
            foreach ($order->items as $item) {
                // Restore stock
                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->increment('stock_quantity', $item->quantity);
                } else {
                    Product::where('id', $item->product_id)
                        ->increment('stock_quantity', $item->quantity);
                }

                $item->update(['fulfillment_status' => 'cancelled']);

            }

            $order->update([
                'status' => 'cancelled',
                'notes'  => $reason ?: $order->notes,
            ]);
        });
    }
}
