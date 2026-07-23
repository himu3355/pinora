# Step 07 — Customer Addresses Table

**Phase:** Database Foundation  
**Depends on:** Step 01 (users table extended)  
**Next step:** `08-migrate-wishlists.md`

---

## 🎯 Goal

Create an `addresses` table to store multiple shipping/billing addresses per customer.  
A customer can save multiple addresses and mark one as default.

---

## 📄 File to Create

**File:** `database/migrations/2026_06_21_000009_create_addresses_table.php`

---

## 🗃️ `addresses` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `user_id` | foreignId → users | no | Owner |
| `type` | enum('shipping','billing','both') | no | Default: 'both' |
| `label` | string | yes | e.g. "Home", "Office", "Mom's place" |
| `full_name` | string | no | Recipient name |
| `phone` | string(20) | no | Contact at this address |
| `address_line_1` | string | no | Flat/House/Building |
| `address_line_2` | string | yes | Street/Area/Colony |
| `landmark` | string | yes | |
| `city` | string | no | |
| `state` | string | no | Indian state |
| `pincode` | string(10) | no | |
| `country` | string | no | Default: 'India' |
| `is_default` | boolean | no | Default: false |
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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['shipping', 'billing', 'both'])->default('both');
            $table->string('label')->nullable(); // "Home", "Office"
            $table->string('full_name');
            $table->string('phone', 20);
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10);
            $table->string('country')->default('India');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
```

---

## 🔒 Business Rules (enforced at Model/Service layer)

1. **Only one default address per user** — when `is_default = true` is set on a new address, the model's `saving` observer unsets `is_default` on all other addresses for that user.

2. **Maximum 10 addresses per user** — enforced in the controller/form validation.

3. **Snapshot on order** — when an order is placed, the address data is **copied** into the `orders` table (shipping_address JSON column). This ensures address changes don't affect historical orders.

---

## ▶️ Artisan Command

```bash
php artisan migrate
```

---

## ✅ Verification

```bash
php artisan tinker
# Schema::getColumnListing('addresses')
```

---

## 📝 Notes

- Indian states list will be a constant array in a `IndianStates` enum/helper class.
- GST calculations use the state to determine CGST+SGST (same state) vs IGST (different state) — see Step 53.
- The `label` field gives addresses a friendly name displayed in checkout: "Deliver to: Home, Mumbai".
