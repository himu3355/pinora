# Step 19 — Wishlist Model

| Field        | Value                                    |
|-------------|------------------------------------------|
| **Goal**    | Create `app/Models/Wishlist.php`         |
| **Depends** | Step 08 (wishlists table migration)      |
| **Next**    | Step 20 (Order & OrderItem Models)       |

---

## Goal Explanation

The Wishlist model is a simple join-table entity: a user saves a product they like. Because wishlists are never "edited" — only added or removed — there is no `updated_at` timestamp. The `toggle()` static helper provides a clean, atomic add/remove toggle that controllers and API endpoints call without duplicating the "does it already exist?" logic.

---

## File to Create

```
app/Models/Wishlist.php
```

---

## Complete PHP Code

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Disable updated_at — wishlist items are never "updated"
    // -------------------------------------------------------------------------

    public const UPDATED_AT = null;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Toggle a product in a user's wishlist.
     *
     * - If the product is NOT in the wishlist, it is added.
     * - If the product IS in the wishlist, it is removed.
     *
     * Returns true  → the product is NOW in the wishlist (just added).
     * Returns false → the product is NO LONGER in the wishlist (just removed).
     *
     * Usage in a controller:
     *   $wishlisted = Wishlist::toggle(auth()->id(), $product->id);
     *   return response()->json(['wishlisted' => $wishlisted]);
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return bool  true if now wishlisted, false if removed
     */
    public static function toggle(int $userId, int $productId): bool
    {
        $existing = static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        static::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return true;
    }

    /**
     * Returns true if the given product is in the given user's wishlist.
     *
     * @param  int  $userId
     * @param  int  $productId
     */
    public static function isWishlisted(int $userId, int $productId): bool
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Returns an array of product IDs in a user's wishlist.
     * Useful for efficiently marking products as wishlisted on a listing page.
     *
     * @param  int  $userId
     * @return array<int>
     */
    public static function getProductIdsForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();
    }
}
```

---

## Notes

1. **`UPDATED_AT = null`** — Setting the class constant to `null` tells Eloquent not to manage an `updated_at` column for this model. The `wishlists` table migration (Step 08) should therefore only have `created_at`, not `updated_at`.

2. **Unique constraint** — The `wishlists` table should have a composite unique index on `(user_id, product_id)` (added in the migration). The `toggle()` method relies on this to prevent duplicate rows if the endpoint is called concurrently. A DB-level unique constraint is the safety net.

3. **`toggle()` atomicity** — The current implementation uses two separate queries (select then delete/insert). For high-concurrency scenarios, wrap in a transaction or use `firstOrCreate` + conditional delete. For most jewellery platforms with moderate traffic, two queries is fine.

4. **`getProductIdsForUser()` usage** — On a product listing page, call this once per page load and pass the resulting array to the view. In Blade/Livewire/Alpine:
   ```php
   $wishlisted = Wishlist::getProductIdsForUser(auth()->id());
   // In view: in_array($product->id, $wishlisted)
   ```
   This avoids N+1 queries when rendering wishlist icons across a grid.

5. **Guest wishlists** — This model is user-centric (requires `user_id`). For guest wishlist support, store product IDs in the session and merge into the DB wishlist on login. Implement this in a `WishlistService`.
