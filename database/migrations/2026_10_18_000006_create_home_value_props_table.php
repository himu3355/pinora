<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_value_props', function (Blueprint $table) {
            $table->id();
            $table->string('title');            // e.g. "22K Gold"
            $table->string('subtitle')->nullable(); // e.g. "શુદ્ધ સોનાની ખાતરી"
            $table->string('icon')->default('sparkles'); // e.g. sparkles, badge, truck, arrows, certificate, gift
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_value_props');
    }
};
