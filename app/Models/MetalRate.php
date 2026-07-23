<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MetalRate extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'metal_type',
        'purity',
        'rate_per_gram',
        'effective_date',
        'updated_by',
        'notes',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'rate_per_gram'  => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * The admin user who last entered / updated this rate.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Rates whose effective_date is today or in the past (i.e., currently in force).
     */
    public function scopeForToday(Builder $query): Builder
    {
        return $query->where('effective_date', '<=', Carbon::today());
    }

    /**
     * Filter rates by metal type (e.g. 'gold', 'silver', 'platinum').
     */
    public function scopeForMetal(Builder $query, string $metalType): Builder
    {
        return $query->where('metal_type', $metalType);
    }

    /**
     * Filter rates by purity string (e.g. '22k', '18k', '925').
     */
    public function scopeForPurity(Builder $query, string $purity): Builder
    {
        return $query->where('purity', $purity);
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Return the most recent MetalRate record for the given metal type and purity
     * that has an effective_date on or before today.
     *
     * Usage:
     *   $rate = MetalRate::getLatestRate('gold', '22k');
     *   $pricePerGram = $rate?->rate_per_gram ?? 0;
     *
     * Results are cached for 60 minutes to avoid hammering the DB on every
     * page load. Clear the cache whenever a new rate is saved
     * (e.g. via an Observer or a Service method).
     *
     * @param  string  $metalType  e.g. 'gold', 'silver', 'platinum'
     * @param  string  $purity     e.g. '24k', '22k', '18k', '14k', '925'
     */
    public static function getLatestRate(string $metalType, string $purity): ?self
    {
        $cacheKey = "metal_rate:{$metalType}:{$purity}";

        $value = cache()->get($cacheKey);

        // If cache doesn't exist, is incomplete class, or is not cached as an array, query DB
        if (!cache()->has($cacheKey) || $value instanceof \__PHP_Incomplete_Class || ($value !== null && !is_array($value))) {
            $rate = static::forMetal($metalType)
                ->forPurity($purity)
                ->forToday()
                ->orderByDesc('effective_date')
                ->orderByDesc('id')
                ->first();

            $value = $rate ? $rate->toArray() : null;
            cache()->put($cacheKey, $value, now()->addMinutes(60));
        }

        if ($value === null) {
            return null;
        }

        $model = new static();
        $model->forceFill($value);
        $model->exists = true;

        return $model;
    }

    /**
     * Flush the cached rate for a specific metal + purity combination.
     * Call this from a MetalRateObserver after create/update.
     */
    public static function flushRateCache(string $metalType, string $purity): void
    {
        cache()->forget("metal_rate:{$metalType}:{$purity}");
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Human-readable label combining metal type and purity.
     * Example: "Gold — 22K"
     */
    public function getLabelAttribute(): string
    {
        return ucfirst($this->metal_type) . ' — ' . strtoupper($this->purity);
    }
}
