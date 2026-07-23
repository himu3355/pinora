<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            // Product snapshot (preserved even if product is deleted)
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('metal_type')->nullable();
            $table->string('purity', 10)->nullable();
            $table->decimal('weight_grams', 8, 3)->nullable();
            $table->decimal('metal_rate_used', 10, 2)->nullable();
            $table->decimal('making_charges', 10, 2)->nullable();

            // Quantity & pricing
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);

            // GST breakdown
            $table->decimal('cgst_rate', 5, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(0);
            $table->decimal('igst_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2);

            // Fulfillment
            $table->enum('fulfillment_status', [
                'pending', 'accepted', 'rejected', 'processing',
                'shipped', 'delivered', 'returned'
            ])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('courier_name')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->text('customization_request')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'vendor_id']);
            $table->index(['vendor_id', 'fulfillment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
