# Step 10 — Payouts Table (Commissions Removed)

**Phase:** Database Foundation  
**Depends on:** Steps 02 (vendors), 09 (order_items)  
**Next step:** `11-migrate-reviews.md`

---

## 🎯 Goal

Create `payouts` table for tracking settlements to vendors.  
*(Note: Commissions system and `commissions` table have been completely removed from the project. Vendors receive 100% of order totals without platform commission deduction).*

---

## 📄 Migration File

**File:** `database/migrations/2026_06_21_000013_create_payouts_table.php`

---

## 🗃️ `payouts` Table Schema

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigIncrements | no | PK |
| `vendor_id` | foreignId → vendors | no | |
| `payout_reference` | string, unique | no | e.g. "PAY-2026-00001" |
| `period_from` | date | no | Settlement period start |
| `period_to` | date | no | Settlement period end |
| `total_orders_amount` | decimal(12,2) | no | Sum of order item totals |
| `total_vendor_earnings` | decimal(12,2) | no | 100% of order totals |
| `adjustments` | decimal(10,2) | no | Default: 0 (manual credits/debits) |
| `final_payout_amount` | decimal(12,2) | no | total_vendor_earnings + adjustments |
| `bank_account_name` | string | yes | Snapshot of bank details |
| `bank_account_number` | string | yes | Snapshot |
| `bank_ifsc_code` | string(20) | yes | Snapshot |
| `bank_name` | string | yes | Snapshot |
| `status` | enum('draft','processed','paid','failed') | no | Default: 'draft' |
| `paid_at` | timestamp | yes | When admin marks as paid |
| `payment_reference` | string | yes | Bank transfer ref/UTR number |
| `notes` | text | yes | Admin notes |
| `processed_by` | foreignId → users | yes | Admin who processed this |
| `timestamps` | | | |

---

## 💻 Migration Code — payouts

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->string('payout_reference')->unique();
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('total_orders_amount', 12, 2);
            $table->decimal('total_vendor_earnings', 12, 2);
            $table->decimal('adjustments', 10, 2)->default(0);
            $table->decimal('final_payout_amount', 12, 2);

            // Bank details snapshot (at time of payout)
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc_code', 20)->nullable();
            $table->string('bank_name')->nullable();

            $table->enum('status', ['draft', 'processed', 'paid', 'failed'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
```
 the system).
- Commissions are created **only when payment is confirmed** — not on `pending` orders.
