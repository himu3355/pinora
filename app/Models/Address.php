<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Indian States & Union Territories (28 States + 8 UTs)
    // -------------------------------------------------------------------------

    public const INDIAN_STATES = [
        // States
        'AN' => 'Andaman and Nicobar Islands',
        'AP' => 'Andhra Pradesh',
        'AR' => 'Arunachal Pradesh',
        'AS' => 'Assam',
        'BR' => 'Bihar',
        'CG' => 'Chhattisgarh',
        'CH' => 'Chandigarh',
        'DN' => 'Dadra and Nagar Haveli and Daman and Diu',
        'DL' => 'Delhi',
        'GA' => 'Goa',
        'GJ' => 'Gujarat',
        'HR' => 'Haryana',
        'HP' => 'Himachal Pradesh',
        'JK' => 'Jammu and Kashmir',
        'JH' => 'Jharkhand',
        'KA' => 'Karnataka',
        'KL' => 'Kerala',
        'LA' => 'Ladakh',
        'LD' => 'Lakshadweep',
        'MP' => 'Madhya Pradesh',
        'MH' => 'Maharashtra',
        'MN' => 'Manipur',
        'ML' => 'Meghalaya',
        'MZ' => 'Mizoram',
        'NL' => 'Nagaland',
        'OD' => 'Odisha',
        'PY' => 'Puducherry',
        'PB' => 'Punjab',
        'RJ' => 'Rajasthan',
        'SK' => 'Sikkim',
        'TN' => 'Tamil Nadu',
        'TS' => 'Telangana',
        'TR' => 'Tripura',
        'UP' => 'Uttar Pradesh',
        'UK' => 'Uttarakhand',
        'WB' => 'West Bengal',
    ];

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'user_id',
        'type',
        'label',
        'full_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'landmark',
        'city',
        'state',
        'pincode',
        'country',
        'is_default',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // =========================================================================
    // Boot — enforce single default address per user
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        /**
         * Before saving: if is_default is being set to true,
         * clear is_default on all other addresses for the same user.
         */
        static::saving(function (Address $address): void {
            if ($address->is_default) {
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });

        /**
         * After deleting the default address: promote the most recently
         * updated remaining address to default.
         */
        static::deleted(function (Address $address): void {
            if ($address->is_default) {
                $next = static::where('user_id', $address->user_id)
                    ->orderByDesc('updated_at')
                    ->first();

                $next?->update(['is_default' => true]);
            }
        });
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Only return the default address(es) — normally one per user.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Filter addresses by type (e.g. 'shipping', 'billing').
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Returns a formatted multi-line address string suitable for display
     * on shipping labels, order confirmations, and email templates.
     *
     * Example output:
     *   Priya Sharma
     *   +91 98765 43210
     *   42, MG Road, Near City Mall
     *   Mumbai — 400001
     *   Maharashtra, India
     */
    public function getFormattedAddressAttribute(): string
    {
        $lines = array_filter([
            $this->full_name,
            $this->phone,
            trim(implode(', ', array_filter([
                $this->address_line_1,
                $this->address_line_2,
            ]))),
            $this->landmark ? "Near {$this->landmark}" : null,
            trim("{$this->city} — {$this->pincode}"),
            trim(implode(', ', array_filter([
                $this->getStateName(),
                $this->country ?? 'India',
            ]))),
        ]);

        return implode("\n", $lines);
    }

    /**
     * Returns the full state name from the INDIAN_STATES map, or the raw
     * state value if not found (handles non-Indian addresses gracefully).
     */
    public function getStateName(): string
    {
        return self::INDIAN_STATES[$this->state] ?? ($this->state ?? '');
    }

    /**
     * Short human-friendly label shown in address selectors.
     * Example: "Home — 42 MG Road, Mumbai"
     */
    public function getShortLabelAttribute(): string
    {
        $type  = ucfirst($this->type ?? $this->label ?? 'Address');
        $line1 = $this->address_line_1 ?? '';
        $city  = $this->city ?? '';

        return "{$type} — {$line1}, {$city}";
    }
}
