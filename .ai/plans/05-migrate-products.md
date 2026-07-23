# Step 05 — Products & Product Variants Tables

**Phase:** Database Foundation  
**Depends on:** Steps 02 (vendors), 03 (categories), 04 (metal_rates)  
**Next step:** `06-migrate-product-images.md`

---

## 🎯 Goal

Create two tables:
1. `products` — Core product listing with jewellery-specific attributes
2. `product_variants` — Size/weight/finish variations of a product

---

## 📄 Files to Create

**File 1:** `database/migrations/2026_06_21_000006_create_products_table.php`  
**File 2:** `database/migrations/2026_06_21_000007_create_product_variants_table.php`

---

## 🗃️ `products` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `vendor_id` | foreignId → vendors | no | Which vendor sells this |
| `category_id` | foreignId → categories | no | |
| `name` | string | no | Product name |
| `slug` | string, unique | no | URL slug |
| `description` | longText | yes | Rich text description |
| `short_description` | text | yes | For listing cards |
| `sku` | string | yes | unique, nullable |
| `metal_type` | enum('gold','silver','platinum','other') | no | |
| `purity` | string(10) | yes | '22K', '925', etc. |
| `weight_grams` | decimal(8,3) | yes | Net weight in grams |
| `loss` | decimal(8,3) | yes | Weight loss during making (grams) |
| `making_charges` | decimal(10,2) | no | Default: 0 |
| `making_charges_type` | enum('fixed','per_gram') | no | Default: 'fixed' |
| `stone_type` | string | yes | Diamond, Ruby, Emerald, etc. |
| `stone_weight_carats` | decimal(8,3) | yes | |
| `stone_quality` | string | yes | VVS1, VS1, etc. |
| `certification_type` | enum('gia','igi','bis_hallmark','sjc','none') | no | Default: 'none' |
| `certification_number` | string | yes | Cert number |
| `certification_file` | string | yes | Uploaded certificate path |
| `certifications` | json | yes | Selected certification checkboxes array ('bis_hallmark', 'certified_diamond', 'certified_jewellery', 'lifetime_exchange') |
| `is_customizable` | boolean | no | Default: false (bespoke orders) |
| `customization_notes` | text | yes | What can be customized |
| `is_featured` | boolean | no | Default: false |
| `is_new_arrival` | boolean | no | Default: true |
| `is_price_on_request` | boolean | no | Default: false (for very high-value items) |
| `base_price` | decimal(12,2) | yes | Override: if set, ignore metal rate formula |
| `discount_percent` | decimal(5,2) | no | Default: 0.00 |
| `stock_quantity` | unsignedInteger | no | Default: 1 |
| `min_order_quantity` | unsignedInteger | no | Default: 1 |
| `status` | enum('draft','active','inactive','out_of_stock') | no | Default: 'draft' |
| `meta_title` | string | yes | SEO |
| `meta_description` | text | yes | SEO |
| `timestamps` | | | |
| `softDeletes` | | | |

---

## 🗃️ `product_variants` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `product_id` | foreignId → products | no | |
| `name` | string | no | e.g. "Size 16", "Yellow Gold Finish" |
| `sku` | string | yes | unique, nullable |
| `weight_grams` | decimal(8,3) | yes | Overrides product weight if set |
| `making_charges` | decimal(10,2) | yes | Overrides product making charges if set |
| `base_price` | decimal(12,2) | yes | Override price for this variant |
| `stock_quantity` | unsignedInteger | no | Default: 0 |
| `sort_order` | unsignedSmallInteger | no | Default: 0 |
| `is_active` | boolean | no | Default: true |
| `timestamps` | | | |

---

## 💻 Migration Code — products

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->enum('metal_type', ['gold', 'silver', 'platinum', 'other']);
            $table->string('purity', 10)->nullable();
            $table->decimal('weight_grams', 8, 3)->nullable();
            $table->decimal('making_charges', 10, 2)->default(0);
            $table->enum('making_charges_type', ['fixed', 'per_gram'])->default('fixed');
            $table->string('stone_type')->nullable();
            $table->decimal('stone_weight_carats', 8, 3)->nullable();
            $table->string('stone_quality')->nullable();
            $table->enum('certification_type', ['gia', 'igi', 'bis_hallmark', 'sjc', 'none'])->default('none');
            $table->string('certification_number')->nullable();
            $table->string('certification_file')->nullable();
            $table->boolean('is_customizable')->default(false);
            $table->text('customization_notes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(true);
            $table->boolean('is_price_on_request')->default(false);
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->unsignedInteger('stock_quantity')->default(1);
            $table->unsignedInteger('min_order_quantity')->default(1);
            $table->enum('status', ['draft', 'active', 'inactive', 'out_of_stock'])->default('draft');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('is_featured');
            $table->index('is_new_arrival');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

---

## 💻 Migration Code — product_variants

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->decimal('weight_grams', 8, 3)->nullable();
            $table->decimal('making_charges', 10, 2)->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
```

---

## 💡 Pricing Logic (how base_price and metal rates interact)

```
if (product.base_price is set):
    use base_price directly (vendor has set a fixed price)
else:
    metal_rate = MetalRate::latest for (metal_type, purity)
    if (making_charges_type == 'fixed'):
        calculated = (weight_grams × metal_rate) + making_charges
    else: // per_gram
        calculated = weight_grams × (metal_rate + making_charges)

apply discount:
    final_metal_price = calculated × (1 - discount_percent / 100)

add GST (3% for jewellery):
    final_price = final_metal_price × 1.03
```

---

## ▶️ Artisan Command

```bash
php artisan migrate
```

---

## 📝 Notes

- `is_price_on_request` hides price and shows "Request Price" button — for ultra-high-value items.
- `base_price` override is useful for fashion jewellery (no metal rate formula needed).
- `softDeletes` — deleting a product marks it as deleted but preserves order history.
- Variants inherit the parent's `metal_type`, `purity`, and `certification_type` but can override `weight_grams` and `making_charges`.
- No `tags` table in V1 — `is_featured` and `is_new_arrival` flags handle curated collections.
