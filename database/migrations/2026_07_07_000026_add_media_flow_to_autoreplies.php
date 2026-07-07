<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_autoreplies', function (Blueprint $table) {
            $table->foreignId('media_template_id')->nullable()->after('ai_key_id')->constrained('wa_media_templates')->nullOnDelete();
            $table->foreignId('flow_id')->nullable()->after('media_template_id')->constrained('wa_flows')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wa_autoreplies', function (Blueprint $table) {
            $table->dropForeign(['media_template_id']);
            $table->dropForeign(['flow_id']);
            $table->dropColumn(['media_template_id', 'flow_id']);
        });
    }
};
