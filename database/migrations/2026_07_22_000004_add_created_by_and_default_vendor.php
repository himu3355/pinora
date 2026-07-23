<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('default_vendor_id')->nullable()->constrained('vendors')->nullOnDelete()->after('remember_token');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_vendor_id']);
            $table->dropColumn('default_vendor_id');
        });
    }
};
