# Step 22 — Review Model

| Field        | Value                                    |
|-------------|------------------------------------------|
| **Goal**    | Create `app/Models/Review.php`           |
| **Depends** | Step 11 (reviews table migration)        |
| **Next**    | Step 23 (Seeders)                        |

---

## Goal Explanation

Product reviews drive trust and conversion on the marketplace. A review is tied to a specific `product`, `user`, and optionally an `order_item` (to flag it as a verified purchase — the customer actually bought the item). Reviews require admin moderation before appearing on the storefront (`status = 'pending' → 'approved' | 'rejected'`). The `getRatingStarsAttribute()` accessor returns a visual star string for quick rendering in email templates, PDFs, or plain-text contexts.

---

## File to Create

```
app/Models/Review.php
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

class Review extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'product_id',
        'user_id',
        'order_item_id',
        'rating',
        'title',
        'body',
        'status',
        'admin_note',
        'approved_at',
        'approved_by',
        'is_verified_purchase',
        'helpful_count',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'rating'               => 'integer',
            'helpful_count'        => 'integer',
            'approved_at'          => 'datetime',
            'is_verified_purchase' => 'boolean',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The order item that prompted this review (nullable for non-purchase reviews).
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * The admin who approved or rejected this review.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Only verified purchase reviews (linked to an order item).
     */
    public function scopeVerifiedPurchase(Builder $query): Builder
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Filter by minimum star rating.
     */
    public function scopeMinRating(Builder $query, int $min): Builder
    {
        return $query->where('rating', '>=', $min);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Returns a visual star string for the review's rating.
     *
     * Examples:
     *   rating 5 → "★★★★★"
     *   rating 3 → "★★★☆☆"
     *   rating 1 → "★☆☆☆☆"
     *
     * Uses Unicode filled star (U+2605) and empty star (U+2606).
     */
    public function getRatingStarsAttribute(): string
    {
        $rating = max(0, min(5, (int) $this->rating));
        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }

    /**
     * Returns the rating as a human-readable label.
     * Example: "4 out of 5 stars"
     */
    public function getRatingLabelAttribute(): string
    {
        return "{$this->rating} out of 5 stars";
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Approve this review. Saves the model.
     *
     * @param  int  $approvingUserId  The admin's user ID.
     */
    public function approve(int $approvingUserId): void
    {
        $this->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvingUserId,
        ]);
    }

    /**
     * Reject this review with an optional admin note. Saves the model.
     *
     * @param  int     $approvingUserId
     * @param  string  $adminNote
     */
    public function reject(int $approvingUserId, string $adminNote = ''): void
    {
        $this->update([
            'status'      => 'rejected',
            'approved_at' => now(),
            'approved_by' => $approvingUserId,
            'admin_note'  => $adminNote,
        ]);
    }

    /**
     * Increment the "helpful" vote count atomically.
     * Safe for concurrent requests.
     */
    public function markHelpful(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Returns true if this review is linked to a completed purchase.
     */
    public function isVerified(): bool
    {
        return $this->is_verified_purchase && ! is_null($this->order_item_id);
    }
}
```

---

## Notes

1. **`is_verified_purchase` flag** — Set by the `OrderService` when an order item reaches `delivered` status and the customer submits a review. The `OrderItem` has a `review()` hasOne relationship, so uniqueness (one review per order item) is enforced at the relationship + DB unique constraint level (`UNIQUE(order_item_id)` in the migration).

2. **Star accessor encoding** — `★` (U+2605) and `☆` (U+2606) are standard Unicode characters supported in all modern fonts. For HTML, you may prefer to emit `<i class="star filled"></i>` tags — do that in a Blade component or Vue component instead, keeping the model accessor for text-only contexts.

3. **`approve()` / `reject()` helpers** — These methods live on the model for convenience but should be called from a `ReviewModerationService` in complex workflows (e.g., sending email notifications to the customer on approval). Do not put notification dispatch logic inside the model.

4. **`helpful_count`** — Incremented via `$this->increment()` which issues a single `UPDATE reviews SET helpful_count = helpful_count + 1` — safe for concurrent AJAX requests without a race condition. Consider rate-limiting "helpful" votes per user per review (store in a separate `review_helpful_votes` pivot table in a future step).

5. **Spam & abuse** — A future step can add a `reports_count` column and automatically set `status = 'flagged'` when a review receives more than N reports. Flag triggers an admin notification.

6. **`Product::reviews()` relationship** — Note that `Product::reviews()` (Step 16) filters `status = 'approved'` by default. The `Review` model itself has no such default scope — raw queries return all statuses. This separation keeps admin queries (needing all statuses) clean while the public API returns only approved reviews through the product relationship.
