<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_sentiment_logs', function (Blueprint $table) {
            $table->string('channel')->nullable()->after('contact_id');
        });
    }

    public function down(): void
    {
        Schema::table('wa_sentiment_logs', function (Blueprint $table) {
            $table->dropColumn('channel');
        });
    }
};
