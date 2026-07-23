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
