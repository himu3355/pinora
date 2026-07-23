# Step 17 — ProductImage Model

| Field        | Value                                    |
|-------------|------------------------------------------|
| **Goal**    | Create `app/Models/ProductImage.php`     |
| **Depends** | Step 06 (product_images table migration) |
| **Next**    | Step 18 (Address Model)                  |

---

## Goal Explanation

Each product can have multiple gallery images. One image must be flagged as the primary display image. The boot hook enforces the single-primary-image constraint at the model layer: whenever `is_primary = true` is set on a `ProductImage`, all sibling images for the same product are automatically set to `is_primary = false`, preventing duplicate primaries regardless of how the data is written (Filament, API, seeder, etc.).

---

## File to Create

```
app/Models/ProductImage.php
```

---

## Complete PHP Code

```php
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
```

---

## Notes

1. **Race condition on concurrent saves** — The `saving` hook issues a bulk `UPDATE` before the triggering record is persisted. In high-concurrency scenarios (API endpoints), wrap in a DB transaction or use a database-level partial unique index (`WHERE is_primary = true`) to guarantee consistency.

2. **New record `id`** — When the hook fires on `creating`, `$image->id` is still `null`. The `??= 0` fallback ensures the `WHERE id != 0` clause never accidentally excludes any row.

3. **Deleted primary promotion** — The `deleted` hook auto-promotes the next image. If the product has no remaining images, `$next` is `null` and `?->update()` is a safe no-op.

4. **S3 / CloudFront** — If the project later moves to S3, `Storage::url()` will return the correct signed or public URL based on the configured disk — no model changes needed.

5. **Filament integration** — In the Filament `ProductResource`, use `SpatieMediaLibraryFileUpload` or a custom `FileUpload` repeater that maps to this model. Set `is_primary` on the first uploaded image automatically in the resource's `afterCreate` hook.

6. **sort_order reordering** — Consider adding a Filament `ReorderAction` or a drag-and-drop image gallery component in the vendor panel. Persist reordering via a dedicated endpoint that updates `sort_order` in bulk.
