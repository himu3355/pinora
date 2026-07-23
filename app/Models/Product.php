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
        'created_by',
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'metal_type',
        'purity',
        'weight_grams',
        'loss',
        'making_charges',
        'making_charges_type',
        'stone_type',
        'stone_weight_carats',
        'stone_quality',
        'certification_type',
        'certification_number',
        'certification_file',
        'certifications',
        'is_customizable',
        'customization_notes',
        'is_featured',
        'is_new_arrival',
        'is_price_on_request',
        'base_price',
        'discount_percent',
        'stock_quantity',
        'min_order_quantity',
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
            'loss'                => 'decimal:3',
            'making_charges'      => 'decimal:2',
            'stone_weight_carats' => 'decimal:3',
            'stone_charges'       => 'decimal:2',
            'base_price'          => 'decimal:2',
            'discount_percent'    => 'decimal:2',
            'stock_quantity'      => 'integer',
            'min_order_quantity'  => 'integer',
            'is_customizable'     => 'boolean',
            'is_featured'         => 'boolean',
            'is_new_arrival'      => 'boolean',
            'is_price_on_request' => 'boolean',
            'certifications'      => 'array',
        ];
    }

    public const AVAILABLE_CERTIFICATIONS = [
        'bis_hallmark' => [
            'label' => 'BIS Hallmark',
            'logo'  => 'images/certifications/bis_hallmark.png',
        ],
        'certified_diamond' => [
            'label' => 'Certified Diamond Jewellery',
            'logo'  => 'images/certifications/certified_diamond.png',
        ],
        'certified_jewellery' => [
            'label' => '100% Certified Jewellery',
            'logo'  => 'images/certifications/certified_jewellery.png',
        ],
        'lifetime_exchange' => [
            'label' => 'Lifetime Exchange and Buy Back',
            'logo'  => 'images/certifications/lifetime_exchange.png',
        ],
    ];

    public function getCertificationBadgesAttribute(): array
    {
        $selected = $this->certifications ?? [];
        if (!is_array($selected)) {
            return [];
        }

        $badges = [];
        foreach ($selected as $key) {
            if (isset(self::AVAILABLE_CERTIFICATIONS[$key])) {
                $badges[$key] = self::AVAILABLE_CERTIFICATIONS[$key];
            }
        }

        return $badges;
    }

    // =========================================================================
    // Boot — auto-generate slug & SKU
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product): void {
            if (empty($product->created_by) && auth()->check()) {
                $product->created_by = auth()->id();
            }

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $query->where('status', 'active')
            ->whereHas('vendor', function (Builder $vendorQuery) {
                $vendorQuery->where('status', 'approved')
                    ->where(function (Builder $q) {
                        $q->whereDoesntHave('subscriptions')
                          ->orWhereHas('subscriptions', function (Builder $subQuery) {
                              $subQuery->where(function (Builder $sub) {
                                  $sub->where('status', 'trialing')
                                      ->where('trial_ends_at', '>=', now());
                              })->orWhere(function (Builder $sub) {
                                  $sub->where('status', 'active');
                              })->orWhere(function (Builder $sub) {
                                  $sub->where('status', 'cancelled')
                                      ->where('ends_at', '>=', now());
                              });
                          });
                    });
            });
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
    public function getCalculatedPriceAttribute(): ?float
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
        $img = $this->primaryImage ?? $this->images->first();
        return $img?->url ?? asset('images/product-placeholder.png');
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
        // Default threshold is 5 if low_stock_threshold is not specified
        $threshold = $this->low_stock_threshold ?? 5;
        return $this->stock_quantity <= $threshold;
    }
}
