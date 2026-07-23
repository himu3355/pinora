# Step 16 — Product & ProductVariant Models

| Field        | Value                                          |
|-------------|------------------------------------------------|
| **Goal**    | Create `app/Models/Product.php` and `app/Models/ProductVariant.php` |
| **Depends** | Step 05 (products + product_variants migrations) |
| **Next**    | Step 17 (ProductImage Model) |

---

## Goal Explanation

Products are the core catalogue entity. Each product belongs to one vendor and one category, can have multiple images, multiple size/weight variants, customer reviews, and order line items. The `Product` model exposes a `calculated_price` accessor that delegates to `PricingService` (injected via the container) so pricing logic is never duplicated in views or controllers.

`ProductVariant` represents optional size / weight / material variations of the same base product (e.g., a ring available in sizes 10–20). If a product has no variants, its own weight and charges are used directly for pricing.

---

## Files to Create

```
app/Models/Product.php
app/Models/ProductVariant.php
```

---

## Complete PHP Code

### `app/Models/Product.php`

```php
<?php

namespace App\Models;

use App\Services\PricingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'metal_type',
        'purity',
        'weight_grams',
        'making_charges',
        'stone_type',
        'stone_weight_carats',
        'stone_charges',
        'base_price',
        'discount_percent',
        'tax_class',
        'stock_quantity',
        'low_stock_threshold',
        'is_customizable',
        'certifications',
        'is_featured',
        'is_new_arrival',
        'is_price_on_request',
        'status',
        'meta_title',
        'meta_description',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'weight_grams'        => 'decimal:3',
            'making_charges'      => 'decimal:2',
            'stone_weight_carats' => 'decimal:3',
            'stone_charges'       => 'decimal:2',
            'base_price'          => 'decimal:2',
            'discount_percent'    => 'decimal:2',
            'stock_quantity'      => 'integer',
            'low_stock_threshold' => 'integer',
            'is_customizable'     => 'boolean',
            'is_featured'         => 'boolean',
            'is_new_arrival'      => 'boolean',
            'is_price_on_request' => 'boolean',
            'certifications'      => 'array',
        ];
    }

    // =========================================================================
    // Boot — auto-generate slug
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }

            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(8));
            }
        });
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * All images ordered by sort_order ascending.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * The single image flagged as primary.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * All active variants ordered by sort_order.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Only approved reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeNewArrivals(Builder $query): Builder
    {
        return $query->where('is_new_arrival', true);
    }

    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Returns the live calculated price via PricingService.
     * The accessor resolves PricingService from the IoC container so
     * it can be mocked in tests.
     *
     * Returns null when is_price_on_request = true.
     */
    public function getCalculatedPriceAttribute(): ?string
    {
        if ($this->is_price_on_request) {
            return null;
        }

        /** @var PricingService $pricing */
        $pricing = app(PricingService::class);

        return $pricing->calculateForProduct($this);
    }

    /**
     * Returns the URL of the primary image, or a transparent 1×1 placeholder.
     */
    public function getPrimaryImageUrlAttribute(): string
    {
        return $this->primaryImage?->url
            ?? asset('images/product-placeholder.png');
    }

    /**
     * Average review rating (0–5), rounded to 1 decimal place.
     */
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
    }

    /**
     * Returns true when stock is at or below the low_stock_threshold.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }
}
```

---

### `app/Models/ProductVariant.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'weight_grams',
        'making_charges',
        'base_price',
        'stock_quantity',
        'sort_order',
        'is_active',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'weight_grams'   => 'decimal:3',
            'making_charges' => 'decimal:2',
            'base_price'     => 'decimal:2',
            'stock_quantity' => 'integer',
            'sort_order'     => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
```

---

## Notes

1. **`PricingService` dependency in accessor** — Using `app(PricingService::class)` inside an Eloquent accessor is intentional but keep in mind it is resolved from the container on every access. Wrap results in a request-level cache (`once()` helper from Laravel 11+) if this accessor is called multiple times per request.

2. **`base_price` vs `calculated_price`** — `base_price` is a vendor-set override price. If set, `PricingService` may use it directly (bypassing metal-rate calculation). If null, the service computes price from `weight_grams × rate_per_gram + making_charges`.

3. **Variant-level pricing** — `ProductVariant` does not expose its own `calculated_price` accessor. Instead, `PricingService::calculateForVariant(ProductVariant $variant)` should be called explicitly, which merges the variant's weight/charges with the parent product's metal type and purity.

4. **`SoftDeletes` on Product** — Ensures that order history referencing a deleted product still resolves correctly. Always use `withTrashed()` in order display views.

5. **Random SKU generation** — In production, replace `Str::random(8)` with a sequential SKU based on category prefix + auto-increment (e.g., `RING-0001`). This is a placeholder for early development.
