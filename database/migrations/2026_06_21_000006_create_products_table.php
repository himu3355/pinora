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
