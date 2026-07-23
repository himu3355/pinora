# Step 12 — Update User Model

| Field        | Value                        |
|-------------|------------------------------|
| **Goal**    | Update `app/Models/User.php` to add all new fields, relationships, and helper methods |
| **Depends** | Step 01 (users table migration) |
| **Next**    | Step 13 (Vendor & VendorDocument Models) |

---

## Goal Explanation

The users table was extended in Step 01 with new columns: `phone`, `avatar`, `birthday`, `anniversary_date`, `gender`, `ring_size`, `bangle_size`, `customer_tag`, `google_id`, and `status`. This step updates the `User` model to reflect those columns, wire up all platform relationships (Vendor, Address, Wishlist, Order, Review), and add domain-specific helper methods used throughout controllers and Filament resources.

The default Laravel scaffold uses `#[Fillable]` PHP 8 attributes. Because we are adding many custom fields we switch to the explicit `$fillable` array style, which is easier to audit and extend.

Spatie `HasRoles` is already present on the model — we do **not** re-add it.

---

## File to Update

```
app/Models/User.php
```

---

## Complete PHP Code

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'birthday',
        'anniversary_date',
        'gender',
        'ring_size',
        'bangle_size',
        'customer_tag',
        'google_id',
        'default_vendor_id',
        'status',
    ];

    // -------------------------------------------------------------------------
    // Hidden attributes (never serialised)
    // -------------------------------------------------------------------------

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthday'          => 'date',
            'anniversary_date'  => 'date',
            'password'          => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->isForceDeleting()) {
                if ($user->vendor) {
                    if ($user->vendor->orderItems()->exists() || $user->vendor->payouts()->exists()) {
                        throw new \Exception("Cannot permanently delete user because their vendor store has associated order items or payouts.");
                    }
                    $user->vendor->forceDelete();
                }
                if ($user->orders()->exists()) {
                    throw new \Exception("Cannot permanently delete user because they have associated orders.");
                }
            } else {
                $user->vendor()?->delete();
            }
        });

        static::restored(function (User $user): void {
            $user->vendor()->withTrashed()->restore();
        });
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * The vendor profile owned by this user (if the user is a vendor).
     */
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function defaultVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'default_vendor_id');
    }

    /**
     * All saved delivery addresses for this user.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Wishlist entries saved by this user.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Orders placed by this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Product reviews written by this user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Convenience accessor: $user->vendor (eager-loadable via relationship).
     * Returns the related Vendor model or null.
     */
    public function getVendorAttribute(): ?Vendor
    {
        return $this->vendor()->first();
    }

    /**
     * Returns the full public URL for the user's avatar.
     * Falls back to a UI-Avatars generated image if no avatar is stored.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        $name    = urlencode($this->name ?? 'User');
        return "https://ui-avatars.com/api/?name={$name}&background=C9A96E&color=fff&size=128";
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Returns true when this user holds the "vendor" Spatie role.
     */
    public function isVendor(): bool
    {
        return $this->hasRole('vendor');
    }

    /**
     * Returns true when this user holds the "customer" Spatie role.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Returns true when this user holds the "super_admin" Spatie role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Checks whether a given product is in this user's wishlist.
     *
     * @param  int  $productId
     */
    public function hasWishlisted(int $productId): bool
    {
        return $this->wishlists()
            ->where('product_id', $productId)
            ->exists();
    }
}
```

---

## Notes

1. **SoftDeletes & FK Integrity** — The `User` model uses `SoftDeletes` trait to prevent hard database deletions that would trigger MySQL 1451 FK constraint violations (e.g. `order_items_vendor_id_foreign` when deleting vendor owners). Soft deleting a user cascade soft-deletes their related `Vendor` profile while maintaining financial & order record integrity.

2. **`booted()` Deleting Hooks** — When soft deleting a user, their vendor is soft-deleted. When force-deleting a user, checks are performed to ensure no `order_items`, `payouts`, or `orders` exist before allowing permanent removal.

3. **`getVendorAttribute()` vs `vendor()` relationship** — The accessor allows `$user->vendor` without an explicit `->first()` call in calling code. Be careful not to confuse the two: `$user->vendor()` returns the `HasOne` builder, while `$user->vendor` hits the accessor (which itself calls `->first()`). Avoid using both in the same eager-load chain — prefer `$user->load('vendor')` and then `$user->getRelation('vendor')`.

