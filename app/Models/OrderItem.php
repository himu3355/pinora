<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'order_id',
        'vendor_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'variant_name',
        'metal_type',
        'purity',
        'weight_grams',
        'metal_rate_used',
        'making_charges',
        'quantity',
        'unit_price',
        'subtotal',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_price',
        'fulfillment_status',
        'tracking_number',
        'tracking_url',
        'courier_name',
        'shipped_at',
        'delivered_at',
        'customization_request',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'weight_grams'     => 'decimal:3',
            'metal_rate_used'  => 'decimal:2',
            'making_charges'   => 'decimal:2',
            'unit_price'       => 'decimal:2',
            'subtotal'         => 'decimal:2',
            'cgst_rate'        => 'decimal:2',
            'sgst_rate'        => 'decimal:2',
            'igst_rate'        => 'decimal:2',
            'cgst_amount'      => 'decimal:2',
            'sgst_amount'      => 'decimal:2',
            'igst_amount'      => 'decimal:2',
            'total_price'      => 'decimal:2',
            'quantity'         => 'integer',
            'shipped_at'       => 'datetime',
            'delivered_at'     => 'datetime',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }


    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByFulfillmentStatus(Builder $query, string $status): Builder
    {
        return $query->where('fulfillment_status', $status);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Returns true when the item has been shipped.
     */
    public function isShipped(): bool
    {
        return $this->fulfillment_status === 'shipped' && ! is_null($this->shipped_at);
    }

    /**
     * Returns true when the item has been delivered.
     */
    public function isDelivered(): bool
    {
        return $this->fulfillment_status === 'delivered' && ! is_null($this->delivered_at);
    }
}
