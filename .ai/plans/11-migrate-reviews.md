# Step 11 — Reviews Table

**Phase:** Database Foundation  
**Depends on:** Steps 01 (users), 05 (products), 09 (order_items)  
**Next step:** `12-model-user.md`

---

## 🎯 Goal

Create a `reviews` table for customer product reviews.  
Reviews require **admin approval** before going live.  
Only customers who have **purchased** the product can submit a review.

---

## 📄 File to Create

**File:** `database/migrations/2026_06_21_000015_create_reviews_table.php`

---

## 🗃️ `reviews` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `product_id` | foreignId → products | no | |
| `user_id` | foreignId → users | no | Reviewer |
| `order_item_id` | foreignId → order_items | yes | Verifies purchase; nullable for future flexibility |
| `rating` | unsignedTinyInteger | no | 1–5 |
| `title` | string | yes | Review headline |
| `body` | text | yes | Detailed review |
| `status` | enum('pending','approved','rejected') | no | Default: 'pending' |
| `admin_note` | text | yes | Why rejected (visible to vendor, not customer) |
| `approved_at` | timestamp | yes | |
| `approved_by` | foreignId → users | yes | Admin who approved |
| `is_verified_purchase` | boolean | no | Default: false. True if order_item_id is set and delivered |
| `helpful_count` | unsignedInteger | no | Default: 0 (future: "Was this helpful?") |
| `timestamps` | | | |

**Unique constraint:** (`product_id`, `user_id`) — one review per user per product.

---

## 💻 Migration Code

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')
                  ->nullable()
                  ->constrained('order_items')
                  ->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->boolean('is_verified_purchase')->default(false);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'user_id']);
            $table->index(['product_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
```

---

## 🔄 Review Submission Flow

```
1. Customer visits a product page
2. System checks: has this user ordered this product AND it was delivered?
   → Yes: Show "Write a Review" form
   → No: Show "Purchase this product to leave a review"
3. Customer submits review
4. Review created with status = 'pending'
5. Admin sees pending review in Admin Panel (Step 30)
6. Admin approves → status = 'approved', approved_at = now()
7. Review appears on product page
```

---

## ⭐ Rating Summary (cached on products table — future optimization)

When displaying product ratings, calculate:

```php
// Efficient aggregate query
$summary = Review::where('product_id', $productId)
                 ->where('status', 'approved')
                 ->selectRaw('COUNT(*) as total, AVG(rating) as average, 
                              SUM(rating = 5) as five_star,
                              SUM(rating = 4) as four_star,
                              SUM(rating = 3) as three_star,
                              SUM(rating = 2) as two_star,
                              SUM(rating = 1) as one_star')
                 ->first();
```

---

## ▶️ Artisan Command

```bash
php artisan migrate
```

---

## ✅ Verification

```bash
php artisan tinker
# Schema::getColumnListing('reviews')
```

---

## 📝 Notes

- `is_verified_purchase` flag shows a "✓ Verified Purchase" badge on the storefront.
- A user can only leave **one review per product** (unique constraint) — but can edit it (re-submit sets status back to 'pending').
- `helpful_count` is a placeholder for a future "Was this helpful?" feature.
- Vendor can **view** approved reviews for their products in the Vendor Panel (Step 36) but CANNOT modify or delete them.
- Only admin can reject/delete reviews.

---

## ✅ All Database Migrations Complete!

After this step, run all migrations together:

```bash
php artisan migrate:fresh --seed
```

This creates the complete database schema for the Pinora platform.

**Migration files created across Steps 01–11:**
```
2026_06_21_000001_add_customer_fields_to_users_table
2026_06_21_000002_create_vendors_table
2026_06_21_000003_create_vendor_documents_table
2026_06_21_000004_create_categories_table
2026_06_21_000005_create_metal_rates_table
2026_06_21_000006_create_products_table
2026_06_21_000007_create_product_variants_table
2026_06_21_000008_create_product_images_table
2026_06_21_000009_create_addresses_table
2026_06_21_000010_create_wishlists_table
2026_06_21_000011_create_orders_table
2026_06_21_000012_create_order_items_table
2026_06_21_000013_create_commissions_table
2026_06_21_000014_create_payouts_table
2026_06_21_000015_create_reviews_table
```
