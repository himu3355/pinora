# Step 03 — Categories Table

**Phase:** Database Foundation  
**Depends on:** Step 02  
**Next step:** `04-migrate-metal-rates.md`

---

## 🎯 Goal

Create a `categories` table for product categorization.  
Categories support a **single level of nesting** (parent → child) to allow structures like:

```
Gold Jewellery
  ├── Necklaces
  ├── Earrings
  ├── Rings
  └── Bangles
Diamond Jewellery
  ├── Solitaire Rings
  └── Pendants
Silver Jewellery
Fashion / Imitation
Platinum
```

---

## 📄 File to Create

**File:** `database/migrations/2026_06_21_000004_create_categories_table.php`

---

## 🗃️ `categories` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `parent_id` | foreignId → categories | yes | NULL = top-level category |
| `name` | string | no | Display name |
| `slug` | string, unique | no | URL-friendly |
| `description` | text | yes | |
| `image` | string | yes | Category image path |
| `icon` | string | yes | Icon class or SVG name |
| `sort_order` | unsignedSmallInteger | no | Default: 0 |
| `is_active` | boolean | no | Default: true |
| `meta_title` | string | yes | SEO |
| `meta_description` | text | yes | SEO |
| `timestamps` | | | |

---

## 💻 Migration Code

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

---

## 🌱 Seeder (inline — run after migration)

Create `database/seeders/CategorySeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $topLevel = [
            ['name' => 'Gold Jewellery',    'icon' => 'heroicon-o-star'],
            ['name' => 'Diamond Jewellery', 'icon' => 'heroicon-o-sparkles'],
            ['name' => 'Silver Jewellery',  'icon' => 'heroicon-o-moon'],
            ['name' => 'Platinum',          'icon' => 'heroicon-o-cube'],
            ['name' => 'Fashion / Imitation','icon' => 'heroicon-o-shopping-bag'],
        ];

        $subCategories = [
            'Gold Jewellery'    => ['Necklaces', 'Earrings', 'Rings', 'Bangles', 'Bracelets', 'Pendants', 'Chains', 'Anklets'],
            'Diamond Jewellery' => ['Solitaire Rings', 'Pendants', 'Earrings', 'Bracelets'],
            'Silver Jewellery'  => ['Rings', 'Earrings', 'Anklets', 'Bracelets'],
            'Platinum'          => ['Rings', 'Pendants', 'Earrings'],
            'Fashion / Imitation' => ['Necklace Sets', 'Earrings', 'Bangles', 'Maang Tikka'],
        ];

        foreach ($topLevel as $i => $cat) {
            $parent = Category::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']),
                'icon'       => $cat['icon'],
                'sort_order' => $i,
                'is_active'  => true,
            ]);

            foreach ($subCategories[$cat['name']] ?? [] as $j => $sub) {
                Category::create([
                    'parent_id'  => $parent->id,
                    'name'       => $sub,
                    'slug'       => Str::slug($cat['name'] . '-' . $sub),
                    'sort_order' => $j,
                    'is_active'  => true,
                ]);
            }
        }
    }
}
```

Add to `DatabaseSeeder.php`:
```php
$this->call(CategorySeeder::class);
```

---

## ▶️ Artisan Commands

```bash
php artisan migrate
php artisan db:seed --class=CategorySeeder
```

---

## ✅ Verification

```bash
php artisan tinker
# App\Models\Category::count()           // Should be > 0
# App\Models\Category::whereNull('parent_id')->count()  // Should be 5
```

---

## 📝 Notes

- Only **two levels** of nesting are needed for this project. The `parent_id` self-reference allows one level of children.
- Do NOT implement unlimited nesting (nested sets / closure table) — it adds complexity without value for this use case.
- `slug` is auto-generated in the model using a `creating` observer (implemented in Step 14).
- `sort_order` controls display order in the storefront.
