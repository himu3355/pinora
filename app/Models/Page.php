<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'content',
        'banner_image',
        'meta_title',
        'meta_description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        if (empty($this->banner_image)) {
            return null;
        }

        if (Str::startsWith($this->banner_image, ['http://', 'https://'])) {
            return $this->banner_image;
        }

        return asset('storage/' . ltrim($this->banner_image, '/'));
    }
}
