<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('phone_verified_at');
            $table->date('birthday')->nullable()->after('avatar');
            $table->date('anniversary_date')->nullable()->after('birthday');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('anniversary_date');
            $table->string('ring_size', 10)->nullable()->after('gender');
            $table->string('bangle_size', 10)->nullable()->after('ring_size');
            $table->enum('customer_tag', ['regular', 'vip', 'wholesale', 'blacklisted'])
                  ->default('regular')->after('bangle_size');
            $table->string('google_id')->nullable()->unique()->after('customer_tag');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'phone_verified_at', 'avatar', 'birthday',
                'anniversary_date', 'gender', 'ring_size', 'bangle_size',
                'customer_tag', 'google_id', 'status',
            ]);
        });
    }
};
