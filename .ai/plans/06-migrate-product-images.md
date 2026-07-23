# Step 06 — Product Images Table

**Phase:** Database Foundation  
**Depends on:** Step 05 (products table)  
**Next step:** `07-migrate-addresses.md`

---

## 🎯 Goal

Create a `product_images` table to store multiple images per product.  
One image is marked as the **primary** (thumbnail) image.

---

## 📄 File to Create

**File:** `database/migrations/2026_06_21_000008_create_product_images_table.php`

---

## 🗃️ `product_images` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `product_id` | foreignId → products | no | |
| `path` | string | no | Storage path |
| `alt_text` | string | yes | Accessibility / SEO |
| `is_primary` | boolean | no | Default: false. One image per product should be true |
| `sort_order` | unsignedSmallInteger | no | Default: 0 |
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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_primary']);
            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
```

---

## 📦 Storage Configuration

Images will be stored using Laravel's storage system.

In `config/filesystems.php`, the `public` disk is already configured.  
No changes needed here — Filament's file upload handles this automatically.

Storage path convention:
```
storage/app/public/products/{product_id}/{filename}.webp
storage/app/public/vendors/{vendor_id}/logo.webp
storage/app/public/vendors/{vendor_id}/banner.webp
storage/app/public/vendors/{vendor_id}/documents/{filename}.pdf
```

---

## ▶️ Artisan Command

```bash
php artisan migrate
php artisan storage:link   # if not already done
```

---

## ✅ Verification

```bash
php artisan tinker
# Schema::getColumnListing('product_images')
```

---

## 📝 Notes

- Maximum **8 images per product** enforced at the application layer (in the Filament form validation), not the database.
- When a vendor uploads images in the Vendor Panel (Step 34), images are stored in the `public` disk.
- The first image uploaded auto-sets `is_primary = true`. Vendor can change which is primary.
- Filament's `SpatieMediaLibraryFileUpload` is an alternative, but for simplicity we use a plain `product_images` table and Filament's native `FileUpload`.
