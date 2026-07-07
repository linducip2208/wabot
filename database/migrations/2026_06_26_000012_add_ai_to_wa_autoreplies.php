<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_autoreplies', function (Blueprint $table) {
            $table->foreignId('ai_key_id')->nullable()->constrained('wa_ai_keys')->nullOnDelete();
            $table->boolean('use_ai')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('wa_autoreplies', function (Blueprint $table) {
            $table->dropForeign(['ai_key_id']);
            $table->dropColumn(['ai_key_id', 'use_ai']);
        });
    }
};
