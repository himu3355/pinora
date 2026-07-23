# Step 02 — Vendors & Vendor Documents Tables

**Phase:** Database Foundation  
**Depends on:** Step 01 (users table extended)  
**Next step:** `03-migrate-categories.md`

---

## 🎯 Goal

Create two tables:
1. `vendors` — Core vendor/store information linked to a user
2. `vendor_documents` — Uploaded business documents (GST, PAN, shop license)

A vendor is a **user with the `vendor` role** who also has a record in the `vendors` table.

---

## 📄 Files to Create

**File 1:** `database/migrations/2026_06_21_000002_create_vendors_table.php`  
**File 2:** `database/migrations/2026_06_21_000003_create_vendor_documents_table.php`

---

## 🗃️ `vendors` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `user_id` | foreignId → users | no | Owner of the vendor account |
| `store_name` | string | no | Display name of the store |
| `store_slug` | string, unique | no | URL-friendly slug (auto-generated) |
| `description` | text | yes | Store description |
| `logo` | string | yes | Logo image path |
| `banner` | string | yes | Banner image path |
| `phone` | string(20) | yes | Store contact phone |
| `email` | string | yes | Store contact email (can differ from user email) |
| `address` | text | yes | Physical store/workshop address |
| `city` | string | yes | |
| `state` | string | yes | |
| `pincode` | string(10) | yes | |
| `gst_number` | string(20) | yes | GST registration number |
| `pan_number` | string(15) | yes | PAN card number |
| `commission_rate` | decimal(5,2) | no | Default: 0.00 (platform's cut %) |
| `bank_account_name` | string | yes | For payouts |
| `bank_account_number` | string | yes | Encrypted |
| `bank_ifsc_code` | string(20) | yes | |
| `bank_name` | string | yes | |
| `status` | enum('pending','approved','suspended','rejected') | no | Default: 'pending' |
| `approved_at` | timestamp | yes | When admin approved |
| `rejection_reason` | text | yes | Why admin rejected |
| `total_sales` | decimal(12,2) | no | Cached total, default: 0 |
| `total_products` | unsignedInteger | no | Cached count, default: 0 |
| `timestamps` | | | created_at, updated_at |
| `softDeletes` | | | deleted_at |

---

## 🗃️ `vendor_documents` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `vendor_id` | foreignId → vendors | no | |
| `type` | enum('gst_certificate','pan_card','shop_license','identity_proof','other') | no | |
| `file_path` | string | no | Storage path |
| `original_name` | string | yes | Original filename |
| `status` | enum('pending','verified','rejected') | no | Default: 'pending' |
| `verified_at` | timestamp | yes | When admin verified |
| `notes` | text | yes | Admin notes |
| `timestamps` | | | |

---

## 💻 Migration Code — vendors table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('store_slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->string('pan_number', 15)->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc_code', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->unsignedInteger('total_products')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
```

---

## 💻 Migration Code — vendor_documents table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'gst_certificate', 'pan_card', 'shop_license',
                'identity_proof', 'other'
            ]);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
    }
};
```

---

## ▶️ Artisan Command

```bash
php artisan migrate
```

---

## ✅ Verification

```bash
php artisan tinker
# Schema::getColumnListing('vendors')
# Schema::getColumnListing('vendor_documents')
```

---

## 📝 Notes

- `bank_account_number` will be encrypted in the model using Laravel's `encrypted` cast.
- `store_slug` will be auto-generated from `store_name` in the Vendor model using a creating observer.
- `total_sales` and `total_products` are denormalized caches — updated by events on order placement and product creation. This avoids expensive COUNT/SUM queries on the listing pages.
- `softDeletes` on vendors — suspending a vendor does NOT delete their data.
- Admin sets `commission_rate` per vendor during approval (or can update later).
