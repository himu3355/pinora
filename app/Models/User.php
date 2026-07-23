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

    /**
     * Determine if the user is authorized to access a given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole('super_admin');
        }

        if ($panel->getId() === 'vendor') {
            return $this->hasRole(['vendor', 'vendor_staff']) || session()->has('acting_as_vendor_id');
        }

        return false;
    }
}
