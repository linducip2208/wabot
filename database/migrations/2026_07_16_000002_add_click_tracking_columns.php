<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('total_clicks')->default(0)->after('failed_count');
        });

        Schema::table('wa_click_events', function (Blueprint $table) {
            $table->timestamp('clicked_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->dropColumn('total_clicks');
        });

        Schema::table('wa_click_events', function (Blueprint $table) {
            $table->timestamp('clicked_at')->nullable(false)->change();
        });
    }
};
