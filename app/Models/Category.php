<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Mass-assignable attributes
    // -------------------------------------------------------------------------

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // =========================================================================
    // Boot — auto-generate slug
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Category $category): void {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });
    }

    /**
     * Generate a URL-safe slug unique within the categories table.
     */
    protected static function generateUniqueSlug(string $name): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * The parent category (null for top-level categories).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Direct child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * All products belonging to this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Only return active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Only return top-level categories (no parent).
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Eager-load the full active sub-tree for menu building.
     */
    public function scopeWithActiveChildren(Builder $query): Builder
    {
        return $query->with([
            'children' => fn (HasMany $q) => $q->active()->orderBy('sort_order'),
        ]);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Returns the full public URL for the category image.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::url($this->image)
            : null;
    }

    /**
     * Returns true when this is a root (top-level) category.
     */
    public function getIsTopLevelAttribute(): bool
    {
        return is_null($this->parent_id);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Returns an ordered collection of ancestors from root → this category.
     * Useful for breadcrumb rendering.
     *
     * @return \Illuminate\Support\Collection<int, Category>
     */
    public function breadcrumbs(): \Illuminate\Support\Collection
    {
        $crumbs   = collect();
        $category = $this;

        while ($category !== null) {
            $crumbs->prepend($category);
            $category = $category->parent;
        }

        return $crumbs;
    }
}
