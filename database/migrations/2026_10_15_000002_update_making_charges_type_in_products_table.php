<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `making_charges_type` ENUM('fixed', 'per_gram', 'percentage') NOT NULL DEFAULT 'percentage'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `making_charges_type` ENUM('fixed', 'per_gram') NOT NULL DEFAULT 'fixed'");
    }
};
