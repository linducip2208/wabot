<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->integer('delay_min_seconds')->default(300)->after('delay_seconds');
            $table->integer('delay_max_seconds')->default(400)->after('delay_min_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->dropColumn(['delay_min_seconds', 'delay_max_seconds']);
        });
    }
};
