<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_type', ['jewellery', 'coin_bar', 'mrp_fixed', 'mrp_linked'])
                  ->default('jewellery')
                  ->after('purity');
            $table->unsignedInteger('mrp_pieces')
                  ->default(1)
                  ->after('base_price')
                  ->comment('For mrp_linked: divide total cost by this many pieces to get per-piece MRP');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'mrp_pieces']);
        });
    }
};
