<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_sessions', function (Blueprint $table) {
            $table->string('channel')->default('baileys')->after('server_id');
            $table->foreignId('meta_account_id')->nullable()->after('server_id')->constrained('wa_meta_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wa_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meta_account_id');
            $table->dropColumn('channel');
        });
    }
};
