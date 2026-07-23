# Step 14 — Category Model

| Field        | Value                                  |
|-------------|----------------------------------------|
| **Goal**    | Create `app/Models/Category.php`       |
| **Depends** | Step 03 (categories table migration)   |
| **Next**    | Step 15 (MetalRate Model)              |

---

## Goal Explanation

The `Category` model supports an unlimited-depth tree structure through a self-referential parent/children relationship. Top-level categories (parent_id = null) act as main jewellery types (Rings, Necklaces, Earrings, etc.), and their children represent sub-categories (Solitaire Rings, Engagement Rings, etc.). Slugs are auto-generated on creation so product URLs remain clean and SEO-friendly.

---

## File to Create

```
app/Models/Category.php
```

---

## Complete PHP Code

```php
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
```

---

## Notes

1. **Infinite depth** — The self-referential `parent/children` design supports unlimited nesting. For performance, avoid querying the full tree recursively in a loop; instead load the tree in one query using a package like `staudenmeir/laravel-adjacency-list` if deep hierarchies are required.

2. **`scopeWithActiveChildren`** — Demonstrates how to chain a named scope inside an eager-load closure using a first-class callable. Requires PHP 8.1+.

3. **`breadcrumbs()` method** — Walks the parent chain in PHP (N queries for N levels). For large category trees, cache this result or use a Nested Set / Closure Table implementation.

4. **Slug on update** — Intentionally, the boot hook only fires `on creating`. Changing a category name after creation does NOT auto-update the slug, because slugs may already be indexed by search engines or referenced in URLs. Add an explicit UI control if re-slugging is desired.

5. **`sort_order`** — Children are ordered by `sort_order` in the relationship. The Filament admin resource should expose a drag-to-reorder interface (e.g., using `Filament\Tables\Columns\Sortable` or a custom reorder action).
