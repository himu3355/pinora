<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'product_id',
        'path',
        'alt_text',
        'is_primary',
        'sort_order',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // =========================================================================
    // Boot — enforce single primary image per product
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        /**
         * Before saving: if is_primary is being set to true,
         * clear is_primary on every other image for the same product.
         *
         * We use the `saving` hook (fires on both create and update) so the
         * constraint is enforced regardless of how the record is written.
         */
        static::saving(function (ProductImage $image): void {
            if ($image->is_primary) {
                static::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id ?? 0)  // 0 covers new (unsaved) records
                    ->update(['is_primary' => false]);
            }
        });

        /**
         * After deleting the primary image: promote the image with the
         * lowest sort_order to primary so the product is never left
         * without a primary image.
         */
        static::deleted(function (ProductImage $image): void {
            if ($image->is_primary) {
                $next = static::where('product_id', $image->product_id)
                    ->orderBy('sort_order')
                    ->first();

                $next?->update(['is_primary' => true]);
            }
        });
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Returns the full public URL for this image.
     * Uses Laravel's Storage facade so it works with local and S3 disks.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Returns the alt text, falling back to the product name if not set.
     */
    public function getAltAttribute(): string
    {
        return $this->alt_text
            ?? ($this->product?->name ?? 'Product image');
    }
}
