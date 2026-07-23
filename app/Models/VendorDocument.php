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
