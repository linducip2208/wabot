<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('can_ai_studio')->default(false)->after('can_use_kanban');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('can_ai_studio');
        });
    }
};
