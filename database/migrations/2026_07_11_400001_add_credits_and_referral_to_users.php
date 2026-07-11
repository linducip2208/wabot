<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('credits_balance')->default(0)->after('password');
            $table->string('referral_code', 20)->nullable()->unique()->after('credits_balance');
            $table->foreignId('referred_by_user_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_user_id']);
            $table->dropColumn(['credits_balance', 'referral_code', 'referred_by_user_id']);
        });
    }
};
