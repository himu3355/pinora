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
