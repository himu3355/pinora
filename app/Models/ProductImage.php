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
         * Before creating: if the product has no primary image yet,
         * set this image as primary automatically.
         */
        static::creating(function (ProductImage $image): void {
            if ($image->product_id && !static::where('product_id', $image->product_id)->where('is_primary', true)->exists()) {
                $image->is_primary = true;
            }
        });

        /**
         * Before saving: if is_primary is being set to true,
         * clear is_primary on every other image for the same product.
         */
        static::saving(function (ProductImage $image): void {
            if ($image->is_primary && $image->product_id) {
                static::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id ?? 0)
                    ->update(['is_primary' => false]);
            }
        });

        /**
         * After deleting the primary image: promote the image with the
         * lowest sort_order to primary so the product is never left
         * without a primary image.
         */
        static::deleted(function (ProductImage $image): void {
            if ($image->is_primary && $image->product_id) {
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
     */
    public function getUrlAttribute(): string
    {
        if (empty($this->path)) {
            return asset('images/product-placeholder.png');
        }

        if (\Illuminate\Support\Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return asset('storage/' . ltrim($this->path, '/'));
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
