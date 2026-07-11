<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('can_affiliate')->default(true)->after('can_use_kanban');
            $table->decimal('affiliate_commission_rate', 5, 2)->default(20.00)->after('can_affiliate');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['can_affiliate', 'affiliate_commission_rate']);
        });
    }
};
