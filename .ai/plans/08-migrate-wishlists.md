# Step 08 — Wishlists Table

**Phase:** Database Foundation  
**Depends on:** Step 01 (users), Step 05 (products)  
**Next step:** `09-migrate-orders.md`

---

## 🎯 Goal

Create a `wishlists` table that allows customers to save products for later.  
A wishlist is a simple pivot between `users` and `products`.

---

## 📄 File to Create

**File:** `database/migrations/2026_06_21_000010_create_wishlists_table.php`

---

## 🗃️ `wishlists` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `user_id` | foreignId → users | no | |
| `product_id` | foreignId → products | no | |
| `created_at` | timestamp | yes | When wishlisted |

**Unique constraint:** (`user_id`, `product_id`) — a product can only be wishlisted once per user.

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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
```

---

## 🔄 Toggle Behavior

The wishlist "heart" button on the storefront works as a toggle:

```php
// WishlistController@toggle
$exists = Wishlist::where('user_id', auth()->id())
                  ->where('product_id', $productId)
                  ->exists();

if ($exists) {
    Wishlist::where(...)->delete();  // Remove
    return response()->json(['wishlisted' => false]);
} else {
    Wishlist::create([...]);         // Add
    return response()->json(['wishlisted' => true]);
}
```

- Works via AJAX on the storefront — no page reload.
- Guest users → redirected to login/register before wishlisting.

---

## ▶️ Artisan Command

```bash
php artisan migrate
```

---

## ✅ Verification

```bash
php artisan tinker
# Schema::getColumnListing('wishlists')
```

---

## 📝 Notes

- No `updated_at` column — wishlist items are either in or out, no updates.
- The `My Wishlist` page (Step 49) shows the product's current price (recalculated live), not the price when wishlisted.
- Wishlist count is shown in the navbar header (loaded via a Livewire component or AJAX).
- If a product is deleted (soft deleted), the wishlist record is also cascade-deleted.
