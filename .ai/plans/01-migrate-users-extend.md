# Step 01 — Extend Users Table

**Phase:** Database Foundation  
**Depends on:** Nothing (first migration)  
**Next step:** `02-migrate-vendors.md`

---

## 🎯 Goal

The existing `users` table only has `name`, `email`, `password`, `remember_token`.  
We need to add customer-specific fields for the jewellery marketplace.

The `users` table is the **single source of truth** for all humans on the platform:  
admins, vendors, vendor staff, and customers.

---

## 📄 Files Created

1. **File:** `database/migrations/2026_06_21_000001_add_customer_fields_to_users_table.php`
2. **File:** `database/migrations/2026_07_23_000001_add_soft_deletes_to_users_table.php` (adds `deleted_at` softDeletes column)

---

## 🗃️ Fields to Add

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `phone` | string(20) | yes | Mobile number |
| `phone_verified_at` | timestamp | yes | OTP verification (future) |
| `avatar` | string | yes | Profile picture path |
| `birthday` | date | yes | For birthday offers |
| `anniversary_date` | date | yes | For anniversary offers |
| `gender` | enum('male','female','other') | yes | Product recommendations |
| `ring_size` | string(10) | yes | e.g. "16", "17.5" |
| `bangle_size` | string(10) | yes | e.g. "2.4", "2.6" |
| `customer_tag` | enum('regular','vip','wholesale','blacklisted') | yes | Default: 'regular' |
| `google_id` | string | yes | For Google OAuth login |
| `status` | enum('active','suspended') | no | Default: 'active' |

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('phone_verified_at');
            $table->date('birthday')->nullable()->after('avatar');
            $table->date('anniversary_date')->nullable()->after('birthday');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('anniversary_date');
            $table->string('ring_size', 10)->nullable()->after('gender');
            $table->string('bangle_size', 10)->nullable()->after('ring_size');
            $table->enum('customer_tag', ['regular', 'vip', 'wholesale', 'blacklisted'])
                  ->default('regular')->after('bangle_size');
            $table->string('google_id')->nullable()->unique()->after('customer_tag');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'phone_verified_at', 'avatar', 'birthday',
                'anniversary_date', 'gender', 'ring_size', 'bangle_size',
                'customer_tag', 'google_id', 'status',
            ]);
        });
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

After running, confirm with:

```bash
php artisan tinker
# Schema::getColumnListing('users')
```

Expected output includes: `phone`, `avatar`, `birthday`, `anniversary_date`, `gender`, `ring_size`, `bangle_size`, `customer_tag`, `google_id`, `status`

---

## 📝 Notes

- `customer_tag` is admin-managed only. Default `regular`.
- `google_id` is for Google OAuth (Step 40).
- `ring_size` / `bangle_size` are jewellery-specific fields unique to this platform.
- Do NOT add vendor-specific fields to the users table — those go in the `vendors` table (Step 02).
