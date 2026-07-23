# Step 04 — Metal Rates Table

**Phase:** Database Foundation  
**Depends on:** Step 03  
**Next step:** `05-migrate-products.md`

---

## 🎯 Goal

Create a `metal_rates` table to store daily gold, silver, and platinum rates  
manually entered by the admin.

This powers the **dynamic pricing formula** used throughout the platform:

```
Final Price = (Weight in grams × Metal Rate per gram) + Making Charges + GST
```

---

## 📄 File to Create

**File:** `database/migrations/2026_06_21_000005_create_metal_rates_table.php`

---

## 🗃️ `metal_rates` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `metal_type` | enum('gold','silver','platinum') | no | Which metal |
| `purity` | string(10) | no | e.g. '24K', '22K', '18K', '999', '925', '950' |
| `rate_per_gram` | decimal(10,2) | no | Rate in INR per gram |
| `effective_date` | date | no | The date this rate applies to |
| `updated_by` | foreignId → users | yes | Admin who entered the rate |
| `notes` | string | yes | Optional admin note |
| `timestamps` | | | |

**Unique constraint:** (`metal_type`, `purity`, `effective_date`) — one rate per metal/purity/day.

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
        Schema::create('metal_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('metal_type', ['gold', 'silver', 'platinum']);
            $table->string('purity', 10); // '24K', '22K', '18K', '999', '925', '950'
            $table->decimal('rate_per_gram', 10, 2);
            $table->date('effective_date');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            // One rate per metal type + purity per day
            $table->unique(['metal_type', 'purity', 'effective_date'], 'metal_rate_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_rates');
    }
};
```

---

## 🌱 Seeder — Seed today's rates as defaults

Create `database/seeders/MetalRateSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\MetalRate;
use Illuminate\Database\Seeder;

class MetalRateSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();

        $rates = [
            // Gold
            ['metal_type' => 'gold', 'purity' => '24K', 'rate_per_gram' => 9500.00],
            ['metal_type' => 'gold', 'purity' => '22K', 'rate_per_gram' => 8700.00],
            ['metal_type' => 'gold', 'purity' => '18K', 'rate_per_gram' => 7100.00],
            // Silver
            ['metal_type' => 'silver', 'purity' => '999', 'rate_per_gram' => 108.00],
            ['metal_type' => 'silver', 'purity' => '925', 'rate_per_gram' => 100.00],
            // Platinum
            ['metal_type' => 'platinum', 'purity' => '950', 'rate_per_gram' => 3200.00],
        ];

        foreach ($rates as $rate) {
            MetalRate::updateOrCreate(
                [
                    'metal_type'     => $rate['metal_type'],
                    'purity'         => $rate['purity'],
                    'effective_date' => $today,
                ],
                ['rate_per_gram' => $rate['rate_per_gram']]
            );
        }
    }
}
```

Add to `DatabaseSeeder.php`:
```php
$this->call(MetalRateSeeder::class);
```

---

## 📊 How Pricing Works (Preview)

```
Gold 22K Ring:
  Weight      = 8.5 grams
  Rate/gram   = ₹8,700  (today's 22K rate)
  Metal Cost  = 8.5 × 8700 = ₹73,950
  Making Chg  = ₹2,000  (fixed by vendor)
  Subtotal    = ₹75,950
  GST 3%      = ₹2,278.50
  ─────────────────────────
  Final Price = ₹78,228.50
```

> Note: Jewellery GST in India is **3%** flat (see Step 53 — GstService).

---

## ▶️ Artisan Commands

```bash
php artisan migrate
php artisan db:seed --class=MetalRateSeeder
```

---

## ✅ Verification

```bash
php artisan tinker
# App\Models\MetalRate::count()    // Should be 6
# App\Models\MetalRate::where('metal_type', 'gold')->get(['purity', 'rate_per_gram'])
```

---

## 📝 Notes

- Admin updates rates daily from the Admin Panel (Step 26).
- **Price snapshots** are stored on `order_items` at time of purchase — rates changing later won't affect past orders.
- No live API integration in V1. This is intentional (Manual Rate Update — Option A).
- The `PricingService` (Step 51) will fetch the **most recent rate** for a given metal/purity using:
  ```php
  MetalRate::where('metal_type', $metal)
            ->where('purity', $purity)
            ->where('effective_date', '<=', today())
            ->latest('effective_date')
            ->first();
  ```
