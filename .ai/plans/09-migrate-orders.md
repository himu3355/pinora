# Step 09 — Orders & Order Items Tables

**Phase:** Database Foundation  
**Depends on:** Steps 01, 02, 05, 07 (users, vendors, products, addresses)  
**Next step:** `10-migrate-commissions-payouts.md`

---

## 🎯 Goal

Create two tables:
1. `orders` — One order per checkout (may span multiple vendors)
2. `order_items` — One row per product line, linked to its vendor

This design supports a **multi-vendor cart**: a single checkout creates one `order` but multiple `order_items` grouped by vendor.

---

## 📄 Files to Create

**File 1:** `database/migrations/2026_06_21_000011_create_orders_table.php`  
**File 2:** `database/migrations/2026_06_21_000012_create_order_items_table.php`

---

## 🗃️ `orders` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `order_number` | string, unique | no | e.g. "PIN-2024-00001" |
| `user_id` | foreignId → users | yes | NULL for guest orders |
| `guest_email` | string | yes | For guest orders |
| `guest_phone` | string(20) | yes | For guest orders |
| `status` | enum | no | See statuses below |
| `shipping_name` | string | no | Snapshot of recipient name |
| `shipping_phone` | string(20) | no | Snapshot |
| `shipping_address_line_1` | string | no | Snapshot |
| `shipping_address_line_2` | string | yes | Snapshot |
| `shipping_landmark` | string | yes | Snapshot |
| `shipping_city` | string | no | Snapshot |
| `shipping_state` | string | no | Snapshot (used for GST) |
| `shipping_pincode` | string(10) | no | Snapshot |
| `shipping_country` | string | no | Snapshot, Default: 'India' |
| `subtotal` | decimal(12,2) | no | Before GST/discount |
| `discount_amount` | decimal(12,2) | no | Default: 0 |
| `cgst_amount` | decimal(10,2) | no | Default: 0 |
| `sgst_amount` | decimal(10,2) | no | Default: 0 |
| `igst_amount` | decimal(10,2) | no | Default: 0 |
| `total_amount` | decimal(12,2) | no | Final amount paid |
| `payment_method` | string | yes | 'razorpay', 'cod', etc. |
| `payment_status` | enum('pending','paid','failed','refunded') | no | Default: 'pending' |
| `payment_reference` | string | yes | Gateway transaction ID |
| `paid_at` | timestamp | yes | |
| `notes` | text | yes | Customer's order note |
| `timestamps` | | | |

**Order Statuses:**
```
'pending'          → Order placed, awaiting payment
'confirmed'        → Payment received
'processing'       → Vendor is preparing
'shipped'          → At least one item shipped
'delivered'        → All items delivered
'cancelled'        → Order cancelled
'refund_requested' → Customer requested refund
'refunded'         → Refund processed
```

---

## 🗃️ `order_items` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `order_id` | foreignId → orders | no | |
| `vendor_id` | foreignId → vendors | no | Which vendor fulfills this |
| `product_id` | foreignId → products | yes | nullable (product may be deleted) |
| `product_variant_id` | foreignId → product_variants | yes | nullable |
| `product_name` | string | no | Snapshot |
| `product_sku` | string | yes | Snapshot |
| `variant_name` | string | yes | Snapshot |
| `metal_type` | string | yes | Snapshot |
| `purity` | string(10) | yes | Snapshot |
| `weight_grams` | decimal(8,3) | yes | Snapshot |
| `metal_rate_used` | decimal(10,2) | yes | Rate at time of order |
| `making_charges` | decimal(10,2) | yes | Snapshot |
| `quantity` | unsignedInteger | no | Default: 1 |
| `unit_price` | decimal(12,2) | no | Price per unit (pre-GST) |
| `subtotal` | decimal(12,2) | no | unit_price × quantity |
| `cgst_rate` | decimal(5,2) | no | Default: 0 |
| `sgst_rate` | decimal(5,2) | no | Default: 0 |
| `igst_rate` | decimal(5,2) | no | Default: 0 |
| `cgst_amount` | decimal(10,2) | no | Default: 0 |
| `sgst_amount` | decimal(10,2) | no | Default: 0 |
| `igst_amount` | decimal(10,2) | no | Default: 0 |
| `total_price` | decimal(12,2) | no | subtotal + GST |
| `fulfillment_status` | enum | no | Default: 'pending' |
| `tracking_number` | string | yes | Courier tracking ID |
| `tracking_url` | string | yes | |
| `courier_name` | string | yes | |
| `shipped_at` | timestamp | yes | |
| `delivered_at` | timestamp | yes | |
| `customization_request` | text | yes | If product is customizable |
| `timestamps` | | | |

**Fulfillment Statuses:**
```
'pending'    → Vendor hasn't acted yet
'accepted'   → Vendor confirmed the item
'rejected'   → Vendor rejected (e.g. out of stock)
'processing' → Being made/packed
'shipped'    → Dispatched with tracking
'delivered'  → Customer received
'returned'   → Customer returned
```

---

## 💻 Migration Code — orders

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped',
                'delivered', 'cancelled', 'refund_requested', 'refunded'
            ])->default('pending');

            // Shipping address snapshot
            $table->string('shipping_name');
            $table->string('shipping_phone', 20);
            $table->string('shipping_address_line_1');
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_landmark')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_pincode', 10);
            $table->string('shipping_country')->default('India');

            // Financials
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            // Payment
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

---

## 💻 Migration Code — order_items

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            // Product snapshot (preserved even if product is deleted)
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('metal_type')->nullable();
            $table->string('purity', 10)->nullable();
            $table->decimal('weight_grams', 8, 3)->nullable();
            $table->decimal('metal_rate_used', 10, 2)->nullable();
            $table->decimal('making_charges', 10, 2)->nullable();

            // Quantity & pricing
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);

            // GST breakdown
            $table->decimal('cgst_rate', 5, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(0);
            $table->decimal('igst_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2);

            // Fulfillment
            $table->enum('fulfillment_status', [
                'pending', 'accepted', 'rejected', 'processing',
                'shipped', 'delivered', 'returned'
            ])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('courier_name')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->text('customization_request')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'vendor_id']);
            $table->index(['vendor_id', 'fulfillment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
```

---

## 📋 Order Number Generation

In the `Order` model's `creating` observer:

```php
// Format: PIN-YYYY-NNNNN (e.g. PIN-2024-00001)
$year = now()->year;
$lastOrder = Order::whereYear('created_at', $year)->latest()->first();
$number = $lastOrder ? (intval(substr($lastOrder->order_number, -5)) + 1) : 1;
$model->order_number = 'PIN-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
```

---

## ▶️ Artisan Command

```bash
php artisan migrate
```

---

## 📝 Notes

- Address fields are **snapshots** — not foreign keys to `addresses` table. This is critical: if a customer changes their address, old orders must still show the original delivery address.
- GST is calculated per `order_item`, not per `order`, because different products may have different rates in future.
- `product_id` is nullable with `nullOnDelete` — if a vendor deletes a product, order history is preserved via the snapshot columns.
- The `order_number` is human-friendly for customer service reference.
