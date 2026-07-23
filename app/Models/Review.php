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
