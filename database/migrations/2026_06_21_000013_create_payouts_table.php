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
