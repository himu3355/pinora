<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Payout extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'vendor_id',
        'payout_reference',
        'period_from',
        'period_to',
        'total_orders_amount',
        'total_vendor_earnings',
        'adjustments',
        'final_payout_amount',
        'bank_account_name',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_name',
        'status',
        'payment_reference',
        'notes',
        'processed_by',
        'paid_at',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'period_from'             => 'date',
            'period_to'               => 'date',
            'total_orders_amount'     => 'decimal:2',
            'total_vendor_earnings'   => 'decimal:2',
            'adjustments'             => 'decimal:2',
            'final_payout_amount'     => 'decimal:2',
            'bank_account_number'     => 'encrypted',
            'paid_at'                 => 'datetime',
        ];
    }

    // =========================================================================
    // Boot — auto-generate payout_reference
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payout $payout): void {
            if (empty($payout->payout_reference)) {
                $payout->payout_reference = static::generatePayoutReference();
            }
        });
    }

    /**
     * Generates a unique payout reference in the format PAY-YYYY-NNNNN.
     * Example: PAY-2026-00007
     */
    protected static function generatePayoutReference(): string
    {
        $year = Carbon::now()->year;

        $last = static::where('payout_reference', 'like', "PAY-{$year}-%")
            ->orderByDesc('payout_reference')
            ->value('payout_reference');

        $sequence = 1;
        if ($last) {
            $parts    = explode('-', $last);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('PAY-%d-%05d', $year, $sequence);
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }


    /**
     * The admin user who processed / confirmed this payout.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Payouts that are still being prepared (not yet sent to the bank).
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Payouts that have been processed but not paid.
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', 'processed');
    }

    /**
     * Payouts that have been successfully transferred.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Payouts for a specific vendor.
     */
    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Marks the payout as paid.
     * Should be called inside a DB transaction.
     *
     * @param  int  $processedByUserId
     */
    public function markAsPaid(int $processedByUserId): void
    {
        $this->update([
            'status'       => 'paid',
            'paid_at'      => now(),
            'processed_by' => $processedByUserId,
        ]);

    }

    /**
     * Returns a formatted period string for display.
     * Example: "01 Jun 2026 – 30 Jun 2026"
     */
    public function getPeriodLabelAttribute(): string
    {
        if (!$this->period_from || !$this->period_to) {
            return '';
        }

        return $this->period_from->format('d M Y')
            . ' – '
            . $this->period_to->format('d M Y');
    }
}
