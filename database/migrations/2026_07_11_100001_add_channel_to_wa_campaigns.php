<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->string('channel')->nullable()->after('session_id')->default('whatsapp');
            $table->foreignId('meta_account_id')->nullable()->after('channel')->constrained('wa_meta_accounts')->nullOnDelete();
            $table->foreignId('telegram_account_id')->nullable()->after('meta_account_id')->constrained('wa_telegram_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->dropForeign(['meta_account_id']);
            $table->dropForeign(['telegram_account_id']);
            $table->dropColumn(['channel', 'meta_account_id', 'telegram_account_id']);
        });
    }
};
