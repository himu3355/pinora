<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Redberry\PageBuilderPlugin\Traits\HasPageBuilder;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, HasPageBuilder;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes (aligned with vendors table columns)
    // -------------------------------------------------------------------------

    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'description',
        'logo',
        'banner',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'gst_number',
        'pan_number',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_account_name',
        'bank_name',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'total_sales',
        'total_products',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'total_sales'        => 'decimal:2',
            'bank_account_number'=> 'encrypted',
            'approved_at'        => 'datetime',
        ];
    }

    // =========================================================================
    // Boot — auto-generate store_slug
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Vendor $vendor): void {
            if (empty($vendor->store_slug)) {
                $vendor->store_slug = static::generateUniqueSlug($vendor->store_name);
            }
        });

        static::updating(function (Vendor $vendor): void {
            if ($vendor->isDirty('store_name') && empty($vendor->store_slug)) {
                $vendor->store_slug = static::generateUniqueSlug($vendor->store_name);
            }
        });

        static::deleting(function (Vendor $vendor): void {
            if ($vendor->isForceDeleting()) {
                if ($vendor->orderItems()->exists() || $vendor->payouts()->exists()) {
                    throw new \Exception("Cannot permanently delete vendor store because it has associated order items or payouts.");
                }
            }
        });
    }

    /**
     * Generate a URL-safe slug that is unique across the vendors table.
     */
    protected static function generateUniqueSlug(string $storeName): string
    {
        $base  = Str::slug($storeName);
        $slug  = $base;
        $count = 1;

        while (static::withTrashed()->where('store_slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(VendorSubscription::class)->latestOfMany();
    }

    /**
     * Determine if the vendor currently has an active subscription or free trial.
     */
    public function hasActiveSubscription(): bool
    {
        $sub = $this->subscription;
        return $sub && $sub->isActive();
    }

    /**
     * Get the remaining free trial days.
     */
    public function trialDaysRemaining(): int
    {
        $sub = $this->subscription;
        if (! $sub || $sub->status !== 'trialing' || ! $sub->trial_ends_at) {
            return 0;
        }

        if ($sub->trial_ends_at->isPast()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($sub->trial_ends_at, false));
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

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', 'suspended');
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Full public URL for the vendor's store logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? Storage::url($this->logo)
            : null;
    }

    /**
     * Full public URL for the vendor's store banner.
     */
    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner
            ? Storage::url($this->banner)
            : null;
    }
}
