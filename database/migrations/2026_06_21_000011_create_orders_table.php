<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped',
                'delivered', 'cancelled', 'refund_requested', 'refunded'
            ])->default('pending');

            // Shipping address snapshot
            $table->string('shipping_name');
            $table->string('shipping_phone', 20);
            $table->string('shipping_address_line_1');
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_landmark')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_pincode', 10);
            $table->string('shipping_country')->default('India');

            // Financials
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            // Payment
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
