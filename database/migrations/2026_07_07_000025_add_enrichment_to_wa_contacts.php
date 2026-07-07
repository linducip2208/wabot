<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_contacts', function (Blueprint $table) {
            $table->string('city')->nullable()->after('tags');
            $table->string('gender')->nullable()->after('city');
            $table->string('source')->nullable()->after('gender'); // manual, import, enrichment
            $table->timestamp('enriched_at')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('wa_contacts', function (Blueprint $table) {
            $table->dropColumn(['city', 'gender', 'source', 'enriched_at']);
        });
    }
};
