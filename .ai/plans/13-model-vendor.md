# Step 13 — Vendor & VendorDocument Models

| Field        | Value                                          |
|-------------|------------------------------------------------|
| **Goal**    | Create `app/Models/Vendor.php` and `app/Models/VendorDocument.php` |
| **Depends** | Step 02 (vendors + vendor_documents migrations) |
| **Next**    | Step 14 (Category Model) |

---

## Goal Explanation

Vendors are the sellers on the Pinora marketplace. Each vendor belongs to exactly one `User` account and can upload multiple identity / business documents for admin verification. These two models power:

- Vendor onboarding flow (status progression: `pending → approved / rejected / suspended`)
- Automatic slug generation for vendor store URLs
- Encrypted storage of sensitive bank account details
- Document upload and verification workflows used in the Filament admin panel

---

## Files to Create

```
app/Models/Vendor.php
app/Models/VendorDocument.php
```

---

## Complete PHP Code

### `app/Models/Vendor.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'store_description',
        'store_logo',
        'store_banner',
        'gstin',
        'pan_number',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_account_name',
        'bank_name',
        'commission_rate',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'total_sales',
        'notes',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'commission_rate'    => 'decimal:2',
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

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
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
        return $this->store_logo
            ? Storage::url($this->store_logo)
            : null;
    }

    /**
     * Full public URL for the vendor's store banner.
     */
    public function getBannerUrlAttribute(): ?string
    {
        return $this->store_banner
            ? Storage::url($this->store_banner)
            : null;
    }
}
```

---

### `app/Models/VendorDocument.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VendorDocument extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'vendor_id',
        'type',
        'file_path',
        'original_name',
        'status',
        'verified_at',
        'notes',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Returns a signed / public URL for the uploaded document file.
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path
            ? Storage::url($this->file_path)
            : null;
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }
}
```

---

## Notes

1. **`bank_account_number` encryption** — The `'encrypted'` cast uses Laravel's built-in `Crypt` facade under the hood. Ensure `APP_KEY` is set before running migrations or seeding; otherwise decryption will throw a `DecryptException`.

2. **Slug uniqueness** — The `generateUniqueSlug()` helper uses `withTrashed()` so that a soft-deleted vendor cannot "free up" a slug for a newly created vendor — preventing stale URL confusion.

3. **`approved_by` column** — Currently stored as a plain integer FK. A relationship to `User::class` can be added later:
   ```php
   public function approvedBy(): BelongsTo
   {
       return $this->belongsTo(User::class, 'approved_by');
   }
   ```

4. **Document types** — Typical values for `VendorDocument.type`: `gstin_certificate`, `pan_card`, `address_proof`, `bank_passbook`, `cancelled_cheque`. Define these as a PHP `enum` (`App\Enums\VendorDocumentType`) in a later step for strict validation.

5. **SoftDeletes on Vendor** — Deleting a vendor soft-deletes their record, preserving order history and commission data. Hard-delete is reserved for GDPR erasure requests (handle separately).
