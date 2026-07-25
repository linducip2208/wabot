<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->json('group_ids')->nullable()->after('recipient_ids');
            $table->json('session_ids')->nullable()->after('session_id');
            $table->string('session_strategy', 20)->default('round_robin')->after('session_ids');
        });
    }

    public function down(): void
    {
        Schema::table('wa_campaigns', function (Blueprint $table) {
            $table->dropColumn(['group_ids', 'session_ids', 'session_strategy']);
        });
    }
};
