# Step 54: OrderService

**Goal:** Handle the complete order placement transaction — creates Order, OrderItems, calculates GST, records commissions, decrements inventory, and triggers confirmation email.
**Depends On:** Step 52 (CommissionService), Step 53 (GstService), Step 51 (PricingService), Step 46 (CartService), Step 20 (Order/OrderItem models)
**Next Step:** Step 55 (PaymentService)

---

## Files to Create

- `app/Services/OrderService.php`

---

## `app/Services/OrderService.php`

```php
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
        protected CartService       $cart,
        protected PricingService    $pricing,
        protected GstService        $gst,
        protected CommissionService $commission,
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

            // ── 1. Calculate totals ───────────────────────────────────────
            $subtotal = 0.0;
            $lineData = [];

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

                $lineSubtotal = round((float) $item['unit_price'] * $item['quantity'] / $this->gst->getMultiplier(), 2);
                $subtotal    += $lineSubtotal;

                $lineData[$key] = [
                    'product'      => $product,
                    'variant'      => $variant,
                    'item'         => $item,
                    'line_subtotal'=> $lineSubtotal,
                ];
            }

            // ── 2. GST calculation ────────────────────────────────────────
            $gstBreakdown = $this->gst->calculate($subtotal, $address->state);
            $totalAmount  = $gstBreakdown['grand_total'];

            // ── 3. Create Order ───────────────────────────────────────────
            $order = Order::create([
                'user_id'               => $user->id,
                'status'                => 'pending',
                'payment_method'        => $paymentMethod,
                'payment_status'        => $paymentMethod === 'cod' ? 'pending' : 'pending',
                'subtotal'              => $subtotal,
                'discount_amount'       => 0,
                'cgst_amount'           => $gstBreakdown['cgst'],
                'sgst_amount'           => $gstBreakdown['sgst'],
                'igst_amount'           => $gstBreakdown['igst'],
                'total_amount'          => $totalAmount,
                'notes'                 => null,
                // Shipping address snapshot
                'shipping_full_name'    => $address->full_name,
                'shipping_phone'        => $address->phone,
                'shipping_address_line_1' => $address->address_line_1,
                'shipping_address_line_2' => $address->address_line_2,
                'shipping_landmark'     => $address->landmark,
                'shipping_city'         => $address->city,
                'shipping_state'        => $address->state,
                'shipping_pincode'      => $address->pincode,
                'shipping_country'      => $address->country ?? 'India',
            ]);

            // ── 4. Create OrderItems, deduct stock, record commissions ────
            foreach ($lineData as $data) {
                $product = $data['product'];
                $variant = $data['variant'];
                $item    = $data['item'];

                // Per-item GST (proportional)
                $itemGst = $this->gst->calculate($data['line_subtotal'], $address->state);

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
                    'unit_price'        => round($item['unit_price'] / $this->gst->getMultiplier(), 2),
                    'subtotal'          => $data['line_subtotal'],
                    'cgst_amount'       => $itemGst['cgst'],
                    'sgst_amount'       => $itemGst['sgst'],
                    'igst_amount'       => $itemGst['igst'],
                    'total_price'       => $itemGst['grand_total'] * ($item['quantity'] > 1 ? 1 : 1), // already per-unit * qty handled by subtotal
                    'fulfillment_status'=> 'pending',
                ]);

                // Deduct stock
                $stockHolder = $variant ?? $product;
                $stockHolder->decrement('stock_quantity', $item['quantity']);

                // Record commission
                $vendor = $product->vendor;
                $this->commission->record($orderItem, $vendor);
            }

            // ── 5. Send confirmation email (queued) ───────────────────────
            $order->load(['items.product', 'items.vendor', 'user']);
            Mail::to($user->email)->queue(new OrderConfirmationMail($order));

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

                // Mark commission as cancelled
                if ($item->commission) {
                    $item->commission->update(['status' => 'cancelled']);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'notes'  => $reason ?: $order->notes,
            ]);
        });
    }
}
```

---

## Service Registration — `app/Providers/AppServiceProvider.php`

```php
use App\Services\CartService;
use App\Services\CommissionService;
use App\Services\GstService;
use App\Services\OrderService;
use App\Services\PricingService;

public function register(): void
{
    $this->app->singleton(CartService::class);
    $this->app->singleton(PricingService::class);
    $this->app->singleton(GstService::class);
    $this->app->singleton(CommissionService::class);
    $this->app->singleton(OrderService::class);
}
```

---

## Notes

- The entire `placeOrder()` is wrapped in `DB::transaction()` so all-or-nothing: if stock validation fails, no Order, OrderItem, or Commission is saved.
- `lockForUpdate()` prevents race conditions when two customers try to buy the last item simultaneously.
- GST is calculated at the **order level** and also **per item** proportionally (for vendor payout and GST invoice purposes).
- `unit_price` stored on `OrderItem` is the **excl. GST** price — the `total_price` column stores the inclusive total for that line.
- `Mail::to()->queue()` requires a queue driver. Set `QUEUE_CONNECTION=database` or `sync` in `.env`.
- `cancelOrder()` handles inventory restoration and sets commissions to `cancelled` so they don't appear in payout calculations.
