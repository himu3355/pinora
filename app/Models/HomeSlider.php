<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HomeSlider extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge_text',
        'title',
        'subtitle',
        'button_text',
        'button_url',
        'desktop_image',
        'mobile_image',
        'badge_pill_text',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getDesktopImageUrlAttribute(): ?string
    {
        if (empty($this->desktop_image)) {
            return asset('images/pinora/hero-banner.jpg');
        }

        if (Str::startsWith($this->desktop_image, ['http://', 'https://'])) {
            return $this->desktop_image;
        }

        return asset('storage/' . ltrim($this->desktop_image, '/'));
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        if (empty($this->mobile_image)) {
            return $this->desktop_image_url;
        }

        if (Str::startsWith($this->mobile_image, ['http://', 'https://'])) {
            return $this->mobile_image;
        }

        return asset('storage/' . ltrim($this->mobile_image, '/'));
    }
}
