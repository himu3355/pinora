<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text')->nullable(); // e.g. "RAJKOT'S"
            $table->string('title')->nullable();      // e.g. "NOSE PIN SPECIALIST"
            $table->string('subtitle')->nullable();   // e.g. "Elegant 22K Gold Nose Pins for Every Occasion."
            $table->string('button_text')->nullable(); // e.g. "SHOP NOSE PINS"
            $table->string('button_url')->nullable();  // e.g. "/shop"
            $table->string('desktop_image')->nullable();
            $table->string('mobile_image')->nullable();
            $table->string('badge_pill_text')->nullable(); // e.g. "22K BIS Hallmarked"
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sliders');
    }
};
