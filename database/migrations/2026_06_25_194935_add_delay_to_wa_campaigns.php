<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->integer('delay_seconds')->default(3)->after('message');
            $table->string('media_url')->nullable()->after('delay_seconds');
            $table->string('message_type')->default('text')->after('media_url');
        });
    }

    public function down(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->dropColumn(['delay_seconds', 'media_url', 'message_type']);
        });
    }
};
