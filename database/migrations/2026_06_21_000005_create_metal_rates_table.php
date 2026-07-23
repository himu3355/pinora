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
